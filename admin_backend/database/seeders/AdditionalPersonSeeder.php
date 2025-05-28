<?php

namespace Database\Seeders;

use App\Models\Person;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class AdditionalPersonSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'username' => 'normaluser',
            'email' => 'normaluser@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->roles()->attach(Role::where('name', 'user')->first());
        $user->companies()->attach(1);
        $user->people()->attach(2); // Link to Jane Smith
    }
}