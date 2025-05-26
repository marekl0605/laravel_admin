<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        $admin = User::where('email', 'andrew0605996@gmail.com')->first();
        if ($admin) {
            $admin->roles()->attach(Role::where('name', 'admin')->first());
        }
    }
}