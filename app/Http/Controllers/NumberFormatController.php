<?php

namespace App\Http\Controllers;

use App\Business\Dao\NumberFormatDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling Emmployee Id Format process
 */
class NumberFormatController extends Controller
{
    public function __construct(Requester $requester, NumberFormatDao $numberFormatDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->numberFormatDao = $numberFormatDao;
        $this->numberFormatFields = array('employeeStatusCode', 'format', 'autonumberId');
    }

    /**
     * Get all employee id format
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "pageInfo" => "required|array"
        ]);

        $reqData = $request->pageInfo;
        $request->merge($reqData);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1',
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $data = $this->numberFormatDao->getAll(
            $offset,
            $limit
        );

        $totalRow=$this->numberFormatDao->getTotalRow();

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));

    }

    /**
     * Get all employee id format in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $lov = $this->numberFormatDao->getLov();

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one company based on employee id format id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $numberFormat = $this->numberFormatDao->getOne(
            $request->id
        );

        $data = array();
        if (count($numberFormat)>0) {
            $data['id'] = $numberFormat->id;
            foreach ($this->numberFormatFields as $field) {
                $data[$field] = $numberFormat->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Employee Id Format to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkEmployeeIdFormat($request);
        $data = array();

        //code must be unique
//        if ($this->numberFormatDao->checkDuplicateEmployeeIdFormat($request->numberFormat) > 0) {
//            throw new AppException(trans('messages.duplicateCode'));
//        }

        DB::transaction(function () use (&$request, &$data) {
            $employeeid = $this->constructEmployeeIdFormat($request);
            $data['id'] = $this->numberFormatDao->save($employeeid);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Country to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer'
        ]);
        $this->checkEmployeeIdFormat($request);

        //code must be unique
//        if ($this->numberFormatDao->checkDuplicateEditEmployeeIdFormat($request->numberFormat,$request->id) > 0) {
//            throw new AppException(trans('messages.duplicateCode'));
//        }

        DB::transaction(function () use (&$request) {
            $employeeid = $this->constructEmployeeIdFormat($request);
            $this->numberFormatDao->update($request->id, $employeeid);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete Country Data from DB
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);

        DB::transaction(function () use (&$request) {
            $this->numberFormatDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update Employee Id format request.
     * @param request
     */
    private function checkEmployeeIdFormat(Request $request)
    {
        $this->validate($request, [
            'employeeStatusCode' => 'max:10',
            'autonumberId' => 'required|max:5',
            'lovNbft' => 'required|max:20',
            'numberFormat' => 'required|max:50'
        ]);
    }

    /**
     * Construct an country object (array).
     * @param request
     */
    private function constructEmployeeIdFormat(Request $request)
    {
        $idFormat = [
            "tenant_id"          => $this->requester->getTenantId(),
            "company_id"         => $this->requester->getCompanyId(),
            "employee_status_code" => $request->employeeStatusCode,
            "format"             => $request->numberFormat,
            "lov_nbft"             => $request->lovNbft,
            "autonumber_id"      => $request->autonumberId,
        ];
        return $idFormat;
    }
}
