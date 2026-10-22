<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['super_admin', 'company_admin'] as $name) {
            Role::query()->updateOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                []
            );
        }
    }
}
