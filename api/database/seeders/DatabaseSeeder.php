<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            EntrepriseSeeder::class,
            UserSeeder::class,
            PointageSeeder::class,
        ]);
    }
}
