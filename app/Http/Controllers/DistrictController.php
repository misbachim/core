<?php

namespace App\Http\Controllers;

use App\Business\Dao\DistrictDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling District process
 */
class DistrictController extends Controller
{
    public function __construct(Requester $requester, DistrictDao $districtDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->districtDao = $districtDao;
        $this->districtFields = array('companyId', 'cityId', 'name');
    }

    /**
     * Get all Districts
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "pageInfo" => "required|array",
            "cityId" => "required|integer"
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

        $data = $this->districtDao->getAll(
            $offset,
            $limit,
            $request->cityId
        );

        $totalRow = $this->districtDao->getTotalRow();

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'), $limit, $totalRow, $pageNo));
    }

    /**
     * Get all districts (lib+districts) in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required|integer"]);
        $this->validate($request, ["cityId" => "required"]);

        $lov = $this->districtDao->getLov(
            $request->cityId
        );

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one District based on District id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $district = $this->districtDao->getOne(
            $request->id
        );

        $data = array();
        if (count($district) > 0) {
            $data['id'] = $district->id;
            $data['name'] = $district->name;
            $data['cityId'] = $district->cityId;
            $data['cityName'] = $district->cityName;
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save District to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkDistrictRequest($request);

        //name must be unique
        if ($this->districtDao->checkDuplicateDistrictName($request->name, $request->cityId) > 0) {
            throw new AppException(trans('messages.duplicateName'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $district = $this->constructDistrict($request);
            $data['id'] = $this->districtDao->save($district);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update District to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);
        $this->checkDistrictRequest($request);

        //name must be unique
        if ($this->districtDao->checkDuplicateEditDistrictName($request->name, $request->cityId, $request->id) > 0) {
            throw new AppException(trans('messages.duplicateName'));
        }

        DB::transaction(function () use (&$request) {
            $district = $this->constructDistrict($request);
            $this->districtDao->update($request->id, $district);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete District Data to DB
     * @param  Request districtId
     */
    public function delete(Request $request)
    {
        $this->validate($request, ["id" => "required|integer"]);

        DB::transaction(function () use (&$request) {
            $this->districtDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update district request.
     * @param request
     */
    private function checkDistrictRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'cityId' => 'required|integer',
            'name' => 'required|max:50'
        ]);
    }

    /**
     * Construct a district object (array).
     * @param request
     */
    private function constructDistrict(Request $request)
    {
        $district = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "city_id" => $request->cityId,
            "name" => $request->name
        ];
        return $district;
    }
}
