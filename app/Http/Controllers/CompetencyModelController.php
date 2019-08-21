<?php

namespace App\Http\Controllers;

use App\Business\Dao\AssignmentDao;
use App\Business\Dao\JobDao;
use App\Business\Dao\LocationDao;
use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonCompetencyModelDao;
use App\Business\Dao\PositionDao;
use App\Business\Dao\UnitDao;
use App\Business\Dao\CompetencyModelDao;
use App\Business\Dao\CompetencyGroupDao;
use App\Business\Dao\CompetencyDao;
use App\Business\Dao\RatingScaleDetailDao;


use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

/**
 * Class for handling Competency Model process
 */
class CompetencyModelController extends Controller
{
    public function __construct(Requester $requester
        , CompetencyModelDao $competencyModelDao
        , CompetencyGroupDao $competencyGroupDao
        , RatingScaleDetailDao $ratingScaleDetailDao
        , PersonCompetencyModelDao $personCompetencyModelDao
        , AssignmentDao $assignmentDao
        , CompetencyDao $competencyDao
        , JobDao $jobDao
        , UnitDao $unitDao
        , LocationDao $locationDao
        , PositionDao $positionDao
        , PersonDao $personDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->competencyModelDao = $competencyModelDao;
        $this->competencyGroupDao = $competencyGroupDao;
        $this->ratingScaleDetailDao = $ratingScaleDetailDao;
        $this->personCompetencyModelDao = $personCompetencyModelDao;
        $this->assignmentDao = $assignmentDao;
        $this->competencyDao = $competencyDao;
        $this->jobDao = $jobDao;
        $this->unitDao = $unitDao;
        $this->locationDao = $locationDao;
        $this->positionDao = $positionDao;
        $this->personDao = $personDao;
    }

