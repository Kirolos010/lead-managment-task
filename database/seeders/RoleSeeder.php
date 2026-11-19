<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin' , 'slug' => 'admin'],
            ['name' => 'Sales Manager', 'slug' => 'sales_manager'],
            ['name' => 'Sales Agent', 'slug' => 'sales_agent'],
        ];
        foreach ($roles as $role) {
            \App\Models\Role::create($role);
        }
    }
}
