<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BirthRecord;
use App\Models\MarriageRecord;
use App\Models\DeathRecord;
use App\Models\RegistrationOffice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ImportRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:import
                            {file : Path to the import file}
                            {type : Type of records (birth|marriage|death)}
                            {--validate-only : Only validate without importing}
                            {--skip-errors : Skip records with errors}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import civil registration records from CSV or JSON';

    protected $imported = 0;
    protected $errors = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $type = $this->argument('type');
        $validateOnly = $this->option('validate-only');
        $skipErrors = $this->option('skip-errors');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return Command::FAILURE;
        }

        $extension = pathinfo($file, PATHINFO_EXTENSION);

        if (!in_array($extension, ['csv', 'json'])) {
            $this->error("Unsupported file format. Use CSV or JSON.");
            return Command::FAILURE;
        }

        $this->info("Importing {$type} records from {$file}...");

        try {
            $records = $extension === 'json'
                ? $this->loadJson($file)
                : $this->loadCsv($file, $type);

            $this->info("Found " . count($records) . " records to process.");

            if ($validateOnly) {
                $this->validateRecords($records, $type);
                $this->displayValidationResults();
            } else {
                $this->importRecords($records, $type, $skipErrors);
                $this->displayImportResults();
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Load JSON file
     */
    protected function loadJson($file)
    {
        $content = file_get_contents($file);
        return json_decode($content, true);
    }

    /**
     * Load CSV file
     */
    protected function loadCsv($file, $type)
    {
        $records = [];
        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $records[] = array_combine($headers, $row);
        }

        fclose($handle);
        return $records;
    }

    /**
     * Validate records without importing
     */
    protected function validateRecords($records, $type)
    {
        $bar = $this->output->createProgressBar(count($records));
        $bar->start();

        foreach ($records as $index => $record) {
            $rules = $this->getValidationRules($type);
            $validator = Validator::make($record, $rules);

            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $index + 2,
                    'errors' => $validator->errors()->all()
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Import records to database
     */
    protected function importRecords($records, $type, $skipErrors)
    {
        $bar = $this->output->createProgressBar(count($records));
        $bar->start();

        DB::beginTransaction();

        try {
            foreach ($records as $index => $record) {
                try {
                    $this->createRecord($record, $type);
                    $this->imported++;
                } catch (\Exception $e) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'errors' => [$e->getMessage()]
                    ];

                    if (!$skipErrors) {
                        DB::rollBack();
                        throw $e;
                    }
                }
                $bar->advance();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Create record in database
     */
    protected function createRecord($data, $type)
    {
        match($type) {
            'birth' => BirthRecord::create([
                'registration_office_id' => $data['registration_office_id'],
                'birth_certificate_no' => $data['birth_certificate_no'] ?? $this->generateCertificateNo('BIR'),
                'date_of_birth' => $data['date_of_birth'],
                'place_of_birth' => $data['place_of_birth'],
                'child_first_name' => $data['child_first_name'] ?? $data['First Name'],
                'child_middle_name' => $data['child_middle_name'] ?? $data['Middle Name'] ?? null,
                'child_last_name' => $data['child_last_name'] ?? $data['Last Name'],
                'gender' => $data['gender'] ?? $data['Gender'],
                'nationality' => $data['nationality'] ?? $data['Nationality'] ?? 'Tanzanian',
                'father_name' => $data['father_name'] ?? $data['Father Name'] ?? null,
                'mother_name' => $data['mother_name'] ?? $data['Mother Name'] ?? null,
                'registration_date' => $data['registration_date'] ?? $data['Registration Date'] ?? now(),
                'status' => $data['status'] ?? $data['Status'] ?? 'pending',
            ]),
            'marriage' => MarriageRecord::create([
                'groom_id' => $data['groom_id'] ?? $data['Groom ID'],
                'bride_id' => $data['bride_id'] ?? $data['Bride ID'],
                'registration_office_id' => $data['registration_office_id'],
                'marriage_certificate_no' => $data['marriage_certificate_no'] ?? $this->generateCertificateNo('MAR'),
                'date_of_marriage' => $data['date_of_marriage'] ?? $data['Date of Marriage'],
                'place_of_marriage' => $data['place_of_marriage'] ?? $data['Place of Marriage'],
                'witness1_name' => $data['witness1_name'] ?? $data['Witness 1'] ?? null,
                'witness2_name' => $data['witness2_name'] ?? $data['Witness 2'] ?? null,
                'registration_date' => $data['registration_date'] ?? $data['Registration Date'] ?? now(),
                'status' => $data['status'] ?? $data['Status'] ?? 'pending',
            ]),
            'death' => DeathRecord::create([
                'deceased_birth_id' => $data['deceased_birth_id'] ?? $data['Deceased Birth ID'],
                'informant_birth_id' => $data['informant_birth_id'] ?? $data['Informant Birth ID'] ?? null,
                'registration_office_id' => $data['registration_office_id'],
                'death_certificate_no' => $data['death_certificate_no'] ?? $this->generateCertificateNo('DE'),
                'date_of_death' => $data['date_of_death'] ?? $data['Date of Death'],
                'place_of_death' => $data['place_of_death'] ?? $data['Place of Death'],
                'cause_of_death' => $data['cause_of_death'] ?? $data['Cause of Death'] ?? null,
                'informant_name' => $data['informant_name'] ?? $data['Informant Name'],
                'informant_relation' => $data['informant_relation'] ?? $data['Informant Relation'],
                'registration_date' => $data['registration_date'] ?? $data['Registration Date'] ?? now(),
                'status' => $data['status'] ?? $data['Status'] ?? 'pending',
            ]),
        };
    }

    /**
     * Get validation rules for record type
     */
    protected function getValidationRules($type)
    {
        return match($type) {
            'birth' => [
                'date_of_birth' => 'required|date',
                'place_of_birth' => 'required|string',
                'gender' => 'required|in:M,F',
            ],
            'marriage' => [
                'groom_id' => 'required|exists:birth_records,id',
                'bride_id' => 'required|exists:birth_records,id',
                'date_of_marriage' => 'required|date',
            ],
            'death' => [
                'deceased_birth_id' => 'required|exists:birth_records,id',
                'date_of_death' => 'required|date',
                'place_of_death' => 'required|string',
            ],
        };
    }

    /**
     * Generate certificate number
     */
    protected function generateCertificateNo($prefix)
    {
        $year = date('Y');
        $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        return "{$prefix}-{$year}-{$random}";
    }

    /**
     * Display validation results
     */
    protected function displayValidationResults()
    {
        if (empty($this->errors)) {
            $this->info("✓ All records are valid!");
        } else {
            $this->warn("Found " . count($this->errors) . " validation errors:");
            foreach ($this->errors as $error) {
                $this->error("Row {$error['row']}: " . implode(', ', $error['errors']));
            }
        }
    }

    /**
     * Display import results
     */
    protected function displayImportResults()
    {
        $this->info("✓ Successfully imported {$this->imported} records.");

        if (!empty($this->errors)) {
            $this->warn("Skipped " . count($this->errors) . " records with errors:");
            foreach ($this->errors as $error) {
                $this->error("Row {$error['row']}: " . implode(', ', $error['errors']));
            }
        }
    }
}
