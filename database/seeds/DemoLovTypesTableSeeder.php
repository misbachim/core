<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DemoLovTypesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'lov_types';
        $this->filename = base_path().'/database/seeds/csvs/demo-lov_types.csv';
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
        // DB::statement('DELETE FROM '.$this->table.' WHERE tenant_id=1234567890');

        // parent::run();
    }
}
