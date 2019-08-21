<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ResponsibilityGroupDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Responsibility Group in ONE company
     */
    public function getAll()
    {
        return
            DB::table('responsibility_groups')
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

    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('responsibility_groups')
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
                    ['eff_end', '>', Carbon::now()]
                ])
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    public function getAllInActive()
    {
        return
            DB::table('responsibility_groups')
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
     * Get one responsibility group based on responsibility group code
     * @param  tenantId, companyId
     */
    public function getOne($responsibilityGroupCode)
    {
        return
            DB::table('responsibility_groups')
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
                    ['code', $responsibilityGroupCode]
                ])
                ->first();
    }

    /**
     * Insert data responsibility group to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'responsibility_groups', $obj);

        return DB::table('responsibility_groups')->insertGetId($obj);
    }

    /**
     * Update data responsibility group to DB
     * @param  array obj, tenantId, companyId, responsibilityGroupId
     */
    public function update($responsibilityGroupId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'responsibility_groups', $obj);

        DB::table('responsibility_groups')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['id', $responsibilityGroupId]
        ])
        ->update($obj);
    }

    public function isCodeDuplicate(string $code)
    {
        return (DB::table('responsibility_groups')->where([
                ['code', $code],
                ['company_id', $this->requester->getCompanyId()],
                ['tenant_id', $this->requester->getTenantId()]
            ])->count() > 0);
    }

    public function getTotalRows()
    {
        return
            DB::table('responsibility_groups')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])->count();
    }

    
}
