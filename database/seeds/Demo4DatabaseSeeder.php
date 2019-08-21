<?php

use Illuminate\Database\Seeder;

class Demo4DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('DatabaseSeeder');        
        
        $this->call('Demo4LovsTableSeeder');
        $this->call('Demo4CompaniesTableSeeder');
        $this->call('Demo4CompanySettingsTableSeeder');
        $this->call('Demo4CountriesTableSeeder');
        $this->call('Demo4ProvincesTableSeeder');
        $this->call('Demo4CitiesTableSeeder');
        $this->call('Demo4PersonsTableSeeder');
        $this->call('Demo4PositionsTableSeeder');
        $this->call('Demo4UnitsTableSeeder');
        $this->call('Demo4JobsTableSeeder');
        $this->call('Demo4EmployeeStatusesTableSeeder');
        $this->call('Demo4AssignmentsTableSeeder');
    }
}
