<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@test.go.tz'],
            [
                'name' => 'Administrator',
                'email' => 'admin@test.go.tz',
                'username' => 'admin',
                'password' => Hash::make('Pod12345'),
            ],
        );

        $role = Role::where("name", "administrator")->get();

        $user->syncRoles([$role]);
    }
}
