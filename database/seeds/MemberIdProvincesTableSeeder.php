<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class MemberIdProvincesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'provinces';
        $this->filename = base_path().'/database/seeds/csvs/memberid/provinces.csv';
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

        DB::select("SELECT setval('provinces_id_seq', (SELECT max(id) from ".$this->table."))");
    }
}