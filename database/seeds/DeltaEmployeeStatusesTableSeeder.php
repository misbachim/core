<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DeltaEmployeeStatusesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'employee_statuses';
        $this->filename = base_path().'/database/seeds/csvs/delta-employee_statuses.csv';
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

        DB::statement('DELETE FROM '.$this->table.' WHERE tenant_id=12345 OR tenant_id=1113223891');

        // parent::run();        

        // DB::select("SELECT setval('employee_types_id_seq', (SELECT max(id) from employee_statuses))");
    }
}
