<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class TestCompetenciesTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'competencies';
        $this->filename = base_path().'/database/seeds/csvs/test-competencies.csv';
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::disableQueryLog();

        DB::table($this->table)->truncate();

        parent::run();
    }
}
