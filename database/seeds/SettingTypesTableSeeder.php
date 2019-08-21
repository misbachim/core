<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class SettingTypesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'setting_types';
        $this->filename = base_path().'/database/seeds/csvs/setting_types.csv';
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
