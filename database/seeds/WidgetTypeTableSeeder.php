<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class WidgetTypeTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'widget_type';
        $this->filename = base_path().'/database/seeds/csvs/widget_type.csv';
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::disableQueryLog();

        DB::table($this->table)->truncate();

        parent::run();
    }
}
