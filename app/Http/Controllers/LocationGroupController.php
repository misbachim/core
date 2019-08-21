<?php

namespace App\Http\Controllers;

use App\Business\Dao\LocationGroupDao;
use App\Business\Dao\LocationGroupDetailDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling location group process
 */
class LocationGroupController extends Controller
{
    public function __construct(
        Requester $requester,
        LocationGroupDao $locationGroupDao,
        LocationGroupDetailDao $locationGroupDetailDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->locationGroupDao = $locationGroupDao;
        $this->locationGroupDetailDao = $locationGroupDetailDao;
        $this->locationGroupFields = array('effBegin', 'effEnd', 'code', 'name');
    }

    /**
     * Get all location groups in one company
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required|integer"]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $data = $this->locationGroupDao->getAll(
            $offset,
            $limit
        );

        $totalRow=$this->locationGroupDao->getTotalRow();

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));

    }

    /**
     * Get All Active location group
     * @param request
     */


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

        $data = $this->locationGroupDao->getAllActive($offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->locationGroupDao->getTotalRow(),
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

        $data = $this->locationGroupDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->locationGroupDao->getTotalRow(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
     * Get one location group on location group id
     * @param request
     */
    public function getOne(Request $request)
    {
        $data = array();
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $locationGroup = $this->locationGroupDao->getOne(
            $request->id
        );

        if (count($locationGroup) > 0) {
            $data['id'] = $locationGroup->id;
            $data['locationGroupDetail'] = $this->locationGroupDetailDao
                ->getAll($request->id);

            foreach ($this->locationGroupFields as $field) {
                $data[$field] = $locationGroup->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Location Group to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkLocationGroupRequest($request);

        //codes must be unique
        if ($this->locationGroupDao->checkDuplicateLocationGroupCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $locationGroup = $this->constructLocationGroup($request);
            $locationGroup['id'] = $this->locationGroupDao->save($locationGroup);
            $this->locationGroupDetailDao->delete($locationGroup['id']);
            $this->saveLocationGroupDetail($request, $locationGroup);

            $data['id'] = $locationGroup['id'];
        });



        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Location Group to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);
        $this->checkLocationGroupRequest($request);

        //codes must be unique
        if ($this->locationGroupDao->checkDuplicateEditLocationGroupCode($request->code,$request->id) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request) {
            $locationGroup = $this->constructLocationGroup($request);
            $locationGroup['id'] = $request->id;

            $this->locationGroupDao->update(
                $request->id,
                $locationGroup
            );
            $this->locationGroupDetailDao->delete($request->id);
            $this->saveLocationGroupDetail($request, $locationGroup);
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

        DB::transaction(function () use (&$request) {
            $this->locationGroupDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }


    /**
     * Validate location save/update request.
     * @param request
     */
    private function checkLocationGroupRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date',
            'effEnd' => 'required|date|after_or_equal:effBegin',
            'name' => 'required|max:50',
            'code' => 'required|max:20|alpha_num',
            'locationGroupDetail' => 'present|array'
        ]);
    }

    /**
     * Construct a locationGroup object for storage (array).
     * @param request
     */
    private function constructLocationGroup(Request $request)
    {
        $locationGroup = [
            "tenant_id"          => $this->requester->getTenantId(),
            "company_id"         => $this->requester->getCompanyId(),
            "eff_begin"          => $request->effBegin,
            "eff_end"            => $request->effEnd,
            "code"               => $request->code,
            "name"               => $request->name
        ];
        return $locationGroup;
    }

    /**
     * Save location group's detailed information.
     * @param request, locationGroup
     */
    private function saveLocationGroupDetail(Request $request, &$locationGroup)
    {
        if ($request->has('locationGroupDetail')) {
            $data = array();
            for ($i=0; $i < count($request->locationGroupDetail); $i++) {
                $this->validate($request, [
                    "locationGroupDetail.$i.code" => 'required'
                ]);

                array_push($data, [
                    "tenant_id"         => $this->requester->getTenantId(),
                    "company_id"        => $this->requester->getCompanyId(),
                    "location_code"       => $request->locationGroupDetail[$i]['code'],
                    "location_group_id" => $locationGroup['id']
                ]);
            }
            $this->locationGroupDetailDao->save($data);
        }
    }
}
