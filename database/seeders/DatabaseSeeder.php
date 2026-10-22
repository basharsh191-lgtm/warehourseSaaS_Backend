<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            PartySeeder::class,
            MovementSeeder::class,
            MovementItemSeeder::class,
            OperationSeeder::class,
        ]);
    }
}
