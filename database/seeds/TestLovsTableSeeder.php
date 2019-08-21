<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class TestLovsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'lovs';
        $this->filename = base_path().'/database/seeds/csvs/test-lovs.csv';
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
    }
}
