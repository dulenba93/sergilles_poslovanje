<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportDatabase extends Command
{
    /**
     * Naziv komande.
     */
    protected $signature = 'export:db';

    /**
     * Opis komande.
     */
    protected $description = 'Exportuje celu MySQL bazu u storage/app/backups folder';

    public function handle()
    {
        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT', 3306);
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');

        $backupPath = storage_path('app/backups');

        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $fileName = $backupPath . '/' . $dbName . '-' . date('Y-m-d_H-i-s') . '.sql';

        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s',
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($fileName)
        );

        $this->info("Pokrećem export baze u fajl: $fileName");

        $result = null;
        system($command, $result);

        if ($result === 0) {
            $this->info("✅ Export završen uspešno!");
        } else {
            $this->error("❌ Greška pri exportovanju baze!");
        }
    }
}
