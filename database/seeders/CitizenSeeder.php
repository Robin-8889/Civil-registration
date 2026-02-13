<?php

namespace Database\Seeders;

use App\Models\Citizen;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitizenSeeder extends Seeder
{
    public function run(): void
    {
        Citizen::create([
            'national_id' => '001-TZ-2000-00001',
            'first_name' => 'John',
            'middle_name' => 'Samuel',
            'last_name' => 'Mkono',
            'date_of_birth' => '2000-05-15',
            'gender' => 'M',
            'place_of_birth' => 'Dar es Salaam',
            'address' => '123 Sokoine Street, Dar es Salaam',
            'phone' => '+255 71 234 5678',
            'email' => 'john.mkono@email.com',
        ]);

        Citizen::create([
            'national_id' => '001-TZ-1998-00002',
            'first_name' => 'Mary',
            'middle_name' => 'Grace',
            'last_name' => 'Mwangi',
            'date_of_birth' => '1998-08-22',
            'gender' => 'F',
            'place_of_birth' => 'Arusha',
            'address' => '456 Avenida Street, Arusha',
            'phone' => '+255 71 987 6543',
            'email' => 'mary.mwangi@email.com',
        ]);

        Citizen::create([
            'national_id' => '001-TZ-1995-00003',
            'first_name' => 'David',
            'middle_name' => 'Peter',
            'last_name' => 'Kamau',
            'date_of_birth' => '1995-03-10',
            'gender' => 'M',
            'place_of_birth' => 'Mwanza',
            'address' => '789 Lake Road, Mwanza',
            'phone' => '+255 71 555 4444',
            'email' => 'david.kamau@email.com',
        ]);

        Citizen::create([
            'national_id' => '001-TZ-2005-00004',
            'first_name' => 'Sarah',
            'middle_name' => 'Alice',
            'last_name' => 'Nkala',
            'date_of_birth' => '2005-12-01',
            'gender' => 'F',
            'place_of_birth' => 'Dar es Salaam',
            'address' => '321 Kariakoo Street, Dar es Salaam',
            'phone' => '+255 71 111 2222',
            'email' => 'sarah.nkala@email.com',
        ]);
    }
}
