<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

/**
 * To be run once and before RolesTableSeeder
 */
class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Log permissions
        Permission::create(['name' => 'create log']);
        Permission::create(['name' => 'edit log']);
        Permission::create(['name' => 'delete log']);
        Permission::create(['name' => 'view log']);

        // // Client permissions
        Permission::create(['name' => 'create client']);
        Permission::create(['name' => 'edit client']);
        Permission::create(['name' => 'delete client']);
        Permission::create(['name' => 'view client']);

        // // User permissions
        Permission::create(['name' => 'create user']);
        Permission::create(['name' => 'edit user']);
        Permission::create(['name' => 'delete user']);
        Permission::create(['name' => 'view user']);
    }
}
