<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class RatingScalesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'rating_scales';
        $this->filename = base_path().'/database/seeds/csvs/test-rating_scales.csv';
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

        DB::select("SELECT setval('rating_scales_id_seq', (SELECT max(id) from rating_scales ))");
    }
}
