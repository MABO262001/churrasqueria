<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $Master             = Role::create(['name' => 'Master']);
        $Administrador      = Role::create(['name' => 'Administrador']);
        $Mesero             = Role::create(['name' => 'Mesero']);
        $Cliente            = Role::create(['name' => 'Cliente']);

    }
}
