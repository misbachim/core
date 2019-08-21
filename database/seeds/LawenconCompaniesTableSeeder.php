<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class LawenconCompaniesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'companies';
        $this->filename = base_path().'/database/seeds/csvs/lawencon/companies.csv';
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

        DB::statement('DELETE FROM '.$this->table.' WHERE id=1325836972');

        parent::run();
    }
}