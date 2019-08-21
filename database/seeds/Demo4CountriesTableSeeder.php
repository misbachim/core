<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class Demo4CountriesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'countries';
        $this->filename = base_path().'/database/seeds/csvs/demo4-countries.csv';
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
