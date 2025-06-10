<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Person;
use Faker\Factory as Faker;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Create people with realistic data
        for ($i = 1; $i <= 25; $i++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            
            Person::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName . '.' . $lastName . '.person' . $i . '@example.com'),
                'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
                'updated_at' => now(),
            ]);
        }

        // Create some people with similar names to existing users (for linking)
        $similarPeople = [
            [
                'first_name' => 'John',
                'last_name' => 'Developer',
                'email' => 'john.developer.person@techcorp.com',
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Manager',
                'email' => 'sarah.manager.person@business.com',
            ],
            [
                'first_name' => 'Mike',
                'last_name' => 'Student',
                'email' => 'mike.student.person@university.edu',
            ],
        ];

        foreach ($similarPeople as $personData) {
            $personData['created_at'] = $faker->dateTimeBetween('-1 year', 'now');
            $personData['updated_at'] = now();
            
            Person::create($personData);
        }

        // Create additional people with various backgrounds
        $professions = [
            ['first_name' => 'Alice', 'last_name' => 'Engineer', 'email' => 'alice.engineer@tech.com'],
            ['first_name' => 'Bob', 'last_name' => 'Designer', 'email' => 'bob.designer@creative.com'],
            ['first_name' => 'Carol', 'last_name' => 'Analyst', 'email' => 'carol.analyst@finance.com'],
            ['first_name' => 'David', 'last_name' => 'Consultant', 'email' => 'david.consultant@advisory.com'],
            ['first_name' => 'Emma', 'last_name' => 'Researcher', 'email' => 'emma.researcher@lab.com'],
            ['first_name' => 'Frank', 'last_name' => 'Coordinator', 'email' => 'frank.coordinator@events.com'],
            ['first_name' => 'Grace', 'last_name' => 'Specialist', 'email' => 'grace.specialist@health.com'],
            ['first_name' => 'Henry', 'last_name' => 'Supervisor', 'email' => 'henry.supervisor@manufacturing.com'],
            ['first_name' => 'Iris', 'last_name' => 'Trainer', 'email' => 'iris.trainer@education.com'],
            ['first_name' => 'Jack', 'last_name' => 'Technician', 'email' => 'jack.technician@support.com'],
            ['first_name' => 'Kate', 'last_name' => 'Administrator', 'email' => 'kate.admin@office.com'],
            ['first_name' => 'Leo', 'last_name' => 'Assistant', 'email' => 'leo.assistant@help.com'],
            ['first_name' => 'Maya', 'last_name' => 'Director', 'email' => 'maya.director@company.com'],
            ['first_name' => 'Noah', 'last_name' => 'Executive', 'email' => 'noah.executive@corp.com'],
            ['first_name' => 'Olivia', 'last_name' => 'Representative', 'email' => 'olivia.rep@sales.com'],
            ['first_name' => 'Paul', 'last_name' => 'Operator', 'email' => 'paul.operator@factory.com'],
            ['first_name' => 'Quinn', 'last_name' => 'Planner', 'email' => 'quinn.planner@logistics.com'],
            ['first_name' => 'Rachel', 'last_name' => 'Coordinator', 'email' => 'rachel.coord@events.com'],
            ['first_name' => 'Sam', 'last_name' => 'Architect', 'email' => 'sam.architect@design.com'],
            ['first_name' => 'Tina', 'last_name' => 'Auditor', 'email' => 'tina.auditor@accounting.com'],
        ];

        foreach ($professions as $personData) {
            $personData['created_at'] = $faker->dateTimeBetween('-18 months', 'now');
            $personData['updated_at'] = now();
            
            Person::create($personData);
        }

        // Create some people with contractor/freelancer emails
        for ($i = 1; $i <= 7; $i++) {
            $firstName = $faker->firstName();
            $lastName = $faker->lastName();
            
            Person::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName . '.' . $lastName . '.freelancer@contractor.com'),
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
}