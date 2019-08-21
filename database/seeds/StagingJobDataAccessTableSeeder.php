<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class StagingJobDataAccessTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'data_access_job';
        $this->filename = base_path() . '/database/seeds/csvs/staging-job_data_access.csv';
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
