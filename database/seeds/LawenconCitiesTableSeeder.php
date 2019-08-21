<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class LawenconCitiesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'cities';
        $this->filename = base_path().'/database/seeds/csvs/lawencon/cities.csv';
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

        DB::statement('DELETE FROM '.$this->table.' WHERE company_id=1325836972');

        parent::run();

        DB::select("SELECT setval('cities_id_seq', (SELECT max(id) from ".$this->table."))");
    }
}