<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class LawenconCompanySettingsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'company_settings';
        $this->filename = base_path().'/database/seeds/csvs/lawencon/company_settings.csv';
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

        DB::statement('DELETE FROM '.$this->table.' WHERE company_id=1325836972');

        parent::run();
    }
}