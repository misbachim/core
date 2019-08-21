<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class EmployeeStatusGradeDao
{
    public function __construct(Requester $requester)
    {
        $this->connection = 'payroll';
        $this->requester = $requester;
    }

    /**
     * Get all employeeStatusGrade for one employee status
     * @param $employeeStatusId
     * @return
     */
    public function getAll($employeeStatusId)
    {
        return
            DB::table('employee_status_grades')
                ->select(
                    'grade_code as gradeCode',
                    'grades.name as grade',
                    'status_employee_id as statusEmployeeId',
                    'payroll_group_code as payrollGroupCode',
                    'benefit_group_code as benefitGroupCode'
                )
                ->leftjoin('grades', function ($join) {
                    $join
                        ->on('grades.code', '=', 'employee_status_grades.grade_code');
                })
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['status_employee_id', $employeeStatusId]
                ])
                ->get();
    }

    /**
     * Insert data employee status grade to DB
     * @param  array obj
     */
    public function save($obj)
    {
        DB::table('employee_status_grades')->insert($obj);
    }

    /**
     * Delete data employee status grade from DB.
     * @param $employeeStatusId
     */
    public function delete($employeeStatusId)
    {
        DB::table('employee_statuse_grades')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['status_employee_id', $employeeStatusId]
        ])
        ->delete();
    }
}
