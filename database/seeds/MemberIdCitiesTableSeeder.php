<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class MemberIdCitiesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'cities';
        $this->filename = base_path().'/database/seeds/csvs/memberid/cities.csv';
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

        DB::statement('DELETE FROM '.$this->table.' WHERE company_id=1082115978');

        parent::run();

        DB::select("SELECT setval('cities_id_seq', (SELECT max(id) from ".$this->table."))");
    }
}