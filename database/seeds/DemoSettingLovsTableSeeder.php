<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DemoSettingLovsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'setting_lovs';
        $this->filename = base_path().'/database/seeds/csvs/demo-setting_lovs.csv';
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

        // // Wipe the table clean before populating
        // DB::statement('DELETE FROM '.$this->table.' WHERE tenant_id=1234567890');

        // parent::run();
    }
}