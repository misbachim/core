<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class MtAttributesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'mt_attributes';
        $this->filename = base_path().'/database/seeds/csvs/mt_attributes.csv';
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
