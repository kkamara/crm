<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * To be run once and after PermissionsTableSeeder
 */
class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create site admin role
        $adminRole = Role::create(['name' => 'admin']);

        // Assign Site admin role permissions
        $adminRole->givePermissionTo([
            'create log', 'edit log', 'delete log', 'view log',
            'create client', 'edit client', 'delete client', 'view client',
            'create user', 'edit user', 'delete user', 'view user'
        ]);

        // Create client admin role
        $clientAdminRole = Role::create(['name' => 'client_admin']);

        // Assign client admin role permissions
        $clientAdminRole->givePermissionTo([
            'create log', 'edit log', 'delete log', 'view log',
            'edit client', 'view client',
            'create user', 'edit user', 'delete user', 'view user'
        ]);

        // Create client user role
        $clientUserRole = Role::create(['name' => 'client_user']);

        // Assign Client User role permissions
        $clientUserRole->givePermissionTo([
            'create log', 'edit log', 'view log',
            'view client',
            'view user',
        ]);

    }
}
