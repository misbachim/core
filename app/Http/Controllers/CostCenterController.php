<?php

namespace App\Http\Controllers;

use App\Business\Dao\CostCenterDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling costCenter process
 */
class CostCenterController extends Controller
{
    public function __construct(Requester $requester, CostCenterDao $costCenterDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->costCenterDao = $costCenterDao;
        $this->costCenterFields = array('effBegin', 'effEnd', 'code', 'name');
    }

    /**
     * Get all costCenters in one company
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required|integer"]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $data = $this->costCenterDao->getAll(
            $offset,
            $limit
        );

        $totalRow=$this->costCenterDao->getTotalRow();

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));

    }

    public function getAllActive(Request $request){
        $this->validate($request, ["companyId" => "required|integer"]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $data = $this->costCenterDao->getAllActive(
            $offset,
            $limit
        );

        $totalRow=$this->costCenterDao->getTotalRow();

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));
    }

    public function getAllInActive(Request $request){
        $this->validate($request, ["companyId" => "required|integer"]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $data = $this->costCenterDao->getAllInActive(
            $offset,
            $limit
        );

        $totalRow=$this->costCenterDao->getTotalRow();

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));
    }

    /**
     * Get all costCenters in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $lov = $this->costCenterDao->getLov();

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one costCenter based on costCenter id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "code" => "required|max:5|alpha_num"
        ]);

        $costCenter = $this->costCenterDao->getOne(
            $request->code
        );

        $data = array();
        if (count($costCenter) > 0) {
            foreach ($this->costCenterFields as $field) {
                $data[$field] = $costCenter->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getDefault(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'positionCode' => 'required'
        ]);

        $costCenter = $this->costCenterDao->getDefault($request->positionCode);

        return $this->renderResponse(new AppResponse($costCenter, trans('messages.dataRetrieved')));
    }

    /**
     * Save costCenter to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkCostCenterRequest($request);
        //name must be unique
        if ($this->costCenterDao->checkDuplicateCostCenName($request->name) > 0) {
            throw new AppException(trans('messages.duplicateName'));
        }
        //code must be unique
        if ($this->costCenterDao->checkDuplicateCostCenCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }
        DB::transaction(function () use (&$request) {
            $costCenter = $this->constructCostCenter($request);
            $this->costCenterDao->save($costCenter);
        });

        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update costCenter to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->checkCostCenterRequest($request);
        //name must be unique
        if ($this->costCenterDao->checkDuplicateEditCostCenName($request->name,$request->code) > 0) {
            throw new AppException(trans('messages.duplicateName'));
        }

        DB::transaction(function () use (&$request) {
            $costCenter = $this->constructCostCenter($request);
            $this->costCenterDao->update(
                $request->code,
                $costCenter
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }


    /**
     * Delete Cost Center Data from DB
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "code" => "required|max:20",
            "companyId" => "required|integer"
        ]);


        DB::transaction(function () use (&$request) {
            $this->costCenterDao->delete($request->code);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }


    /**
     * Validate save/update costCenter request.
     * @param request
     */
    private function checkCostCenterRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date',
            'effEnd' => 'required|date|after_or_equal:effBegin',
            'name' => 'required|max:50',
            'code' => 'required|max:5|alpha_num'
        ]);
    }

    /**
     * Construct a costCenter object (array).
     * @param request
     */
    private function constructCostCenter(Request $request)
    {
        $costCenter = [
            "tenant_id"  => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "eff_begin"  => $request->effBegin,
            "eff_end"    => $request->effEnd,
            "name"       => $request->name,
            "code"       => $request->code,
            "is_deleted" => false
        ];
        return $costCenter;
    }
}
