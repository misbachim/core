<?php

namespace App\Http\Controllers;

use App\Business\Dao\LocationDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling location process
 */
class LocationController extends Controller
{
    public function __construct(Requester $requester, LocationDao $locationDao)
    {
        parent::__construct();
        $this->requester = $requester;
        $this->locationDao = $locationDao;
        $this->locationFields = array(
            'effBegin', 'effEnd',
            'description', 'code', 'name', 'taxOfficeCode', 'address', 'postalCode', 'phone', 'fax', 'latitude', 'longitude'
        );
        $this->locationExtraFields = array('cityCode', 'cityName', 'provinceCode', 'provinceName', 'countryCode', 'countryName');
    }
    /**
     * Get all location in one company
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
        $data = $this->locationDao->getAll(
            $offset,
            $limit
        );
        $totalRow = $this->locationDao->getTotalRow();
        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'), $limit, $totalRow, $pageNo));
    }

    /**
     * Get all Active Location in ONE company
     */
    public function getAllActive(Request $request)
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
        $data = $this->locationDao->getAllActive(
            $offset,
            $limit
        );
        $totalRow = $this->locationDao->getTotalRow();
        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'), $limit, $totalRow, $pageNo));
    }

    /**
     * Get all Inactive Location in ONE company
     */
    public function getAllInActive(Request $request)
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
        $data = $this->locationDao->getAllInActive();
        $totalRow = $this->locationDao->getTotalRow();
        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'), $limit, $totalRow, $pageNo));
    }
    
    /**
     * Get all location in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);
        $lov = $this->locationDao->getLov();
        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }
    /*
        get all location for clocking
        */
    public function getClockingLocations(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "curLatitude" => "required|numeric",
            "curLongitude" => "required|numeric",
            "radius" => "required|integer"
        ]);
        $data = [];
        $location = $this->locationDao->getAll(
            0,
            10000
        );
        info('$location', [$location]);
        if ($location) {
            for ($i = 0; $i < count($location); $i++) {
                if ($location[$i]->latitude != null && $location[$i]->longitude != null) {
                    $latitude = $location[$i]->latitude;
                    $longitude = $location[$i]->longitude;
                    $radius = $this->getMetersBetweenPoints($request->curLatitude, $request->curLongitude, $latitude, $longitude);
                    if ($radius <= $request->radius) {
                        array_push($data, [
                            'locationCode' => $location[$i]->code,
                            'locationName' => $location[$i]->name,
                            'latitude' => $location[$i]->latitude,
                            'longitude' => $location[$i]->longitude
                        ]);
                    }
                    info('radius', [$radius]);
                }
            }
        }
        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }
    private function getMetersBetweenPoints($latitude1, $longitude1, $latitude2, $longitude2)
    {
        if (($latitude1 == $latitude2) && ($longitude1 == $longitude2)) {
            return 0;
        } // distance is zero because they're the same point
        $p1 = deg2rad($latitude1);
        $p2 = deg2rad($latitude2);
        $dp = deg2rad($latitude2 - $latitude1);
        $dl = deg2rad($longitude2 - $longitude1);
        $a = (sin($dp / 2) * sin($dp / 2)) + (cos($p1) * cos($p2) * sin($dl / 2) * sin($dl / 2));
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $r = 6371008; // Earth's average radius, in meters
        $d = $r * $c;
        return $d; // distance, in meters
    }
    /**
     * Get one location in one company based on location id
     * @param request
     */
    public function getOne(Request $request)
    {
        $data = array();
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);
        $location = $this->locationDao->getOne(
            $request->id
        );
        if (count($location) > 0) {
            $data['id'] = $location->id;
            $allFields = array_merge(
                $this->locationFields,
                $this->locationExtraFields
            );
            foreach ($allFields as $field) {
                $data[$field] = $location->$field;
            }
        }
        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }
    /**
     * Get one location in one company based on location code
     * @param request
     */
    public function getOneByCode(Request $request)
    {
        $data = array();
        $this->validate($request, [
            "companyId" => "required|integer",
            "code" => "required"
        ]);
        $location = $this->locationDao->getOneByCode(
            $request->code
        );
        if (count($location) > 0) {
            $data['id'] = $location->id;
            $allFields = array_merge(
                $this->locationFields,
                $this->locationExtraFields
            );
            foreach ($allFields as $field) {
                $data[$field] = $location->$field;
            }
        }
        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }
    public function getDefault(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required'
        ]);
        $location = $this->locationDao->getDefault();
        return $this->renderResponse(new AppResponse($location, trans('messages.dataRetrieved')));
    }
    /**
     * Save location to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkLocationRequest($request);
        //names must be unique
        if ($this->locationDao->checkDuplicateLocationName($request->name) > 0) {
            throw new AppException(trans('messages.duplicateName'));
        }
        //code must be unique
        if ($this->locationDao->checkDuplicateLocationCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }
        DB::transaction(function () use (&$request, &$data) {
            $location = $this->constructLocation($request);
            $data['id'] = $this->locationDao->save($location);
        });
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }
    /**
     * Update location to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer'
        ]);
        $this->checkLocationRequest($request);
        //names must be unique
        if ($this->locationDao->checkDuplicateEditLocationName($request->name, $request->id) > 0) {
            throw new AppException(trans('messages.duplicateName'));
        }
        DB::transaction(function () use (&$request) {
            $location = $this->constructLocation($request);
            unset($location['code']);
            $this->locationDao->update(
                $request->id,
                $location
            );
        });
        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }
    /**
     * Delete Location Group Data from DB
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required|integer",
            "companyId" => "required|integer"
        ]);
        //        if ($this->costCenterDao->getTotalUsage($request->code) > 0) {
        //            throw new AppException(trans('messages.dataInUse'));
        //        }
        DB::transaction(function () use (&$request) {
            $this->locationDao->delete($request->id);
        });
        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
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
        $data = $this->locationDao->search($request->searchQuery, $offset, $limit);
        return $this->renderResponse(new AppResponse($data, trans('messages.allDataRetrieved')));
    }
    /**
     * Validate location save/update request.
     * @param request
     */
    private function checkLocationRequest($request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date',
            'effEnd' => 'required|date|after_or_equal:effBegin',
            'code' => 'required|max:20|alpha_num',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'name' => 'required|max:50',
            'description' => 'present|max:255',
            'taxOfficeCode' => 'present|max:5',
            'cityCode' => 'required|string',
            'address' => 'present|max:255',
            'postalCode' => 'present|max:10',
            'phone' => 'present|max:50',
            'fax' => 'present|max:50'
        ]);
    }
    /**
     * Construct a location object for storage (array).
     * @param request
     */
    private function constructLocation(Request $request)
    {
        $location = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "code" => $request->code,
            "name" => $request->name,
            "description" => $request->description,
            "tax_office_code" => $request->taxOfficeCode,
            "city_code" => $request->cityCode,
            "address" => $request->address,
            "postal_code" => $request->postalCode,
            "phone" => $request->phone,
            "fax" => $request->fax,
            "latitude" => $request->latitude ? $request->latitude : null,
            "longitude" => $request->longitude ? $request->longitude : null,
        ];
        return $location;
    }
}
