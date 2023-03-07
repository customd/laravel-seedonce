<?php

use CustomD\SeedOnce\Traits\SeedOnce;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    use SeedOnce;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'Test User',
            'email' => 'test@customd.com',
            'password' => bcrypt('password'),
        ]);
    }
}
