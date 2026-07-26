<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\GarmentTarget;
use App\Models\GarmentItem;
use App\Models\ServicePrice;
use Illuminate\Support\Facades\File;

class CheckoutController extends Controller
{
    /**
     * Display the tactile checkout main screen.
     */
    public function index()
    {
        $services = Service::all();
        $targets = GarmentTarget::all();
        
        // Load items with their pricing
        $items = GarmentItem::with('servicePrices')->get();

        // Retrieve choice dictionaries (with fallbacks)
        $colors = $this->getDictionary('Couleur.db', [
            'argent', 'azur', 'beige', 'blanc', 'blanc cassé', 'bleu', 'bleu ciel', 
            'bleu marine', 'bleu turquoise', 'bordeaux', 'brun', 'écru', 'fauve', 
            'grenat', 'gris', 'ivoire', 'jaune', 'kaki', 'marron', 'mauve', 'or', 
            'orange', 'rose', 'rouge', 'saumon', 'sépia', 'vert', 'vert émeraude', 'violet'
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
            'guestClient'
        ));
    }

    /**
     * Helper to read choice dictionary files from the source MSK folders or fallback to hardcoded list
     */
    private function getDictionary($filename, $fallback)
    {
        $sourcePath = 'c:/Users/hadib/OneDrive/Bureau/MSK-DRY-PLUS-2022/db/' . $filename;
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
    }

    /**
     * Helper to read item patterns from old MSK folder Menu/0/2
     */
    private function getPatterns($fallback)
    {
        $sourcePath = 'c:/Users/hadib/OneDrive/Bureau/MSK-DRY-PLUS-2022/Menu/0/2';
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
    }
}
