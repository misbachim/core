<?php

use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('DatabaseSeeder');
        $this->call('TestCompaniesTableSeeder');
        $this->call('TestCompanySettingsTableSeeder');
        $this->call('TestCountriesTableSeeder');
        $this->call('TestProvincesTableSeeder');
        $this->call('TestEducationSpecializationsTableSeeder');
        $this->call('TestEducationInstitutionsTableSeeder');
        $this->call('TestCompetencyModelsTableSeeder');
        $this->call('TestLovsTableSeeder');
        $this->call('TestCredentialsTableSeeder');
    }
}
