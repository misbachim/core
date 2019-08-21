<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResponsibilityDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Responsibility in ONE company
     */
    public function getAll()
    {
        return
            DB::table('responsibilities')
                ->select(
                    'responsibilities.id',
                    'responsibilities.responsibility_group_code as responsibilityGroupCode',
                    'responsibility_groups.name as responsibilityGroupName',
                    'responsibilities.code',
                    'responsibilities.name',
                    'responsibilities.description',
                    'responsibilities.eff_begin as effBegin',
                    'responsibilities.eff_end as effEnd'
                )
                ->leftJoin('responsibility_groups', 'responsibility_groups.code', '=', 'responsibilities.responsibility_group_code')
                ->where([
                    ['responsibilities.tenant_id', $this->requester->getTenantId()],
                    ['responsibilities.company_id', $this->requester->getCompanyId()]
                ])
                ->orderBy('responsibilities.eff_end', 'desc')
                ->orderBy('responsibilities.id', 'asc')
                ->get();
    }

    /**
     * Get all Active Responsibility  in ONE company 
     */

    public function getAllActive($offset = null, $limit = null)
    {
        return
                DB::table('responsibilities')
                ->select(
                    'responsibilities.id',
                    'responsibilities.responsibility_group_code as responsibilityGroupCode',
                    'responsibility_groups.name as responsibilityGroupName',
                    'responsibilities.code',
                    'responsibilities.name',
                    'responsibilities.description',
                    'responsibilities.eff_begin as effBegin',
                    'responsibilities.eff_end as effEnd'
                )
                ->leftJoin('responsibility_groups', 'responsibility_groups.code', '=', 'responsibilities.responsibility_group_code')
                ->where([
                    ['responsibilities.tenant_id', $this->requester->getTenantId()],
                    ['responsibilities.company_id', $this->requester->getCompanyId()],
                    ['responsibilities.eff_begin', '<=', Carbon::now()],
                    ['responsibilities.eff_end', '>', Carbon::now()]
                ])
                ->orderByRaw('responsibilities.eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get All Inactive Responsibility in ONE company
     */
    public function getAllInActive()
    {
        return
            DB::table('responsibilities')
            ->select(
                'responsibilities.id',
                'responsibilities.responsibility_group_code as responsibilityGroupCode',
                'responsibility_groups.name as responsibilityGroupName',
                'responsibilities.code',
                'responsibilities.name',
                'responsibilities.description',
                'responsibilities.eff_begin as effBegin',
                'responsibilities.eff_end as effEnd'
            )
            ->leftJoin('responsibility_groups', 'responsibility_groups.code', '=', 'responsibilities.responsibility_group_code')
            ->where([
                ['responsibilities.tenant_id', $this->requester->getTenantId()],
                ['responsibilities.company_id', $this->requester->getCompanyId()],
                ['responsibilities.eff_end', '<', Carbon::now()]
            ])
            ->get();
    }

    /**
     * Get all Responsibility in ONE company by Responsibility Group Code
     */
    public function getAllByResponsibilityGroup($responsibilityGroupCode)
    {
        return
            DB::table('responsibilities')
                ->select(
                    'responsibilities.id',
                    'responsibilities.responsibility_group_code as responsibilityGroupCode',
                    'responsibility_groups.name as responsibilityGroupName',
                    'responsibilities.code',
                    'responsibilities.name',
                    'responsibilities.description',
                    'responsibilities.eff_begin as effBegin',
                    'responsibilities.eff_end as effEnd'
                )
                ->leftJoin('responsibility_groups', 'responsibility_groups.code', '=', 'responsibilities.responsibility_group_code')
                ->where([
                    ['responsibilities.responsibility_group_code', $responsibilityGroupCode],
                    ['responsibilities.tenant_id', $this->requester->getTenantId()],
                    ['responsibilities.company_id', $this->requester->getCompanyId()]
                ])
                ->orderBy('responsibilities.eff_end', 'desc')
                ->orderBy('responsibilities.id', 'asc')
                ->get();
    }


    public function getAllActiveWithoutRespGroup()
    {
        return
            DB::table('responsibilities')
                ->select(
                    'id',
                    'code',
                    'name',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['responsibility_group_code', null],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>', Carbon::now()]
                ])
                ->get();
    }

    /**
     * Get one Responsibilities based on responsibility code
     * @param  tenantId, companyId
     */
    public function getOne($responsibilityCode)
    {
        return
            DB::table('responsibilities')
                ->select(
                    'responsibilities.id',
                    'responsibilities.responsibility_group_code as responsibilityGroupCode',
                    'responsibility_groups.name as responsibilityGroupName',
                    'responsibilities.code',
                    'responsibilities.name',
                    'responsibilities.description',
                    'responsibilities.used_for as usedFor',
                    'responsibilities.used_for_value as usedForValue',
                    'responsibilities.eff_begin as effBegin',
                    'responsibilities.eff_end as effEnd'
                )
                ->leftJoin('responsibility_groups', 'responsibility_groups.code', '=', 'responsibilities.responsibility_group_code')
                ->where([
                    ['responsibilities.tenant_id', $this->requester->getTenantId()],
                    ['responsibilities.company_id', $this->requester->getCompanyId()],
                    ['responsibilities.code', $responsibilityCode]
                ])
                ->first();
    }

    /**
     * Insert data responsibilities to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'responsibilities', $obj);

        return DB::table('responsibilities')->insertGetId($obj);
    }

    /**
     * Update data responsibilities to DB
     * @param  array obj, tenantId, companyId, workingConditionId
     */
    public function update($responsibilityId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'responsibilities', $obj);

        DB::table('responsibilities')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['id', $responsibilityId]
        ])
        ->update($obj);
    }

    /**
     * Update data responsibilities to DB
     * @param  array obj, tenantId, companyId, workingConditionId
     */
    public function updateByCode($responsibilityCode, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'responsibilities', $obj);

        DB::table('responsibilities')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['code', $responsibilityCode]
            ])
            ->update($obj);
    }

    /**
     * Update data responsibilities to DB
     * @param  array obj, tenantId, companyId, workingConditionId
     */
    public function updateByResponsibilityGroupCode($responsibilityGroupCode, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'responsibilities', $obj);

        DB::table('responsibilities')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['responsibility_group_code', $responsibilityGroupCode]
            ])
            ->update($obj);
    }

    public function isCodeDuplicate(string $code)
    {
        return (DB::table('responsibilities')->where([
                ['code', $code],
                ['company_id', $this->requester->getCompanyId()],
                ['tenant_id', $this->requester->getTenantId()]
            ])->count() > 0);
    }

    public function getTotalRows()
    {
        return
            DB::table('responsibilities')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])->count();
    }


}
