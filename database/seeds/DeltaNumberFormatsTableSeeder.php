<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DeltaNumberFormatsTableSeeder extends CsvSeeder {
    public function __construct()
    {
        $this->table = 'number_formats';
        $this->filename = base_path().'/database/seeds/csvs/delta-number_formats.csv';
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
        DB::statement('DELETE FROM '.$this->table.' WHERE tenant_id=1113223891');

        parent::run();

        DB::select("SELECT  setval('employee_id_formats_id_seq', (SELECT max(id) from number_formats))");
    }

}