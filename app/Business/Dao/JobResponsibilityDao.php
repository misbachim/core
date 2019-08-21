<?php
namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;

class JobResponsibilityDao
{
    /**
     * Get all responsibilities from DB for a job.
     * @param jobId
     */
    public function getAll($jobCode)
    {
        return
            DB::table('job_responsibilities')
                ->select(
                    'id',
                    'description',
                    'job_code as jobCode',
                    'is_appraisal as isAppraisal',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['job_code', $jobCode]
                ])
                ->get();
    }

    /**
     * Insert job responsibilities data into DB.
     * @param obj
     */
    public function save($obj)
    {
        DB::table('job_responsibilities')->insert($obj);
    }

    /**
     * Delete job grades data from DB by id.
     * @param jobId
     */
    public function delete($jobCode)
    {
        DB::table('job_responsibilities')->where('job_code', $jobCode)->delete();
    }
}
