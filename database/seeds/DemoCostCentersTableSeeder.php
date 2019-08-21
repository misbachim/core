<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DemoCostCentersTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'cost_centers';
        $this->filename = base_path().'/database/seeds/csvs/demo-cost_centers.csv';
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
        DB::statement('DELETE FROM '.$this->table.' WHERE tenant_id=1234567890');

        parent::run();
    }
}
