<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// use Spatie\Permission\Models\Permission;
// use Spatie\Permission\Models\Role;
use App\Models\Role;
use App\Models\Permission;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role1 = Role::firstOrCreate([
            'name' => 'administrator',
            'guard_name' => 'sanctum'
        ]);

        $permissionsArray1 = Permission::all();

        $role1->syncPermissions($permissionsArray1);

        $role2 = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'sanctum'
        ]);

        $permissionsArray2 = Permission::where('name', 'LIKE', '%view%')->get();

        $role2->syncPermissions($permissionsArray2);
    }
}
