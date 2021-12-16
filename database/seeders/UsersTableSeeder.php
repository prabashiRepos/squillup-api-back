<?php

namespace Database\Seeders;
use DB;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = \App\Models\User::create([
            'first_name' => 'Jerushan',
            'last_name' => 'Jerushan',
            'email' => 'jerushan6@gmail.com',
            'country_code' => '94',
            'phone' => '761833619',
            'password' => '12345678',
        ]);

        $user->attachRole('developer');

        $user = \App\Models\User::create([
            'first_name' => 'saba',
            'last_name' => 'saba',
            'email' => 'saba@gmail.com',
            'country_code' => '94',
            'phone' => '7712345678',
            'password' => '12345678',
        ]);

        $user->attachRole('superadmin');

        $user = \App\Models\User::create([
            'first_name' => 'parent',
            'last_name' => 'test',
            'email' => 'parent@test.com',
            'country_code' => '94',
            'phone' => '7712345679',
            'password' => '12345678',
        ]);

        $user->attachRole('parent');
    }
}
