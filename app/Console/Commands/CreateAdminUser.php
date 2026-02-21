<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
        {--email=admin@civil-registration.go.tz : Admin email address}
        {--password=admin123456 : Admin password}
        {--name=System Administrator : Admin name}';

    protected $description = 'Create a system administrator account';

    public function handle(): int
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("❌ User with email {$email} already exists");
            return self::FAILURE;
        }

        try {
            // Create admin user
            $admin = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'is_system_admin' => true,
                'is_approved' => true,
                'email_verified_at' => now(),
            ]);

            $this->info('✅ System Administrator created successfully!');
            $this->newLine();
            $this->info('Login Credentials:');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Email', $email],
                    ['Password', $password],
                    ['Role', 'System Administrator'],
                ]
            );
            $this->newLine();
            $this->warn('⚠️  Important: Change the password after first login!');
            $this->info('URL: http://localhost/civil-registration/login');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to create admin user: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
