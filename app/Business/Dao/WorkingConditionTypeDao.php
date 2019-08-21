<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkingConditionTypeDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Working Condition Type in ONE company
     */
    public function getAll()
    {
        return
            DB::table('working_condition_types')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]
                ])
                ->orderBy('eff_end', 'desc')
                ->orderBy('id', 'asc')
                ->get();
    }

    /**
     * Get All Active Working Condition Type in One company
     */
    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('working_condition_types')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()]
                ])
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get All InActive Working Condition Type in One company
     */
    public function getAllInActive()
    {
        return
        DB::table('working_condition_types')
            ->select(
                'id',
                'code',
                'name',
                'description',
                'eff_begin as effBegin',
                'eff_end as effEnd'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['eff_end', '<', Carbon::now()]
                ])
            ->get();
    }

    /**
     * Get one Working Condition Type based on working condition type code
     * @param  tenantId, companyId
     */
    public function getOne($workingConditionTypeCode)
    {
        return
            DB::table('working_condition_types')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $workingConditionTypeCode]
                ])
                ->first();
    }

    /**
     * Insert data Working Condition Type to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'working_condition_types', $obj);

        return DB::table('working_condition_types')->insertGetId($obj);
    }

    /**
     * Update data Working Condition Type to DB
     * @param  array obj, tenantId, companyId, workingConditionTypeId
     */
    public function update($workingConditionTypeId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'working_condition_types', $obj);

        DB::table('working_condition_types')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['id', $workingConditionTypeId]
        ])
        ->update($obj);
    }

    public function isCodeDuplicate(string $code)
    {
        return (DB::table('working_condition_types')->where([
                ['code', $code],
                ['company_id', $this->requester->getCompanyId()],
                ['tenant_id', $this->requester->getTenantId()]
            ])->count() > 0);
    }

    public function getTotalRows()
    {
        return
            DB::table('working_condition_types')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])->count();
    }
}
