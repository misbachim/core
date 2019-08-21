<?php
namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;

class PositionGradeDao
{
    /**
     * Get all grades from DB for a position.
     * @param positionId
     */
    public function getAll($positionCode)
    {
        return
            DB::table('position_grades')
                ->selectRaw(
                    'grade_code as "code",'.
                    'grades.name as "name",'.
                    'coalesce(position_grades.bottom_rate, grades.bottom_rate) as "bottomRate",'.
                    'coalesce(position_grades.mid_rate,grades.mid_rate) as "midRate",'.
                    'coalesce(position_grades.top_rate, grades.top_rate) as "topRate"'
                )
                ->join('grades', 'grades.code', '=', 'grade_code')
                ->where([
                    ['position_code', $positionCode]
                ])
                ->get();
    }

    /**
     * Insert position grades data into DB.
     * @param obj
     */
    public function save($obj)
    {
        DB::table('position_grades')->insert($obj);
    }

    /**
     * Delete position grades data from DB by id.
     * @param positionId
     */
    public function delete($positionCode)
    {
        DB::table('position_grades')->where('position_code', $positionCode)->delete();
    }
}
