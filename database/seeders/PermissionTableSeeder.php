<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// use Spatie\Permission\Models\Permission;
use App\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = [
            [
                'name' => 'create user',
                'guard_name' => 'sanctum'
            ],
            [
                'name' => 'update user',
                'guard_name' => 'sanctum'
            ],
            [
                'name' => 'view user',
                'guard_name' => 'sanctum'
            ],
            [
                'name' => 'delete user',
                'guard_name' => 'sanctum'
            ],
            [
                'name' => 'create role',
                'guard_name' => 'sanctum'
            ],
            [
                'name' => 'update role',
                'guard_name' => 'sanctum'
            ],
            [
                'name' => 'view role',
                'guard_name' => 'sanctum'
            ],
            [
                'name' => 'delete role',
                'guard_name' => 'sanctum'
            ],
            [
                'name' => 'create permission',
                'guard_name' => 'sanctum'
            ],
            [
                'name' => 'update permission',
                'guard_name' => 'sanctum'
            ],
            [
                'name' => 'view permission',
                'guard_name' => 'sanctum'
            ],
            [
                'name' => 'delete permission',
                'guard_name' => 'sanctum'
            ],
            [
                'name' => 'view audit log',
                'guard_name' => 'sanctum'
            ],
        ];

        foreach ($records as $record) {
            Permission::firstOrCreate($record);
        }
    }
}
