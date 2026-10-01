<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SchoolSeeder::class,
            UserSeeder::class,
            QuranMasterSeeder::class,
            QuranReadingLevelSeeder::class,
            QuranTargetTemplateSeeder::class,
        ]);
    }
}
