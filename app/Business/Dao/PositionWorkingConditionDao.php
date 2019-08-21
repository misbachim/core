<?php
namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;

class PositionWorkingConditionDao
{
    /**
     * Get all working conditions from DB for a position.
     * @param jobId
     */
    public function getAll($positionCode)
    {
        return
            DB::table('position_working_conditions')
                ->select(
                    'id',
                    'description',
                    'position_code as positionCode',
                    'is_essential as isEssential',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['position_code', $positionCode]
                ])
                ->get();
    }

    /**
     * Insert position working conditions data into DB.
     * @param obj
     */
    public function save($obj)
    {
        DB::table('position_working_conditions')->insert($obj);
    }

    /**
     * Delete position working conditions data from DB by id.
     * @param positionCode
     */
    public function delete($positionCode)
    {
        DB::table('position_working_conditions')->where('position_code', $positionCode)->delete();
    }
}
