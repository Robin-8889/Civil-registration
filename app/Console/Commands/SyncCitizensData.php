<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BirthRecord;
use App\Models\Citizen;
use App\Models\MarriageRecord;
use App\Models\DeathRecord;
use App\Models\RegistrationOffice;

class SyncCitizensData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-citizens-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync birth records data to citizens table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting citizens data sync...');

        // Clear existing citizens data
        Citizen::truncate();
        $this->info('Cleared existing citizens data.');

        // Get all birth records that are not rejected with their registration office
        $birthRecords = BirthRecord::whereIn('status', ['pending', 'registered'])
            ->with('office')
            ->get();

        $bar = $this->output->createProgressBar($birthRecords->count());

        foreach ($birthRecords as $birthRecord) {
            // Get marriage status and details - marriage records use groom_id/bride_id (not birth_record_id)
            $marriage = MarriageRecord::where(function ($query) use ($birthRecord) {
                $query->where('groom_id', $birthRecord->id)
                      ->orWhere('bride_id', $birthRecord->id);
            })->whereIn('status', ['pending', 'registered'])->first();

            // Get death status and details - uses deceased_birth_id
            $death = DeathRecord::where('deceased_birth_id', $birthRecord->id)->first();

            // Create citizen record from birth record data
            Citizen::create([
                'birth_record_id' => $birthRecord->id,
                'first_name' => $birthRecord->child_first_name,
                'middle_name' => $birthRecord->child_middle_name,
                'last_name' => $birthRecord->child_last_name,
                'gender' => $birthRecord->gender,
                'date_of_birth' => $birthRecord->date_of_birth,
                'birth_certificate_no' => $birthRecord->birth_certificate_no,
                'place_of_birth' => $birthRecord->place_of_birth,
                'birth_registration_date' => $birthRecord->registration_date,
                'father_name' => $birthRecord->father_name,
                'mother_name' => $birthRecord->mother_name,
                'nationality' => $birthRecord->nationality,
                'registration_office_id' => $birthRecord->registration_office_id,
                'region' => $birthRecord->office->region ?? 'Unknown',
                'record_status' => $birthRecord->status,
                'is_married' => $marriage ? 1 : 0,
                'marriage_record_id' => $marriage->id ?? null,
                'marriage_certificate_no' => $marriage->marriage_certificate_no ?? null,
                'marriage_date' => $marriage->date_of_marriage ?? null,
                'is_dead' => $death ? 1 : 0,
                'death_record_id' => $death->id ?? null,
                'death_certificate_no' => $death->death_certificate_no ?? null,
                'death_date' => $death->date_of_death ?? null,
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info('✓ Citizens data sync completed successfully!');
        $this->info("Total citizens synced: {$birthRecords->count()}");
    }
}

