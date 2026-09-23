<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Get backup configuration and list of existing backups.
     */
    public function index(Request $request)
    {
        $currentDir = $this->backupService->getBackupDirectory();
        $backups = $this->backupService->getBackupsList();
        $isDefault = ($currentDir === base_path('sauvegardes'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'directory' => $currentDir,
                'is_default' => $isDefault,
                'backups' => $backups,
            ]);
        }

        return response()->json([
            'directory' => $currentDir,
            'is_default' => $isDefault,
            'backups' => $backups,
        ]);
    }

    /**
     * Update the backup directory.
     */
    public function updateDirectory(Request $request)
    {
        $request->validate([
            'directory' => 'required|string|min:2|max:500',
        ]);

        $rawPath = trim($request->input('directory'));

        try {
            $savedPath = $this->backupService->setBackupDirectory($rawPath);
            $backups = $this->backupService->getBackupsList();

            return response()->json([
                'success' => true,
                'message' => 'Emplacement de sauvegarde mis à jour avec succès.',
                'directory' => $savedPath,
                'backups' => $backups,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la configuration du dossier : " . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Trigger an immediate database backup.
     */
    public function runBackup(Request $request)
    {
        try {
            $result = $this->backupService->createBackup();
            $backups = $this->backupService->getBackupsList();

            return response()->json([
                'success' => true,
                'message' => 'Sauvegarde de la base de données créée avec succès !',
                'backup' => $result,
                'backups' => $backups,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la sauvegarde : " . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a specific backup file.
     */
    public function download(string $filename)
    {
        // Sanitize to prevent path traversal
        $safeFilename = basename($filename);

        if (!str_ends_with(strtolower($safeFilename), '.sql')) {
            abort(400, "Type de fichier invalide.");
        }

        $dir = $this->backupService->getBackupDirectory();
        $fullPath = rtrim($dir, "\\/") . DIRECTORY_SEPARATOR . $safeFilename;

        if (!File::exists($fullPath)) {
            abort(404, "Le fichier de sauvegarde demandé n'existe pas ou a été déplacé.");
        }

        return response()->download($fullPath, $safeFilename, [
            'Content-Type' => 'application/sql',
        ]);
    }
}
