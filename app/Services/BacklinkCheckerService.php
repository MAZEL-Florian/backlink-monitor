<?php

namespace App\Services;

use App\Models\Backlink;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class BacklinkCheckerService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => false,
            'allow_redirects' => true,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection' => 'keep-alive',
            ]
        ]);
    }

    public function check(Backlink $backlink): array
    {
        $startTime = microtime(true);
        
        Log::info("=== DÉBUT VÉRIFICATION BACKLINK {$backlink->id} ===");
        Log::info("URL Source: {$backlink->source_url}");
        Log::info("URL Cible: {$backlink->target_url}");
        
        try {
            // ÉTAPE 1: Vérifier si l'URL source est accessible
            Log::info("ÉTAPE 1: Vérification de l'accessibilité de l'URL source");
            $response = $this->client->get($backlink->source_url);
            $responseTime = round((microtime(true) - $startTime) * 1000);
            $statusCode = $response->getStatusCode();
            $content = $response->getBody()->getContents();

            Log::info("URL source accessible", [
                'status_code' => $statusCode,
                'content_length' => strlen($content),
                'response_time' => $responseTime . 'ms'
            ]);

            // ÉTAPE 2: Chercher l'URL cible dans le contenu
            Log::info("ÉTAPE 2: Recherche de l'URL cible dans le contenu");
            $linkFound = $this->findTargetLink($content, $backlink->target_url);

            $result = [
                'status_code' => $statusCode,
                'is_active' => $linkFound['found'],
                'is_dofollow' => $linkFound['is_dofollow'],
                'anchor_text' => $linkFound['anchor_text'],
                'response_time' => $responseTime,
                'exact_match' => $linkFound['exact_match'],
            ];

            Log::info("=== RÉSULTAT FINAL ===", $result);
            return $result;

        } catch (RequestException $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000);
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;

            Log::error("ERREUR: URL source inaccessible", [
                'error' => $e->getMessage(),
                'status_code' => $statusCode,
                'response_time' => $responseTime
            ]);

            return [
                'status_code' => $statusCode,
                'is_active' => false,
                'is_dofollow' => $backlink->is_dofollow,
                'anchor_text' => $backlink->anchor_text,
                'response_time' => $responseTime,
                'error_message' => $e->getMessage(),
                'exact_match' => false,
            ];
        }
    }

    private function findTargetLink(string $content, string $targetUrl): array
    {
        Log::info("Recherche de l'URL cible: {$targetUrl}");
        
        // Extraire le chemin de l'URL cible pour Wikipedia
        $targetPath = parse_url($targetUrl, PHP_URL_PATH);
        Log::info("Chemin de l'URL cible: {$targetPath}");

        // MÉTHODE 1: Recherche de tous les liens dans la page
        $allLinks = $this->extractAllLinks($content);
        Log::info("Total de liens trouvés: " . count($allLinks));

        // Afficher quelques liens pour debug
        $sampleLinks = array_slice($allLinks, 0, 10);
        foreach ($sampleLinks as $i => $link) {
            Log::info("Lien exemple {$i}: {$link['href']} -> '{$link['text']}'");
        }

        // MÉTHODE 2: Chercher des correspondances
        foreach ($allLinks as $link) {
            $href = $link['href'];
            $text = $link['text'];
            
            // Vérifications multiples
            if ($this->isUrlMatch($href, $targetUrl)) {
                Log::info("✓ CORRESPONDANCE TROUVÉE!", [
                    'href' => $href,
                    'text' => $text,
                    'target' => $targetUrl
                ]);
                
                return [
                    'found' => true,
                    'anchor_text' => $text,
                    'is_dofollow' => $link['is_dofollow'],
                    'exact_match' => $href === $targetUrl
                ];
            }
        }

        // MÉTHODE 3: Recherche par texte d'ancrage si fourni
        if (!empty($targetPath)) {
            foreach ($allLinks as $link) {
                $href = $link['href'];
                
                // Pour Wikipedia, chercher les liens relatifs
                if (strpos($href, $targetPath) !== false) {
                    Log::info("✓ CORRESPONDANCE PAR CHEMIN!", [
                        'href' => $href,
                        'text' => $link['text'],
                        'target_path' => $targetPath
                    ]);
                    
                    return [
                        'found' => true,
                        'anchor_text' => $link['text'],
                        'is_dofollow' => $link['is_dofollow'],
                        'exact_match' => false
                    ];
                }
            }
        }

        Log::info("✗ Aucune correspondance trouvée");
        return [
            'found' => false,
            'anchor_text' => null,
            'is_dofollow' => true,
            'exact_match' => false
        ];
    }

    private function extractAllLinks(string $content): array
    {
        $links = [];
        
        // Utiliser DOMDocument pour une extraction plus fiable
        try {
            $dom = new \DOMDocument();
            @$dom->loadHTML($content);
            $linkElements = $dom->getElementsByTagName('a');
            
            foreach ($linkElements as $element) {
                $href = $element->getAttribute('href');
                $text = trim($element->textContent);
                $rel = $element->getAttribute('rel');
                
                if (!empty($href)) {
                    $links[] = [
                        'href' => $href,
                        'text' => $text,
                        'is_dofollow' => !str_contains(strtolower($rel), 'nofollow')
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("Erreur DOM, utilisation de regex", ['error' => $e->getMessage()]);
            
            // Fallback avec regex
            preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $content, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $href = $match[1];
                $text = strip_tags($match[2]);
                $fullTag = $match[0];
                
                $links[] = [
                    'href' => $href,
                    'text' => trim($text),
                    'is_dofollow' => !preg_match('/rel=["\'][^"\']*nofollow[^"\']*["\']/', $fullTag)
                ];
            }
        }
        
        return $links;
    }

    private function isUrlMatch(string $href, string $targetUrl): bool
    {
        // Correspondance exacte
        if ($href === $targetUrl) {
            return true;
        }
        
        // Correspondance avec URL absolue vs relative
        $targetPath = parse_url($targetUrl, PHP_URL_PATH);
        if ($href === $targetPath) {
            return true;
        }
        
        // Correspondance avec domaine
        $targetHost = parse_url($targetUrl, PHP_URL_HOST);
        if (strpos($href, $targetHost) !== false && strpos($href, $targetPath) !== false) {
            return true;
        }
        
        // Correspondance sans protocole
        $hrefNormalized = preg_replace('/^https?:\/\//', '', $href);
        $targetNormalized = preg_replace('/^https?:\/\//', '', $targetUrl);
        if ($hrefNormalized === $targetNormalized) {
            return true;
        }
        
        // Correspondance avec variations (www, slash final, etc.)
        $variations = $this->getUrlVariations($targetUrl);
        foreach ($variations as $variation) {
            if ($href === $variation) {
                return true;
            }
            
            $variationPath = parse_url($variation, PHP_URL_PATH);
            if ($href === $variationPath) {
                return true;
            }
        }
        
        return false;
    }

    private function getUrlVariations(string $url): array
    {
        $variations = [];
        
        // Version avec et sans www
        if (strpos($url, '://www.') !== false) {
            $variations[] = str_replace('://www.', '://', $url);
        } else {
            $variations[] = str_replace('://', '://www.', $url);
        }
        
        // Version http/https
        if (strpos($url, 'https://') === 0) {
            $variations[] = str_replace('https://', 'http://', $url);
        } else {
            $variations[] = str_replace('http://', 'https://', $url);
        }
        
        // Version sans slash final
        $variations[] = rtrim($url, '/');
        
        // Version avec slash final
        if (!str_ends_with($url, '/')) {
            $variations[] = $url . '/';
        }
        
        // Supprimer les doublons
        return array_unique($variations);
    }
}
