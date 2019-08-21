<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DeltaUnitsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'units';
        $this->filename = base_path().'/database/seeds/csvs/delta-units.csv';
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
        
        // DB::select("SELECT setval('units_id_seq', (SELECT max(id) from units))");
    }
}
