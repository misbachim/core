<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DeltaOrgStructureHierarchiesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'org_structure_hierarchies';
        $this->filename = base_path().'/database/seeds/csvs/delta-org_structure_hierarchies.csv';
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Recommended when importing larger CSVs
        DB::disableQueryLog();

        DB::statement('DELETE FROM '.$this->table.' WHERE tenant_id=12345 OR tenant_id=1113223891');

        // parent::run(); 
    }
}
