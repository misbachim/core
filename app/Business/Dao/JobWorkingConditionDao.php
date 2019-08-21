<?php
namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;

class JobWorkingConditionDao
{
    /**
     * Get all working condition from DB for a job.
     * @param jobId
     */
    public function getAll($jobCode)
    {
        return
            DB::table('job_working_conditions')
                ->select(
                    'id',
                    'description',
                    'job_code as jobCode',
                    'is_essential as isEssential',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['job_code', $jobCode]
                ])
                ->get();
    }

    /**
     * Insert job working condition data into DB.
     * @param obj
     */
    public function save($obj)
    {
        DB::table('job_working_conditions')->insert($obj);
    }

    /**
     * Delete job working condition data from DB by id.
     * @param jobId
     */
    public function delete($jobCode)
    {
        DB::table('job_working_conditions')->where('job_code', $jobCode)->delete();
    }
}
