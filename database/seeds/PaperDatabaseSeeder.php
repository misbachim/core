<?php

use Illuminate\Database\Seeder;

class PaperDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call('DatabaseSeeder');        
        
        $this->call('PaperCompaniesTableSeeder');
        $this->call('PaperCompanySettingsTableSeeder');
        $this->call('PaperLovsTableSeeder');
        $this->call('PaperCountriesTableSeeder');
        $this->call('PaperProvincesTableSeeder');
        $this->call('PaperCitiesTableSeeder');
    
        $this->call('PaperJobsDataAccessTableSeeder');
        $this->call('PaperLocationsTableSeeder');
        

        
        // $this->call('PaperPositionsTableSeeder');
        // $this->call('PaperPosDataAccesssTableSeeder');
        // $this->call('PaperUniDataAccesssTableSeeder');
        // $this->call('PaperPersonFamiliesTableSeeder');
        // $this->call('PaperPersonLanguagessTableSeeder');

        // $this->call('PaperAssignmentReasonsTableSeeder');
        // $this->call('PaperAssignmentsTableSeeder');
        // $this->call('PaperCostCentersTableSeeder');
        // $this->call('PaperEmployeeStatusesTableSeeder');
        // $this->call('PaperGradesTableSeeder');
        // $this->call('PaperJobsTableSeeder');
        // $this->call('PaperOrgStructureHierarchiesTableSeeder');
        // $this->call('PaperOrgStructuresTableSeeder');
        // $this->call('PaperPersonsTableSeeder');
        // $this->call('PaperUnitsTableSeeder');
        // $this->call('PaperUnitTypesTableSeeder');
    }
}
