<?php
namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;

class PositionResponsibilityDao
{
    /**
     * Get all responsibilities from DB for a position.
     * @param jobId
     */
    public function getAll($positionCode)
    {
        return
            DB::table('position_responsibilities')
                ->select(
                    'id',
                    'description',
                    'position_code as positionCode',
                    'is_appraisal as isAppraisal',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['position_code', $positionCode]
                ])
                ->get();
    }

    /**
     * Insert position responsibilities data into DB.
     * @param obj
     */
    public function save($obj)
    {
        DB::table('position_responsibilities')->insert($obj);
    }

    /**
     * Delete position responsibilities data from DB by id.
     * @param positionCode
     */
    public function delete($positionCode)
    {
        DB::table('position_responsibilities')->where('position_code', $positionCode)->delete();
    }
}
