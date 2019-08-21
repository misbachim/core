<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DeltaPersonsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'persons';
        $this->filename = base_path().'/database/seeds/csvs/delta-persons.csv';
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
        
        // DB::select("SELECT setval('persons_id_seq', (SELECT max(id) from persons))");
    }
}
