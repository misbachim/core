<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayRateDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all pay rate  in One Company
     * @param  tenantId, companyId
     */
    public function getAll($tenantId, $companyId)
    {
        return
            DB::table('pay_rates')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', $tenantId],
                    ['company_id', $companyId],
                    ['is_deleted', 0]
                ])
                ->get();
    }

    /**
     * Get one pay rate based on pay rate id
     * @param  tenantId, companyId, payRateId
     */
    public function getOne($tenantId, $companyId, $payRateId)
    {
        return
            DB::table('pay_rates')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', $tenantId],
                    ['company_id', $companyId],
                    ['id', $payRateId],
                    ['is_deleted', 0]
                ])
                ->first();
    }

    /**
     * Insert data pay rate to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('pay_rates')-> insertGetId($obj);
    }

    /**
     * Update data pay rates to DB
     * @param  array obj, tenantId, companyId, payRateId
     */
    public function update($tenantId, $companyId, $payRateId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('pay_rates')
        ->where([
            ['tenant_id', $tenantId],
            ['company_id', $companyId],
            ['id', $payRateId]
        ])
        ->update($obj);
    }
}
