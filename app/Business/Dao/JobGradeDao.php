<?php
namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;

class JobGradeDao
{
    /**
     * Get all grades from DB for a job.
     * @param jobId
     */
    public function getAll($jobCode)
    {
        return
            DB::table('job_grades')
                ->selectRaw(
                    'grade_code as "code",'.
                    'grades.name as "name",'.
                    'coalesce(job_grades.bottom_rate, grades.bottom_rate) as "bottomRate",'.
                    'coalesce(job_grades.mid_rate, grades.mid_rate) as "midRate",'.
                    'coalesce(job_grades.top_rate, grades.top_rate) as "topRate"'
                )
                ->join('grades', 'grades.code', '=', 'grade_code')
                ->where([
                    ['job_code', $jobCode]
                ])
                ->get();
    }

    /**
     * Insert job grades data into DB.
     * @param obj
     */
    public function save($obj)
    {
        DB::table('job_grades')->insert($obj);
    }

    /**
     * Delete job grades data from DB by id.
     * @param jobId
     */
    public function delete($jobCode)
    {
        DB::table('job_grades')->where('job_code', $jobCode)->delete();
    }
}
