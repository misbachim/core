<?php

namespace App\Http\Controllers;

use App\Business\Dao\EmployeeStatusDao;
use App\Business\Dao\Payroll\BenefitGroupDao;
use App\Business\Dao\Payroll\PayrollGroupDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling employeeType process
 * @property Requester requester
 * @property EmployeeStatusDao employeeTypeDao
 */
class EmployeeStatusController extends Controller
{
    public function __construct(Requester $requester, EmployeeStatusDao $employeeStatusDao, BenefitGroupDao $benefitGroupDao, PayrollGroupDao $payrollGroupDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->employeeStatusDao = $employeeStatusDao;
        $this->benefitGroupDao = $benefitGroupDao;
        $this->payrollGroupDao = $payrollGroupDao;
    }

    /**
     * Get all employeeTypes in one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $employeeTypes = $this->employeeStatusDao->getAll();

        $resp = new AppResponse($employeeTypes, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, ['companyId' => 'required']);

        $activeEmployeeTypes = $this->employeeStatusDao->getAll();

        $resp = new AppResponse($activeEmployeeTypes, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one employeeType based on employeeType id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "code" => "required"
        ]);

        $employeeStatus = $this->employeeStatusDao->getOne($request->code);
        if ($employeeStatus !== null) {
            $payrollGroup = $this->payrollGroupDao->get($employeeStatus->payrollGroupCode);
            if (count($payrollGroup) > 0) {
                $employeeStatus->payrollGroup = $payrollGroup->name;
            }
            $benefitGroup = $this->benefitGroupDao->getBenefitGroup($employeeStatus->benefitGroupCode);
            if (count($benefitGroup) > 0) {
                $employeeStatus->benefitGroup = $benefitGroup->name;
            }
        }
        $resp = new AppResponse($employeeStatus, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save employeeType to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkEmployeeTypeRequest($request);
        if ($this->employeeStatusDao->isCodeDuplicate($request->code)) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $employeeType = $this->constructEmployeeType($request);
            $this->employeeStatusDao->save($employeeType);
        });

        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update employeeType to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['code' => 'required']);
        $this->checkEmployeeTypeRequest($request);

        DB::transaction(function () use (&$request) {
            $employeeType = $this->constructEmployeeType($request);
            $this->employeeStatusDao->update($request->code, $employeeType);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete an employee type.
     * @param Request $request
     * @return AppResponse
     */
    public function delete(Request $request)
    {

        DB::transaction(function () use (&$request) {
            $this->employeeStatusDao->delete($request->code);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataDeleted')));
    }

    /**
     * Validate save/update employeeType request.
     * @param Request $request
     */
    private function checkEmployeeTypeRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'code' => 'required|max:20|alpha_num',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'name' => 'required|max:50',
            'generatePaklaring' => 'required|boolean',
            'isPermanent' => 'nullable|boolean',
            'paklaringTemplatesId' => 'nullable|integer',
            'employeeStatusDocumentTemplatesId' => 'nullable|integer'
        ]);

        if($request['isPermanent'] == false){
            $this->validate($request, ['workingMonth' => 'required|integer']);
        }
    }

    /**
     * Construct an employeeType object (array).
     * @param Request $request
     * @return array
     */
    private function constructEmployeeType(Request $request)
    {
        $employeeType = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "code" => $request->code,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "name" => $request->name,
            "working_month" => $request->workingMonth,
            "is_permanent" => $request->isPermanent,
            "generate_paklaring" => $request->generatePaklaring,
            "paklaring_templates_id" => $request->paklaringTemplatesId,
            "employee_status_document_templates_id" => $request->employeeStatusDocumentTemplatesId
        ];
        return $employeeType;
    }
}
