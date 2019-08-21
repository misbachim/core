<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class TestCompetencyModelsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'competency_models';
        $this->filename = base_path().'/database/seeds/csvs/test-competency_models.csv';
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

        // DB::select("SELECT setval('education_specializations_id_seq', (SELECT max(id) from education_specializations))");
    }
}
