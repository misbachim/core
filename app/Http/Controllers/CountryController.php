<?php

namespace App\Http\Controllers;

use App\Business\Dao\CountryDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling Country process
 */
class CountryController extends Controller
{
    public function __construct(Requester $requester, CountryDao $countryDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->countryDao = $countryDao;
        $this->countryFields = array('code', 'name', 'dialCode', 'nationality');
    }

    /**
     * Get all countries
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

        $data = $this->countryDao->getAll(
            $offset,
            $limit
        );

        $totalRow=$this->countryDao->getTotalRow();

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));

    }

    /**
     * Get all countries (lib+countries) in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $lov = $this->countryDao->getLov();

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one company based on Country id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $country = $this->countryDao->getOne(
            $request->id
        );

        $data = array();
        if (count($country)>0) {
            $data['id'] = $country->id;
            foreach ($this->countryFields as $field) {
                $data[$field] = $country->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Country to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkCountryRequest($request);
        $data = array();

        //code must be unique
        if ($this->countryDao->checkDuplicateCountryCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $country = $this->constructCountry($request);
            $data['id'] = $this->countryDao->save($country);
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
        $this->checkCountryRequest($request);

        //code must be unique
        if ($this->countryDao->checkDuplicateEditCountryCode($request->code,$request->id) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request) {
            $country = $this->constructCountry($request);
            $this->countryDao->update($request->id, $country);
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

        if ($this->countryDao->getTotalUsage($request->id) > 0) {
            throw new AppException(trans('messages.dataInUse'));
        }

        DB::transaction(function () use (&$request) {
            $this->countryDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update country request.
     * @param request
     */
    private function checkCountryRequest(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|max:2|alpha_num',
            'name' => 'required|max:50',
            'dialCode' => 'required|max:5',
            'nationality' => 'required|max:20'
        ]);
    }

    /**
     * Construct an country object (array).
     * @param request
     */
    private function constructCountry(Request $request)
    {
        $country = [
            "tenant_id"          => $this->requester->getTenantId(),
            "company_id"         => $this->requester->getCompanyId(),
            "code"               => $request->code,
            "name"               => $request->name,
            "dial_code"          => $request->dialCode,
            "nationality"        => $request->nationality,
        ];
        return $country;
    }
}
