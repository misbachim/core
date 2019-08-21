<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class StagingPosDataAccessTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'data_access_pos';
        $this->filename = base_path() . '/database/seeds/csvs/staging-pos_data_access.csv';
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
