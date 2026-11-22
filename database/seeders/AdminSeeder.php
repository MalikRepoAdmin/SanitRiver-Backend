<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $adminGuard = 'admin';
        $adminRole = Role::findOrCreate('admin', $adminGuard);

        $admins = [
            [
                'email' => 'malik@admin.com',
                'username' => 'adminbackend1',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'radit@admin.com',
                'username' => 'adminfrontend1',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'angel@admin.com',
                'username' => 'adminfrontend2',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'email' => 'zahrah@admin.com',
                'username' => 'adminbackend2',
                'password' => Hash::make('password123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($admins as $adminData) {
            $admin = Admin::create($adminData);
            $admin->syncRoles([$adminRole]);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
