<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BirthRecord;
use App\Models\MarriageRecord;
use App\Models\DeathRecord;
use App\Models\RegistrationOffice;
use Carbon\Carbon;

class ExportRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:export
                            {type : Type of records (birth|marriage|death|all)}
                            {--region= : Filter by region}
                            {--format=csv : Export format (csv|json)}
                            {--year= : Filter by year}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export civil registration records to CSV or JSON';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $region = $this->option('region');
        $format = $this->option('format');
        $year = $this->option('year');

        $this->info("Exporting {$type} records...");

        $exportPath = storage_path("app/exports");

        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0755, true);
        }

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = "{$type}_records_{$timestamp}.{$format}";
        $filepath = "{$exportPath}/{$filename}";

        try {
            if ($type === 'all') {
                $this->exportAll($region, $year, $format, $exportPath, $timestamp);
            } else {
                $this->exportByType($type, $region, $year, $format, $filepath);
            }

            $this->info("✓ Export completed successfully!");
            $this->info("Location: {$filepath}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("✗ Export failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Export all record types
     */
    protected function exportAll($region, $year, $format, $exportPath, $timestamp)
    {
        $types = ['birth', 'marriage', 'death'];

        foreach ($types as $type) {
            $filename = "{$type}_records_{$timestamp}.{$format}";
            $filepath = "{$exportPath}/{$filename}";
            $this->exportByType($type, $region, $year, $format, $filepath);
        }
    }

    /**
     * Export by specific type
     */
    protected function exportByType($type, $region, $year, $format, $filepath)
    {
        $query = $this->buildQuery($type, $region, $year);
        $records = $query->get();

        if ($format === 'json') {
            $this->exportJson($records, $filepath);
        } else {
            $this->exportCsv($records, $filepath, $type);
        }

        $count = $records->count();
        $size = round(filesize($filepath) / 1024, 2);
        $this->line("  - {$count} {$type} records exported ({$size} KB)");
    }

    /**
     * Build query based on filters
     */
    protected function buildQuery($type, $region, $year)
    {
        $model = match($type) {
            'birth' => BirthRecord::class,
            'marriage' => MarriageRecord::class,
            'death' => DeathRecord::class,
        };

        $query = $model::with('office');

        if ($region) {
            $query->whereHas('office', function($q) use ($region) {
                $q->where('region', $region);
            });
        }

        if ($year) {
            $query->whereYear('registration_date', $year);
        }

        return $query;
    }

    /**
     * Export to JSON format
     */
    protected function exportJson($records, $filepath)
    {
        $data = json_encode($records->toArray(), JSON_PRETTY_PRINT);
        file_put_contents($filepath, $data);
    }

    /**
     * Export to CSV format
     */
    protected function exportCsv($records, $filepath, $type)
    {
        $file = fopen($filepath, 'w');

        // Write headers based on type
        $headers = $this->getCsvHeaders($type);
        fputcsv($file, $headers);

        // Write data
        foreach ($records as $record) {
            $row = $this->formatCsvRow($record, $type);
            fputcsv($file, $row);
        }

        fclose($file);
    }

    /**
     * Get CSV headers by record type
     */
    protected function getCsvHeaders($type)
    {
        return match($type) {
            'birth' => [
                'ID', 'Certificate No', 'First Name', 'Middle Name', 'Last Name',
                'Gender', 'Date of Birth', 'Place of Birth', 'Nationality',
                'Father Name', 'Mother Name', 'Registration Office', 'Region',
                'Registration Date', 'Status', 'Created At'
            ],
            'marriage' => [
                'ID', 'Certificate No', 'Groom ID', 'Bride ID',
                'Date of Marriage', 'Place of Marriage',
                'Witness 1', 'Witness 2', 'Registration Office', 'Region',
                'Registration Date', 'Status', 'Created At'
            ],
            'death' => [
                'ID', 'Certificate No', 'Deceased Birth ID', 'Informant Birth ID',
                'Date of Death', 'Place of Death', 'Cause of Death',
                'Informant Name', 'Informant Relation', 'Registration Office', 'Region',
                'Registration Date', 'Status', 'Created At'
            ],
        };
    }

    /**
     * Format CSV row by record type
     */
    protected function formatCsvRow($record, $type)
    {
        $office = $record->office;

        return match($type) {
            'birth' => [
                $record->id,
                $record->birth_certificate_no,
                $record->child_first_name,
                $record->child_middle_name,
                $record->child_last_name,
                $record->gender,
                $record->date_of_birth,
                $record->place_of_birth,
                $record->nationality,
                $record->father_name,
                $record->mother_name,
                $office->office_name,
                $office->region,
                $record->registration_date,
                $record->status,
                $record->created_at
            ],
            'marriage' => [
                $record->id,
                $record->marriage_certificate_no,
                $record->groom_id,
                $record->bride_id,
                $record->date_of_marriage,
                $record->place_of_marriage,
                $record->witness1_name ?? '',
                $record->witness2_name ?? '',
                $office->office_name,
                $office->region,
                $record->registration_date,
                $record->status,
                $record->created_at
            ],
            'death' => [
                $record->id,
                $record->death_certificate_no,
                $record->deceased_birth_id,
                $record->informant_birth_id,
                $record->date_of_death,
                $record->place_of_death,
                $record->cause_of_death,
                $record->informant_name,
                $record->informant_relation,
                $office->office_name,
                $office->region,
                $record->registration_date,
                $record->status,
                $record->created_at
            ],
        };
    }
}
