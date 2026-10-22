<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect([
            'inventory.view',
            'inventory.manage',
            'movements.manage',
        ])->map(fn (string $name) => Permission::query()->updateOrCreate(
            ['name' => $name, 'guard_name' => 'web'],
            []
        ));

        Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'super_admin')
            ->firstOrFail()
            ->syncPermissions($permissions);

        Role::query()
            ->where('guard_name', 'web')
            ->where('name', 'company_admin')
            ->firstOrFail()
            ->syncPermissions($permissions->whereIn('name', [
                'inventory.view',
                'inventory.manage',
            ]));
    }
}
