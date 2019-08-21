<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

class CodeceptionTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        // Jangan dirubah ya
        $this->table = '';
        $this->filename = '';
        $this->hashable = '';
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Contoh isian array
         *     $arr = [
         *             countries' => ['countries_id_seq', 'countries.csv'],
         *             ];
         * @var [type]
         */
        $arr = [
            // 'countries' => ['countries_id_seq', 'countries.csv'],
            // 'provinces' => ['provinces_id_seq', 'provinces.csv'],
            // 'cities' => ['cities_id_seq', 'cities.csv'],
            // 'grades' => ['grades_id_seq', 'grades.csv'],
            'jobs' => ['jobs_id_seq', 'jobs.csv'],
            'lovs' => ['', 'lovs.csv'],
            'positions' => ['', 'positions.csv'],
            'units' => ['units_id_seq', 'units.csv'],
            'locations' => ['locations_id_seq', 'locations.csv'],
            // 'employee_statuses' => ['', 'employee_statuses.csv'],
            // 'assignments' => ['assignments_id_seq', 'assignments.csv'],
            'data_access_pos' => ['', 'pos_data_access.csv'],
            'data_access_uni' => ['', 'uni_data_access.csv'],
            // 'persons' => ['persons_id_seq', 'persons.csv'],
            'data_access_job' => ['', 'job_data_access.csv'],
            'workflows' => ['workflows_id_seq', 'workflows.csv'],
            'autonumbers' => ['autonumbers_id_seq', 'gard-autonumbers.csv'],
        ];

        // Recommended when importing larger CSVs
        DB::disableQueryLog();

        foreach ($arr as $table => $adata) {
            $this->table = $table;
            $this->filename = base_path() . '/database/seeds/csvs/codeception/' . $adata[1];

            DB::table($this->table)->truncate();
            parent::run();

            if ($adata[0])
                DB::select("SELECT setval('" . $adata[0] . "', (SELECT max(id) from " . $table . "))");
        }
    }
}