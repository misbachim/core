<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DemoEmployeeStatusesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'employee_statuses';
        $this->filename = base_path().'/database/seeds/csvs/demo-employee_statuses.csv';
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
