<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class DemoAssignmentReasonsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'assignment_reasons';
        $this->filename = base_path().'/database/seeds/csvs/demo-assignment_reasons.csv';
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
        
       DB::select("SELECT setval('assignment_reasons_id_seq', (SELECT max(id) from assignment_reasons))");
    }
}
