<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class PaperCompaniesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'companies';
        $this->filename = base_path().'/database/seeds/csvs/paper-companies.csv';
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

        DB::statement('DELETE FROM '.$this->table.' WHERE tenant_id=1735757575');

        parent::run();
    }
}