    /**
     * Get all Competency Model
     * @param request
     */
    public function getAll(Request $request)
    {
        $data = array();
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
        $limit  = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $getCompetencyModel = $this->competencyModelDao->getAll(
            $offset,
            $limit
        );

        $data = $this->getDataCollect($getCompetencyModel);
        $totalRow = count($data);

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));
    }

    /**
     * Get all Competency Model
     * @param request
     */
    public function getAllActive(Request $request)
    {
        $data = array();
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
        $limit  = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $getCompetencyModel = $this->competencyModelDao->getAllActive(
            $offset,
            $limit
        );

        $data = $this->getDataCollect($getCompetencyModel);
        $totalRow = count($data);

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));
    }

    public function getDataCollect($getCompetencyModel) {
        $data = array();

        $dataCollect = collect($getCompetencyModel)->unique('code');
        $dataCollect->values()->all();

        foreach ($dataCollect as $competencyModel) {
            array_push($data, [
                'id' => $competencyModel->id,
                'code' => $competencyModel->code,
                'name' => $competencyModel->name,
                'description' => $competencyModel->description,
                'effBegin' => $competencyModel->effBegin,
                'effEnd' => $competencyModel->effEnd,
                'competencyGroupList' => $this->competencyModelDao->getCompetencyGroupByCompetencyModelId($competencyModel->id)
            ]);
        }
        return $data;
    }

    /**
     * Get all inActive Competency Model
     * @param request
     */
    public function getAllInactive(Request $request)
    {
        $data = array();
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
        $limit  = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $getCompetencyModel = $this->competencyModelDao->getAllInactive(
            $offset,
            $limit
        );

        $uniqueCompetencyModel = collect($getCompetencyModel)->unique('code');
        $uniqueCompetencyModel->values()->all();

        foreach ($uniqueCompetencyModel as $competencyModel) {
            array_push($data, [
                'id' => $competencyModel->id,
                'code' => $competencyModel->code,
                'name' => $competencyModel->name,
                'description' => $competencyModel->description,
                'effBegin' => $competencyModel->effBegin,
                'effEnd' => $competencyModel->effEnd,
                'competencyGroupList' => $this->competencyModelDao->getCompetencyGroupByCompetencyModelId($competencyModel->id)
            ]);
        }
        $totalRow = count($data);

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));
    }

    /**
     * Get lov effective date Competency Model in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $getCompetencyModel = $this->competencyModelDao->getAllActive(
            null, null
        );

        $lov = $this->getDataCollect($getCompetencyModel);

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one effective date Competency Model based on Competency Model id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $competencyModel = $this->competencyModelDao->getOne(
            $request->id , $request->companyId
        );

        $competencyModel->competencyGroupList = $this->competencyModelDao->structureGetCompetencyGroupList($competencyModel->id);
        $competencyModel->usedByEmployee = $this->getUsedByEmployee($competencyModel->code);
        $competencyModel->usedByPosition = $this->getUsedByPosition($competencyModel->code);

        $resp = new AppResponse($competencyModel, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * get History Competency Model from DB
     * @param request
     */
    public function getHistory(Request $request) {

        $this->validate($request, [
            "companyId" => "required|integer",
            'code' => 'required|max:50|exists:competency_models,code',
            'id' => 'required|integer|exists:competency_models,id']);

        $data = $this->competencyModelDao->getHistory($request->code, $request->id);
        $totalRow = count($data);

        if($totalRow > 0) {
            for ($i = 0 ; $i < $totalRow ; $i ++) {
                $data[$i]->competencyGroupList = $this->competencyModelDao->getCompetencyGroupByCompetencyModelId($data[$i]->id);
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Competency Model to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkCompetencyModelRequest($request);
        $data = array();

        //code must be unique
        if ($this->competencyModelDao->isCodeDuplicate($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $competencyModel = $this->constructCompetencyModel($request);
            $data['id'] = $this->competencyModelDao->save($competencyModel);

            $this->saveCompetencyModelCompetencyGroups($request, $data['id']);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Competency Model to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer'
        ]);
        $this->checkCompetencyModelRequest($request);
        $data = array();

        DB::transaction(function () use (&$request, &$data) {
            $competencyModel = $this->constructCompetencyModel($request);

            $objUpdate = [
                'eff_end' => Carbon::now()
            ];
            $this->competencyModelDao->update($request->id, $objUpdate);

            $data['id'] = $this->competencyModelDao->save($competencyModel);
            $this->saveCompetencyModelCompetencyGroups($request, $data['id']);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update Competency Model request.
     * @param request
     */
    private function checkCompetencyModelRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'code' => 'required|max:20|alpha_dash',
            'name' => 'required|max:255',
            'description' => 'present|max:255',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'competencyGroupList' => 'present|array'
        ]);
    }

    /**
     * Construct an Competency Model object (array).
     * @param request
     */
    private function constructCompetencyModel(Request $request)
    {
        $competencyModel = [
            "tenant_id"              => $this->requester->getTenantId(),
            "company_id"             => $request->companyId,
            "code"                   => $request->code,
            "name"                   => $request->name,
            "description"            => $request->description,
            "eff_begin"              => $request->effBegin,
            "eff_end"                => $request->effEnd
        ];
        return $competencyModel;
    }

    /**
     * Save Competency Model Competency Groups.
     * @param Request $request
     */
    private
    function saveCompetencyModelCompetencyGroups(Request $request, $competencyModelId)
    {
        for ($i = 0; $i < count($request->competencyGroupList); $i++) {
            $this->validate($request, [
                "competencyGroupList.$i.code" => 'required|max:20|alpha_dash'
            ]);
            $data = array();
            $competencyModelCompetencyGroups = $request->competencyGroupList[$i];
            array_push($data, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'competency_group_code' => $competencyModelCompetencyGroups['code'],
                'competency_model_id' => $competencyModelId,
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now()
            ]);
            $this->competencyModelDao->saveCompetencyModelCompetencyGroups($data);
        }
    }

    private
    function getUsedByEmployee($competencyModelCode) {
        $data = array();
        $getDataPersonCompetencyModel = $this->personCompetencyModelDao->getAllEmployeeIdByCompetencyModelCode($competencyModelCode);

        if(count($getDataPersonCompetencyModel) > 0) {
            for($i = 0 ; $i < count($getDataPersonCompetencyModel) ; $i++) {

                //get assignment by employee id
                $getAssignment = $this->assignmentDao->getOneLastAssignmentByEmployeeId($getDataPersonCompetencyModel[$i]->employeeId);

                $getJob        = $this->jobDao->getOneJobByCode($getAssignment->jobCode);
                $getUnit       = $this->unitDao->getOneUnitByCode($getAssignment->unitCode);
                $getPosition   = $this->positionDao->getOnePositionByCode($getAssignment->positionCode);
                $getLocation   = $this->locationDao->getOneLocationByCode($getAssignment->locationCode);

                $getDataPerson = $this->personDao->getOnePersonById($getAssignment->personId);

                array_push($data, [
                    'employeeName' => $getDataPerson->firstName.' '.$getDataPerson->lastName,
                    'employeeId' => $getAssignment->employeeId,
                    'email' => $getDataPerson->email,
                    'jobName' => $getJob->name,
                    'unitName' => $getUnit->name,
                    'positionName' => $getPosition->name,
                    'locationName' => $getLocation->name,
                ]);
            }
        }

        return $data;
    }

    private
    function getUsedByPosition($competencyModelCode) {
        $data = array();

        $getDataPositionCompetencyModel = $this->competencyModelDao->getAllPositionCodeByCompetencyModelCode($competencyModelCode);
        if (count($getDataPositionCompetencyModel) > 0) {
            for($i = 0 ; $i < count($getDataPositionCompetencyModel); $i++) {

                $getPosition = $this->positionDao->getOnePositionByCode($getDataPositionCompetencyModel[$i]->positionCode);
                if($getPosition) {
                    $getJob      = $this->jobDao->getOneJobByCode($getPosition->jobCode);
                    $getUnit     = $this->unitDao->getOneUnitByCode($getPosition->unitCode);

                    $getJobName  = $getJob ? $getJob->name : '';
                    $getUnitName = $getUnit ? $getUnit->name : '';

                    array_push($data, [
                        'positionCode' => $getPosition->code,
                        'description' => $getPosition->description,
                        'jobName' => $getJobName,
                        'unitName' => $getUnitName
                    ]);
                }else{
                    array_push($data, [
                        'positionCode' => '',
                        'description' => '',
                        'jobName' => '',
                        'unitName' => ''
                    ]);
                }
            }
        }
        return $data;
    }

    /**
     * Get All Competency Detail by Model in one company
     * @param request
     */
    public function getAllCompetency(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $competencyModel = $this->competencyModelDao->getOne($request->id , $request->companyId);
        $competencyModel->competencyGroupList = $this->getCompetencyDetail($competencyModel->id);

        $resp = new AppResponse($competencyModel, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getCompetencyDetail($competencyModelId) {
        $data = array();
        $competencyGroups = $this->competencyModelDao->getCompetencyGroupByCompetencyModelId($competencyModelId);

        foreach ($competencyGroups as $competencyGroup) {
            array_push($data, [
                'id' => $competencyGroup->id,
                'code' => $competencyGroup->code,
                'name' => $competencyGroup->name,
                'description' => $competencyGroup->description,
                'competencyList' => $this->competencyGroupDao->getCompetencyByCompetencyGroupId($competencyGroup->id)
            ]);
        }
        return $data;
    }


    /**
     * @description get all competency model -> competency group -> competencies
     */
    public function getAllCompetencyByModelCode(Request $request)
    {
        $data = $this->competencyModelDao->getAllWithoutLimit($request->code);

        $listData = [];
        $data->list = $this->competencyModelDao->getAllGroupByModelId($data->id);
        foreach ($data->list as $k => $val) {
            $data->list[$k] = $this->competencyModelDao->getAllCompetencyByGroupId($val->competencyGroupId);

            foreach ($data->list[$k] as $index => $comp) {
                $dataCompetency = $this->competencyDao->getOneCompetency($comp->competency_code);
                $dataCompetency->ratingScale = $this->ratingScaleDetailDao->getRatingLevelByCode($dataCompetency->ratingScaleCode);
                array_push($listData, $dataCompetency);
            }
        }
        $collect = collect($listData);
        $dataCollect = $collect->unique('code')->values()->all();
        $data->list = $dataCollect;

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

}
