<?php

namespace App\Http\Controllers;

use App\Business\Dao\UnitDao;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Business\Dao\UM\MenuDataAccessDao;

/**
 * Class for handling unit process
 */
class UnitController extends Controller
{
    public function __construct(
        Requester $requester,
        UnitDao $unitDao,
        MenuDataAccessDao $menuDataAccessDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->unitDao = $unitDao;
        $this->unitFields = array(
            'effBegin', 'effEnd', 'code', 'name',
            'locationCode', 'locationName', 'locationAddress', 'costCenterCode', 'costCenterName',
            'unitTypeCode', 'unitTypeName'
        );
        $this->menuDataAccessDao = $menuDataAccessDao;
    }

    /**
     * Get all units in one company
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required|integer"]);

        $units = $this->unitDao->getAll();

        $resp = new AppResponse($units, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all active units in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required|integer"]);

        $activeUnits = $this->unitDao->getAllActive();

        $resp = new AppResponse($activeUnits, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getSLov(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|exists:companies,id|integer',
            'menuCode' => 'present'
        ]);

        $prsentInDataAccess = $this->menuDataAccessDao->getMenuDataAccessByMenuCode($request->menuCode);

        // if not Super Admin and menuCode exist in menu data access
        if (!$this->requester->getIsUserSA() && count($prsentInDataAccess)) {
            $lov = $this->unitDao->getSLov($request->menuCode);
        } else {
            $lov = $this->unitDao->getAllActive();
        }

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one unit based on unit id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "code" => "required"
        ]);

        $unit = $this->unitDao->getOne(
            $request->code
        );

        $data = array();
        if (count($unit) > 0) {
            $data['code'] = $unit->code;
            foreach ($this->unitFields as $field) {
                $data[$field] = $unit->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save unit to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkUnitRequest($request);

        //code must be unique
        if ($this->unitDao->checkDuplicateUnitCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }


        DB::transaction(function () use (&$request, &$data) {
            $unit = $this->constructUnit($request);
            $data['id'] = $this->unitDao->save($unit);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update unit to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['code' => 'required|exists:units,code']);
        $this->checkUnitRequest($request);

        DB::transaction(function () use (&$request) {
            $unit = $this->constructUnit($request);
            unset($unit['code']);
            $this->unitDao->update(
                $request->code,
                $unit
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }


    /**
     * Delete Unit Type Data from DB
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, ['code' => 'required']);

//        if ($this->unitTypeDao->getTotalUsage($request->code) > 0) {
//            throw new AppException(trans('messages.dataInUse'));
//        }

        DB::transaction(function () use (&$request) {
            $this->unitDao->delete($request->code);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update unit request.
     * @param request
     */
    private function checkUnitRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date',
            'effEnd' => 'required|date|after_or_equal:effBegin',
            'code' => 'required|max:20|alpha_dash',
            'name' => 'required|max:50',
            'locationCode' => 'present|alpha_num',
            'costCenterCode' => 'present|alpha_num',
            'unitTypeCode' => 'required|alpha_num'
        ]);
    }

    /**
     * Construct a unit object (array).
     * @param request
     */
    private function constructUnit(Request $request)
    {
        $unit = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "name" => $request->name,
            "code" => $request->code,
            "location_code" => $request->locationCode,
            "cost_center_code" => $request->costCenterCode,
            "unit_type_code" => $request->unitTypeCode
        ];
        return $unit;
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'searchQuery' => 'present|string|max:50',
            'pageInfo' => 'required|array'
        ]);

        $reqData = $request->pageInfo;
        $request->merge($reqData);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1',
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        $data = $this->unitDao->search($request->searchQuery, $offset, $limit);

        return $this->renderResponse(new AppResponse($data, trans('messages.allDataRetrieved')));
    }

    public function searchCustom(Request $request)
    {

        $this->validate($request, [
            "companyId" => "required|integer",
            "unit" => "required"
        ]);

        $search = $this->unitDao->searchCustom($request->unit, $request->param);

        return $this->renderResponse(new AppResponse($search, trans('messages.allDataRetrieved')));
    }


    public function getAllActive(Request $request){
        $this->validate($request, [
            "companyId" => "required",
            'pageInfo' => 'required|array'
        ]);
        $request->merge($request->pageInfo);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1'
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $pageLimit = PagingAppResponse::getPageLimit($request->pageInfo);

        $data = $this->unitDao->getAllActive($offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->unitDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    public function getAllInActive(Request $request){
        $this->validate($request, [
            "companyId" => "required",
            'pageInfo' => 'required|array'
        ]);
        $request->merge($request->pageInfo);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1'
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $pageLimit = PagingAppResponse::getPageLimit($request->pageInfo);

        $data = $this->unitDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->unitDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

}
