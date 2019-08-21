<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class EmployeeProjectDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all employee project for one employee
     * @param $employeeId
     */
    public function getAll($offset, $limit, $employeeId)
    {
        $params = [
            'tenantId' => $this->requester->getTenantId(),
            'companyId' => $this->requester->getCompanyId(),
            'employeeId' => $employeeId,
            'sysdate' => Carbon::now(),
            'limit' => $limit,
            'offset' => $offset
        ];

        $query = "
                select
                \"employee_projects\".\"project_code\" as \"projectCode\",
                \"employee_projects\".\"eff_begin\" as \"effBegin\",
                \"employee_projects\".\"eff_end\" as \"effEnd\",
                \"employee_projects\".\"weight\",
                \"employee_projects\".\"id\",
                \"employee_projects\".\"employee_id\" as \"employeeId\",
                \"projects\".\"location_code\" as \"locationCode\",
                \"projects\".\"projectmanager_id\" as \"pmID\",
                \"projects\".\"supervisor_id\" as \"spvId\",
                \"projects\".\"name\" as \"projectName\"
                from
                (
                 select
                  distinct project_code,
                   eff_begin,
                   eff_end,
                   weight,
                   id,
                   employee_id
                 from
                  employee_projects
                 where
                  tenant_id= :tenantId
                  and company_id= :companyId
                  and employee_id= :employeeId
                ) employee_projects
                join
                (
                 select
                  location_code,
                  projectmanager_id,
                  supervisor_id,
                  code,
                  name
                 from
                  projects
                 where
                  tenant_id= :tenantId
                  and company_id= :companyId
                  and eff_begin <= :sysdate
                  and eff_end > :sysdate
                ) projects
                on projects.code = employee_projects.project_code
                limit :limit
                offset :offset
        ";

        return DB::select($query, $params);

    }

    /**
     * @param
     * @return
     */
    public function getTotalRow($employeeId)
    {
        $now = Carbon::now();
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return DB::table('employee_projects')
            ->join('projects', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('projects.code', '=', 'employee_projects.project_code')
                    ->where([
                        ['projects.tenant_id', $tenantId],
                        ['projects.company_id', $companyId],
                        ['projects.eff_begin', '<=', $now],
                        ['projects.eff_end', '>', $now]
                    ]);
            })
            ->where([
                ['employee_projects.tenant_id', $tenantId],
                ['employee_projects.company_id', $companyId],
                ['employee_projects.employee_id', $employeeId]
            ])
            ->count();
    }

    /**
     * @param
     * @return
     */
    public function getTotalRowWithQuery($employeeId, $query)
    {
        $now = Carbon::now();
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        if ($query) {
            $search = strtolower("%$query%");
        } else {
            $search = "%";
        }

        return DB::table('employee_projects')
            ->join('projects', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('projects.code', '=', 'employee_projects.project_code')
                    ->where([
                        ['projects.tenant_id', $tenantId],
                        ['projects.company_id', $companyId],
                        ['projects.eff_begin', '<=', $now],
                        ['projects.eff_end', '>', $now]
                    ]);
            })
            ->where([
                ['employee_projects.tenant_id', $tenantId],
                ['employee_projects.company_id', $companyId],
                ['employee_projects.employee_id', $employeeId]
            ])
            ->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(projects.code) like ?', [$search]);
                $query->orWhereRaw('LOWER(projects.name) like ?', [$search]);
            })
            ->count();
    }

    /**
     * Get all active employee project for one employee
     * @param $employeeId
     */
    public function getAllActive($employeeId)
    {
        $params = [
            'tenantId' => $this->requester->getTenantId(),
            'companyId' => $this->requester->getCompanyId(),
            'employeeId' => $employeeId,
            'sysdate' => Carbon::now()
        ];

        $query = "
                select
                \"employee_projects\".\"project_code\" as \"projectCode\",
                \"employee_projects\".\"eff_begin\" as \"effBegin\",
                \"employee_projects\".\"eff_end\" as \"effEnd\",
                \"employee_projects\".\"weight\",
                \"employee_projects\".\"id\",
                \"employee_projects\".\"employee_id\" as \"employeeId\",
                \"projects\".\"location_code\" as \"locationCode\",
                \"projects\".\"projectmanager_id\" as \"pmID\",
                \"projects\".\"supervisor_id\" as \"spvId\",
                \"projects\".\"name\" as \"projectName\",
                \"projects\".\"description\" as \"projectDescription\"
                from
                (
                 select
                  distinct project_code,
                   eff_begin,
                   eff_end,
                   weight,
                   id,
                   employee_id
                 from
                  employee_projects
                 where
                  tenant_id= :tenantId
                  and company_id= :companyId
                  and employee_id= :employeeId
                  and eff_begin <= :sysdate
                  and eff_end > :sysdate
                ) employee_projects
                join
                (
                 select
                  location_code,
                  projectmanager_id,
                  supervisor_id,
                  code,
                  name,
                  description
                 from
                  projects
                 where
                  tenant_id= :tenantId
                  and company_id= :companyId
                  and eff_begin <= :sysdate
                  and eff_end > :sysdate
                ) projects
                on projects.code = employee_projects.project_code
        ";

        return DB::select($query, $params);
    }

    /**
     * Get all active employee project for assignment
     * @param $employeeId
     */
    public function getAllActiveForAssignment($employeeId)
    {
        $now = Carbon::now();
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('employee_projects')
                ->select(
                    'employee_projects.eff_begin as effBegin',
                    'employee_projects.eff_end as effEnd',
                    'employee_projects.weight as weight',
                    'employee_id as employeeId',
                    'project_code as projectCode'
                )
                ->where([
                    ['employee_projects.tenant_id', $tenantId],
                    ['employee_projects.company_id', $companyId],
                    ['employee_projects.employee_id', $employeeId],
                    ['employee_projects.eff_begin', '<=', $now],
                    ['employee_projects.eff_end', '>', $now]
                ])
                ->get();
    }

    public function searchWithLimit($offset, $limit, $employeeId, $query)
    {
        $params = [
            'tenantId' => $this->requester->getTenantId(),
            'companyId' => $this->requester->getCompanyId(),
            'employeeId' => $employeeId,
            'searchString' => $query ? strtolower("%$query%") : "%",
            'sysdate' => Carbon::now(),
            'limit' => $limit,
            'offset' => $offset
        ];

        $query = "
                select 
                    \"employee_projects\".\"project_code\" as \"projectCode\",
                    \"employee_projects\".\"eff_begin\" as \"effBegin\",
                    \"employee_projects\".\"eff_end\" as \"effEnd\",
                    \"employee_projects\".\"weight\",
                    \"employee_projects\".\"id\",
                    \"employee_projects\".\"employee_id\" as \"employeeId\",
                    \"projects\".\"location_code\" as \"locationCode\",
                    \"projects\".\"projectmanager_id\" as \"pmID\",
                    \"projects\".\"supervisor_id\" as \"spvId\",
                    \"projects\".\"name\" as \"projectName\"
                 from
                (
                 select 
                  distinct project_code,
                  eff_begin,
                  eff_end,
                  weight,
                  id,
                  employee_id 
                 from 
                  employee_projects 
                 where 
                  tenant_id= :tenantId 
                  and company_id= :companyId
                  and employee_id= :employeeId
                ) employee_projects
                join
                (
                 select 
                  * 
                 from
                  projects 
                 where 
                  tenant_id= :tenantId 
                  and company_id= :companyId 
                  and eff_begin <= :sysdate 
                  and eff_end > :sysdate
                ) projects
                on projects.code = employee_projects.project_code
                where 
                LOWER(employee_projects.project_code) like  :searchString  
                or LOWER(projects.name) like  :searchString 
                or LOWER(projects.description) like :searchString
                limit :limit
                offset :offset
        ";

        return DB::select($query, $params);
    }

    public function search($employeeId, $query)
    {
        info('search', [$query]);

        $params = [
            'tenantId' => $this->requester->getTenantId(),
            'companyId' => $this->requester->getCompanyId(),
            'employeeId' => $employeeId,
            'searchString' => strtolower("%$query%"),
            'sysdate' => Carbon::now()
        ];

        $query = "
                select
                \"project_code\" as \"projectCode\", \"projects\".\"name\" as \"projectName\", \"projects\".\"description\" as \"projectDescription\"
                from
                (
                select
                  distinct project_code
                 from
                  employee_projects
                 where
                  tenant_id= :tenantId
                  and company_id= :companyId
                  and employee_id= :employeeId
                  and eff_begin <= :sysdate
                  and eff_end > :sysdate
                ) employee_projects
                join
                (
                 select
                  *
                 from
                  projects
                 where
                  tenant_id= :tenantId
                  and company_id= :companyId
                  and eff_begin <= :sysdate
                  and eff_end > :sysdate
                ) projects
                on projects.code = employee_projects.project_code
                where
                LOWER(employee_projects.project_code) like  :searchString
                or LOWER(projects.name) like  :searchString
                or LOWER(projects.description) like :searchString
        ";

        return DB::select($query, $params);
    }

    /**
     * Get all employee project for one employee
     * @param $employeeId
     */
    public function getAllByAssignmentDate($employeeId, $effBegin, $effEnd)
    {
        $now = Carbon::now();
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        info('beg', [$effBegin]);
        info('end', [$effEnd]);

        return
            DB::table('employee_projects')
                ->select(
                    'employee_projects.eff_begin as effBegin',
                    'employee_projects.eff_end as effEnd',
                    'employee_projects.weight as weight',
                    'employee_projects.id',
                    'employee_id as employeeId',
                    'project_code as projectCode',
                    'projects.name as projectName',
                    'projects.location_code as locationCode',
                    'projects.projectmanager_id as pmID',
                    'projects.supervisor_id as spvId'
                )
                ->leftJoin('projects', function ($join) use ($companyId, $tenantId, $now) {
                    $join->on('projects.code', '=', 'employee_projects.project_code')
                        ->where([
                            ['projects.tenant_id', $tenantId],
                            ['projects.company_id', $companyId],
                            ['projects.eff_end', '>', $now]
                        ]);
                })
                ->where([
                    ['employee_projects.tenant_id', $tenantId],
                    ['employee_projects.company_id', $companyId],
                    ['employee_projects.eff_begin', '>=', $effBegin],
                    ['employee_projects.eff_begin', '<=', $effEnd],
                    ['employee_projects.employee_id', $employeeId]
                ])
                ->get();
    }

    /**
     * Get one employee project based on employeeProjectId
     * @param $employeeId
     * @param $employeeProjectId
     * @return
     */
    public function getOne($employeeProjectId)
    {
        return
            DB::table('employee_projects')
                ->select(
                    'id',
                    'project_code as projectCode',
                    'employee_id as employeeId',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'weight as weight'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['id', $employeeProjectId]
                ])
                ->first();
    }

    /**
     * Get one last employee project based on employeeId
     * @param $employeeId
     * @return
     */
    public function getLastOne($employeeId)
    {
        return
            DB::table('employee_projects')
                ->select(
                    'id',
                    'project_code as projectCode',
                    'employee_id as employeeId',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'weight as weight'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['employee_id', $employeeId]
                ])
                ->orderBy('eff_end', 'desc')
                ->first();
    }

    /**
     * Insert data employee project to DB
     * @param  array $obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('employee_projects')->insertGetId($obj);
    }

    /**
     * Update data employee project based on employeeProjectId
     * @param $employeeId
     * @param $employeeProjectId
     * @param $obj
     */
    public function update($employeeId, $employeeProjectId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('employee_projects')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['employee_id', $employeeId],
                ['id', $employeeProjectId]
            ])
            ->update($obj);
    }

    /**
     * Delete data employee project based on employeeProjectId
     * @param $employeeId
     * @param $employeeProjectId
     */
    public function deleteByEmployeeId($employeeId)
    {
        DB::table('employee_projects')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['employee_id', $employeeId]
            ])
            ->delete();
    }


    /*
    |------------------------------------------------------------------------
    | ambil semua data employee project bedasarkan parameter dan employee ID
    |------------------------------------------------------------------------
    |
    |
    */
    public function getSearchBox($param, $employeeId) {
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        $searchString = strtolower("%$param%");

           $sql = DB::table('employee_projects')
            ->select(
                'projects.id',
                'projects.code as code',
                'projects.name as name',
                'projects.description as description'
            )
            ->join('projects',  function ($join) use($tenantId, $companyId) {
                $join->on('projects.code','employee_projects.project_code')
                    ->where([
                        ['projects.tenant_id', $tenantId],
                        ['projects.company_id', $companyId]
                    ]);
            })
            ->where(function($where) use($tenantId, $companyId, $searchString, $employeeId) {
                $where->where([
                    ['employee_projects.tenant_id', $tenantId],
                    ['employee_projects.company_id', $companyId],
                    ['employee_projects.employee_id', $employeeId]
                ])
                ->whereRaw('LOWER(projects.name) like ?', [$searchString]);
            })
            ->orWhere(function($where) use($tenantId, $companyId, $searchString, $employeeId) {
                $where->where([
                    ['employee_projects.tenant_id', $tenantId],
                    ['employee_projects.company_id', $companyId],
                    ['employee_projects.employee_id', $employeeId]
                ])
                ->whereRaw('LOWER(projects.code) like ?', [$searchString]);
            });
            return $sql->get();
    }
}
