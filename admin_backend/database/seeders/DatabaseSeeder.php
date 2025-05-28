<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            PersonSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            AdditionalPersonSeeder::class,
        ]);
    }
}