<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class EmployeeStatusDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all employeeType for one person
     * @param $companyId
     * @return
     */
    public function getAll()
    {
        return
            DB::table('employee_statuses')
            ->select(
                'code',
                'name',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'payroll_group_code as payrollGroupCode',
                'benefit_group_code as benefitGroupCode',
                'working_month as workingMonth',
                'is_permanent as isPermanent',
                'generate_paklaring as generatePaklaring',
                'paklaring_templates_id as paklaringTemplatesId',
                'employee_status_document_templates_id as employeeStatusDocumentTemplatesId'
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
     * Get one employeeType based on employeeTypeCode
     * @param $companyId
     * @param $employeeStatusCode
     * @return
     */
    public function getOne($employeeStatusCode)
    {
        return
            DB::table('employee_statuses')
            ->select(
                'id',
                'code',
                'name',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'benefit_group_code as benefitGroupCode',
                'payroll_group_code as payrollGroupCode',
                'working_month as workingMonth',
                'generate_paklaring as generatePaklaring',
                'isPermanent as isPermanent',
                'paklaring_templates_id as paklaringTemplatesId',
                'employee_status_document_templates_id as employeeStatusDocumentTemplatesId'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['code', $employeeStatusCode]
            ])
            ->first();
    }

    /**
     * Insert data employeeType to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'employee_statuses', $obj);

        DB::table('employee_statuses')->insert($obj);
    }

    /**
     * Update data employeeType to DB
     * @param $companyId
     * @param $employeeStatusCode
     * @param $obj
     */
    public function update($employeeStatusCode, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'employee_statuses', $obj);

        DB::table('employee_statuses')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['code', $employeeStatusCode]
            ])
            ->update($obj);
    }

    /**
     * Delete data employeeType from DB.
     * @param $companyId
     * @param $employeeTypeCode
     */
    public function delete($employeeStatusCode)
    {
        DB::table('employee_statuses')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['code', $employeeStatusCode]
            ])
            ->delete();
    }

    public function isCodeDuplicate(string $code)
    {
        return (DB::table('employee_statuses')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count() > 0);
    }
}
