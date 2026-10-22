<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $demoCompanyId = DB::table('companies')
            ->where('name', 'Demo Warehouse')
            ->value('id');

        $users = [
            [
                'name' => 'Demo Super Admin',
                'email' => 'admin@example.test',
                'company_id' => null,
                'role' => 'super_admin',
            ],
            [
                'name' => 'Demo Company Admin',
                'email' => 'manager@example.test',
                'company_id' => $demoCompanyId,
                'role' => 'company_admin',
            ],
        ];

        foreach ($users as $attributes) {
            $role = $attributes['role'];

            $user = User::query()->updateOrCreate(
                ['email' => $attributes['email']],
                [...$attributes, 'password' => 'password', 'is_active' => true]
            );

            $user->syncRoles([$role]);

            if ($role === 'company_admin') {
                $user->syncPermissions(['inventory.view']);
            }
        }
    }
}
