<?php

namespace Database\Seeders;

use App\Models\Person;
use Illuminate\Database\Seeder;

class PersonSeeder extends Seeder
{
    public function run(): void
    {
        $person = Person::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);
        $person->companies()->attach(1);
    }
}