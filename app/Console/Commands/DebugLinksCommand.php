<?php

namespace App\Console\Commands;

use App\Services\BacklinkCheckerService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class DebugLinksCommand extends Command
{
    protected $signature = 'backlink:debug-links {url : URL à analyser} {--target= : URL cible à chercher}';
    protected $description = 'Débugger les liens d\'une page web';

    public function handle()
    {
        $url = $this->argument('url');
        $target = $this->option('target');
        
        $this->info("Analyse des liens de: {$url}");
        if ($target) {
            $this->info("Recherche de: {$target}");
        }
        $this->line('');

        try {
            $client = new Client([
                'timeout' => 30,
                'verify' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ]
            ]);
            
            $response = $client->get($url);
            $content = $response->getBody()->getContents();
            
            $this->info("Page récupérée (HTTP {$response->getStatusCode()})");
            $this->info("Taille du contenu: " . strlen($content) . " caractères");
            $this->line('');
            
            // Extraire tous les liens
            $links = $this->extractLinks($content);
            $this->info("Total de liens trouvés: " . count($links));
            $this->line('');
            
            if ($target) {
                $this->info("Recherche de correspondances avec: {$target}");
                $targetPath = parse_url($target, PHP_URL_PATH);
                $this->info("Chemin cible: {$targetPath}");
                $this->line('');
                
                $matches = [];
                foreach ($links as $link) {
                    if ($this->isMatch($link['href'], $target, $targetPath)) {
                        $matches[] = $link;
                    }
                }
                
                if (!empty($matches)) {
                    $this->info("✅ Correspondances trouvées:");
                    foreach ($matches as $match) {
                        $this->line("  • {$match['href']} -> \"{$match['text']}\"");
                    }
                } else {
                    $this->error("❌ Aucune correspondance trouvée");
                    $this->line('');
                    $this->info("Liens similaires (même domaine):");
                    $targetHost = parse_url($target, PHP_URL_HOST);
                    foreach ($links as $link) {
                        if (strpos($link['href'], $targetHost) !== false || strpos($link['href'], '/wiki/') !== false) {
                            $this->line("  • {$link['href']} -> \"{$link['text']}\"");
                        }
                    }
                }
            } else {
                // Afficher tous les liens
                foreach (array_slice($links, 0, 20) as $i => $link) {
                    $this->line(($i + 1) . ". {$link['href']} -> \"{$link['text']}\"");
                }
                
                if (count($links) > 20) {
                    $this->info("... et " . (count($links) - 20) . " autres liens");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function extractLinks(string $content): array
    {
        $links = [];
        
        try {
            $dom = new \DOMDocument();
            @$dom->loadHTML($content);
            $linkElements = $dom->getElementsByTagName('a');
            
            foreach ($linkElements as $element) {
                $href = $element->getAttribute('href');
                $text = trim($element->textContent);
                
                if (!empty($href)) {
                    $links[] = [
                        'href' => $href,
                        'text' => $text
                    ];
                }
            }
        } catch (\Exception $e) {
            // Fallback regex
            preg_match_all('/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $content, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $links[] = [
                    'href' => $match[1],
                    'text' => trim(strip_tags($match[2]))
                ];
            }
        }
        
        return $links;
    }

    private function isMatch(string $href, string $target, string $targetPath): bool
    {
        return $href === $target || 
               $href === $targetPath || 
               strpos($href, $targetPath) !== false ||
               strpos($target, $href) !== false;
    }
}
