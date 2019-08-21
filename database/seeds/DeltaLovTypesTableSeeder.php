<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DeltaLovTypesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'lov_types';
        $this->filename = base_path().'/database/seeds/csvs/delta-lov_types.csv';
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
