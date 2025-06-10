<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Create admin user
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin',
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'phone' => $faker->phoneNumber(),
            'password' => Hash::make('password'),
            'address' => $faker->address(),
            'date_of_birth' => $faker->dateTimeBetween('-50 years', '-18 years'),
            'employee_id' => 'EMP001',
            'status' => 'active',
            'metadata' => json_encode(['role' => 'admin', 'department' => 'IT']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create test users with varied data
        $providers = ['google', 'github', 'facebook', null];
        $statuses = ['active', 'inactive', 'suspended'];
        $departments = ['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations', 'R&D'];

        for ($i = 1; $i <= 30; $i++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            $provider = $faker->randomElement($providers);
            $status = $faker->randomElement($statuses);
            
            $userData = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => strtolower($firstName . '.' . $lastName . $i),
                'name' => $firstName . ' ' . $lastName,
                'email' => strtolower($firstName . '.' . $lastName . $i . '@example.com'),
                'email_verified_at' => $faker->boolean(80) ? $faker->dateTimeBetween('-1 year', 'now') : null,
                'phone' => $faker->boolean(70) ? $faker->phoneNumber() : null,
                'password' => Hash::make('password'),
                'address' => $faker->boolean(60) ? $faker->address() : null,
                'date_of_birth' => $faker->boolean(80) ? $faker->dateTimeBetween('-65 years', '-18 years') : null,
                'status' => $status,
                'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
                'updated_at' => now(),
            ];

            // Add provider data if applicable
            if ($provider) {
                $userData['provider'] = $provider;
                $userData['provider_id'] = $faker->randomNumber(8);
            }

            // Add employee or student ID randomly
            if ($faker->boolean(70)) {
                $userData['employee_id'] = 'EMP' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            }
            
            if ($faker->boolean(30)) {
                $userData['student_id'] = 'STU' . str_pad($faker->randomNumber(4), 4, '0', STR_PAD_LEFT);
            }

            // Add avatar occasionally
            if ($faker->boolean(40)) {
                $userData['avatar'] = 'https://ui-avatars.com/api/?name=' . urlencode($firstName . '+' . $lastName);
            }

            // Add metadata
            $userData['metadata'] = json_encode([
                'department' => $faker->randomElement($departments),
                'hire_date' => $faker->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                'position' => $faker->jobTitle(),
                'salary_range' => $faker->randomElement(['30k-50k', '50k-70k', '70k-100k', '100k+']),
                'skills' => $faker->randomElements(['PHP', 'JavaScript', 'Python', 'Java', 'React', 'Vue', 'Laravel', 'Node.js'], $faker->numberBetween(1, 4)),
            ]);

            User::create($userData);
        }

        // Create some users with specific patterns for testing
        $specialUsers = [
            [
                'first_name' => 'John',
                'last_name' => 'Developer',
                'username' => 'john.dev',
                'name' => 'John Developer',
                'email' => 'john.developer@techcorp.com',
                'employee_id' => 'DEV001',
                'status' => 'active',
                'metadata' => json_encode(['department' => 'IT', 'position' => 'Senior Developer']),
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Manager',
                'username' => 'sarah.mgr',
                'name' => 'Sarah Manager',
                'email' => 'sarah.manager@business.com',
                'employee_id' => 'MGR001',
                'status' => 'active',
                'metadata' => json_encode(['department' => 'Operations', 'position' => 'Project Manager']),
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Student',
                'username' => 'mike.student',
                'name' => 'Mike Student',
                'email' => 'mike.student@university.edu',
                'student_id' => 'STU2024001',
                'status' => 'active',
                'metadata' => json_encode(['department' => 'Education', 'position' => 'Intern']),
            ],
        ];

        foreach ($specialUsers as $userData) {
            $userData['email_verified_at'] = now();
            $userData['password'] = Hash::make('password');
            $userData['phone'] = $faker->phoneNumber();
            $userData['address'] = $faker->address();
            $userData['date_of_birth'] = $faker->dateTimeBetween('-40 years', '-20 years');
            $userData['created_at'] = $faker->dateTimeBetween('-1 year', 'now');
            $userData['updated_at'] = now();
            
            User::create($userData);
        }
    }
}