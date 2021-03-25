<?php

namespace Database\Seeders;

use App\Models\Node;
use App\Models\Organism;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call(
            [
                UsersTableSeeder::class,
                // OrganismsTableSeeder::class,
            ]
        );
    }
}
