<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class AssignmentDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get assignments based on person id
     * @param $companyId
     * @param $personId
     * @return
     */
    public function getAll($companyId, $personId)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('assignments')
                ->select(
                    'assignments.id',
                    'assignments.employee_id as employeeId',
                    'assignments.person_id as personId',
                    'employee_statuses.code as employeeStatusCode',
                    'employee_statuses.name as employeeStatusName',
                    'assignments.eff_begin as effBegin',
                    'assignments.eff_end as effEnd',
                    'positions.name as positionName',
                    'jobs.name as jobName',
                    'units.name as unitName',
                    'action_types.val_data as action',
                    'assignment_reasons.description as assignmentReasonDescription',
                    'locations.name as locationName',
                    'assignment_statuses.val_data as assignmentStatus',
                    'assignments.is_primary as isPrimary'
                )
                ->distinct()
                ->join('employee_statuses', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignments.employee_status_code', '=', 'employee_statuses.code')
                        ->where([
                            ['employee_statuses.tenant_id', $tenantId],
                            ['employee_statuses.company_id', $companyId]
                        ]);
                })
                ->join('positions', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignments.position_code', '=', 'positions.code')
                        ->where([
                            ['positions.tenant_id', $tenantId],
                            ['positions.company_id', $companyId]
                        ]);
                })
                ->join('jobs', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignments.job_code', '=', 'jobs.code')
                        ->where([
                            ['jobs.tenant_id', $tenantId],
                            ['jobs.company_id', $companyId]
                        ]);
                })
                ->join('units', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignments.unit_code', '=', 'units.code')
                        ->where([
                            ['units.tenant_id', $tenantId],
                            ['units.company_id', $companyId]
                        ]);
                })
                ->leftJoin('locations', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignments.location_code', '=', 'locations.code')
                        ->where([
                            ['locations.tenant_id', $tenantId],
                            ['locations.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as action_types', function ($join) use ($companyId, $tenantId) {
                    $join->on('action_types.key_data', '=', 'assignments.lov_acty')
                        ->where([
                            ['action_types.lov_type_code', 'ACTY'],
                            ['action_types.tenant_id', $tenantId],
                            ['action_types.company_id', $companyId]
                        ]);
                })
                ->leftJoin('assignment_reasons', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignment_reasons.code', '=', 'assignments.assignment_reason_code')
                        ->where([
                            ['assignment_reasons.tenant_id', $tenantId],
                            ['assignment_reasons.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as assignment_statuses', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignment_statuses.key_data', '=', 'assignments.lov_asta')
                        ->where([
                            ['assignment_statuses.lov_type_code', 'ASTA'],
                            ['assignment_statuses.tenant_id', $tenantId],
                            ['assignment_statuses.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['assignments.tenant_id', $tenantId],
                    ['assignments.company_id', $companyId],
                    ['assignments.person_id', $personId]
                ])
                ->get();
    }

    /**
     * Get assignments based on position code
     * @param $positionCode
     * @return
     */
    public function getAllEmployeeByPositionCode($positionCode)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('assignments')
                ->select(
                    'assignments.employee_id as employeeId',
                    'assignments.eff_begin as effBegin',
                    'assignments.eff_end as effEnd',
                    'assignments.position_code as positionCode',
                    'positions.name as positionName'
                )
                ->distinct()
                ->leftjoin('positions', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignments.position_code', '=', 'positions.code')
                        ->where([
                            ['positions.tenant_id', $tenantId],
                            ['positions.company_id', $companyId]
                        ]);
                })
                ->leftjoin('lovs as action_types', function ($join) use ($companyId, $tenantId) {
                    $join->on('action_types.key_data', '=', 'assignments.lov_acty')
                        ->where([
                            ['action_types.lov_type_code', 'ACTY'],
                            ['action_types.tenant_id', $tenantId],
                            ['action_types.company_id', $companyId]
                        ]);
                })
                ->leftjoin('lovs as assignment_statuses', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignment_statuses.key_data', '=', 'assignments.lov_asta')
                        ->where([
                            ['assignment_statuses.lov_type_code', 'ASTA'],
                            ['assignment_statuses.tenant_id', $tenantId],
                            ['assignment_statuses.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['assignments.tenant_id', $tenantId],
                    ['assignments.company_id', $companyId],
                    ['assignments.position_code', $positionCode],
                    ['assignments.lov_asta', 'ACT'],
                    ['assignments.eff_begin', '<=', Carbon::now()],
                    ['assignments.eff_end', '>=', Carbon::now()]
                ])
                ->get();
    }



    /**
     * Get assignments based on position code
     * @param $positionCode
     * @return
     */
    public function getAllEmployeePercentFitByPositionCode($positionCode, $offset, $limit)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $today = Carbon::today();
        return
        DB::table('assignments')
        ->selectRaw(
            'assignments.id,' .
            'assignments.employee_id as "employeeId",' .
            '(CONCAT(persons.first_name,\' \', persons.last_name)) as "fullName"'
        )
        ->join('persons', function ($join) use ($companyId, $tenantId, $today) {
            $join->on('persons.id', '=', 'assignments.person_id')
                ->where([
                    ['persons.tenant_id', $tenantId],
                    ['persons.eff_begin', '<=', $today],
                    ['persons.eff_end', '>=', $today]
                ]);
        })
        ->where([
            ['assignments.tenant_id', $tenantId],
            ['assignments.company_id', $companyId],
            ['assignments.position_code', $positionCode],
            ['assignments.eff_end', '>=', Carbon::now()],
            ['assignments.eff_begin', '<=', Carbon::now()],
            ['assignments.lov_asta', 'ACT']
        ])
        ->limit($limit)
        ->offset($offset)
        ->get();
    }


    /*
    |-----------------------------------------------
    | get total row employee fit per position code
    |-----------------------------------------------
    |
    |
    */
    public function getTotalRowsAllEmployeePercentFitByPositionCode($code) {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $today = Carbon::today();
        return
        DB::table('assignments')
        ->select(
            'assignments.id'
        )
        ->join('persons', function ($join) use ($companyId, $tenantId, $today) {
            $join->on('persons.id', '=', 'assignments.person_id')
                ->where([
                    ['persons.tenant_id', $tenantId],
                    ['persons.eff_begin', '<=', $today],
                    ['persons.eff_end', '>=', $today]
                ]);
        })
        ->where([
            ['assignments.tenant_id', $tenantId],
            ['assignments.company_id', $companyId],
            ['assignments.position_code', $code],
            ['assignments.eff_end', '>=', Carbon::now()],
            ['assignments.eff_begin', '<=', Carbon::now()],
            ['assignments.lov_asta', 'ACT']
        ])
        ->get()->count();
    }

    public function getEmployeeByEmployeeId($employeeId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('assignments')
                ->selectRaw(
                    '(CONCAT(persons.first_name,\' \', persons.last_name)) as "employeeName",' .
                    'persons.file_photo as "filePhoto",' .
                    'assignments.position_code as positionCode,' .
                    'assignments.employee_id as "employeeId",' .
                    'positions.name as positionName'
                )
                ->leftjoin('positions', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignments.position_code', '=', 'positions.code')
                        ->where([
                            ['positions.tenant_id', $tenantId],
                            ['positions.company_id', $companyId]
                        ]);
                })
                ->leftjoin('persons', function ($join) use ($companyId, $tenantId) {
                    $join->on('persons.id', '=', 'assignments.person_id')
                        ->where([
                            ['persons.tenant_id', $tenantId]
                        ]);
                })
                ->where([
                    ['assignments.tenant_id', $tenantId],
                    ['assignments.company_id', $companyId],
                    ['assignments.employee_id', $employeeId],
                    ['assignments.lov_asta', 'ACT'],
                    ['assignments.eff_begin', '<=', Carbon::now()],
                    ['assignments.eff_end', '>=', Carbon::now()]
                ])
                ->first();
    }

    public function getEmployeeByPositionCode($positionCode)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('assignments')
                ->selectRaw(
                    '(CONCAT(persons.first_name,\' \', persons.last_name)) as "employeeName",' .
                    'persons.file_photo as "filePhoto",' .
                    'assignments.position_code as positionCode,' .
                    'assignments.employee_id as "employeeId",' .
                    'positions.name as positionName'
                )
                ->leftjoin('positions', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignments.position_code', '=', 'positions.code')
                        ->where([
                            ['positions.tenant_id', $tenantId],
                            ['positions.company_id', $companyId]
                        ]);
                })
                ->leftjoin('persons', function ($join) use ($companyId, $tenantId) {
                    $join->on('persons.id', '=', 'assignments.person_id')
                        ->where([
                            ['persons.tenant_id', $tenantId]
                        ]);
                })
                ->where([
                    ['assignments.tenant_id', $tenantId],
                    ['assignments.company_id', $companyId],
                    ['assignments.position_code', $positionCode],
                    ['assignments.lov_asta', 'ACT'],
                    ['assignments.eff_begin', '<=', Carbon::now()],
                    ['assignments.eff_end', '>=', Carbon::now()]
                ])
                ->first();
    }

    public function getPositionByUnitCode($unitCode)
    {

        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('positions')
                ->select(
                    'positions.code'
                )
                ->where([
                    ['unit_code', $unitCode],
                    ['company_id', $companyId],
                    ['tenant_id', $tenantId],
                    ['is_head', true]
                ])
                ->first();
    }

    public function getLov($personId)
    {
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        return
            DB::table('assignments')
                ->select(
                    'assignments.employee_id as employeeId',
                    'assignments.eff_begin'
                )
                ->distinct('assignments.employee_id')
                ->where([
                    ['assignments.tenant_id', $tenantId],
                    ['assignments.company_id', $companyId],
                    ['assignments.person_id', $personId]
                ])
                ->orderBy('assignments.eff_begin')
                ->get();
    }

    /**
     * Get assignment based on person id and id
     * @param $companyId
     * @param $personId
     * @param $assignmentId
     * @return
     */
    public function getOneByEmployeeId($employeeId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('assignments')
                ->select(
                    'assignments.employee_id as employeeId',
                    'assignments.eff_begin as effBegin',
                    'assignments.eff_end as effEnd',
                    'assignments.position_code as positionCode',
                    'superiors.employee_id as supervisorId',
                    'persons.id as personId',
                    'persons.first_name as firstName',
                    'persons.last_name as lastName',
                    'persons.file_photo as filePhoto',
                    'assignments.unit_code as unitCode',
                    'units.name as unitName',
                    'assignments.location_code as locationCode',
                    'positions.name as positionName'
                )
                ->leftjoin('units', function ($join) use ($companyId, $tenantId) {
                    $join->on('units.code', '=', 'assignments.unit_code')
                        ->where([
                            ['units.tenant_id', $tenantId],
                            ['units.company_id', $companyId]
                        ]);
                })
                ->leftjoin('positions', function ($join) use ($companyId, $tenantId) {
                    $join->on('positions.code', '=', 'assignments.position_code')
                        ->where([
                            ['positions.tenant_id', $tenantId],
                            ['positions.company_id', $companyId]
                        ]);
                })
                ->leftjoin('persons', function ($join) use ($companyId, $tenantId) {
                    $join->on('persons.id', '=', 'assignments.person_id')
                        ->where([
                            ['persons.tenant_id', $tenantId]
                        ]);
                })
                ->leftjoin('assignments as superiors', function ($join) use ($companyId, $tenantId) {
                    $join->on('superiors.person_id', '=', 'assignments.supervisor_id')
                        ->where([
                            ['superiors.tenant_id', $tenantId]
                        ]);
                })
                ->where([
                    ['assignments.tenant_id', $tenantId],
                    ['assignments.employee_id', $employeeId],
                    ['assignments.eff_begin', '<=', Carbon::now()],
                    ['assignments.eff_end', '>=', Carbon::now()],
                    ['assignments.lov_asta', 'ACT']
                ])
                ->first();
    }

    public function getHistoryEmployeeBenefit($personId)
    {
        return
            DB::table('assignments')
                ->select(
                    'assignments.employee_id as employeeId',
                    'assignments.eff_begin as effBegin',
                    'assignments.eff_end as effEnd',
                    'assignments.position_code as positionCode'
                //                    'persons.first_name as firstName',
                //                    'persons.last_name as lastName'
                )
                //                ->leftJoin('persons', 'persons.id', '=', 'assignments.person_id')
                //                ->leftJoin('assignments', 'assignments.person_id', '=', 'persons.id')
                ->where([
                    ['assignments.tenant_id', $this->requester->getTenantId()],
                    ['assignments.person_id', $personId],
                    ['assignments.eff_end', '<', Carbon::now()]
                ])
                ->get();
    }

    /**
     * Get assignment based on person id and id
     * @param $companyId
     * @param $personId
     * @param $assignmentId
     * @return
     */
    public function getOne($companyId, $personId, $assignmentId)
    {
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('assignments')
                ->selectRaw(
                    'assignments.id,' .
                    'assignments.is_primary as "isPrimary",' .
                    'assignments.lov_acty as "lovActy",' .
                    'action_types.val_data as "actionType",' .
                    'assignments.person_id as "personId",' .
                    'assignments.employee_id as "employeeId",' .
                    'assignments.assignment_reason_code as "assignmentReasonCode",' .
                    'assignment_reasons.description as "reason",' .
                    'assignments.unit_code as "unitCode",' .
                    'units.name as "unit",' .
                    'assignments.job_code as "jobCode",' .
                    'jobs.name as "job",' .
                    'assignments.position_code as "positionCode",' .
                    'positions.name as "position",' .
                    'positions.is_head as "positionIsHead",' .
                    'assignments.lov_asta as "lovAsta",' .
                    'assignment_statuses.val_data as "assignmentStatus",' .
                    'assignments.location_code as "locationCode",' .
                    'locations.name as "location",' .
                    'assignments.employee_status_code as "employeeStatusCode",' .
                    'employee_statuses.name as "employeeStatusName", ' .
                    'assignments.eff_begin as "effBegin",' .
                    'assignments.eff_end as "effEnd",' .
                    'assignments.supervisor_id as "supervisorId",' .
                    '(supervisors.first_name || \' \' || supervisors.last_name) as "supervisor",' .
                    'assignments.cost_center_code as "costCenterCode",' .
                    'cost_centers.name as "costCenter",' .
                    'assignments.grade_code as "gradeCode",' .
                    'grades.name as "grade",' .
                    'assignments.assignment_doc_number as "assignmentDocNumber",' .
                    'assignments.file_assignment_doc as "fileAssignmentDoc",' .
                    'assignments.note',
                    []
                )
                ->leftJoin('positions', function ($join) use ($companyId, $tenantId) {
                    $join->on('positions.code', '=', 'assignments.position_code')
                        ->where([
                            ['positions.tenant_id', $tenantId],
                            ['positions.company_id', $companyId]
                        ]);
                })
                ->leftJoin('employee_statuses', function ($join) use ($companyId, $tenantId) {
                    $join->on('employee_statuses.code', '=', 'assignments.employee_status_code')
                        ->where([
                            ['employee_statuses.tenant_id', $tenantId],
                            ['employee_statuses.company_id', $companyId]
                        ]);
                })
                ->leftJoin('assignment_reasons', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignment_reasons.code', '=', 'assignments.assignment_reason_code')
                        ->where([
                            ['assignment_reasons.tenant_id', $tenantId],
                            ['assignment_reasons.company_id', $companyId]
                        ]);
                })
                ->leftJoin('units', function ($join) use ($companyId, $tenantId) {
                    $join->on('units.code', '=', 'assignments.unit_code')
                        ->where([
                            ['units.tenant_id', $tenantId],
                            ['units.company_id', $companyId]
                        ]);
                })
                ->leftJoin('jobs', function ($join) use ($companyId, $tenantId) {
                    $join->on('jobs.code', '=', 'assignments.job_code')
                        ->where([
                            ['jobs.tenant_id', $tenantId],
                            ['jobs.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as action_types', function ($join) use ($companyId, $tenantId) {
                    $join->on('action_types.key_data', '=', 'assignments.lov_acty')
                        ->where([
                            ['action_types.lov_type_code', 'ACTY'],
                            ['action_types.tenant_id', $tenantId],
                            ['action_types.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as assignment_statuses', function ($join) use ($companyId, $tenantId) {
                    $join->on('assignment_statuses.key_data', '=', 'assignments.lov_asta')
                        ->where([
                            ['assignment_statuses.lov_type_code', 'ASTA'],
                            ['assignment_statuses.tenant_id', $tenantId],
                            ['assignment_statuses.company_id', $companyId]
                        ]);
                })
                ->leftJoin('locations', function ($join) use ($companyId, $tenantId) {
                    $join->on('locations.code', '=', 'assignments.location_code')
                        ->where([
                            ['locations.tenant_id', $tenantId],
                            ['locations.company_id', $companyId]
                        ]);
                })
                ->leftJoin('persons as supervisors', function ($join) use ($companyId, $tenantId) {
                    $join->on('supervisors.id', '=', 'assignments.supervisor_id')
                        ->where([
                            ['supervisors.tenant_id', $tenantId]
                        ]);
                })
                ->leftJoin('cost_centers', function ($join) use ($companyId, $tenantId) {
                    $join->on('cost_centers.code', '=', 'assignments.cost_center_code')
                        ->where([
                            ['cost_centers.tenant_id', $tenantId],
                            ['cost_centers.company_id', $companyId]
                        ]);
                })
                ->leftJoin('grades', function ($join) use ($companyId, $tenantId) {
                    $join->on('grades.code', '=', 'assignments.grade_code')
                        ->where([
                            ['grades.tenant_id', $tenantId],
                            ['grades.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['assignments.tenant_id', $tenantId],
                    ['assignments.company_id', $companyId],
                    ['assignments.person_id', $personId],
                    ['assignments.id', $assignmentId]
                ])
                ->first();
    }

    /**
     * Get assignment based on person id and id
     * @param $companyId
     * @param $personId
     * @param $assignmentId
     * @return
     */
    public function getFirstAssignment($personId)
    {
        return
            DB::table('assignments')
                ->select(
                    'eff_begin as firstEffBegin'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['person_id', $personId],
                    ['lov_acty', 'HIRE'],
                    ['is_primary', true]
                ])
                ->orderBy('eff_begin', 'desc')
                ->first();
    }

    /**
     * Insert data Assignment to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'assignments', $obj);

        return DB::table('assignments')->insertGetId($obj);
    }

    public function cloneFromTransaction($assignmentId)
    {
        DB::statement(
            '
            INSERT INTO assignments
            SELECT
                tenant_id, company_id, id, n_person_id as person_id, n_eff_begin as eff_begin,
                n_eff_end as eff_end, n_is_primary as is_primary, n_employee_id as employee_id,
                n_employee_status_code as employee_status_code, n_cost_center_code as cost_center_code,
                n_grade_code as grade_code, n_lov_asta as lov_asta, n_supervisor_id as supervisor_id,
                created_by, created_at, updated_by, updated_at, n_file_assignment_doc as file_assignment_doc,
                n_note as note, now(), n_assignment_doc_number as assignment_doc_number,
                n_location_code as location_code, n_unit_code as unit_code, n_job_code as job_code,
                n_position_code as position_code, n_assignment_reason_code as assignment_reason_code,
                n_lov_acty as lov_acty
            FROM assignment_transactions
            '
        );
    }

    /**
     * Update data Assignment to DB
     * @param $companyId
     * @param $personId
     * @param $assignmentId
     * @param $obj
     */
    public function update($companyId, $personId, $assignmentId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'assignments', $obj);

        DB::table('assignments')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $companyId],
                ['person_id', $personId],
                ['id', $assignmentId]
            ])
            ->update($obj);
    }

    public function getPrevPrimaryAssignment($companyId, $personId, $effBegin, $id = null)
    {
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('assignments')
                ->select(
                    'assignments.id',
                    'positions.name as positionName',
                    'employee_statuses.name as employeeTypeName',
                    'assignments.eff_begin as effBegin',
                    'assignments.eff_end as effEnd'
                )
                ->join('positions', function ($join) use ($companyId, $tenantId) {
                    $join->on('positions.code', '=', 'assignments.position_code')
                        ->where([
                            ['positions.tenant_id', $tenantId],
                            ['positions.company_id', $companyId]
                        ]);
                })
                ->join('employee_statuses', function ($join) use ($companyId, $tenantId) {
                    $join->on('employee_statuses.code', '=', 'assignments.employee_status_code')
                        ->where([
                            ['employee_statuses.tenant_id', $tenantId],
                            ['employee_statuses.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['assignments.tenant_id', $this->requester->getTenantId()],
                    ['assignments.company_id', $companyId],
                    ['assignments.person_id', $personId],
                    ['assignments.id', '!=', $id],
                    ['assignments.eff_begin', '<=', $effBegin],
                    ['assignments.is_primary', true]
                ])
                ->orderBy('assignments.eff_begin', 'DESC')
                ->limit(1)
                ->first();
    }

    public function getNextPrimaryAssignment($companyId, $personId, $effBegin, $id)
    {
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('assignments')
                ->select(
                    'assignments.id',
                    'positions.name as positionName',
                    'employee_statuses.name as employeeTypeName',
                    'assignments.eff_begin as effBegin',
                    'assignments.eff_end as effEnd'
                )
                ->join('positions', function ($join) use ($companyId, $tenantId) {
                    $join->on('positions.code', '=', 'assignments.position_code')
                        ->where([
                            ['positions.tenant_id', $tenantId],
                            ['positions.company_id', $companyId]
                        ]);
                })
                ->join('employee_statuses', function ($join) use ($companyId, $tenantId) {
                    $join->on('employee_statuses.code', '=', 'assignments.employee_status_code')
                        ->where([
                            ['employee_statuses.tenant_id', $tenantId],
                            ['employee_statuses.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['assignments.tenant_id', $this->requester->getTenantId()],
                    ['assignments.company_id', $companyId],
                    ['assignments.person_id', $personId],
                    ['assignments.id', '!=', $id],
                    ['assignments.eff_begin', '>', $effBegin],
                    ['assignments.is_primary', true]
                ])
                ->orderBy('assignments.eff_begin', 'ASC')
                ->limit(1)
                ->first();
    }

    public function getLastPrimaryAssignment($companyId, $personId)
    {
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('assignments')
                ->select(
                    'assignments.id',
                    'assignments.is_primary as isPrimary',
                    'assignments.employee_id as employeeId',
                    'action_types.val_data as actionType',
                    'assignments.employee_status_code as employeeStatusCode',
                    'employee_statuses.name as employeeStatusName',
                    'employee_statuses.working_month as employeeStatusWorkingMonth',
                    'positions.code as positionCode',
                    'positions.name as position',
                    'jobs.code as jobCode',
                    'jobs.name as job',
                    'units.code as unitCode',
                    'units.name as unit',
                    'locations.code as locationCode',
                    'locations.name as location',
                    'assignments.eff_begin as effBegin',
                    'assignments.eff_end as effEnd',
                    'cost_centers.code as costCenterCode',
                    'cost_centers.name as costCenter',
                    'grades.code as gradeCode',
                    'grades.name as grade',
                    'assignments.supervisor_id as supervisorId'
                )
                ->join('positions', function ($join) use ($companyId, $tenantId) {
                    $join->on('positions.code', '=', 'assignments.position_code')
                        ->where([
                            ['positions.tenant_id', $tenantId],
                            ['positions.company_id', $companyId]
                        ]);
                })
                ->join('jobs', function ($join) use ($companyId, $tenantId) {
                    $join->on('jobs.code', '=', 'assignments.job_code')
                        ->where([
                            ['jobs.tenant_id', $tenantId],
                            ['jobs.company_id', $companyId]
                        ]);
                })
                ->join('units', function ($join) use ($companyId, $tenantId) {
                    $join->on('units.code', '=', 'assignments.unit_code')
                        ->where([
                            ['units.tenant_id', $tenantId],
                            ['units.company_id', $companyId]
                        ]);
                })
                ->leftJoin('locations', function ($join) use ($companyId, $tenantId) {
                    $join->on('locations.code', '=', 'assignments.location_code')
                        ->where([
                            ['locations.tenant_id', $tenantId],
                            ['locations.company_id', $companyId]
                        ]);
                })
                ->leftJoin('cost_centers', function ($join) use ($companyId, $tenantId) {
                    $join->on('cost_centers.code', '=', 'assignments.cost_center_code')
                        ->where([
                            ['cost_centers.tenant_id', $tenantId],
                            ['cost_centers.company_id', $companyId]
                        ]);
                })
                ->leftJoin('grades', function ($join) use ($companyId, $tenantId) {
                    $join->on('grades.code', '=', 'assignments.grade_code')
                        ->where([
                            ['grades.tenant_id', $tenantId],
                            ['grades.company_id', $companyId]
                        ]);
                })
                ->join('lovs as action_types', function ($join) use ($companyId, $tenantId) {
                    $join->on('action_types.key_data', '=', 'assignments.lov_acty')
                        ->where([
                            ['action_types.lov_type_code', 'ACTY'],
                            ['action_types.tenant_id', $tenantId],
                            ['action_types.company_id', $companyId]
                        ]);
                })
                ->join('employee_statuses', function ($join) use ($companyId, $tenantId) {
                    $join->on('employee_statuses.code', '=', 'assignments.employee_status_code')
                        ->where([
                            ['employee_statuses.tenant_id', $tenantId],
                            ['employee_statuses.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['assignments.tenant_id', $tenantId],
                    ['assignments.company_id', $companyId],
                    ['assignments.person_id', $personId],
                    ['assignments.is_primary', true],
                    ['assignments.eff_begin', '<=', Carbon::now()]
                ])
                ->orderBy('assignments.eff_begin', 'DESC')
                ->limit(1)
                ->first();
    }

    public function clearPrimary($companyId, $personId, $effBegin, $effEnd)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'assignment', ['is_primary' => false]);

        DB::table('assignments')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $companyId],
                ['person_id', $personId],
                ['is_primary', true],
            ])
            ->where(function ($query) use ($effBegin, $effEnd) {
                $query->whereBetween('eff_begin', [$effBegin, $effEnd])
                    ->orWhereBetween('eff_end', [$effBegin, $effEnd]);
            })
            ->update([
                'is_primary' => false
            ]);
    }

    public function cloneLastPrimaryAssignment($termination)
    {
        DB::insert(
            '
                INSERT INTO assignment_transactions(tenant_id, company_id, n_person_id, n_eff_begin,
                    n_eff_end, n_is_primary, n_employee_id, n_employee_status_code, n_cost_center_code,
                    n_grade_code, n_lov_asta, n_supervisor_id, created_by, created_at, updated_by,
                    updated_at, n_file_assignment_doc, n_note, n_final_process_date,
                    n_assignment_doc_number, n_location_code, n_unit_code, n_job_code, n_position_code,
                    n_assignment_reason_code, n_lov_acty, is_approved)
                SELECT
                    tenant_id, company_id, person_id, :eff_begin, :eff_end, is_primary, employee_id,
                    employee_status_code, cost_center_code, grade_code, :lov_asta, supervisor_id,
                    created_by, created_at, updated_by, now(), :file_assignment_doc, note, :final_process_date,
                    :assignment_doc_number, location_code, unit_code, job_code, position_code,
                    :assignment_reason_code, :lov_acty, FALSE
                FROM assignments
                WHERE
                    tenant_id = :tenant_id AND
                    company_id = :company_id AND
                    person_id = :person_id AND
                    is_primary = TRUE AND
                    eff_begin <= now()
                ORDER BY eff_begin DESC
                LIMIT 1;
            ',
            $termination
        );
    }

    public function setPrimaryAssignmentsStatus($companyId, $personId, $status)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('assignments')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $companyId],
                ['person_id', $personId],
                ['is_primary', true]
            ])
            ->update([
                'lov_asta' => $status
            ]);
    }

    public function endSecondaryAssignments($companyId, $personId, $effBegin)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('assignments')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $companyId],
                ['person_id', $personId],
                ['is_primary', false]
            ])
            ->update([
                'eff_end' => Carbon::parse($effBegin)->subDay()->format('Y-m-d')
            ]);
    }

    public function deleteLastTerminationAssignment($companyId, $personId)
    {
        DB::table('assignments')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $companyId],
                ['person_id', $personId],
                ['lov_acty', 'TERM']
            ])
            ->orderBy('eff_begin', 'DESC')
            ->take(1)
            ->delete();
    }

    //get active person for workflow
    public function getActivePersonLastAssignmentStatus($companyId, $employeeId)
    {
        $today = Carbon::today();
        $tenantId = $this->requester->getTenantId();

        return DB::table('assignments')
            ->select(
                'assignments.employee_id as employeeId',
                'assignments.lov_asta as status'
            )
            ->join('persons', function ($join) use ($tenantId, $today) {
                $join->on('persons.id', '=', 'assignments.person_id')
                    ->where([
                        ['persons.tenant_id', $tenantId],
                        ['persons.eff_begin', '<=', $today],
                        ['persons.eff_end', '>=', $today]
                    ]);
            })
            ->where([
                ['assignments.tenant_id', $this->requester->getTenantId()],
                ['assignments.company_id', $companyId],
                ['assignments.employee_id', $employeeId]
            ])
            ->orderBy('assignments.eff_begin', 'DESC')
            ->first();
    }

    //check active employee for workflow
    public function isAssignmentActive($companyId, $employeeId)
    {
        $today = Carbon::today();
        $tenantId = $this->requester->getTenantId();

        return DB::table('assignments')
            ->join('persons', function ($join) use ($tenantId, $today) {
                $join->on('persons.id', '=', 'assignments.person_id')
                    ->where([
                        ['persons.tenant_id', $tenantId],
                        ['persons.eff_begin', '<=', $today],
                        ['persons.eff_end', '>=', $today]
                    ]);
            })
            ->where([
                ['assignments.tenant_id', $this->requester->getTenantId()],
                ['assignments.company_id', $companyId],
                ['assignments.employee_id', $employeeId]
            ])
            ->orderBy('assignments.eff_begin', 'DESC')
            ->first();
    }

    public function isPersonActiveInCompany($companyId, $personId)
    {
        $today = Carbon::today();
        return DB::table('assignments')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $companyId],
                    ['person_id', $personId],
                    ['eff_begin', '<=', $today],
                    ['eff_end', '>=', $today]
                ])
                ->count() > 0;
    }

    public function doesEmployeeIdBelongToPerson($companyId, $personId, $employeeId)
    {
        return DB::table('assignments')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $companyId],
                    ['person_id', $personId],
                    ['employee_id', $employeeId]
                ])
                ->count() > 0;
    }

    public function getOneLastAssignmentByPersonId($personId)
    {
        return
            DB::table('assignments')
                ->select(
                    'assignments.id',
                    'assignments.person_id as personId',
                    'assignments.employee_id as employeeId',
                    'assignments.location_code as locationCode',
                    'assignments.position_code as positionCode',
                    'assignments.job_code as jobCode',
                    'assignments.unit_code as unitCode',
                    'assignments.eff_begin as effBegin',
                    'assignments.eff_end as effEnd',
                    'assignments.supervisor_id as supervisorId'
                )
                ->where([
                    ['assignments.tenant_id', $this->requester->getTenantId()],
                    ['assignments.company_id', $this->requester->getCompanyId()],
                    ['assignments.person_id', $personId],
                    ['assignments.is_primary', true]
                ])
                ->orderBy('assignments.eff_end', 'DESC')
                ->first();
    }

    public function getOneLastAssignmentByEmployeeId($employeeId)
    {
        return
            DB::table('assignments')
                ->select(
                    'assignments.id',
                    'assignments.person_id as personId',
                    'assignments.employee_id as employeeId',
                    'assignments.location_code as locationCode',
                    'assignments.position_code as positionCode',
                    'assignments.job_code as jobCode',
                    'assignments.unit_code as unitCode',
                    'assignments.eff_begin as effBegin',
                    'assignments.eff_end as effEnd',
                    'assignments.supervisor_id as supervisorId'
                )
                ->where([
                    ['assignments.tenant_id', $this->requester->getTenantId()],
                    ['assignments.company_id', $this->requester->getCompanyId()],
                    ['assignments.employee_id', $employeeId],
                    ['assignments.is_primary', true]
                ])
                ->orderBy('assignments.eff_end', 'DESC')
                ->first();
    }

    public function getOneEmployeeId($employeeId)
    {
        return
            DB::table('assignments')
                ->select(
                    'assignments.id',
                    'assignments.person_id as personId',
                    'assignments.employee_id as employeeId'
                )
                ->where([
                    ['assignments.tenant_id', $this->requester->getTenantId()],
                    ['assignments.company_id', $this->requester->getCompanyId()],
                    ['assignments.employee_id', $employeeId]
                ])
                ->orderBy('assignments.eff_end', 'DESC')
                ->first();
    }

    /**
     * check if position isHead is exist in this position
     */
    public function checkPositionVacant($positionCode, $beginDate, $endDate, $employeeId)
    {
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        $today = Carbon::today();

        return
            DB::table('assignments')
                ->selectRaw(
                    'assignments.id,' .
                    'assignments.person_id as "personId",' .
                    'assignments.employee_id as "employeeId",' .
                    'assignments.eff_begin as "effBegin",' .
                    'assignments.eff_end as "effEnd",' .
                    '(CONCAT(persons.first_name,\' \', persons.last_name)) as "fullName"'
                )
                ->join('persons', function ($join) use ($companyId, $tenantId, $today) {
                    $join->on('persons.id', '=', 'assignments.person_id')
                        ->where([
                            ['persons.tenant_id', $tenantId],
                            ['persons.eff_begin', '<=', $today],
                            ['persons.eff_end', '>=', $today]
                        ]);
                })
                ->where([
                    ['assignments.tenant_id', $tenantId],
                    ['assignments.company_id', $companyId],
                    ['assignments.position_code', $positionCode],
                    ['assignments.eff_end', '>=', $beginDate],
                    ['assignments.eff_begin', '<=', $endDate],
                    ['assignments.lov_asta', 'ACT'],
                    ['assignments.employee_id', '!=', $employeeId]
                ])
                ->where(function ($query) use ($beginDate, $endDate) {

                    return $query->where('assignments.eff_begin', '<=', $endDate)
                        ->orWhere('assignments.eff_end', '>=', $beginDate);
                })
                ->get();
    }
}
