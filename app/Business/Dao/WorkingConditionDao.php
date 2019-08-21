<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkingConditionDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Working Condition in ONE company
     */
    public function getAll()
    {
        return
            DB::table('working_conditions')
                ->select(
                    'working_conditions.id',
                    'working_conditions.working_condition_type_code as workingConditionTypeCode',
                    'working_condition_types.name as workingConditionTypeName',
                    'working_conditions.code',
                    'working_conditions.name',
                    'working_conditions.description',
                    'working_conditions.eff_begin as effBegin',
                    'working_conditions.eff_end as effEnd'
                )
                ->leftJoin('working_condition_types', 'working_condition_types.code', '=', 'working_conditions.working_condition_type_code')
                ->where([
                    ['working_conditions.tenant_id', $this->requester->getTenantId()],
                    ['working_conditions.company_id', $this->requester->getCompanyId()]
                ])
                ->orderBy('working_conditions.eff_end', 'desc')
                ->orderBy('working_conditions.id', 'asc')
                ->get();
    }

    /**
     * Get All Active Working Condition in One company
     */
    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('working_conditions')
            ->select(
                'working_conditions.id',
                'working_conditions.working_condition_type_code as workingConditionTypeCode',
                'working_condition_types.name as workingConditionTypeName',
                'working_conditions.code',
                'working_conditions.name',
                'working_conditions.description',
                'working_conditions.eff_begin as effBegin',
                'working_conditions.eff_end as effEnd'
            )
            ->leftJoin('working_condition_types', 'working_condition_types.code', '=', 'working_conditions.working_condition_type_code')
            ->where([
                ['working_conditions.tenant_id', $this->requester->getTenantId()],
                ['working_conditions.company_id', $this->requester->getCompanyId()],
                ['working_conditions.eff_begin', '<=', Carbon::now()],
                ['working_conditions.eff_end', '>=', Carbon::now()]
            ])
            ->orderByRaw('working_conditions.eff_end DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Get All InActive Working Condition in One company
     */
    public function getAllInActive()
    {
        return
            DB::table('working_conditions')
            ->select(
                'working_conditions.id',
                'working_conditions.working_condition_type_code as workingConditionTypeCode',
                'working_condition_types.name as workingConditionTypeName',
                'working_conditions.code',
                'working_conditions.name',
                'working_conditions.description',
                'working_conditions.eff_begin as effBegin',
                'working_conditions.eff_end as effEnd'
            )
            ->leftJoin('working_condition_types', 'working_condition_types.code', '=', 'working_conditions.working_condition_type_code')
            ->where([
                ['working_conditions.tenant_id', $this->requester->getTenantId()],
                ['working_conditions.company_id', $this->requester->getCompanyId()],
                ['working_conditions.eff_end', '<', Carbon::now()]
                ])
            ->get();
    }
    
    /**
     * Get one Working Condition based on working condition code
     * @param  tenantId, companyId
     */
    public function getOne($workingConditionCode)
    {
        return
            DB::table('working_conditions')
                ->select(
                    'working_conditions.id',
                    'working_conditions.working_condition_type_code as workingConditionTypeCode',
                    'working_condition_types.name as workingConditionTypeName',
                    'working_conditions.code',
                    'working_conditions.name',
                    'working_conditions.description',
                    'working_conditions.eff_begin as effBegin',
                    'working_conditions.eff_end as effEnd'
                )
                ->leftJoin('working_condition_types', 'working_condition_types.code', '=', 'working_conditions.working_condition_type_code')
                ->where([
                    ['working_conditions.tenant_id', $this->requester->getTenantId()],
                    ['working_conditions.company_id', $this->requester->getCompanyId()],
                    ['working_conditions.code', $workingConditionCode]
                ])
                ->first();
    }

    /**
     * Insert data Working Condition to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'working_conditions', $obj);

        return DB::table('working_conditions')->insertGetId($obj);
    }

    /**
     * Update data Working Condition to DB
     * @param  array obj, tenantId, companyId, workingConditionId
     */
    public function update($workingConditionId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'working_conditions', $obj);

        DB::table('working_conditions')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['id', $workingConditionId]
        ])
        ->update($obj);
    }

    public function isCodeDuplicate(string $code)
    {
        return (DB::table('working_conditions')->where([
                ['code', $code],
                ['company_id', $this->requester->getCompanyId()],
                ['tenant_id', $this->requester->getTenantId()]
            ])->count() > 0);
    }

    public function getTotalRows()
    {
        return
            DB::table('working_conditions')
            ->leftJoin('working_condition_types', 'working_condition_types.code', '=', 'working_conditions.working_condition_type_code')
            ->where([
                ['working_conditions.tenant_id', $this->requester->getTenantId()],
                ['working_conditions.company_id', $this->requester->getCompanyId()]
            ])->count();
    }
}
