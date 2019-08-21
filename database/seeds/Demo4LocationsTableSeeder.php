<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class Demo4LocationsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'locations';
        $this->filename = base_path().'/database/seeds/csvs/demo4-locations.csv';
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
        DB::statement('DELETE FROM '.$this->table.' WHERE tenant_id=1000000001');

        parent::run();
    }
}
