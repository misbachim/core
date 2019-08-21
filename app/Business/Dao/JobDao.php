<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JobDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all job in ONE company
     * @param  tenantId, companyId
     */
    public function getAll($offset, $limit)
    {
        return
            DB::table('jobs')
            ->select(
                'jobs.id',
                'jobs.eff_begin as effBegin',
                'jobs.eff_end as effEnd',
                'jobs.code',
                'jobs.name',
                'jobs.description',
                'job_families.name as jobFamilyName',
                'job_categories.name as jobCategoryName'
            )
            ->leftJoin('job_families', function ($join) {
                $join
                    ->on('job_families.code', '=', 'jobs.job_family_code')
                    ->on('job_families.tenant_id', '=', 'jobs.tenant_id')
                    ->on('job_families.company_id', '=', 'jobs.company_id');
            })
            ->leftJoin('job_categories', function ($join) {
                $join
                    ->on('job_categories.code', '=', 'jobs.job_category_code')
                    ->on('job_categories.tenant_id', '=', 'jobs.tenant_id')
                    ->on('job_categories.company_id', '=', 'jobs.company_id');
            })
            ->where([
                ['jobs.tenant_id', $this->requester->getTenantId()],
                ['jobs.company_id', $this->requester->getCompanyId()]
            ])
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('jobs')
            ->select(
                'jobs.id',
                'jobs.eff_begin as effBegin',
                'jobs.eff_end as effEnd',
                'jobs.code',
                'jobs.name',
                'jobs.description'
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

    public function getAllInActive()
    {
        return
            DB::table('jobs')
            ->select(
                'jobs.id',
                'jobs.eff_begin as effBegin',
                'jobs.eff_end as effEnd',
                'jobs.code',
                'jobs.name',
                'jobs.description'
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
            DB::table('jobs')
            ->leftJoin('job_families', 'job_families.code', '=', 'jobs.job_family_code')
            ->where([
                ['jobs.tenant_id', $this->requester->getTenantId()],
                ['jobs.company_id', $this->requester->getCompanyId()]
            ])->count();
    }

    /**
     * Get one job based on job id
     * @param  tenantId, companyId, jobId
     */
    public function getOne($jobId)
    {
        return
            DB::table('jobs')
            ->select(
                'jobs.id',
                'jobs.eff_begin as effBegin',
                'jobs.eff_end as effEnd',
                'jobs.code',
                'jobs.name',
                'jobs.description',
                'ordinal',
                'job_family_code as jobFamilyCode',
                'job_category_code as jobCategoryCode'
            )
            ->leftJoin('job_families', function ($join) {
                $join
                    ->on('job_families.code', '=', 'jobs.job_family_code')
                    ->on('job_families.tenant_id', '=', 'jobs.tenant_id')
                    ->on('job_families.company_id', '=', 'jobs.company_id');
            })
            ->leftJoin('job_categories', function ($join) {
                $join
                    ->on('job_categories.code', '=', 'jobs.job_category_code')
                    ->on('job_categories.tenant_id', '=', 'jobs.tenant_id')
                    ->on('job_categories.company_id', '=', 'jobs.company_id');
            })
            ->where([
                ['jobs.tenant_id', $this->requester->getTenantId()],
                ['jobs.company_id', $this->requester->getCompanyId()],
                ['jobs.id', $jobId]
            ])
            ->first();
    }
    public function getOneCode($jobCode)
    {
        return
            DB::table('jobs')
            ->select(
                'jobs.id',
                'jobs.eff_begin as effBegin',
                'jobs.eff_end as effEnd',
                'jobs.code',
                'jobs.name',
                'jobs.description',
                'ordinal',
                'job_family_code as jobFamilyCode',
                'job_families.name as jobFamilyName',
                'job_category_code as jobCategoryCode',
                'job_categories.name as jobCategoryName'
            )
            ->leftJoin('job_families', function ($join) {
                $join
                    ->on('job_families.code', '=', 'jobs.job_family_code')
                    ->on('job_families.tenant_id', '=', 'jobs.tenant_id')
                    ->on('job_families.company_id', '=', 'jobs.company_id');
            })
            ->leftJoin('job_categories', function ($join) {
                $join
                    ->on('job_categories.code', '=', 'jobs.job_category_code')
                    ->on('job_categories.tenant_id', '=', 'jobs.tenant_id')
                    ->on('job_categories.company_id', '=', 'jobs.company_id');
            })
            ->where([
                ['jobs.tenant_id', $this->requester->getTenantId()],
                ['jobs.company_id', $this->requester->getCompanyId()],
                ['jobs.code', $jobCode]
            ])
            ->first();
    }

    /**
     * get one data for a job
     * this function is used in education institution and
     * specialization Used By Employee
     * @param  string $jobCode
     */
    public function getOneJobByCode($jobCode)
    {
        return
            DB::table('jobs')
            ->select(
                'jobs.id',
                'jobs.code',
                'jobs.name',
                'jobs.description'
            )
            ->where([
                ['jobs.tenant_id', $this->requester->getTenantId()],
                ['jobs.company_id', $this->requester->getCompanyId()],
                ['jobs.code', $jobCode]
            ])
            ->first();
    }

    /**
     * Insert data job to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'jobs', $obj);

        return DB::table('jobs')->insertGetId($obj);
    }

    /**
     * Update data job to DB
     * @param $jobId
     * @param $obj
     */
    public function update($jobId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'jobs', $obj);

        DB::table('jobs')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $jobId]
            ])
            ->update($obj);
    }

    public function getSLov($menuCode)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $roleIds = $this->requester->getRoleIds();

        $roleIds_param = 'array[' . implode(",", $roleIds) . ']';

        $query = DB::table(DB::raw('f_job_lovs(' . $tenantId . ',' . $companyId . ',\'' . $menuCode . '\',' . $roleIds_param . ')'))
            ->select(
                'job_code as code',
                'job_name as name'
            );

        return $query->get();
    }

    public function isCodeDuplicate(string $code)
    {
        return (DB::table('jobs')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count() > 0);
    }

    public function search($query, $offset, $limit)
    {
        $now = Carbon::now();
        $searchString = strtolower("%$query%");
        return
            DB::table('jobs')
            ->select('code', 'name')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['eff_begin', '<=', $now],
                ['eff_end', '>=', $now]
            ])
            ->whereRaw('LOWER(name) like ?', [$searchString])
            ->offset($offset)
            ->limit($limit)
            ->get();
    }
}
