<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // System Admin - can manage all offices and users
        User::create([
            'name' => 'System Admin',
            'email' => 'sysadmin@civilreg.tz',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_system_admin' => true,
            'is_approved' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Office Admin/Registrar - can manage records in assigned office
        User::create([
            'name' => 'Registrar Dar es Salaam',
            'email' => 'registrar@dar.tz',
            'password' => Hash::make('password123'),
            'registration_office_id' => 1,
            'role' => 'registrar',
            'is_system_admin' => false,
            'is_approved' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Clerk - can edit/view records in assigned office
        User::create([
            'name' => 'Clerk Arusha',
            'email' => 'clerk@arusha.tz',
            'password' => Hash::make('password123'),
            'registration_office_id' => 2,
            'role' => 'clerk',
            'is_system_admin' => false,
            'is_approved' => true,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Citizen - no system access
        User::create([
            'name' => 'Citizen User',
            'email' => 'citizen@example.tz',
            'password' => Hash::make('password123'),
            'role' => 'citizen',
            'is_system_admin' => false,
            'is_approved' => false,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}
