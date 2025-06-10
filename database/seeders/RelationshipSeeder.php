<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Person;
use Faker\Factory as Faker;

class RelationshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        $users = User::all();
        $companies = Company::all();
        $people = Person::all();

        // 1. Attach Users to Companies
        foreach ($users as $user) {
            // Each user belongs to 1-3 companies
            $companyCount = $faker->numberBetween(1, 3);
            $randomCompanies = $companies->random($companyCount);
            
            $user->companies()->attach($randomCompanies->pluck('id')->toArray());
        }

        // 2. Attach People to Companies
        foreach ($people as $person) {
            // Each person belongs to 1-2 companies
            $companyCount = $faker->numberBetween(1, 2);
            $randomCompanies = $companies->random($companyCount);
            
            $person->companies()->attach($randomCompanies->pluck('id')->toArray());
        }

        // 3. Link some People to Users (authenticated people)
        // About 60% of people will be linked to user accounts
        $peopleToLink = $people->random(intval($people->count() * 0.6));
        
        foreach ($peopleToLink as $person) {
            // Link to 1 user account (most common case)
            if ($faker->boolean(85)) {
                $randomUser = $users->random(1)->first();
                if (!$person->users()->where('user_id', $randomUser->id)->exists()) {
                    $person->users()->attach($randomUser->id);
                }
            } 
            // Occasionally link to 2 user accounts (shared identity)
            else {
                $randomUsers = $users->random(2);
                foreach ($randomUsers as $randomUser) {
                    if (!$person->users()->where('user_id', $randomUser->id)->exists()) {
                        $person->users()->attach($randomUser->id);
                    }
                }
            }
        }

        // 4. Create specific relationship patterns for testing

        // Link similar named users and people
        $johnUser = User::where('first_name', 'John')->where('last_name', 'Developer')->first();
        $johnPerson = Person::where('first_name', 'John')->where('last_name', 'Developer')->first();
        if ($johnUser && $johnPerson) {
            $johnPerson->users()->attach($johnUser->id);
        }

        $sarahUser = User::where('first_name', 'Sarah')->where('last_name', 'Manager')->first();
        $sarahPerson = Person::where('first_name', 'Sarah')->where('last_name', 'Manager')->first();
        if ($sarahUser && $sarahPerson) {
            $sarahPerson->users()->attach($sarahUser->id);
        }

        $mikeUser = User::where('first_name', 'Mike')->where('last_name', 'Student')->first();
        $mikePerson = Person::where('first_name', 'Mike')->where('last_name', 'Student')->first();
        if ($mikeUser && $mikePerson) {
            $mikePerson->users()->attach($mikeUser->id);
        }

        // 5. Ensure some companies have good distribution of users and people
        $techCompany = Company::where('name', 'Tech Innovations Inc.')->first();
        if ($techCompany) {
            // Add more users to tech company
            $techUsers = $users->where('metadata', 'like', '%IT%')->take(8);
            foreach ($techUsers as $user) {
                if (!$user->companies()->where('company_id', $techCompany->id)->exists()) {
                    $user->companies()->attach($techCompany->id);
                }
            }
            
            // Add tech-related people
            $techPeople = $people->filter(function($person) {
                return str_contains($person->email, 'tech') || 
                       str_contains($person->last_name, 'Engineer') || 
                       str_contains($person->last_name, 'Developer');
            })->take(5);
            
            foreach ($techPeople as $person) {
                if (!$person->companies()->where('company_id', $techCompany->id)->exists()) {
                    $person->companies()->attach($techCompany->id);
                }
            }
        }

        // 6. Create some large companies with many employees
        $largeCompanies = $companies->take(3);
        foreach ($largeCompanies as $company) {
            // Add 10-15 random users
            $companyUsers = $users->random($faker->numberBetween(10, 15));
            foreach ($companyUsers as $user) {
                if (!$user->companies()->where('company_id', $company->id)->exists()) {
                    $user->companies()->attach($company->id);
                }
            }
            
            // Add 8-12 random people
            $companyPeople = $people->random($faker->numberBetween(8, 12));
            foreach ($companyPeople as $person) {
                if (!$person->companies()->where('company_id', $company->id)->exists()) {
                    $person->companies()->attach($company->id);
                }
            }
        }

        // 7. Create some small companies with few employees
        $smallCompanies = $companies->skip(20)->take(5);
        foreach ($smallCompanies as $company) {
            // Add 2-4 users
            $companyUsers = $users->random($faker->numberBetween(2, 4));
            foreach ($companyUsers as $user) {
                if (!$user->companies()->where('company_id', $company->id)->exists()) {
                    $user->companies()->attach($company->id);
                }
            }
            
            // Add 1-3 people
            $companyPeople = $people->random($faker->numberBetween(1, 3));
            foreach ($companyPeople as $person) {
                if (!$person->companies()->where('company_id', $company->id)->exists()) {
                    $person->companies()->attach($company->id);
                }
            }
        }

        // 8. Ensure admin user is linked to multiple companies
        $adminUser = User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            $adminCompanies = $companies->random(5);
            foreach ($adminCompanies as $company) {
                if (!$adminUser->companies()->where('company_id', $company->id)->exists()) {
                    $adminUser->companies()->attach($company->id);
                }
            }
        }

        $this->command->info('Relationships created successfully!');
        $this->command->info('Statistics:');
        $this->command->info('- Total Users: ' . $users->count());
        $this->command->info('- Total Companies: ' . $companies->count());
        $this->command->info('- Total People: ' . $people->count());
        $this->command->info('- People linked to Users: ' . Person::whereHas('users')->count());
        $this->command->info('- People not linked to Users: ' . Person::whereDoesntHave('users')->count());
    }
}