<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $userGuard = 'user'; // Match the guard_name used by the models
        $adminGuard = 'admin';

        $permissions = [
            'create-sungais',
            'show-sungai',
            'list-sungais',
            'edit-sungais',
            'delete-sungais',

            'create-laporan_sungais',
            'show-laporan_sungai',
            'list-laporan_sungais',
            'edit-laporan_sungais',
            'delete-laporan_sungais',

            'create-users',
            'show-user',
            'show-current-user',
            'list-users',
            'edit-users',
            'delete-users',

            'create-admins',
            'show-admin',
            'show-current-admin',
            'list-admins',
            'edit-admins',
            'delete-admins',
        ];

        /**
         * Create permissions & roles for the 'user' guard
         */
        $userPermissions = collect($permissions)->map(
            fn (string $permission) => Permission::findOrCreate($permission, $userGuard)
        );

        $userRole = Role::findOrCreate('user', $userGuard);

        $userRole->syncPermissions([
            'create-sungais',
            'create-laporan_sungais',

            'show-sungai',
            'show-laporan_sungai',
            'show-current-user',

            'list-sungais',
            'list-laporan_sungais',

            'edit-laporan_sungais',

            'delete-laporan_sungais',
        ]);

        /**
         * Create permissions & roles for the 'admin' guard
         */
        $adminPermissions = collect($permissions)->map(
            fn (string $permission) => Permission::findOrCreate($permission, $adminGuard)
        );

        $adminRole = Role::findOrCreate('admin', $adminGuard);

        // All permissions
        $adminRole->syncPermissions($adminPermissions);

        // Assign roles to existing models (matching each model's guard)
        if ($user = User::first()) {
            $user->syncRoles([$userRole]);
        }

        if ($admin = Admin::first()) {
            $admin->syncRoles([$adminRole]);
        }

        // Reset cache again after seeding
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}