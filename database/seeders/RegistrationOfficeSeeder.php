<?php

namespace Database\Seeders;

use App\Models\RegistrationOffice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegistrationOfficeSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['name' => 'Dar es Salaam', 'location' => 'Dar es Salaam', 'district' => 'Dar es Salaam'],
            ['name' => 'Arusha', 'location' => 'Arusha', 'district' => 'Arusha'],
            ['name' => 'Kilimanjaro', 'location' => 'Moshi', 'district' => 'Moshi'],
            ['name' => 'Tanga', 'location' => 'Tanga', 'district' => 'Tanga'],
            ['name' => 'Mwanza', 'location' => 'Mwanza', 'district' => 'Mwanza'],
            ['name' => 'Kagera', 'location' => 'Bukoba', 'district' => 'Bukoba'],
            ['name' => 'Kigoma', 'location' => 'Kigoma', 'district' => 'Kigoma'],
            ['name' => 'Tabora', 'location' => 'Tabora', 'district' => 'Tabora'],
            ['name' => 'Singida', 'location' => 'Singida', 'district' => 'Singida'],
            ['name' => 'Dodoma', 'location' => 'Dodoma', 'district' => 'Dodoma'],
            ['name' => 'Iringa', 'location' => 'Iringa', 'district' => 'Iringa'],
            ['name' => 'Mbeya', 'location' => 'Mbeya', 'district' => 'Mbeya'],
            ['name' => 'Rukwa', 'location' => 'Sumbawanga', 'district' => 'Sumbawanga'],
            ['name' => 'Ruvuma', 'location' => 'Songea', 'district' => 'Songea'],
            ['name' => 'Lindi', 'location' => 'Lindi', 'district' => 'Lindi'],
            ['name' => 'Mtwara', 'location' => 'Mtwara', 'district' => 'Mtwara'],
            ['name' => 'Morogoro', 'location' => 'Morogoro', 'district' => 'Morogoro'],
            ['name' => 'Coast', 'location' => 'Bagamoyo', 'district' => 'Bagamoyo'],
        ];

        foreach ($regions as $region) {
            RegistrationOffice::create([
                'office_name' => $region['name'] . ' Regional Office',
                'location' => $region['location'],
                'district' => $region['district'],
                'region' => $region['name'],
                'phone' => '+255 ' . rand(200, 999) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
                'email' => strtolower(str_replace(' ', '', $region['name'])) . '@civilreg.go.tz',
                'address' => $region['location'] . ' Civil Registration Office',
                'status' => 'active',
            ]);
        }
    }
}
