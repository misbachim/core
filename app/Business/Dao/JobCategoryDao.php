<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JobCategoryDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Job Category in ONE company
     */
    public function getAll()
    {
        return
            DB::table('job_categories')
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
     * Get All Active Job Category in One company
     */
    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('job_categories')
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
     * Get All InActive Job Category in One company
     */
    public function getAllInActive()
    {
        return
            DB::table('job_categories')
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
     * Get one Job Category based on job category code
     * @param  tenantId, companyId
     */
    public function getOne($jobCategoryCode)
    {
        return
            DB::table('job_categories')
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
                    ['code', $jobCategoryCode]
                ])
                ->first();
    }

    /**
     * Insert data Job Category to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'job_categories', $obj);

        return DB::table('job_categories')->insertGetId($obj);
    }

    /**
     * Update data Job Category to DB
     * @param  array obj, tenantId, companyId, jobCategoryId
     */
    public function update($jobCategoryId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'job_categories', $obj);

        DB::table('job_categories')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['id', $jobCategoryId]
        ])
        ->update($obj);
    }

    public function isCodeDuplicate(string $code)
    {
        return (DB::table('job_categories')->where([
                ['code', $code],
                ['company_id', $this->requester->getCompanyId()],
                ['tenant_id', $this->requester->getTenantId()]
            ])->count() > 0);
    }

    public function getTotalRows()
    {
        return
            DB::table('job_categories')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])->count();
    }
}
