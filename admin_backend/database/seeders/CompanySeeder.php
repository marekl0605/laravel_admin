<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create(['name' => 'Acme Corp', 'email' => 'contact@acme.com']);
        Company::create(['name' => 'Globex Inc', 'email' => 'info@globex.com']);
    }
}