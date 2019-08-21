<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class Demo4JobsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'jobs';
        $this->filename = base_path().'/database/seeds/csvs/demo4-jobs.csv';
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

        // parent::run();
        
        // DB::select("SELECT setval('jobs_id_seq', (SELECT max(id) from jobs))");
    }
}
