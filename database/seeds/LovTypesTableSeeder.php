<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class LovTypesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'lov_types';
        $this->filename = base_path().'/database/seeds/csvs/lov_types.csv';
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
