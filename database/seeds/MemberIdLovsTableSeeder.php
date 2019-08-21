<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class MemberIdLovsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'lovs';
        $this->filename = base_path().'/database/seeds/csvs/memberid/lovs.csv';
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

        DB::statement('DELETE FROM '.$this->table.' WHERE company_id=1082115978');

        parent::run();
    }
}
