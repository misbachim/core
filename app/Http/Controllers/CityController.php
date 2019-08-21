<?php

namespace App\Http\Controllers;

use App\Business\Dao\CityDao;
use App\Business\Dao\ProvinceDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;

/**
 * Class for handling City process
 */
class CityController extends Controller
{
    public function __construct(
        Requester $requester,
        CityDao $cityDao,
        ProvinceDao $provinceDao
    )
    {
        parent::__construct();

        $this->requester = $requester;
        $this->cityDao = $cityDao;
        $this->provinceDao = $provinceDao;
        $this->cityFields = array('provinceCode', 'code', 'name');
    }

    /**
     * Get all City
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "provinceCode" => "required|string",
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

        $data = $this->cityDao->getAll(
            $offset,
            $limit,
            $request->provinceCode
        );

        $totalRow = $this->cityDao->getTotalRow(
            $this->requester->getTenantId()
        );

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'), $limit, $totalRow, $pageNo));

    }

    /**
     * Get all cities (lib+cities) in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);
        $this->validate($request, ["provinceCode" => "required"]);

        $lov = $this->cityDao->getLov(
            $request->provinceCode
        );

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one city in one company based on city id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "id" => "required|integer",
            "companyId" => "required|integer"
        ]);

        $city = $this->cityDao->getOne(
            $request->id
        );

        $data = array();
        if (count($city) > 0) {
            $data['id'] = $city->id;
            foreach ($this->cityFields as $field) {
                $data[$field] = $city->$field;
            }
            $data['provinceName'] = $city->provinceName;
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save City to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkCityRequest($request);

        //code must be unique
        if ($this->cityDao->checkDuplicateCityCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }
        DB::transaction(function () use (&$request, &$data) {
            $city = $this->constructCity($request);
            $data['id'] = $this->cityDao->save($city);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update City to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);
        $this->checkCityRequest($request);

        //code must be unique
        if ($this->cityDao->checkDuplicateEditCityCode($request->code, $request->id) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request) {
            $city = $this->constructCity($request);
            $this->cityDao->update($request->id, $city);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete City Data to DB
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);

        if ($this->cityDao->getTotalUsage($request->id) > 0) {
            throw new AppException(trans('messages.dataInUse'));
        }

        DB::transaction(function () use (&$request) {
            $this->cityDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update city request.
     * @param request
     */
    private function checkCityRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'provinceCode' => 'required|string',
            'code' => 'required|max:5|alpha_num',
            'name' => 'required|max:50'
        ]);
    }

    /**
     * Construct a city object (array).
     * @param request
     */
    private function constructCity(Request $request)
    {
        $city = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "province_code" => $request->provinceCode,
            "code" => $request->code,
            "name" => $request->name
        ];
        return $city;
    }


    /**
     * get data city by country id
     * @param request
     */
    public function searchCity(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "param" => "required"
        ]);
        $data = $this->cityDao->getSearch($request->param, $request->column);
        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }
}
