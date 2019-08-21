<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class ReportTemplatesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'report_templates';
        $this->filename = base_path() . '/database/seeds/csvs/report_templates.csv';
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

        DB::select("SELECT setval('report_templates_id_seq', (SELECT max(id) from report_templates ))");
    }
}
