<?php

namespace App\Http\Controllers;

use App\Business\Dao\ReadinessLevelDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB, Log;
use App\Business\Dao\SuccessionPoolDao;
use App\Business\Dao\SuccessorListDao;
use App\Business\Dao\PositionDao;
use App\Business\Dao\AssignmentDao;


/**
 * Class for handling asset process
 */
class SuccessionPoolController extends Controller
{
    public function __construct(
        Requester $requester,
        SuccessionPoolDao $successionPoolDao,
        SuccessorListDao $successorListDao,
        PositionDao $positionDao,
        AssignmentDao $assignmentDao,
        ReadinessLevelDao $readinessLevelDao
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->successionPoolDao = $successionPoolDao;
        $this->successorListDao  = $successorListDao;
        $this->positionDao = $positionDao;
        $this->assignmentDao = $assignmentDao;
        $this->readinessLevel = $readinessLevelDao;
    }

    /*
    |-----------------------------
    | get all data dari database
    |-----------------------------
    | @param $request <object>
    |
    |
    */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
        ]);

        $data = $this->successionPoolDao->getAll($request->companyId);
        if(count($data) > 0) {
            foreach($data as $datum) {
                $getPosition         = $this->positionDao->getOne($datum->positionCode);
                $datum->positionName = $getPosition->name;
            }
        }

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);
        $data = (object)[];

        $getOneParent          = $this->successionPoolDao->getOne($request->id);
        $getSuccessorList      = $this->successorListDao->getAll($request->companyId, $request->id);
        $getEmployeeByPosition = $this->assignmentDao->getEmployeeByPositionCode($getOneParent->positionCode);
