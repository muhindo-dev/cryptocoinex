<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Sets up the RBAC role/permission matrix and syncs every existing user's
 * Spatie role from their legacy `role` column.
 */
class RbacSeeder extends Seeder
{
    private const PERMISSIONS = [
        'access-admin', 'manage-users', 'manage-assets',
        'manage-students', 'manage-settings', 'manage-tournaments', 'view-activity',
    ];

    private const MATRIX = [
        'admin' => '*',
        'instructor' => ['access-admin', 'manage-assets', 'manage-students', 'manage-tournaments', 'view-activity'],
        'moderator' => ['access-admin', 'manage-students', 'view-activity'],
        'student' => [],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        foreach (self::MATRIX as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms === '*' ? self::PERMISSIONS : $perms);
        }

        // Sync existing users from their legacy role column.
        $synced = 0;
        foreach (User::all() as $user) {
            $user->syncSpatieRole();
            $synced++;
        }

        $this->command->info("RbacSeeder: roles+permissions set, {$synced} users synced.");
    }
}
