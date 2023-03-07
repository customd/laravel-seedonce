<?php

use CustomD\SeedOnce\Traits\SeedOnce;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use SeedOnce;

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->call(RolesTableSeeder::class);
    }
}
