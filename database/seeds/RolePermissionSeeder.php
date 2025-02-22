<?php

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Class RolePermissionSeeder.
 *
 * @see https://spatie.be/docs/laravel-permission/v5/basic-usage/multiple-guards
 *
 * @package App\Database\Seeds
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Enable these options if you need same role and other permission for User
         * Else, please follow the below steps for admin guard
         */

        // Create Roles and Permissions for Admin
        // $roleSuperAdmin = Role::create(['name' => 'superadmin']);
        // $roleAdmin = Role::create(['name' => 'admin']);
        // $roleEditor = Role::create(['name' => 'editor']);
        // $roleUser = Role::create(['name' => 'user']);


        // Permission List as array for Admin and User
        $permissions = [
            // Admin Permissions
            [
                'group_name' => 'dashboard',
                'permissions' => [
                    'dashboard.view',
                    'dashboard.edit',
                ]
            ],
            [
                'group_name' => 'admin',
                'permissions' => [
                    'admin.create',
                    'admin.view',
                    'admin.edit',
                    'admin.delete',
                    'admin.approve',
                ]
            ],
            [
                'group_name' => 'role',
                'permissions' => [
                    'role.create',
                    'role.view',
                    'role.edit',
                    'role.delete',
                    'role.approve',
                ]
            ],
            [
                'group_name' => 'profile',
                'permissions' => [
                    'profile.view',
                    'profile.edit',
                    'profile.delete',
                    'profile.update',
                ]
            ],
            // User Permissions
            [
                'group_name' => 'user',
                'permissions' => [
                    'user.create',
                    'user.view',
                    'user.edit',
                    'user.delete',
                    'user.approve',
                ]
            ],
            [
                'group_name' => 'user_profile',
                'permissions' => [
                    'user_profile.view',
                    'user_profile.edit',
                    'user_profile.update',
                ]
            ],
        ];


        // Create and Assign Permissions for Admin
        $admin = Admin::where('username', 'superadmin')->first();
        $roleSuperAdmin = $this->maybeCreateSuperAdminRole($admin);

        // Create and Assign Admin Permissions
        foreach ($permissions as $permissionGroup) {
            $permissionGroupName = $permissionGroup['group_name'];
            foreach ($permissionGroup['permissions'] as $permissionName) {
                $permissionExist = Permission::where('name', $permissionName)->first();
                if (is_null($permissionExist)) {
                    $permission = Permission::create([
                        'name' => $permissionName,
                        'group_name' => $permissionGroupName,
                        'guard_name' => 'admin' // Guard for Admin
                    ]);
                    $roleSuperAdmin->givePermissionTo($permission);
                    $permission->assignRole($roleSuperAdmin);
                }
            }
        }

        // Assign super admin role permission to superadmin user
        if ($admin) {
            $admin->assignRole($roleSuperAdmin);
        }

        // Create and Assign Permissions for User
        $roleUser = Role::create(['name' => 'user', 'guard_name' => 'web']);  // Role for User

        foreach ($permissions as $permissionGroup) {
            $permissionGroupName = $permissionGroup['group_name'];
            foreach ($permissionGroup['permissions'] as $permissionName) {
                if (str_contains($permissionName, 'user.')) {  // Check for User-specific permissions
                    $permissionExist = Permission::where('name', $permissionName)->first();
                    if (is_null($permissionExist)) {
                        $permission = Permission::create([
                            'name' => $permissionName,
                            'group_name' => $permissionGroupName,
                            'guard_name' => 'web' // Guard for User
                        ]);
                        $roleUser->givePermissionTo($permission);
                        $permission->assignRole($roleUser);
                    }
                }
            }
        }

        // Assign role to some users (just an example)
        $user = User::where('email', 'user@example.com')->first();
        if ($user) {
            $user->assignRole($roleUser);
        }
    }

    /**
     * Helper method to create Super Admin Role if it doesn't exist
     */
    private function maybeCreateSuperAdminRole($admin): Role
    {
        if (is_null($admin)) {
            $roleSuperAdmin = Role::create(['name' => 'superadmin', 'guard_name' => 'admin']);
        } else {
            $roleSuperAdmin = Role::where('name', 'superadmin')->where('guard_name', 'admin')->first();
        }

        if (is_null($roleSuperAdmin)) {
            $roleSuperAdmin = Role::create(['name' => 'superadmin', 'guard_name' => 'admin']);
        }

        return $roleSuperAdmin;
    }
}
