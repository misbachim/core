<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class TestProvincesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'provinces';
        $this->filename = base_path().'/database/seeds/csvs/test-provinces.csv';
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

        // Wipe the table clean before populating
        DB::table($this->table)->truncate();

        parent::run();

        // Set ID sequence to the correct value.
        DB::select("SELECT setval('provinces_id_seq', (SELECT max(id) + 1 FROM provinces))");
    }
}
