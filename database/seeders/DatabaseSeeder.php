<?php

namespace Database\Seeders;

use EcomForYou\Installer\Database\Seeders\DatabaseSeeder as BagistoDatabaseSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(BagistoDatabaseSeeder::class);
    }
}