//        info(print_r($getOneParent->positionCode, true));
//        info(print_r($getEmployeeByPosition, true));

        $getPosition = $this->positionDao->getOnePositionByCode($getOneParent->positionCode);
        $getOneParent->positionName = $getPosition->name;

        $getEmployeeByEmployeeId = $this->assignmentDao->getEmployeeByEmployeeId($getOneParent->interim);

        info(print_r($getEmployeeByEmployeeId, true));

        $data = $getOneParent;
        $data->incumbent = [
            'employeeName' => $getEmployeeByPosition ? $getEmployeeByPosition->employeeName : null,
            'employeeId'   => $getEmployeeByPosition ? $getEmployeeByPosition->employeeId : null,
            'filePhoto'    => $getEmployeeByPosition ? $getEmployeeByPosition->filePhoto : null
        ];

        $data->interim = [
            'employeeName' => $getEmployeeByEmployeeId ? $getEmployeeByEmployeeId->employeeName : null,
            'employeeId'   => $getEmployeeByEmployeeId ? $getEmployeeByEmployeeId->employeeId : null,
            'filePhoto'    => $getEmployeeByEmployeeId ? $getEmployeeByEmployeeId->filePhoto : null
        ];

        if(count($getSuccessorList) > 0) {
            foreach($getSuccessorList as $datum) {
                $getOneAssignment = $this->assignmentDao->getOneByEmployeeId($datum->employeeId);
                $datum->positionCode = $getOneAssignment->positionCode;
                $datum->positionName = $getOneAssignment->positionName;

                $getReadiness         = $this->readinessLevel->getOne($request->companyId, $datum->readinessId);
                info(print_r($getReadiness, true));
                $datum->readinessName = $getReadiness ? $getReadiness->name : '-';

                $getEmployeeByEmployeeId = $this->assignmentDao->getEmployeeByEmployeeId($datum->employeeId);
                $datum->employeeName     = $getEmployeeByEmployeeId->employeeName;
                $datum->person           = $getEmployeeByEmployeeId;
            }
        }

        $data->detail = count($getSuccessorList) > 0 ? $getSuccessorList : [];

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getSuccessorCandidate(Request $request)
    {
        $this->validate($request, [
                'id' => 'required|integer|exists:succession_pools,id',
                'companyId' => 'required|integer|exists:companies,id',
                'pageInfo' => 'present|nullable|array',
                'pageInfo.pageLimit' => 'required_with:pageInfo|integer|min:0',
                'pageInfo.pageNo' => 'required_with:pageInfo|integer|min:1'
            ]
        );

        $offset  = PagingAppResponse::getOffset($request->pageInfo);
        $limit   = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo  = PagingAppResponse::getPageNo($request->pageInfo);

        $getEmployeeInSuccessionPool = $this->successorListDao->getAll($request->companyId, $request->id);
        $manyEmployeeId = array();

        if(count($getEmployeeInSuccessionPool) > 0) {
            foreach ($getEmployeeInSuccessionPool as $successor) {
                array_push($manyEmployeeId, $successor->employeeId);
            }
        }

        $data     = $this->successionPoolDao->getEmployeeNotInSuccession($offset, $limit, $manyEmployeeId);
        $totalRow = $this->successionPoolDao->getTotalRowsEmployeeNotInSuccession($manyEmployeeId);

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'), $limit, $totalRow, $pageNo));
    }

    /*
    |--------------------------------------------------
    | save data ke database
    |--------------------------------------------------
    |
    |
    */
    public function save(Request $request)
    {
        $this->checkRequest($request);

        DB::transaction(function () use (&$request, &$data) {

            $obj = $this->constructData($request);
            $this->successionPoolDao->save($obj);
        });
        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    public function saveSuccessor(Request $request)
    {
        if ($request->state === 'M') {
            DB::transaction(function () use (&$request) {
                if ($request->has('data')) {
                    for ($i = 0; $i < count($request->data); $i++)
                    {
                        $dataReq = new \Illuminate\Http\Request();
                        $data = (array)$request->data[$i];

                        $dataReq->replace([
                            'succession_pool_id' => $data['successionPoolId']
                        ]);
                        $this->validate($dataReq, [
                            "succession_pool_id" => 'required|integer|exists:succession_pools,id'
                        ]);
                        info(print_r($data,true));
                        $obj = [
                            'tenant_id' => $this->requester->getTenantId(),
                            'company_id' => $request->companyId,
                            'succession_pool_id' => $data['successionPoolId'],
                            'employee_id' => $data['employeeId'],
                            'readiness_id' => $data['readinessId'],
                            'note' => $data['note']
                        ];
                        $this->successorListDao->save($obj);
                    }
                }
            });
        } else {

            $data = $request->data;
            $newRequest = new \Illuminate\Http\Request();
            $newRequest->replace([
                'companyId' => $request->companyId,
                'employeeId' => $data['employeeId'],
                'readinessId' => $data['readinessId']
            ]);
            $this->validate($newRequest, [
                    'companyId' => 'required|integer|exists:companies,id',
                    'employeeId' => 'required|exists:assignments,employee_id',
                    'readinessId' => 'required|integer|exists:readiness_levels,id'
                ]
            );

            DB::transaction(function () use (&$data) {
                $constructData = $this->constructSuccessorList($data);
                $this->successorListDao->save($constructData);
            });
        }

        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /*
    |--------------------------------------------------
    | update data ke database
    |--------------------------------------------------
    |
    */
    public function update(Request $request)
    {
        $this->validate($request, [
            'state' => 'required'
        ]);

        if($request->state === 'updateInterim') {
            $this->validate($request, ['id' => 'required|integer|exists:succession_pools,id']);

            DB::transaction(function () use (&$request) {
                $objUpdate = [
                    'interim' => $request->employeeId,
                ];
                $this->successionPoolDao->update($objUpdate, $request->id);
            });

        } else if ($request->state === 'updateSuccession') {
            $this->validate($request, ['id' => 'required|integer|exists:succession_pools,id']);

            DB::transaction(function () use (&$request) {
                $objUpdate = $this->constructData($request);
                $this->successionPoolDao->update($objUpdate, $request->id);
            });

        } else if ($request->state === 'updateSuccessorList') {

            $this->validate($request, ['id' => 'required|integer|exists:successor_lists,id']);

            DB::transaction(function () use (&$request) {
                $constructData = $this->constructSuccessorList($request);
                $this->successorListDao->update($constructData, $request->id);
            });
        }

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    public function deleteSuccessor(Request $request) {

        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'id' => 'required|integer|exists:successor_lists,id'
        ]);

        DB::transaction(function () use (&$request) {
            $this->successorListDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /*
    |-----------------------------
    | check request data dari ui
    |-----------------------------
    |
    */
    public function checkRequest ($request) {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'positionCode' => 'required|exists:positions,code'
        ]);
    }

    /*
    |-----------------------------------------------
    | construct object data yang akan di saveDuties
    |-----------------------------------------------
    |
    */
    public function constructData ($request) {
        return
        [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "position_code" => $request->positionCode,
            "key" => $request->key,
            "critical" => $request->critical,
            "reason" => $request->reason
        ];
    }

    public function constructSuccessorList($request) {

        return
        [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "succession_pool_id" => $request['successionPoolId'],
            "employee_id" => $request['employeeId'],
            "note" => $request['note'],
            "readiness_id" => $request['readinessId']
        ];
    }
}
