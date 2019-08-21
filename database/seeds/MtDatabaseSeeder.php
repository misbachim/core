<?php

use Illuminate\Database\Seeder;

class MtDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('MtModulesTableSeeder');
        $this->call('MtAttributesTableSeeder');
    }
}
