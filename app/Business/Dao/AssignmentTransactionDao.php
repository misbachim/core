<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class AssignmentTransactionDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get assignmentTransactions based on one company
     * @param $companyId
     * @return
     */
    public function getAll($companyId)
    {
        return
            DB::table('assignment_transactions')
                ->select(
                    'assignment_transactions.id',
                    'assignment_transactions.n_employee_id as employeeId',
                    'employee_statuses.name as employeeStatusName',
                    'persons.id as personId',
                    'persons.first_name as personFirstName',
                    'persons.last_name as personLastName',
                    'assignment_transactions.n_eff_begin as effBegin',
                    'assignment_transactions.n_eff_end as effEnd',
                    'positions.name as positionName',
                    'action_types.val_data as action',
                    'assignment_reasons.description as assignmentReasonDescription',
                    'locations.name as locationName',
                    'assignment_statuses.val_data as assignmentStatus',
                    'assignment_transactions.n_is_primary as isPrimary'
                )
                ->leftJoin('persons', 'persons.id', '=', 'assignment_transactions.n_person_id')
                ->leftjoin('employee_statuses', 'assignment_transactions.n_employee_status_code', '=', 'employee_statuses.code')
                ->leftjoin('positions', 'assignment_transactions.n_position_code', '=', 'positions.code')
                ->leftjoin('locations', 'assignment_transactions.n_location_code', '=', 'locations.code')
                ->leftjoin('lovs as action_types', 'action_types.key_data', '=', 'assignment_transactions.n_lov_acty')
                ->leftjoin('assignment_reasons', 'assignment_reasons.code', '=', 'assignment_transactions.n_assignment_reason_code')
                ->leftjoin('lovs as assignment_statuses', 'assignment_statuses.key_data', '=', 'assignment_transactions.n_lov_asta')
                ->where([
                    ['assignment_transactions.tenant_id', $this->requester->getTenantId()],
                    ['assignment_transactions.company_id', $companyId],
                    ['is_approved', false]
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
        return
            DB::table('assignment_transactions')
                ->select(
                    'assignment_transactions.id',
                    'assignment_transactions.n_is_primary as isPrimary',
                    'assignment_transactions.n_lov_acty as lovActy',
                    'persons.id as personId',
                    'persons.first_name as personFirstName',
                    'persons.last_name as personLastName',
                    'assignment_transactions.n_employee_id as employeeId',
                    'assignment_reasons.description as assignmentReasonDescription',
                    'units.name as unitName',
                    'jobs.name as jobName',
                    'positions.name as positionName',
                    'assignment_statuses.val_data as assignmentStatus',
                    'locations.name as locationName',
                    'employee_statuses.name as employeeStatusName',
                    'assignment_transactions.n_eff_begin as effBegin',
                    'assignment_transactions.n_eff_end as effEnd',
                    'supervisors.first_name as supervisorFirstName',
                    'supervisors.last_name as supervisorLastName',
                    'cost_centers.name as costCenterName',
                    'grades.name as gradeName',
                    'assignment_transactions.n_assignment_doc_number as assignmentDocNumber',
                    'assignment_transactions.n_file_assignment_doc as fileAssignmentDoc',
                    'assignment_transactions.n_note as note'
                )
                ->leftJoin('persons', 'persons.id', '=', 'assignment_transactions.n_person_id')
                ->leftJoin('assignment_reasons', 'assignment_reasons.code', '=', 'assignment_transactions.n_assignment_reason_code')
                ->leftJoin('units', 'units.code', '=', 'assignment_transactions.n_unit_code')
                ->leftJoin('jobs', 'jobs.code', '=', 'assignment_transactions.n_job_code')
                ->leftJoin('positions', 'positions.code', '=', 'assignment_transactions.n_position_code')
                ->leftJoin('lovs as assignment_statuses', 'assignment_statuses.key_data', '=', 'assignment_transactions.n_lov_asta')
                ->leftJoin('locations', 'locations.code', '=', 'assignment_transactions.n_location_code')
                ->leftjoin('employee_statuses', 'assignment_transactions.n_employee_status_code', '=', 'employee_statuses.code')
                ->leftJoin('persons as supervisors', 'supervisors.id', '=', 'assignment_transactions.n_supervisor_id')
                ->leftJoin('cost_centers', 'cost_centers.code', '=', 'assignment_transactions.n_cost_center_code')
                ->leftJoin('grades', 'grades.code', '=', 'assignment_transactions.n_grade_code')
                ->where([
                    ['assignment_transactions.tenant_id', $this->requester->getTenantId()],
                    ['assignment_transactions.company_id', $companyId],
                    ['assignment_transactions.n_person_id', $personId],
                    ['assignment_transactions.id', $assignmentId]
                ])
                ->first();
    }

    /**
     * Insert data AssignmentTransaction to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'assignment_transactions', $obj);

        return DB::table('assignment_transactions')->insertGetId($obj);
    }

    /**
     * Update data AssignmentTransaction to DB
     * @param $companyId
     * @param $personId
     * @param $assignmentId
     * @param $obj
     */
    public function update($companyId, $personId, $assignmentId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'assignment_transactions', $obj);

        DB::table('assignment_transactions')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $companyId],
            ['n_person_id', $personId],
            ['id', $assignmentId]
        ])
        ->update($obj);
    }

    public function clearPrimary($companyId, $personId)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'assignment_transactions', ['n_is_primary' => false]);

        DB::table('assignment_transactions')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $companyId],
                ['n_person_id', $personId],
                ['n_is_primary', true]
            ])
            ->update([
                'n_is_primary' => false
            ]);
    }

    public function hasUnapprovedAssignments($companyId, $personId)
    {
        $count = DB::table('assignment_transactions')
                     ->select('id')
                     ->where([
                         ['tenant_id', $this->requester->getTenantId()],
                         ['company_id', $companyId],
                         ['n_person_id', $personId],
                         ['is_approved', false]
                     ])
                     ->count();
        return $count > 0;
    }

}
