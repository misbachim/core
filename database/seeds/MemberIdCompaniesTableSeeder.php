<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class MemberIdCompaniesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'companies';
        $this->filename = base_path().'/database/seeds/csvs/memberid/companies.csv';
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

        DB::statement('DELETE FROM '.$this->table.' WHERE id=1082115978');

        parent::run();
    }
}