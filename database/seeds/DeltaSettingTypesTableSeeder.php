<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DeltaSettingTypesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'setting_types';
        $this->filename = base_path().'/database/seeds/csvs/delta-setting_types.csv';
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
