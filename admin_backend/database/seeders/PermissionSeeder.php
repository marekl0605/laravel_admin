<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'manage-users', 'description' => 'Manage all users'],
            ['name' => 'manage-companies', 'description' => 'Manage companies'],
            ['name' => 'manage-people', 'description' => 'Manage people'],
            ['name' => 'view-users', 'description' => 'View users'],
        ];

        foreach ($permissions as $perm) {
            Permission::create($perm);
        }

        $adminRole = Role::where('name', 'admin')->first();
        $adminRole->permissions()->attach(Permission::all()->pluck('id'));
    }
}