<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--type=full : Type of backup (full or incremental)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a backup of the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        $type = $this->option('type');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $backupPath = storage_path("app/backups");

        // Create backup directory if it doesn't exist
        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $filename = "{$type}_backup_{$database}_{$timestamp}.sql";
        $filepath = "{$backupPath}/{$filename}";

        // Build mysqldump command
        $command = sprintf(
            'mysqldump --single-transaction --routines --triggers --user=%s --password=%s --host=%s %s > %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        // Execute backup
        exec($command, $output, $returnVar);

        if ($returnVar === 0 && file_exists($filepath)) {
            $size = filesize($filepath);
            $sizeInMB = round($size / 1048576, 2);

            $this->info("Backup completed successfully!");
            $this->info("File: {$filename}");
            $this->info("Size: {$sizeInMB} MB");
            $this->info("Location: {$filepath}");

            // Log backup
            $this->logBackup($filename, $sizeInMB, $type);

            // Clean old backups
            $this->cleanOldBackups();

            return Command::SUCCESS;
        } else {
            $this->error("Backup failed!");
            return Command::FAILURE;
        }
    }

    /**
     * Log backup information
     */
    protected function logBackup($filename, $size, $type)
    {
        $logFile = storage_path('logs/backups.log');
        $logEntry = sprintf(
            "[%s] %s backup created: %s (%s MB)\n",
            Carbon::now()->toDateTimeString(),
            ucfirst($type),
            $filename,
            $size
        );
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Clean old backups based on retention policy
     */
    protected function cleanOldBackups()
    {
        $backupPath = storage_path("app/backups");
        $files = glob("{$backupPath}/*.sql");

        // Keep backups: 30 days for daily, 90 days for weekly, 365 days for monthly
        $now = Carbon::now();

        foreach ($files as $file) {
            $fileTime = Carbon::createFromTimestamp(filemtime($file));
            $daysOld = $now->diffInDays($fileTime);

            // Delete daily backups older than 30 days
            if (strpos($file, 'full_backup') !== false && $daysOld > 30) {
                unlink($file);
                $this->info("Deleted old backup: " . basename($file));
            }
        }
    }
}
