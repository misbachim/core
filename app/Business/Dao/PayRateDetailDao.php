<?php
namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;

class PayRateDetailDao
{
    /**
     * Get pay rate detail
     * @param  payRateId
     */
    public function getAll($payRateId)
    {
        return
            DB::table('pay_rate_details')
                ->select(
                    'pay_rate_details.company_id as companyId',
                    'grades.name as gradeName',
                    'grade_id as gradeId',
                    'bottom_rate as bottomRate',
                    'top_rate as topRate'
                ) ->leftJoin('grades', function ($join) {
                    $join->on('grades.id', '=', 'pay_rate_details.grade_id');
                })
                ->where([
                    ['pay_rate_details.pay_rate_id', $payRateId]
                ])
                ->get();
    }

    /**
     * Insert data pay rate details to DB
     * @param array obj
     */
    public function save($obj)
    {
        DB::table('pay_rate_details')->insert($obj);
    }

    /**
     * Delete data pay rate details from DB
     * @param payRateId
     */
    public function delete($payRateId)
    {
        DB::table('pay_rate_details')->where('pay_rate_id', $payRateId)->delete();
    }
}
