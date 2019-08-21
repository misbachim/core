<?php

use Illuminate\Database\Seeder;

class DemoDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('DatabaseSeeder');        
        
        $this->call('DemoLovsTableSeeder');
        $this->call('DemoSettingLovsTableSeeder');
        $this->call('DemoLovTypesTableSeeder');
        $this->call('DemoSettingTypesTableSeeder');
        $this->call('DemoProvincesTableSeeder');
        $this->call('DemoCountriesTableSeeder');
        $this->call('DemoCitiesTableSeeder');

        $this->call('DemoJobsDataAccessTableSeeder');
        $this->call('DemoPositionsTableSeeder');
        $this->call('DemoPosDataAccesssTableSeeder');
        $this->call('DemoUniDataAccesssTableSeeder');
        $this->call('DemoPersonFamiliesTableSeeder');
        $this->call('DemoPersonLanguagessTableSeeder');


        $this->call('DemoAssignmentReasonsTableSeeder');
        $this->call('DemoAssignmentsTableSeeder');
        $this->call('DemoCompaniesTableSeeder');
        $this->call('DemoCompanySettingsTableSeeder');
        $this->call('DemoCostCentersTableSeeder');
        $this->call('DemoEmployeeStatusesTableSeeder');
        $this->call('DemoGradesTableSeeder');
        $this->call('DemoJobsTableSeeder');
        $this->call('DemoLocationsTableSeeder');
        $this->call('DemoOrgStructureHierarchiesTableSeeder');
        $this->call('DemoOrgStructuresTableSeeder');
        $this->call('DemoPersonsTableSeeder');
        $this->call('DemoUnitsTableSeeder');
        $this->call('DemoUnitTypesTableSeeder');
    }
}
