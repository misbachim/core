<?php

use Illuminate\Database\Seeder;

class MemberIdDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('DatabaseSeeder');        

        $this->call('MemberIdCountriesTableSeeder');
        $this->call('MemberIdProvincesTableSeeder');
        $this->call('MemberIdCitiesTableSeeder');
        $this->call('MemberIdCompaniesTableSeeder');
        $this->call('MemberIdCompanySettingsTableSeeder');        
        $this->call('MemberIdLovsTableSeeder');
    }
}
