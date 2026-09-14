<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\GarmentTarget;
use App\Models\GarmentItem;
use App\Models\ServicePrice;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class CheckoutController extends Controller
{
    /**
     * Display the tactile checkout main screen.
     */
    public function index(Request $request)
    {
        $services = Service::all();
        $targets = GarmentTarget::all();
        
        $editingOrder = null;
        if ($request->has('order_id')) {
            $editingOrder = \App\Models\Order::with(['client', 'orderItems.service', 'orderItems.garmentItem'])->find($request->input('order_id'));
            
            // Only allow admin to edit orders, and only pending orders
            if ($editingOrder) {
                if (auth()->user()->role !== 'admin') {
                    abort(403, 'Seul un administrateur peut modifier une commande.');
                }
                if ($editingOrder->status !== 'pending') {
                    abort(422, 'Seules les commandes en cours peuvent être modifiées.');
                }
            }
        }
        
        // Load items with their pricing
        $items = GarmentItem::with('servicePrices')->get();

        // Retrieve choice dictionaries (with fallbacks)
        $colors = $this->getDictionary('Couleur.db', [
            'argent', 'azur', 'beige', 'blanc', 'blanc cassé', 'bleu', 'bleu ciel', 
            'bleu marine', 'bleu turquoise', 'blond', 'blond vénitien', 'bordeaux', 'brun', 'châtain', 'écru', 'fauve', 
            'fushia', 'grenat', 'gris', 'indigo', 'ivoire', 'jaune', 'kaki', 'marron', 'mauve', 'noir', 'or', 
            'orange', 'rose', 'rouge', 'saumon', 'sépia', 'vert', 'vert eau', 'vert émeraude', 'vert olive', 'vert pistache', 'violet'
        ]);

        $defects = $this->getDictionary('Defauts.db', [
            'Bouton Brisé', 'Bouton Manquant', 'Bulle', 'Col Déchiré', 'Déchiré', 
            'Délavé', 'Manchette Déchirée', 'Marque de Repassage', 'Repassage Service', 
            'Tissu Boulochage', 'Trou'
        ]);

        $stains = $this->getDictionary('Taches.db', [
            'Aliments', 'Alcool', 'Biro', 'Boue', 'Café', 'Couleur Purge', 
            'Collier Souillée', 'Eau de Javel', 'EncreGraisse', 'Maquillage', 
            'Moisissure', 'Parfum', 'Peinture', 'Pétrole', 'Rouille', 'Col souillé', 
            'Sang', 'Transpiration', 'Vin'
        ]);

        $patterns = $this->getPatterns([
            'A carreaux', 'A rayures', 'Bi color', 'Florale', 'Moucheté', 'Pied de poule'
        ]);

        // Get the latest ticket number to display/suggest next ticket
        $lastOrder = \App\Models\Order::orderBy('id', 'desc')->first();
        $nextTicketNumber = $lastOrder ? str_pad(intval($lastOrder->ticket_number) + 1, 6, '0', STR_PAD_LEFT) : '000001';

        $guestClient = \App\Models\Client::firstOrCreate(
            ['code' => 'GUEST'],
            [
                'name' => 'Client Passage',
                'discount_percent' => 0,
                'credit' => 0.00
            ]
        );

        return view('checkout.index', compact(
            'services',
            'targets',
            'items',
            'colors',
            'patterns',
            'defects',
            'stains',
            'nextTicketNumber',
            'guestClient',
            'editingOrder'
        ));
    }

    /**
     * Helper to read choice dictionary files from the source MSK folders or fallback to hardcoded list (cached)
     */
    private function getDictionary($filename, $fallback)
    {
        return Cache::remember("dict_{$filename}", 3600, function () use ($filename, $fallback) {
            $sourcePath = storage_path('app/db/' . $filename);

            if (File::exists($sourcePath)) {
                try {
                    $content = mb_convert_encoding(File::get($sourcePath), 'UTF-8', 'Windows-1252');
                    $lines = explode("\n", str_replace("\r\n", "\n", $content));
                    $items = array_filter(array_map('trim', $lines));
                    if (count($items) > 0) {
                        return array_values($items);
                    }
                } catch (\Exception $e) {
                    // Ignore exception and use fallback
                }
            }
            return $fallback;
        });
    }

    /**
     * Helper to read item patterns from old MSK folder Menu/0/2 (cached)
     */
    private function getPatterns($fallback)
    {
        return Cache::remember('patterns_list', 3600, function () use ($fallback) {
            $sourcePath = storage_path('app/Menu/0/2');

            if (File::isDirectory($sourcePath)) {
                try {
                    $files = File::files($sourcePath);
                    $patterns = [];
                    foreach ($files as $file) {
                        if ($file->getExtension() === 'txt') {
                            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                            if (str_starts_with($filename, '0-')) {
                                // Clean leading 0- and spaces
                                $clean = preg_replace('/^0-\s*/', '', $filename);
                                // Correct spelling of A reure to A rayures
                                if (strtolower($clean) === 'a reure') {
                                    $clean = 'A rayures';
                                } elseif (strtolower($clean) === 'mouchte') {
                                    $clean = 'Moucheté';
                                }
                                $patterns[] = $clean;
                            }
                        }
                    }
                    if (count($patterns) > 0) {
                        return array_values(array_unique($patterns));
                    }
                } catch (\Exception $e) {
                    // Ignore and use fallback
                }
            }
            return $fallback;
        });
    }

    /**
     * Serve option images dynamically (colors, patterns, defects, stains).
     */
    public function getOptionImage($type, $name)
    {
        $name = urldecode($name);
        $searchName = strtolower(trim($name));

        // 1. Check OneDrive or Fallback local Menu folder for colors/patterns
        $menuPath = 'c:/Users/hadib/OneDrive/Bureau/MSK-DRY-PLUS-2022/Menu/0/2';
        if (!File::isDirectory($menuPath)) {
            $menuPath = storage_path('app/Menu/0/2');
        }

        if (File::isDirectory($menuPath)) {
            // Apply spell mapping to match file names
            $mappedName = $searchName;
            if ($type === 'patterns') {
                if ($searchName === 'a rayures') $mappedName = 'a reure';
                elseif ($searchName === 'moucheté') $mappedName = 'mouchte';
            } elseif ($type === 'colors') {
                if ($searchName === 'bleu ciel') $mappedName = 'b.cile';
                elseif ($searchName === 'bleu marine') $mappedName = 'bleu nuit';
                elseif ($searchName === 'marron') $mappedName = 'maron';
                elseif ($searchName === 'orange') $mappedName = 'oronge';
                elseif ($searchName === 'vert') $mappedName = 'ver';
            }

            // Find matching jpg/jpeg/png using cached file mapping
            $filesInfo = Cache::remember('menu_files_list', 3600, function () use ($menuPath) {
                if (!File::isDirectory($menuPath)) {
                    return [];
                }
                try {
                    $files = File::files($menuPath);
                    $list = [];
                    foreach ($files as $file) {
                        $ext = strtolower($file->getExtension());
                        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                            $cleanFile = strtolower(trim($filename));
                            
                            $list[$cleanFile] = [
                                'path' => $file->getPathname(),
                                'ext' => $ext
                            ];
                            
                            $cleanPattern = preg_replace('/^0-\s*/', '', $cleanFile);
                            $list[$cleanPattern] = [
                                'path' => $file->getPathname(),
                                'ext' => $ext
                            ];
                        }
                    }
                    return $list;
                } catch (\Exception $e) {
                    return [];
                }
            });

            if (isset($filesInfo[$mappedName])) {
                $info = $filesInfo[$mappedName];
                if (File::exists($info['path'])) {
                    $fileContent = File::get($info['path']);
                    return response($fileContent, 200)->header("Content-Type", "image/" . ($info['ext'] === 'png' ? 'png' : 'jpeg'));
                }
            }
        }

        // 2. Check public directory custom uploaded option images (if any)
        $slug = preg_replace('/[^a-z0-9]/', '_', strtolower($name));
        $customPath = public_path("images/options/{$type}/" . $slug . ".jpg");
        if (File::exists($customPath)) {
            $fileContent = File::get($customPath);
            return response($fileContent, 200)->header("Content-Type", "image/jpeg");
        }

        // 3. Fallback: Generate custom premium SVG in real-time!
        $svg = $this->generateSvgPlaceholder($type, $name);
        return response($svg, 200)->header("Content-Type", "image/svg+xml");
    }

    private function generateSvgPlaceholder($type, $name)
    {
        $bg = '#0f172a'; // slate-900 dark bg
        
        $svgContent = '';
        
        if ($type === 'colors') {
            // Map colors
            $colorMap = [
                'argent' => '#c0c0c0', 'azur' => '#f0ffff', 'beige' => '#f5f5dc', 'blanc' => '#ffffff',
                'blanc cassé' => '#f5f5f0', 'bleu' => '#1e3a8a', 'bleu ciel' => '#38bdf8', 'bleu marine' => '#1e1b4b',
                'bleu turquoise' => '#06b6d4', 'bordeaux' => '#7f1d1d', 'brun' => '#78350f', 'écru' => '#f5f5e9',
                'fauve' => '#b45309', 'grenat' => '#991b1b', 'gris' => '#6b7280', 'ivoire' => '#fffff0',
                'jaune' => '#eab308', 'kaki' => '#606730', 'marron' => '#451a03', 'mauve' => '#c084fc',
                'or' => '#eab308', 'orange' => '#f97316', 'rose' => '#ec4899', 'rouge' => '#ef4444',
                'saumon' => '#fca5a5', 'sépia' => '#78350f', 'vert' => '#22c55e', 'vert émeraude' => '#10b981',
                'vert eau' => '#a7f3d0', 'vert pistache' => '#bef264', 'vert olive' => '#65a30d',
                'violet' => '#8b5cf6', 'noir' => '#000000', 'fushia' => '#d946ef'
            ];
            $search = strtolower(trim($name));
            $hex = $colorMap[$search] ?? '#475569';
            
            $svgContent = "<circle cx='50' cy='50' r='32' fill='{$hex}' stroke='#334155' stroke-width='2'/>";
        } elseif ($type === 'patterns') {
            // Dynamic SVG patterns
            $search = strtolower(trim($name));
            if ($search === 'a carreaux') {
                $svgContent = "<rect width='100' height='100' fill='none'/>
                              <path d='M 0,20 L 100,20 M 0,40 L 100,40 M 0,60 L 100,60 M 0,80 L 100,80 M 20,0 L 20,100 M 40,0 L 40,100 M 60,0 L 60,100 M 80,0 L 80,100' stroke='#334155' stroke-width='2'/>";
            } elseif ($search === 'a rayures') {
                $svgContent = "<rect width='100' height='100' fill='none'/>
                              <path d='M 0,15 L 100,15 M 0,35 L 100,35 M 0,55 L 100,55 M 0,75 L 100,75 M 0,95 L 100,95' stroke='#334155' stroke-width='4'/>";
            } elseif ($search === 'bi color') {
                $svgContent = "<path d='M 0,0 L 100,100 L 0,100 Z' fill='#1e1b4b'/>
                              <path d='M 0,0 L 100,0 L 100,100 Z' fill='#312e81'/>";
            } elseif ($search === 'florale') {
                $svgContent = "<circle cx='50' cy='50' r='10' fill='#eab308'/>
                              <circle cx='50' cy='32' r='8' fill='#db2777' opacity='0.7'/>
                              <circle cx='50' cy='68' r='8' fill='#db2777' opacity='0.7'/>
                              <circle cx='32' cy='50' r='8' fill='#db2777' opacity='0.7'/>
                              <circle cx='68' cy='50' r='8' fill='#db2777' opacity='0.7'/>";
            } else {
                $svgContent = "<circle cx='20' cy='20' r='2' fill='#475569'/>
                              <circle cx='50' cy='30' r='3' fill='#475569'/>
                              <circle cx='80' cy='25' r='2' fill='#475569'/>
                              <circle cx='30' cy='60' r='3' fill='#475569'/>
                              <circle cx='70' cy='70' r='2' fill='#475569'/>
                              <circle cx='45' cy='85' r='4' fill='#475569'/>";
            }
        } elseif ($type === 'defects') {
            $search = strtolower(trim($name));
            if (str_contains($search, 'bouton')) {
                $svgContent = "<circle cx='50' cy='50' r='22' fill='none' stroke='#f59e0b' stroke-width='3'/>
                              <circle cx='42' cy='42' r='2.5' fill='#f59e0b'/>
                              <circle cx='58' cy='42' r='2.5' fill='#f59e0b'/>
                              <circle cx='42' cy='58' r='2.5' fill='#f59e0b'/>
                              <circle cx='58' cy='58' r='2.5' fill='#f59e0b'/>
                              <path d='M 35,35 L 65,65' stroke='#ef4444' stroke-width='3.5'/>";
            } elseif (str_contains($search, 'déchir') || str_contains($search, 'trou') || str_contains($search, 'col')) {
                $svgContent = "<path d='M 25,50 Q 40,25 50,50 T 75,50' fill='none' stroke='#ef4444' stroke-width='4' stroke-linecap='round'/>
                              <circle cx='50' cy='50' r='6' fill='#0f172a' stroke='#ef4444' stroke-width='2.5'/>";
            } else {
                $svgContent = "<path d='M 50,18 L 82,72 L 18,72 Z' fill='none' stroke='#f59e0b' stroke-width='4' stroke-linejoin='round'/>
                              <text x='50' y='64' font-family='system-ui, sans-serif' font-size='26' font-weight='bold' fill='#f59e0b' text-anchor='middle'>!</text>";
            }
        } else {
            $search = strtolower(trim($name));
            $stainColor = '#8b5cf6'; // default purple
            if (str_contains($search, 'café') || str_contains($search, 'aliment') || str_contains($search, 'boue')) {
                $stainColor = '#b45309'; // brown
            } elseif (str_contains($search, 'sang') || str_contains($search, 'vin')) {
                $stainColor = '#b91c1c'; // dark red
            } elseif (str_contains($search, 'encre') || str_contains($search, 'biro') || str_contains($search, 'peint')) {
                $stainColor = '#2563eb'; // blue
            } elseif (str_contains($search, 'javel')) {
                $stainColor = '#fef08a'; // yellow
            } elseif (str_contains($search, 'transpi')) {
                $stainColor = '#cbd5e1'; // slate
            }
            
            $svgContent = "<path d='M50 25 C62 20, 78 32, 70 48 C62 64, 78 78, 52 74 C26 70, 22 80, 18 60 C14 40, 28 26, 50 25 Z' fill='{$stainColor}' opacity='0.8'/>";
        }
        
        return "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'>
                    <rect width='100' height='100' fill='{$bg}'/>
                    {$svgContent}
                </svg>";
    }
}
