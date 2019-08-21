<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DeltaSettingLovsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'setting_lovs';
        $this->filename = base_path().'/database/seeds/csvs/delta-setting_lovs.csv';
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

        // parent::run();
    }
}
