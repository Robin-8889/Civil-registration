<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class DatabaseRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore {file? : Backup file to restore}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database from a backup file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('⚠️  WARNING: This will replace the current database with the backup!');

        if (!$this->confirm('Do you want to continue?')) {
            $this->info('Restore cancelled.');
            return Command::SUCCESS;
        }

        $backupPath = storage_path("app/backups");
        $file = $this->argument('file');

        // If no file specified, list available backups
        if (!$file) {
            $files = glob("{$backupPath}/*.sql");

            if (empty($files)) {
                $this->error('No backup files found!');
                return Command::FAILURE;
            }

            $this->info('Available backup files:');
            foreach ($files as $index => $filepath) {
                $size = filesize($filepath);
                $sizeInMB = round($size / 1048576, 2);
                $date = Carbon::createFromTimestamp(filemtime($filepath))->format('Y-m-d H:i:s');
                $this->line(($index + 1) . ". " . basename($filepath) . " ({$sizeInMB} MB) - {$date}");
            }

            $choice = $this->ask('Enter the number of the backup to restore');

            if (!isset($files[$choice - 1])) {
                $this->error('Invalid choice!');
                return Command::FAILURE;
            }

            $file = basename($files[$choice - 1]);
        }

        $filepath = "{$backupPath}/{$file}";

        if (!file_exists($filepath)) {
            $this->error("Backup file not found: {$file}");
            return Command::FAILURE;
        }

        $this->info("Restoring from: {$file}");

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        // Create a backup before restore
        $this->info('Creating safety backup of current database...');
        $this->call('db:backup', ['--type' => 'pre-restore']);

        // Build mysql restore command
        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s %s < %s',
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($database),
            escapeshellarg($filepath)
        );

        // Execute restore
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            $this->info("✓ Database restored successfully from {$file}");

            // Log restore
            $this->logRestore($file);

            return Command::SUCCESS;
        } else {
            $this->error("✗ Restore failed!");
            return Command::FAILURE;
        }
    }

    /**
     * Log restore operation
     */
    protected function logRestore($filename)
    {
        $logFile = storage_path('logs/backups.log');
        $logEntry = sprintf(
            "[%s] Database restored from: %s\n",
            Carbon::now()->toDateTimeString(),
            $filename
        );
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
}
