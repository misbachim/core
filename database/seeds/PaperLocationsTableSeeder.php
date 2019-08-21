<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class PaperLocationsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'locations';
        $this->filename = base_path().'/database/seeds/csvs/paper-locations.csv';
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

        DB::statement('DELETE FROM '.$this->table.' WHERE tenant_id=1735757575');

        parent::run();

        DB::select("SELECT setval('locations_id_seq', (SELECT max(id) from locations))");
    }
}
