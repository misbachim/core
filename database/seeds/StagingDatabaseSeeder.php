<?php

use Illuminate\Database\Seeder;

class StagingDatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call('DatabaseSeeder');
        $this->call('StagingMtModulesTableSeeder');
        $this->call('StagingMtAttributesTableSeeder');
        $this->call('StagingJobDataAccessTableSeeder');
        $this->call('StagingPosDataAccessTableSeeder');
        $this->call('StagingUniDataAccessTableSeeder');
        $this->call('LovTypesTableSeeder');
        $this->call('TestLovsTableSeeder');
    }
}
