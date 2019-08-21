<?php

use Illuminate\Database\Seeder;

class LawenconDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('DatabaseSeeder');        

        $this->call('LawenconCountriesTableSeeder');
        $this->call('LawenconProvincesTableSeeder');
        $this->call('LawenconCitiesTableSeeder');
        $this->call('LawenconCompaniesTableSeeder');
        $this->call('LawenconCompanySettingsTableSeeder');        
        $this->call('LawenconLovsTableSeeder');
    }
}
