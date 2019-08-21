<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DeltaPersonFamiliesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'person_families';
        $this->filename = base_path().'/database/seeds/csvs/delta-person_families.csv';
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
        
        // DB::select("SELECT setval('person_families_id_seq', (SELECT max(id) from person_families))");
    }
}
