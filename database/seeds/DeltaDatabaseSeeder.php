<?php

use Illuminate\Database\Seeder;

class DeltaDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('DatabaseSeeder');        
        
        $this->call('DeltaLovsTableSeeder');
//        $this->call('DeltaSettingLovsTableSeeder');
//        $this->call('DeltaLovTypesTableSeeder');
//        $this->call('DeltaSettingTypesTableSeeder');
        $this->call('DeltaProvincesTableSeeder');
        $this->call('DeltaCountriesTableSeeder');
        $this->call('DeltaCitiesTableSeeder');

        $this->call('DeltaJobsDataAccessTableSeeder');
        $this->call('DeltaPositionsTableSeeder');
        $this->call('DeltaPosDataAccesssTableSeeder');
        $this->call('DeltaUniDataAccesssTableSeeder');
        $this->call('DeltaPersonFamiliesTableSeeder');
        $this->call('DeltaPersonLanguagessTableSeeder');

        $this->call('DeltaAssignmentReasonsTableSeeder');
        $this->call('DeltaAssignmentsTableSeeder');
        $this->call('DeltaCompaniesTableSeeder');
        $this->call('DeltaCompanySettingsTableSeeder');
        $this->call('DeltaCostCentersTableSeeder');
        $this->call('DeltaEmployeeStatusesTableSeeder');
        $this->call('DeltaGradesTableSeeder');
        $this->call('DeltaJobsTableSeeder');
        $this->call('DeltaLocationsTableSeeder');
        $this->call('DeltaOrgStructureHierarchiesTableSeeder');
        $this->call('DeltaOrgStructuresTableSeeder');
        $this->call('DeltaPersonsTableSeeder');
        $this->call('DeltaUnitsTableSeeder');
        $this->call('DeltaUnitTypesTableSeeder');
    }
}
