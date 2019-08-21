<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class TestCompetencyGroupsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'competency_groups';
        $this->filename = base_path().'/database/seeds/csvs/test-competency_groups.csv';
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
