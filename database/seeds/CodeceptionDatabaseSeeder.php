<?php

use Illuminate\Database\Seeder;

class CodeceptionDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('DatabaseSeeder');
        $this->call('CodeceptionTableSeeder');
    }
}
