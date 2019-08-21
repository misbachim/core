<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JobFamilyDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Job Family in ONE company
     */
    public function getAll()
    {
        return
            DB::table('job_families')
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
     * Get All Active Job Family in One company
     */
    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('job_families')
            ->select(
                'id',
                'code',
                'description',
                'name',
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
     * Get All InActive Job Family in One company
     */
    public function getAllInActive()
    {
        return
            DB::table('job_families')
            ->select(
                'id',
                'code',
                'description',
                'name',
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
     * Get one Job Family based on job family code
     * @param  tenantId, companyId
     */
    public function getOne($jobFamilyCode)
    {
        return
            DB::table('job_families')
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
                    ['code', $jobFamilyCode]
                ])
                ->first();
    }

    /**
     * Insert data Job Family to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'job_families', $obj);

        return DB::table('job_families')->insertGetId($obj);
    }

    /**
     * Update data Job Family to DB
     * @param  array obj, tenantId, companyId, jobFamilyId
     */
    public function update($jobFamilyId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'job_families', $obj);

        DB::table('job_families')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $jobFamilyId]
            ])
            ->update($obj);
    }

    public function isCodeDuplicate(string $code)
    {
        return (DB::table('job_families')->where([
                ['code', $code],
                ['company_id', $this->requester->getCompanyId()],
                ['tenant_id', $this->requester->getTenantId()]
            ])->count() > 0);
    }

    public function getTotalRows()
    {
        return
            DB::table('job_families')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])->count();
    }
}
