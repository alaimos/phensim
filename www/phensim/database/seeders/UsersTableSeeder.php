<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $user = User::create(
            [
                'name'              => 'Admin Admin',
                'email'             => 'admin@phensim.it',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'affiliation'       => 'To the infinity… and beyond!',
            ]
        );
        $user->is_admin = true;
        $user->save();

        User::create(
            [
                'name'              => 'User User',
                'email'             => 'user@phensim.it',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'affiliation'       => 'Somewhere over the rainbow!',
            ]
        );
    }
}
