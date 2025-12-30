<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Trainer;
use App\Models\Package;
use App\Models\Schedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@heatttraining.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create Sample Trainers
        $trainer1 = Trainer::create([
            'name' => 'John Smith',
            'specialty' => 'Strength Training',
            'bio' => 'Certified personal trainer with 10 years of experience in strength and conditioning.',
            'is_active' => true,
        ]);

        $trainer2 = Trainer::create([
            'name' => 'Sarah Johnson',
            'specialty' => 'Cardio & Weight Loss',
            'bio' => 'Expert in cardio training and weight management programs.',
            'is_active' => true,
        ]);

        $trainer3 = Trainer::create([
            'name' => 'Mike Chen',
            'specialty' => 'CrossFit & HIIT',
            'bio' => 'CrossFit Level 2 certified with focus on high-intensity training.',
            'is_active' => true,
        ]);

        // Create Sample Packages
        Package::create([
            'name' => 'Starter Pack',
            'description' => 'Perfect for beginners. Get started with 5 training sessions.',
            'price' => 2500.00,
            'total_credits' => 5,
            'days_valid' => 30,
            'is_active' => true,
        ]);

        Package::create([
            'name' => 'Premium Pack',
            'description' => 'Most popular! 10 training sessions with extended validity.',
            'price' => 4500.00,
            'total_credits' => 10,
            'days_valid' => 60,
            'is_active' => true,
        ]);

        Package::create([
            'name' => 'Elite Pack',
            'description' => 'Best value! 20 training sessions for serious athletes.',
            'price' => 8000.00,
            'total_credits' => 20,
            'days_valid' => 90,
            'is_active' => true,
        ]);

        Package::create([
            'name' => 'Monthly Unlimited',
            'description' => 'Unlimited training sessions for 30 days.',
            'price' => 12000.00,
            'total_credits' => 100,
            'days_valid' => 30,
            'is_active' => true,
        ]);

        // Create Sample Schedules for next 7 days
        $trainers = [$trainer1, $trainer2, $trainer3];
        $times = [
            ['06:00:00', '07:00:00'],
            ['08:00:00', '09:00:00'],
            ['10:00:00', '11:00:00'],
            ['14:00:00', '15:00:00'],
            ['16:00:00', '17:00:00'],
            ['18:00:00', '19:00:00'],
        ];

        for ($day = 0; $day < 7; $day++) {
            $date = now()->addDays($day)->toDateString();
            
            foreach ($trainers as $trainer) {
                // Each trainer has 3-4 random time slots per day
                $selectedTimes = array_rand($times, rand(3, 4));
                
                foreach ((array)$selectedTimes as $timeIndex) {
                    Schedule::create([
                        'trainer_id' => $trainer->id,
                        'date' => $date,
                        'start_time' => $times[$timeIndex][0],
                        'end_time' => $times[$timeIndex][1],
                        'max_bookings' => 1,
                        'current_bookings' => 0,
                        'is_available' => true,
                    ]);
                }
            }
        }

        $this->command->info('Sample data seeded successfully!');
        $this->command->info('Admin credentials:');
        $this->command->info('Email: admin@heatttraining.com');
        $this->command->info('Password: password');
    }
}
