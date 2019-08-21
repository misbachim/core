<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class RatingScaleDetailsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'rating_scale_details';
        $this->filename = base_path().'/database/seeds/csvs/test-rating_scale_details.csv';
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

        // DB::select("SELECT setval('rating_scale_details_id_seq', (SELECT max(id) from rating_scale_details ))");
    }
}
