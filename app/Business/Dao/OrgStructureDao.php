<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrgStructureDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all org structures in One Company
     * @param  tenantId, companyId
     */
    public function getAll($tenantId, $companyId)
    {
        return
            DB::table('org_structures')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'name',
                    'description',
                    'is_primary as isPrimary'
                )
                ->where([
                    ['tenant_id', $tenantId],
                    ['company_id', $companyId]
                ])
                ->get();
    }

    /**
     * Get all Active orgStructure in One Company
     */
    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('org_structures')
            ->select(
                'id',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'name',
                'description',
                'is_primary as isPrimary'
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
     * Get All InActive orgStructure in One Company
     */
    public function getAllInActive()
    {
        return
            DB::table('org_structures')
            ->select(
                'id',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'name',
                'description',
                'is_primary as isPrimary'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['eff_end', '<', Carbon::now()]
            ])
            ->get();
    }

    public function getTotalRows()
    {
        return
            DB::table('org_structures')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])->count();
    }


    /**
     * Get one org structures based on id
     * @param  tenantId, companyId, orgStructureId
     */
    public function getOne($tenantId, $companyId, $orgStructureId)
    {
        return
            DB::table('org_structures')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'name',
                    'description',
                    'is_primary as isPrimary'
                )
                ->where([
                    ['tenant_id', $tenantId],
                    ['company_id', $companyId],
                    ['id', $orgStructureId]
                ])
                ->first();
    }

    /**
     * Get one org structures based on id
     * @param  tenantId, companyId, orgStructureId
     */
    public function getOnePrimary($companyId)
    {
        return
            DB::table('org_structures')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'name',
                    'description',
                    'is_primary as isPrimary'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $companyId],
                    ['is_primary', true]
                ])
                ->first();
    }

    /**
     * Insert data org structure to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'org_structures', $obj);

        return DB::table('org_structures')-> insertGetId($obj);
    }

    /**
     * Update data org structure to DB
     * @param  array org, tenantId, companyId, id
     */
    public function update($tenantId, $companyId, $id, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'org_structures', $obj);

        DB::table('org_structures')
        ->where([
            ['tenant_id', $tenantId],
            ['company_id', $companyId],
            ['id', $id]
        ])
        ->update($obj);
    }

    public function delete($id)
    {
        DB::table('org_structures')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $id]
            ])
            ->delete();
    }

    public function updateAll($obj)
    {
        LogDao::insertLogImpact($this->requester->getLogId(), 'org_structures', $obj);

        DB::table('org_structures')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])
            ->update($obj);
    }
}
