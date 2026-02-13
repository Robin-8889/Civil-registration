<?php

namespace Database\Seeders;

use App\Models\BirthRecord;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BirthRecordSeeder extends Seeder
{
    public function run(): void
    {
        BirthRecord::create([
            'child_id' => 4,
            'registration_office_id' => 1,
            'birth_certificate_no' => 'BIR-2024-00001',
            'date_of_birth' => '2024-01-15',
            'place_of_birth' => 'Muhimbili Hospital, Dar es Salaam',
            'child_first_name' => 'Sarah',
            'child_middle_name' => 'Alice',
            'child_last_name' => 'Nkala',
            'gender' => 'F',
            'father_name' => 'Faustine Nkala',
            'mother_name' => 'Miriam Nkala',
            'registration_date' => '2024-01-20',
            'status' => 'pending',
        ]);

        BirthRecord::create([
            'child_id' => 1,
            'registration_office_id' => 1,
            'birth_certificate_no' => 'BIR-2024-00002',
            'date_of_birth' => '2024-02-10',
            'place_of_birth' => 'Dar es Salaam Clinic',
            'child_first_name' => 'Michael',
            'child_middle_name' => 'James',
            'child_last_name' => 'Mkono',
            'gender' => 'M',
            'father_name' => 'John Mkono',
            'mother_name' => 'Elizabeth Mkono',
            'registration_date' => '2024-02-15',
            'status' => 'registered',
        ]);
    }
}
