<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;

class RubricsController extends Controller
{
    private function getDbPath()
    {
        $primary = 'c:/Users/hadib/OneDrive/Bureau/MSK-DRY-PLUS-2022/db';
        if (File::isDirectory($primary)) {
            return $primary;
        }
        $fallback = storage_path('app/db');
        File::ensureDirectoryExists($fallback);
        return $fallback;
    }

    private function getMenuPath()
    {
        $primary = 'c:/Users/hadib/OneDrive/Bureau/MSK-DRY-PLUS-2022/Menu/0/2';
        if (File::isDirectory($primary)) {
            return $primary;
        }
        $fallback = storage_path('app/Menu/0/2');
        File::ensureDirectoryExists($fallback);
        return $fallback;
    }

    /**
     * Display admin dictionary/rubrics interface.
     */
    public function index()
    {
        $colors = $this->loadDictionary('Couleur.db', [
            'argent', 'azur', 'beige', 'blanc', 'blanc cassé', 'bleu', 'bleu ciel', 
            'bleu marine', 'bleu turquoise', 'blond', 'blond vénitien', 'bordeaux', 'brun', 'châtain', 'écru', 'fauve', 
            'fushia', 'grenat', 'gris', 'indigo', 'ivoire', 'jaune', 'kaki', 'marron', 'mauve', 'noir', 'or', 
            'orange', 'rose', 'rouge', 'saumon', 'sépia', 'vert', 'vert eau', 'vert émeraude', 'vert olive', 'vert pistache', 'violet'
        ]);

        $defects = $this->loadDictionary('Defauts.db', [
            'Bouton Brisé', 'Bouton Manquant', 'Bulle', 'Col Déchiré', 'Déchiré', 
            'Délavé', 'Manchette Déchirée', 'Marque de Repassage', 'Repassage Service', 
            'Tissu Boulochage', 'Trou'
        ]);

        $stains = $this->loadDictionary('Taches.db', [
            'Aliments', 'Alcool', 'Biro', 'Boue', 'Café', 'Couleur Purge', 
            'Collier Souillée', 'Eau de Javel', 'EncreGraisse', 'Maquillage', 
            'Moisissure', 'Parfum', 'Peinture', 'Pétrole', 'Rouille', 'Col souillé', 
            'Sang', 'Transpiration', 'Vin'
        ]);

        $patterns = $this->loadPatterns([
            'A carreaux', 'A rayures', 'Bi color', 'Florale', 'Moucheté', 'Pied de poule'
        ]);

        return view('admin.rubrics.index', compact('colors', 'defects', 'stains', 'patterns'));
    }

    /**
     * Update a dictionary/rubric list.
     */
    public function saveRubric(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:colors,defects,stains,patterns',
            'items' => 'nullable|array',
            'items.*' => 'required|string|max:100'
        ]);

        $type = $validated['type'];
        $items = array_values(array_filter(array_map('trim', $validated['items'] ?? [])));

        try {
            if ($type === 'patterns') {
                $this->savePatternsList($items);
                Cache::forget('patterns_list');
                Cache::forget('menu_files_list');
            } else {
                $filename = '';
                if ($type === 'colors') $filename = 'Couleur.db';
                elseif ($type === 'defects') $filename = 'Defauts.db';
                elseif ($type === 'stains') $filename = 'Taches.db';

                $this->saveDictionary($filename, $items);
                Cache::forget("dict_{$filename}");
                Cache::forget('menu_files_list');
            }

            return redirect()->route('admin.rubrics.index', ['tab' => $type])->with('success', 'La rubrique a été mise à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('admin.rubrics.index', ['tab' => $type])->withErrors(['error' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()]);
        }
    }

    /**
     * Read choice dictionary (Windows-1252 -> UTF-8)
     */
    private function loadDictionary($filename, $fallback)
    {
        $filePath = $this->getDbPath() . '/' . $filename;
        if (File::exists($filePath)) {
            try {
                $content = mb_convert_encoding(File::get($filePath), 'UTF-8', 'Windows-1252');
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
     * Write choice dictionary (UTF-8 -> Windows-1252)
     */
    private function saveDictionary($filename, $items)
    {
        $dbPath = $this->getDbPath();
        File::ensureDirectoryExists($dbPath);
        $filePath = $dbPath . '/' . $filename;

        // Convert items to Windows-1252 and join by CR-LF
        $content = mb_convert_encoding(implode("\r\n", $items) . "\r\n", 'Windows-1252', 'UTF-8');
        File::put($filePath, $content);
    }

    /**
     * Load patterns from old MSK folder Menu/0/2
     */
    private function loadPatterns($fallback)
    {
        $menuPath = $this->getMenuPath();
        if (File::isDirectory($menuPath)) {
            try {
                $files = File::files($menuPath);
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

    /**
     * Save patterns list (create/delete 0-[PatternName].txt files)
     */
    private function savePatternsList($newPatterns)
    {
        $menuPath = $this->getMenuPath();
        File::ensureDirectoryExists($menuPath);

        // Standardize new patterns list for filesystem comparison
        $standardizedNew = [];
        foreach ($newPatterns as $pat) {
            // Keep mapping spelling for the files
            $fsName = $pat;
            if (strtolower($pat) === 'a rayures') {
                $fsName = 'A reure';
            } elseif (strtolower($pat) === 'moucheté') {
                $fsName = 'Mouchte';
            }
            $standardizedNew[$fsName] = $pat; // key: FsName, value: Original Name
        }

        // Get currently existing patterns in the directory
        $existingFiles = [];
        if (File::isDirectory($menuPath)) {
            $files = File::files($menuPath);
            foreach ($files as $file) {
                $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                if (str_starts_with($filename, '0-')) {
                    $clean = preg_replace('/^0-\s*/', '', $filename);
                    $ext = strtolower($file->getExtension());
                    if ($ext === 'txt') {
                        $existingFiles[$clean] = true;
                    }
                }
            }
        }

        // 1. Create files for added patterns
        foreach ($standardizedNew as $fsName => $origName) {
            $txtPath = $menuPath . '/0-' . $fsName . '.txt';
            if (!File::exists($txtPath)) {
                // Write '0' to file in Windows-1252
                File::put($txtPath, "0\r\n");
            }
        }

        // 2. Delete files for removed patterns
        foreach ($existingFiles as $fsName => $exists) {
            if (!isset($standardizedNew[$fsName])) {
                $txtPath = $menuPath . '/0-' . $fsName . '.txt';
                $jpgPath = $menuPath . '/0-' . $fsName . '.jpg';
                if (File::exists($txtPath)) {
                    File::delete($txtPath);
                }
                if (File::exists($jpgPath)) {
                    File::delete($jpgPath);
                }
            }
        }
    }
}
