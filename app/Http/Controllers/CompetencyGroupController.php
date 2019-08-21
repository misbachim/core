<?php

namespace App\Http\Controllers;

use Log;
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
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling Competency Group process
 */
class CompetencyGroupController extends Controller
{
    public function __construct(Requester $requester
        , CompetencyGroupDao $competencyGroupDao
        , CompetencyModelDao $competencyModelDao
        , PersonCompetencyModelDao $personCompetencyModelDao
        , AssignmentDao $assignmentDao
        , JobDao $jobDao
        , UnitDao $unitDao
        , LocationDao $locationDao
        , PositionDao $positionDao
        , PersonDao $personDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->competencyGroupDao = $competencyGroupDao;
        $this->competencyModelDao = $competencyModelDao;
        $this->personCompetencyModelDao = $personCompetencyModelDao;
        $this->assignmentDao = $assignmentDao;
        $this->jobDao = $jobDao;
        $this->unitDao = $unitDao;
        $this->locationDao = $locationDao;
        $this->positionDao = $positionDao;
        $this->personDao = $personDao;
    }

    /**
     * Get All Competency Group
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

        $getCompetencyGroup = $this->competencyGroupDao->getAll(
            $offset,
            $limit
        );

        $dataCollect = collect($getCompetencyGroup)->unique('code');
        $dataCollect->values()->all();
        
        foreach ($dataCollect as $competencyGroup) {
            array_push($data, [
                'id' => $competencyGroup->id,
                'code' => $competencyGroup->code,
                'name' => $competencyGroup->name,
                'description' => $competencyGroup->description,
                'effBegin' => $competencyGroup->effBegin,
                'effEnd' => $competencyGroup->effEnd,
                'competencyList' => $this->competencyGroupDao->getCompetencyByCompetencyGroupId($competencyGroup->id)
            ]);
        }
        $totalRow = count($data);

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));
    }

    /**
     * Get All Active Competency Group
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

        $getCompetencyGroup = $this->competencyGroupDao->getAllActive(
            $offset,
            $limit
        );

        $dataCollect = collect($getCompetencyGroup)->unique('code');
        $dataCollect->values()->all();
        
        foreach ($dataCollect as $competencyGroup) {
            array_push($data, [
                'id' => $competencyGroup->id,
                'code' => $competencyGroup->code,
                'name' => $competencyGroup->name,
                'description' => $competencyGroup->description,
                'effBegin' => $competencyGroup->effBegin,
                'effEnd' => $competencyGroup->effEnd,
                'competencyList' => $this->competencyGroupDao->getCompetencyByCompetencyGroupId($competencyGroup->id)
            ]);
        }
        $totalRow = count($data);

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));
    }


    /**
     * Get All Inactive Competency Group
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

        $getCompetencyGroup = $this->competencyGroupDao->getAllInactive(
            $offset,
            $limit
        );

        $uniqueCompetencyGroup = collect($getCompetencyGroup)->unique('code');
        $uniqueCompetencyGroup->values()->all();

        foreach ($uniqueCompetencyGroup as $competencyGroup) {
            array_push($data, [
                'id' => $competencyGroup->id,
                'code' => $competencyGroup->code,
                'name' => $competencyGroup->name,
                'description' => $competencyGroup->description,
                'effBegin' => $competencyGroup->effBegin,
                'effEnd' => $competencyGroup->effEnd,
                'competencyList' => $this->competencyGroupDao->getCompetencyByCompetencyGroupId($competencyGroup->id)
            ]);
        }
        $totalRow = count($data);

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));
    }

    /**
     * Get one effective date Competency Group based on Competency Group id
     * @param request
     */
    public function getOne(Request $request)
    {
        $dataEmployee = array();
        $dataPosition = array();
        $a = 0; $b = 0;

        $data = $this->competencyGroupDao->getOne(
            $request->id , $request->companyId
        );

        $data->competencyList = $this->competencyGroupDao->structureGetCompetencyList($data->id);
        $dataCompetencyModel = $this->getUsedByCompetencyModel($data->code);

        $data->usedByCompetencyModel = $dataCompetencyModel;

        if(count($data->usedByCompetencyModel) > 0) {
            info('compModelCount', [count($data->usedByCompetencyModel)]);
            info('compGroup', [$data->usedByCompetencyModel]);
            for($i = 0 ; $i < count($data->usedByCompetencyModel) ; $i++) {

                $getDataEmployee = $this->getUsedByEmployee($data->usedByCompetencyModel[$i]['code']);
                info('compGroup1', [$getDataEmployee]);
                if(!empty($getDataEmployee)) {
                    for($x = 0 ; $x < count($getDataEmployee) ; $x++) {
                        $dataEmployee[$a] = $getDataEmployee[$x];
                        $a++;
                    }
                }

                $getDataPosition = $this->getUsedByPosition($data->usedByCompetencyModel[$i]['code']);
                if(!empty($getDataPosition)) {
                    for($x = 0 ; $x < count($getDataPosition) ; $x++) {
                        $dataPosition[$b] = $getDataPosition[$x];
                        $b++;
                    }
                }
            }
        }
        
        $dataCollectEmployee = collect($dataEmployee)->unique('code')->all();
        $data->usedByEmployee = $dataCollectEmployee;
        $dataCollectPosition = collect($dataPosition)->unique('code')->all();
        $data->usedByPosition = $dataCollectPosition;

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * get History Competency Group from DB
     * @param request
     */
    public function getHistory(Request $request) {

        $this->validate($request, [
            "companyId" => "required|integer",
            'code' => 'required|max:50|exists:competency_groups,code',
            'id' => 'required|integer|exists:competency_groups,id']);

        $data = $this->competencyGroupDao->getHistory($request->code, $request->id);

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }


    /**
     * Save Competency Group to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkCompetencyGroupRequest($request);
        $data = array();

        //code must be unique
        if ($this->competencyGroupDao->isCodeDuplicate($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $competencyGroup = $this->constructCompetencyGroup($request);
            $data['id'] = $this->competencyGroupDao->save($competencyGroup);

            $this->saveCompetencyGroupCompetencies($request, $data['id']);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Competency Group to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer'
        ]);
        $this->checkCompetencyGroupRequest($request);
        $data = array();

        DB::transaction(function () use (&$request, &$data) {
            $competencyGroup = $this->constructCompetencyGroup($request);

            $objUpdate = [
                'eff_end' => Carbon::now()
            ];
            $this->competencyGroupDao->update($request->id, $objUpdate);

            $data['id'] = $this->competencyGroupDao->save($competencyGroup);
            $this->saveCompetencyGroupCompetencies($request, $data['id']);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update Competency Group request.
     * @param request
     */
    private function checkCompetencyGroupRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'code' => 'required|max:20|alpha_dash',
            'name' => 'required|max:255',
            'description' => 'present|max:255',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'competencyList' => 'present|array'
        ]);
    }

    /**
     * Construct an Competency Groupl object (array).
     * @param request
     */
    private function constructCompetencyGroup(Request $request)
    {
        $competencyGroup = [
            "tenant_id"              => $this->requester->getTenantId(),
            "company_id"             => $request->companyId,
            "code"                   => $request->code,
            "name"                   => $request->name,
            "description"            => $request->description,
            "eff_begin"              => $request->effBegin,
            "eff_end"                => $request->effEnd
        ];
        return $competencyGroup;
    }

    /**
     * Save Competency Group Competencies.
     * @param Request $request
     */
    private function saveCompetencyGroupCompetencies(Request $request, $competencyGroupId)
    {
        for ($i = 0; $i < count($request->competencyList); $i++) {
            $this->validate($request, [
                "competencyList.$i.code" => 'required|max:20|alpha_dash'
            ]);
            $data = array();
            $competencyGroupCompetencies = $request->competencyList[$i];
            array_push($data, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'competency_code' => $competencyGroupCompetencies['code'],
                'competency_group_id' => $competencyGroupId,
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now()
            ]);
            $this->competencyGroupDao->saveCompetencyGroupCompetencies($data);
        }
    }    

    /**
     * Get lov effective date Competency Group in one company
     * @param request4
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $lov = $this->competencyGroupDao->getLov();

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    private function getUsedByCompetencyModel($competencyGroupCode) {
        $data = array();

        $getCompetencyModel = $this->competencyGroupDao->getCompetencyModelByCompetencyGroupCode($competencyGroupCode);
//        $dataCollect = collect($getCompetencyModel);
//        $dataCollect->values()->all();
        if (count($getCompetencyModel) > 0) {
            foreach ($getCompetencyModel as $competencyModel) {
                array_push($data, [
                    'id' => $competencyModel->id,
                    'code' => $competencyModel->code,
                    'name' => $competencyModel->name,
                    'description' => $competencyModel->description,
                    'competencyGroupList' => $this->competencyModelDao->getCompetencyGroupByCompetencyModelId($competencyModel->id)
                ]);
            }
        }

        return $data;
    }

    private function getUsedByEmployee($competencyModelCode) {
        $data = array();
        $getDataPersonCompetencyModel = $this->personCompetencyModelDao->getAllEmployeeIdByCompetencyModelCode($competencyModelCode);

        if(count($getDataPersonCompetencyModel) > 0) {
            for($i = 0 ; $i < count($getDataPersonCompetencyModel) ; $i++) {

                $getAssignment = $this->assignmentDao->getOneLastAssignmentByEmployeeId($getDataPersonCompetencyModel[$i]->employeeId);

                // Log::info(print_r($getDataPersonCompetencyModel[$i]->employeeId));

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


    private function getUsedByPosition($competencyModelCode) {
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
}
