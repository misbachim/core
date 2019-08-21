<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class ProjectDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all project in ONE company
     */
    public function getAll()
    {
        $now = Carbon::now();
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('projects')
                ->select(
                    'projects.id as id',
                    'projects.code as code',
                    'projects.name as name',
                    'projects.eff_begin as effBegin',
                    'projects.eff_end as effEnd',
                    'projects.supervisor_id as supervisor',
                    'projects.quota',
                    'persons_sv.first_name as svFirstName',
                    'persons_sv.last_name as svLastName',
                    'projects.projectmanager_id as projectmanager',
                    'persons_pm.first_name as pmFirstName',
                    'persons_pm.last_name as pmLastName',
                    'projects.description',
                    'projects.location_code as location',
                    'locations.name as locationName'
                )
                ->distinct('projects.id')
                ->leftjoin('assignments as assignments_pm', function ($join) use ($companyId, $tenantId, $now) {
                    $join->on('assignments_pm.employee_id', '=', 'projects.projectmanager_id')
                        ->where([
                            ['assignments_pm.tenant_id', $tenantId],
                            ['assignments_pm.company_id', $companyId],
                            ['assignments_pm.eff_begin', '<=', $now],
                            ['assignments_pm.eff_end', '>=', $now],
                            ['assignments_pm.is_primary', '=', true]
                        ]);
                })
                ->leftjoin('persons as persons_pm', function ($join) use ($companyId, $tenantId) {
                    $join->on('persons_pm.id', '=', 'assignments_pm.person_id')
                        ->where([
                            ['persons_pm.tenant_id', $tenantId]
                        ]);
                })
                ->leftjoin('assignments as assignments_sv', function ($join) use ($companyId, $tenantId, $now) {
                    $join->on('assignments_sv.employee_id', '=', 'projects.supervisor_id')
                        ->where([
                            ['assignments_sv.tenant_id', $tenantId],
                            ['assignments_sv.company_id', $companyId],
                            ['assignments_sv.eff_begin', '<=', $now],
                            ['assignments_sv.eff_end', '>=', $now],
                            ['assignments_sv.is_primary', '=', true]
                        ]);
                })
                ->leftjoin('persons as persons_sv', function ($join) use ($companyId, $tenantId) {
                    $join->on('persons_sv.id', '=', 'assignments_sv.person_id')
                        ->where([
                            ['persons_sv.tenant_id', $tenantId]
                        ]);
                })
                ->leftjoin('locations as locations', function ($join) use ($companyId, $tenantId) {
                    $join->on('locations.code', '=', 'projects.location_code')
                        ->where([
                            ['locations.tenant_id', $tenantId],
                            ['locations.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['projects.tenant_id', $this->requester->getTenantId()],
                    ['projects.company_id', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    public function getLov()
    {
        return
            DB::table('projects')
                ->select(
                    'code',
                    'name',
                    'description'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()]
                ])
                ->get();
    }

    /**
     * Get one project based on project code
     */
    public function getOne($projectCode)
    {
        return
            DB::table('projects')
                ->select(
                    'id',
                    'code',
                    'name',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'description',
                    'supervisor_id as supervisorId',
                    'projectmanager_id as projectManagerId',
                    'quota',
                    'location_code as locationCode'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $projectCode]
                ])
                ->first();
    }

    /**
     * Insert data project to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'projects', $obj);

        return DB::table('projects')->insert($obj);
    }

    /**
     * Update data project to DB
     * @param  array obj, tenantId, companyId, projectCode
     */
    public function update($id, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'projects', $obj);

        DB::table('projects')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $id]
            ])
            ->update($obj);
    }

    public function isCodeDuplicate(string $code)
    {
        return (DB::table('projects')->where([
                ['code', $code],
                ['company_id', $this->requester->getCompanyId()],
                ['tenant_id', $this->requester->getTenantId()]
            ])->count() > 0);
    }
}
