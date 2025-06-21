<?php

namespace App\Services;

use App\Models\Backlink;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class BacklinkCheckerService
{
    private Client $client;
    private string $userAgent;

    public function __construct()
    {
        $this->userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => false,
            'allow_redirects' => [
                'max' => 5,
                'track_redirects' => true
            ],
            'headers' => [
                'User-Agent' => $this->userAgent,
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
        Log::info("Domaine du projet: {$backlink->project->domain}");
        
        try {
            Log::info("ÉTAPE 1: Vérification de l'accessibilité de l'URL source");
            $response = $this->client->get($backlink->source_url);
            $responseTime = round((microtime(true) - $startTime) * 1000);
            $statusCode = $response->getStatusCode();
            $content = $response->getBody()->getContents();
            $contentType = $response->getHeader('Content-Type')[0] ?? null;
            $contentLength = strlen($content);

            $redirects = [];
            if ($response->hasHeader('X-Guzzle-Redirect-History')) {
                $redirects = $response->getHeader('X-Guzzle-Redirect-History');
            }

            Log::info("URL source accessible", [
                'status_code' => $statusCode,
                'content_length' => $contentLength,
                'content_type' => $contentType,
                'response_time' => $responseTime . 'ms',
                'redirects_count' => count($redirects)
            ]);

            Log::info("ÉTAPE 2: Recherche de liens vers le domaine du projet");
            $linkFound = $this->findProjectLinks($content, $backlink->project->domain);

            if ($linkFound['found'] && $linkFound['target_url']) {
                $backlink->update(['target_url' => $linkFound['target_url']]);
                Log::info("URL cible mise à jour: {$linkFound['target_url']}");
            }

            $result = [
                'status_code' => $statusCode,
                'is_active' => $linkFound['found'],
                'is_dofollow' => $linkFound['is_dofollow'],
                'anchor_text' => $linkFound['anchor_text'],
                'response_time' => $responseTime,
                'exact_match' => $linkFound['exact_match'],
                'target_url' => $linkFound['target_url'],
                'user_agent' => $this->userAgent,
                'redirects' => $redirects,
                'content_length' => $contentLength,
                'content_type' => $contentType,
                'raw_response' => config('app.debug') ? substr($content, 0, 1000) : null,
            ];

            Log::info("=== RÉSULTAT FINAL ===", array_except($result, ['raw_response']));
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
                'is_dofollow' => true,
                'anchor_text' => null,
                'response_time' => $responseTime,
                'error_message' => $e->getMessage(),
                'exact_match' => false,
                'target_url' => null,
                'user_agent' => $this->userAgent,
                'redirects' => [],
                'content_length' => null,
                'content_type' => null,
                'raw_response' => null,
            ];
        }
    }

    private function findProjectLinks(string $content, string $projectDomain): array
    {
        $projectHost = parse_url($projectDomain, PHP_URL_HOST);
        Log::info("Recherche de liens vers le domaine: {$projectHost}");
        
        $allLinks = $this->extractAllLinks($content);
        Log::info("Total de liens trouvés: " . count($allLinks));

        $projectLinks = [];
        foreach ($allLinks as $link) {
            $href = $link['href'];
            
            if ($this->isProjectLink($href, $projectHost, $projectDomain)) {
                $projectLinks[] = $link;
                Log::info("Lien vers le projet trouvé: {$href} -> '{$link['text']}'");
            }
        }

        if (!empty($projectLinks)) {
            $bestLink = $this->selectBestLink($projectLinks, $projectDomain);
            
            return [
                'found' => true,
                'anchor_text' => $bestLink['text'],
                'is_dofollow' => $bestLink['is_dofollow'],
                'exact_match' => $bestLink['href'] === $projectDomain,
                'target_url' => $this->normalizeUrl($bestLink['href'], $projectDomain),
            ];
        }

        Log::info("✗ Aucun lien vers le projet trouvé");
        return [
            'found' => false,
            'anchor_text' => null,
            'is_dofollow' => true,
            'exact_match' => false,
            'target_url' => null,
        ];
    }

    private function extractAllLinks(string $content): array
    {
        $links = [];
        
        try {
            $dom = new \DOMDocument();
            @$dom->loadHTML($content);
            $linkElements = $dom->getElementsByTagName('a');
            
            foreach ($linkElements as $element) {
                $href = $element->getAttribute('href');
                $rel = $element->getAttribute('rel');
                
                if (!empty($href)) {
                    $anchorText = $this->extractAnchorText($element);
                    
                    $links[] = [
                        'href' => $href,
                        'text' => $anchorText,
                        'is_dofollow' => !str_contains(strtolower($rel), 'nofollow')
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning("Erreur DOM, utilisation de regex", ['error' => $e->getMessage()]);
            
            $links = array_merge($links, $this->extractLinksWithRegex($content));
        }
        
        return $links;
    }

    private function extractAnchorText(\DOMElement $element): string
    {
        $images = $element->getElementsByTagName('img');
        
        if ($images->length > 0) {
            $img = $images->item(0);
            $alt = $img->getAttribute('alt');
            $src = $img->getAttribute('src');
            
            if (!empty($alt)) {
                return '[IMG] ' . trim($alt);
            } else {
                $filename = basename(parse_url($src, PHP_URL_PATH));
                return '[IMG] ' . $filename;
            }
        }
        
        $text = trim($element->textContent);
        
        if (empty($text)) {
            $title = $element->getAttribute('title');
            if (!empty($title)) {
                return '[TITLE] ' . $title;
            }
            
            return '[LIEN VIDE]';
        }
        
        return $text;
    }

    private function extractLinksWithRegex(string $content): array
    {
        $links = [];
        
        preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $href = $match[1];
            $linkContent = $match[2];
            $fullTag = $match[0];
            
            if (preg_match('/<img[^>]+alt=["\']([^"\']*)["\'][^>]*>/i', $linkContent, $imgMatch)) {
                $alt = $imgMatch[1];
                $text = !empty($alt) ? '[IMG] ' . $alt : '[IMG] Image sans alt';
            } elseif (preg_match('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $linkContent, $imgMatch)) {
                $src = $imgMatch[1];
                $filename = basename(parse_url($src, PHP_URL_PATH));
                $text = '[IMG] ' . $filename;
            } else {
                $text = trim(strip_tags($linkContent));
                if (empty($text)) {
                    $text = '[LIEN VIDE]';
                }
            }
            
            $links[] = [
                'href' => $href,
                'text' => $text,
                'is_dofollow' => !preg_match('/rel=["\'][^"\']*nofollow[^"\']*["\']/', $fullTag)
            ];
        }
        
        return $links;
    }

    private function isProjectLink(string $href, string $projectHost, string $projectDomain): bool
    {
        if (str_contains($href, $projectHost)) {
            return true;
        }
        
        if ($href === $projectDomain) {
            return true;
        }
        
        $variations = $this->getUrlVariations($projectDomain);
        foreach ($variations as $variation) {
            if ($href === $variation || str_contains($href, parse_url($variation, PHP_URL_HOST))) {
                return true;
            }
        }
        
        return false;
    }

    private function selectBestLink(array $projectLinks, string $projectDomain): array
    {
        foreach ($projectLinks as $link) {
            if ($link['href'] === $projectDomain) {
                return $link;
            }
        }
        
        foreach ($projectLinks as $link) {
            if ($link['is_dofollow']) {
                return $link;
            }
        }
        
        return $projectLinks[0];
    }

    private function normalizeUrl(string $href, string $projectDomain): string
    {
        if (str_starts_with($href, 'http')) {
            return $href;
        }
        
        $projectParsed = parse_url($projectDomain);
        $baseUrl = $projectParsed['scheme'] . '://' . $projectParsed['host'];
        
        if (str_starts_with($href, '/')) {
            return $baseUrl . $href;
        }
        
        return $baseUrl . '/' . $href;
    }

    private function getUrlVariations(string $url): array
    {
        $variations = [];
        
        if (strpos($url, '://www.') !== false) {
            $variations[] = str_replace('://www.', '://', $url);
        } else {
            $variations[] = str_replace('://', '://www.', $url);
        }
        
        if (strpos($url, 'https://') === 0) {
            $variations[] = str_replace('https://', 'http://', $url);
        } else {
            $variations[] = str_replace('http://', 'https://', $url);
        }
        
        $variations[] = rtrim($url, '/');
        
        if (!str_ends_with($url, '/')) {
            $variations[] = $url . '/';
        }
        
        return array_unique($variations);
    }
}
