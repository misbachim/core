<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkflowDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Workflow for one company
     * @param
     */
    public function getAll($companyId)
    {
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('workflows')
                ->select(
                    'workflows.id',
                    'workflows.lov_wfty as lovWfty',
                    'workflows.is_default as isDefault',
                    'workflows.unit_code as unitCode',
                    'workflows.employee_id as employeeId',
                    'wfty.val_data as valData'
                )
                ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                    $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY'],
                            ['wfty.tenant_id', $tenantId],
                            ['wfty.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['workflows.tenant_id', $this->requester->getTenantId()],
                    ['workflows.company_id', $companyId],
                    ['wfty.tenant_id', $this->requester->getTenantId()],
                    ['wfty.company_id', $companyId]
                ])
                ->get();
    }

    /**
     * Get all Workflow for one company
     * @param
     */
    public function getAllDefaultByWorkflowType($lovWfty)
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        return DB::table('workflows')
            ->select(
                'workflows.id',
                'workflows.lov_wfty as lovWfty',
                'workflows.is_default as isDefault',
                'workflows.employee_id as employeeId',
                'workflows.unit_code as unitCode',
                'wfty.val_data as valData'
            )
            ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                    ->where([
                        ['wfty.lov_type_code', 'WFTY'],
                        ['wfty.tenant_id', $tenantId],
                        ['wfty.company_id', $companyId]
                    ]);
            })
            ->where([
                ['workflows.tenant_id', $tenantId],
                ['workflows.company_id', $companyId],
                ['workflows.lov_wfty', $lovWfty],
                ['workflows.is_default', true],
            ])
            ->get();
    }

    /**
     * Get all Workflow unit for one company
     * @param
     */
    public function getAllUnitByWorkflowType($offset, $limit, $lovWfty, $search = null)
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        $query = DB::table('workflows')
                ->select(
                    'workflows.id',
                    'workflows.lov_wfty as lovWfty',
                    'workflows.unit_code as unitCode',
                    'wfty.val_data as valData',
                    'units.name as unitName'
                )
                ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                    $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY'],
                            ['wfty.tenant_id', $tenantId],
                            ['wfty.company_id', $companyId]
                        ]);
                })
                ->leftjoin('units', function ($join) use ($companyId, $tenantId) {
                    $join->on('units.code', '=', 'workflows.unit_code')
                        ->where([
                            ['units.tenant_id', $tenantId],
                            ['units.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['workflows.tenant_id', $tenantId],
                    ['workflows.company_id', $companyId],
                    ['workflows.lov_wfty', $lovWfty],
                    ['workflows.is_default', false],
                    ['workflows.unit_code','!=',null]
                ])
                ->offset($offset)
                ->limit($limit)
                ->orderBy('workflows.id', 'DESC');

        if($search) {
            $stringSearch = strtolower("%$search%");
            $query->whereRaw('LOWER(units.name) like ?', [$stringSearch]);
        }

        return $query->get();
    }

    public function totalRowUnitWorkflowType($lovWfty, $search = null)
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        $query = DB::table('workflows')
            ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                    ->where([
                        ['wfty.lov_type_code', 'WFTY'],
                        ['wfty.tenant_id', $tenantId],
                        ['wfty.company_id', $companyId]
                    ]);
            })
            ->leftjoin('units', function ($join) use ($companyId, $tenantId) {
                $join->on('units.code', '=', 'workflows.unit_code')
                    ->where([
                        ['units.tenant_id', $tenantId],
                        ['units.company_id', $companyId]
                    ]);
            })
            ->where([
                ['workflows.tenant_id', $tenantId],
                ['workflows.company_id', $companyId],
                ['workflows.lov_wfty', $lovWfty],
                ['workflows.is_default', false],
                ['workflows.unit_code','!=',null]
            ]);

        if($search) {
            $stringSearch = strtolower("%$search%");
            $query->whereRaw('LOWER(units.name) like ?', [$stringSearch]);
        }

        return $query->count();
    }

    /**
     * Get all Workflow employee for one company
     * @param
     */
    public function getAllEmployeeByWorkflowType($offset, $limit, $lovWfty, $search = null)
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        $query = DB::table('workflows')
            ->selectRaw(
                'workflows.id,' .
                'workflows.lov_wfty as "lovWfty",' .
                'workflows.employee_id as "employeeId",' .
                'wfty.val_data as "valData",' .
                '(CONCAT(persons.first_name,\' \', persons.last_name)) as "employeeName"'
            )
            ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                    ->where([
                        ['wfty.lov_type_code', 'WFTY'],
                        ['wfty.tenant_id', $tenantId],
                        ['wfty.company_id', $companyId]
                    ]);
            })
            ->leftJoin('assignments', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.employee_id', '=', 'workflows.employee_id')
                    ->where([
                        ['assignments.tenant_id', $tenantId],
                        ['assignments.company_id', $companyId],
                        ['assignments.eff_begin', '<=', Carbon::now()],
                        ['assignments.eff_end','>=', Carbon::now()],
                        ['assignments.is_primary', true]
                    ]);
            })
            ->leftJoin('persons', function ($join) use ($companyId, $tenantId) {
                $join->on('persons.id', '=', 'assignments.person_id')
                    ->where([
                        ['persons.tenant_id', $tenantId]
                    ]);
            })
            ->where([
                ['workflows.tenant_id', $tenantId],
                ['workflows.company_id', $companyId],
                ['workflows.lov_wfty', $lovWfty],
                ['workflows.is_default', false],
                ['workflows.employee_id','!=',null]
            ])
            ->offset($offset)
            ->limit($limit)
            ->orderBy('workflows.id', 'DESC');

        if($search) {
            $stringSearch = strtolower("%$search%");
            $query->whereRaw('LOWER(CONCAT(persons.first_name,\' \', persons.last_name)) like ?', [$stringSearch]);
        }

        return $query->get();
    }

    public function totalRowEmployeeWorkflowType($lovWfty, $search = null)
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        $query = DB::table('workflows')
            ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                    ->where([
                        ['wfty.lov_type_code', 'WFTY'],
                        ['wfty.tenant_id', $tenantId],
                        ['wfty.company_id', $companyId]
                    ]);
            })
            ->leftJoin('assignments', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.employee_id', '=', 'workflows.employee_id')
                    ->where([
                        ['assignments.tenant_id', $tenantId],
                        ['assignments.company_id', $companyId],
                        ['assignments.eff_begin', '<=', Carbon::now()],
                        ['assignments.eff_end','>=', Carbon::now()],
                        ['assignments.is_primary', true]
                    ]);
            })
            ->leftJoin('persons', function ($join) use ($companyId, $tenantId) {
                $join->on('persons.id', '=', 'assignments.person_id')
                    ->where([
                        ['persons.tenant_id', $tenantId]
                    ]);
            })
            ->where([
                ['workflows.tenant_id', $tenantId],
                ['workflows.company_id', $companyId],
                ['workflows.lov_wfty', $lovWfty],
                ['workflows.is_default', false],
                ['workflows.employee_id','!=',null],
            ]);

        if($search) {
            $stringSearch = strtolower("%$search%");
            $query->whereRaw('LOWER(CONCAT(persons.first_name,\' \', persons.last_name)) like ?', [$stringSearch]);
        }

        return $query->count();
    }

    /**
     * Get all Workflow location for one company
     * @param
     */
    public function getAllLocationByWorkflowType($offset, $limit, $lovWfty, $search = null)
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        $query = DB::table('workflows')
            ->select(
                'workflows.id',
                'workflows.lov_wfty as lovWfty',
                'workflows.location_code as locationCode',
                'wfty.val_data as valData',
                'locations.name as locationName'
            )
            ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                    ->where([
                        ['wfty.lov_type_code', 'WFTY'],
                        ['wfty.tenant_id', $tenantId],
                        ['wfty.company_id', $companyId]
                    ]);
            })
            ->leftjoin('locations', function ($join) use ($companyId, $tenantId) {
                $join->on('locations.code', '=', 'workflows.location_code')
                    ->where([
                        ['locations.tenant_id', $tenantId],
                        ['locations.company_id', $companyId]
                    ]);
            })
            ->where([
                ['workflows.tenant_id', $tenantId],
                ['workflows.company_id', $companyId],
                ['workflows.lov_wfty', $lovWfty],
                ['workflows.is_default', false],
                ['workflows.location_code','!=',null]
            ])
            ->offset($offset)
            ->limit($limit)
            ->orderBy('workflows.id', 'DESC');

        if($search) {
            $stringSearch = strtolower("%$search%");
            $query->whereRaw('LOWER(locations.name) like ?', [$stringSearch]);
        }

        return $query->get();
    }

    public function totalRowLocationWorkflowType($lovWfty, $search = null)
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        $query = DB::table('workflows')
            ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                    ->where([
                        ['wfty.lov_type_code', 'WFTY'],
                        ['wfty.tenant_id', $tenantId],
                        ['wfty.company_id', $companyId]
                    ]);
            })
            ->leftjoin('locations', function ($join) use ($companyId, $tenantId) {
                $join->on('locations.code', '=', 'workflows.location_code')
                    ->where([
                        ['locations.tenant_id', $tenantId],
                        ['locations.company_id', $companyId]
                    ]);
            })
            ->where([
                ['workflows.tenant_id', $tenantId],
                ['workflows.company_id', $companyId],
                ['workflows.lov_wfty', $lovWfty],
                ['workflows.is_default', false],
                ['workflows.location_code','!=',null]
            ])
            ->orderBy('workflows.id', 'DESC');

        if($search) {
            $stringSearch = strtolower("%$search%");
            $query->whereRaw('LOWER(locations.name) like ?', [$stringSearch]);
        }

        return $query->count();
    }

    /**
     * Get all Workflow project for one company
     * @param
     */
    public function getAllProjectByWorkflowType($offset, $limit, $lovWfty, $search = null)
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        $query = DB::table('workflows')
            ->select(
                'workflows.id',
                'workflows.lov_wfty as lovWfty',
                'workflows.project_code as projectCode',
                'wfty.val_data as valData',
                'projects.name as projectName'
            )
            ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                    ->where([
                        ['wfty.lov_type_code', 'WFTY'],
                        ['wfty.tenant_id', $tenantId],
                        ['wfty.company_id', $companyId]
                    ]);
            })
            ->leftjoin('projects', function ($join) use ($companyId, $tenantId) {
                $join->on('projects.code', '=', 'workflows.project_code')
                    ->where([
                        ['projects.tenant_id', $tenantId],
                        ['projects.company_id', $companyId]
                    ]);
            })
            ->where([
                ['workflows.tenant_id', $tenantId],
                ['workflows.company_id', $companyId],
                ['workflows.lov_wfty', $lovWfty],
                ['workflows.is_default', false],
                ['workflows.project_code','!=',null]
            ])
            ->offset($offset)
            ->limit($limit)
            ->orderBy('workflows.id', 'DESC');

        if($search) {
            $stringSearch = strtolower("%$search%");
            $query->whereRaw('LOWER(projects.name) like ?', [$stringSearch]);
        }

        return $query->get();
    }

    public function totalRowProjectWorkflowType($lovWfty, $search = null)
    {
        $tenantId  = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        $query = DB::table('workflows')
            ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                    ->where([
                        ['wfty.lov_type_code', 'WFTY'],
                        ['wfty.tenant_id', $tenantId],
                        ['wfty.company_id', $companyId]
                    ]);
            })
            ->leftjoin('projects', function ($join) use ($companyId, $tenantId) {
                $join->on('projects.code', '=', 'workflows.project_code')
                    ->where([
                        ['projects.tenant_id', $tenantId],
                        ['projects.company_id', $companyId]
                    ]);
            })
            ->where([
                ['workflows.tenant_id', $tenantId],
                ['workflows.company_id', $companyId],
                ['workflows.lov_wfty', $lovWfty],
                ['workflows.is_default', false],
                ['workflows.project_code','!=',null]
            ])
            ->orderBy('workflows.id', 'DESC');

        if($search) {
            $stringSearch = strtolower("%$search%");
            $query->whereRaw('LOWER(projects.name) like ?', [$stringSearch]);
        }

        return $query->count();
    }

    /**
     * Get one Workflow Default based on WFTY
     * @param
     */
    public function getOneByWorkflowTypeDefault($companyId, $lovWfty)
    {
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('workflows')
                ->select(
                    'workflows.id',
                    'workflows.lov_wfty as lovWfty',
                    'workflows.is_default as isDefault',
                    'workflows.unit_code as unitCode',
                    'workflows.employee_id as employeeId',
                    'wfty.val_data as valData'
                )
                ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                    $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY'],
                            ['wfty.tenant_id', $tenantId],
                            ['wfty.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['workflows.tenant_id', $this->requester->getTenantId()],
                    ['workflows.company_id', $companyId],
                    ['workflows.lov_wfty', $lovWfty],
                    ['workflows.is_default', true],
                    ['wfty.tenant_id', $this->requester->getTenantId()],
                    ['wfty.company_id', $companyId]
                ])
                ->first();
    }

    /**
     * Get one Workflow Unit based on WFTY
     * @param
     */
    public function getOneByWorkflowTypeAndUnit($companyId, $lovWfty, $unitCode)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('workflows')
                ->select(
                    'workflows.id',
                    'workflows.lov_wfty as lovWfty',
                    'workflows.is_default as isDefault',
                    'workflows.unit_code as unitCode',
                    'workflows.employee_id as employeeId',
                    'wfty.val_data as valData'
                )
                ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                    $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY'],
                            ['wfty.tenant_id', $tenantId],
                            ['wfty.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['workflows.tenant_id', $this->requester->getTenantId()],
                    ['workflows.company_id', $companyId],
                    ['workflows.lov_wfty', $lovWfty],
                    ['wfty.tenant_id', $this->requester->getTenantId()],
                    ['wfty.company_id', $companyId],
                    ['workflows.unit_code', $unitCode]
                ])
                ->first();
    }

    /**
     * Get one Workflow Employee based on WFTY
     * @param
     */
    public function getOneByWorkflowTypeAndEmployee($companyId, $lovWfty, $requesterId)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('workflows')
                ->select(
                    'workflows.id',
                    'workflows.lov_wfty as lovWfty',
                    'workflows.is_default as isDefault',
                    'workflows.unit_code as unitCode',
                    'workflows.employee_id as employeeId',
                    'wfty.val_data as valData'
                )
                ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                    $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY'],
                            ['wfty.tenant_id', $tenantId],
                            ['wfty.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['workflows.tenant_id', $tenantId],
                    ['workflows.company_id', $companyId],
                    ['workflows.lov_wfty', $lovWfty],
                    ['wfty.tenant_id', $tenantId],
                    ['wfty.company_id', $companyId],
                    ['workflows.employee_id', $requesterId]
                ])
                ->first();
    }

    /**
     * Get one Workflow Project based on WFTY
     * @param
     */
    public function getOneByWorkflowTypeAndProject($companyId, $lovWfty, $projectCode)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('workflows')
                ->select(
                    'workflows.id',
                    'workflows.lov_wfty as lovWfty',
                    'workflows.employee_id as employeeId',
                    'workflows.project_code as projectCode',
                    'wfty.val_data as valData'
                )
                ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                    $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY'],
                            ['wfty.tenant_id', $tenantId],
                            ['wfty.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['workflows.tenant_id', $this->requester->getTenantId()],
                    ['workflows.company_id', $companyId],
                    ['workflows.lov_wfty', $lovWfty],
                    ['wfty.tenant_id', $this->requester->getTenantId()],
                    ['wfty.company_id', $companyId],
                    ['workflows.project_code', $projectCode]
                ])
                ->first();
    }

    /**
     * Get one Workflow Location based on WFTY
     * @param
     */
    public function getOneByWorkflowTypeAndLocation($companyId, $lovWfty, $locationCode)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('workflows')
                ->select(
                    'workflows.id',
                    'workflows.lov_wfty as lovWfty',
                    'workflows.is_default as isDefault',
                    'workflows.unit_code as unitCode',
                    'workflows.employee_id as employeeId',
                    'workflows.location_code as locationCode',
                    'wfty.val_data as valData'
                )
                ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                    $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY'],
                            ['wfty.tenant_id', $tenantId],
                            ['wfty.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['workflows.tenant_id', $this->requester->getTenantId()],
                    ['workflows.company_id', $companyId],
                    ['workflows.lov_wfty', $lovWfty],
                    ['wfty.tenant_id', $this->requester->getTenantId()],
                    ['wfty.company_id', $companyId],
                    ['workflows.location_code', $locationCode]
                ])
                ->first();
    }


    /**
     * Get one Workflow based on WFTY
     * @param  lov_wfty
     */
    public function getOne($id, $companyId)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('workflows')
                ->select(
                    'workflows.id',
                    'workflows.lov_wfty as lovWfty',
                    'workflows.is_default as isDefault',
                    'workflows.unit_code as unitCode',
                    'workflows.employee_id as employeeId',
                    'units.name as unit',
                    'wfty.val_data as valData'
                )
                ->join('lovs as wfty', function ($join) use ($companyId, $tenantId) {
                    $join->on('wfty.key_data', '=', 'workflows.lov_wfty')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY'],
                            ['wfty.tenant_id', $tenantId],
                            ['wfty.company_id', $companyId]
                        ]);
                })
                ->leftjoin('units', function ($join) use ($companyId, $tenantId) {
                    $join->on('units.code', '=', 'workflows.unit_code')
                        ->where([
                            ['units.tenant_id', $tenantId],
                            ['units.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['workflows.tenant_id', $this->requester->getTenantId()],
                    ['workflows.company_id', $companyId],
                    ['wfty.tenant_id', $this->requester->getTenantId()],
                    ['wfty.company_id', $companyId],
                    ['workflows.id', $id]
                ])
                ->first();
    }

    /**
     * Save data workflows
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'workflows', $obj);

        return DB::table('workflows')->insertGetId($obj);
    }

    /**
     * Update data workflow to DB
     * @param id, array obj
     */
    public function update($id, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'workflows', $obj);

        DB::table('workflows')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $id]
            ])
            ->update($obj);
    }

    /**
     * Delete data workflows
     * @param  array obj, workflowId
     */
    function delete($workflowId, $companyId)
    {
        DB::table('workflows')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $companyId],
                ['id', $workflowId]
            ])
            ->delete();
    }

    public function checkDuplicateWorkflowWfty(string $lovWfty)
    {
        return (DB::table('workflows')->where([
                ['lov_wfty', $lovWfty],
                ['company_id', $this->requester->getCompanyId()],
                ['tenant_id', $this->requester->getTenantId()]
            ])->count() > 0);
    }

}
