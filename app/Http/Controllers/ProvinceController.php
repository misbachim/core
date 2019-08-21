<?php

namespace App\Http\Controllers;

use App\Business\Dao\ProvinceDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling Province process
 */
class ProvinceController extends Controller
{
    public function __construct(Requester $requester, ProvinceDao $provinceDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->provinceDao = $provinceDao;
        $this->provinceFields = array('countryCode', 'code', 'name');
    }

    /**
     * Get all Provinces in One Company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "countryCode" => "required|string",
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

        $data = $this->provinceDao->getAll(
            $offset,
            $limit,
            $request->countryCode
        );

        $totalRow=$this->provinceDao->getTotalRow();

        $resp = new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo);
        return $this->renderResponse($resp);
    }

    /**
     * Get all provinces (lib+provinces) in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required|integer"]);
        $this->validate($request, ["countryCode" => "required|string"]);

        $lov = $this->provinceDao->getLov(
            $request->countryCode
        );

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }


    /**
     * Get one province in one company based on province id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $province = $this->provinceDao->getOne(
            $request->id
        );

        $data = array();
        if (count($province)>0) {
            $data['id'] = $province->id;
            foreach ($this->provinceFields as $field) {
                $data[$field] = $province->$field;
            }
            $data['countryName'] = $province->countryName;
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Province to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkProvinceRequest($request);
        $data = array();
        //code must be unique
        if ($this->provinceDao->checkDuplicateProvinceCode($request->code, $request->countryCode) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }
        DB::transaction(function () use (&$request, &$data) {
            $province = $this->constructProvince($request);
            $data['id'] = $this->provinceDao->save($province);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Province to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkProvinceRequest($request);

        //code must be unique
        if ($this->provinceDao->checkDuplicateEditProvinceCode($request->code,$request->id) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }
        DB::transaction(function () use (&$request) {
            $province = $this->constructProvince($request);
            $this->provinceDao->update($request->id, $province);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete Province Data to DB
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);

        if ($this->provinceDao->getTotalUsage($request->id) > 0) {
            throw new AppException(trans('messages.dataInUse'));
        }

        DB::transaction(function () use (&$request) {
            $this->provinceDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update province request.
     * @param request
     */
    private function checkProvinceRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'countryCode' => 'required|string',
            'code' => 'required|max:2|alpha_num',
            'name' => 'required|max:50'
        ]);
    }

    /**
     * Construct an province object (array).
     * @param request
     */
    private function constructProvince(Request $request)
    {
        $province = [
            "tenant_id"          => $this->requester->getTenantId(),
            "company_id"         => $request->companyId,
            "country_code"       => $request->countryCode,
            "code"               => $request->code,
            "name"               => $request->name,
        ];
        return $province;
    }
}
