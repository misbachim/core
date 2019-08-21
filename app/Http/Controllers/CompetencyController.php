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
use App\Business\Dao\ItemBehaviourDao;
use App\Business\Dao\ItemProficiencyDao;
use App\Business\Dao\RatingScaleDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling Competency process
 */
class CompetencyController extends Controller
{
    public function __construct(
        Requester $requester,
        CompetencyDao $competencyDao,
        ItemBehaviourDao $itemBehaviourDao,
        ItemProficiencyDao $itemProficiencyDao,
        RatingScaleDao $ratingScaleDao,
        CompetencyGroupDao $competencyGroupDao,
        CompetencyModelDao $competencyModelDao,
        PersonCompetencyModelDao $personCompetencyModelDao,
        AssignmentDao $assignmentDao,
        JobDao $jobDao,
        UnitDao $unitDao,
        LocationDao $locationDao,
        PositionDao $positionDao,
        PersonDao $personDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->competencyDao = $competencyDao;
        $this->itemBehaviourDao = $itemBehaviourDao;
        $this->itemProficiencyDao = $itemProficiencyDao;
        $this->ratingScaleDao = $ratingScaleDao;
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
     * Get all Competency
     * @param request
     */
    public function getAll(Request $request)
    {

        $dataCompetency = array();
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

        $getCompetency = $this->competencyDao->getAll($offset,$limit);

        $dataCollect = collect($getCompetency)->unique('code');
        $dataCollect->values()->all();

        foreach ($dataCollect as $competency) {
            array_push($dataCompetency, [
                'code' => $competency->code,
                'name' => $competency->name,
                'description' => $competency->description,
                'type' => $competency->type,
                'typeName' => $competency->typeName,
                'ratingScaleCode' => $competency->ratingScaleCode,
                'coreCompetency' => $competency->coreCompetency,
                'effBegin' => $competency->effBegin,
                'effEnd' => $competency->effEnd
            ]);
        }

        $countRowCompetency = count($dataCompetency);

        return $this->renderResponse(new PagingAppResponse($dataCompetency, trans('messages.allDataRetrieved'), $limit, $countRowCompetency, $pageNo));
    }

    /**
     * Get all Competency
     * @param request
     */
    public function getAllActive(Request $request)
    {

        $dataCompetency = array();
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

        $getCompetency = $this->competencyDao->getAllActive(
            $offset,
            $limit
        );

        $dataCollect = collect($getCompetency)->unique('code');
        $dataCollect->values()->all();

        foreach ($dataCollect as $competency) {
            array_push($dataCompetency, [
                'code' => $competency->code,
                'name' => $competency->name,
                'description' => $competency->description,
                'type' => $competency->type,
                'typeName' => $competency->typeName,
                'ratingScaleCode' => $competency->ratingScaleCode,
                'coreCompetency' => $competency->coreCompetency,
                'effBegin' => $competency->effBegin,
                'effEnd' => $competency->effEnd
            ]);
        }

        $countRowCompetency = count($dataCompetency);

        return $this->renderResponse(new PagingAppResponse($dataCompetency, trans('messages.allDataRetrieved'), $limit, $countRowCompetency, $pageNo));
    }

    /**
     * Get all Inactive Competency
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
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $getCompetency = $this->competencyDao->getAllInactive(
            $offset,
            $limit
        );

        $uniqueCompetency = collect($getCompetency)->unique('code');
        $uniqueCompetency->values()->all();

        foreach ($uniqueCompetency as $competency) {
            array_push($data, [
                'code' => $competency->code,
                'name' => $competency->name,
                'description' => $competency->description,
                'type' => $competency->type,
                'typeName' => $competency->typeName,
                'ratingScaleCode' => $competency->ratingScaleCode,
                'coreCompetency' => $competency->coreCompetency,
                'effBegin' => $competency->effBegin,
                'effEnd' => $competency->effEnd
            ]);
        }

        $countRowCompetency = count($data);

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'), $limit, $countRowCompetency, $pageNo));

    }

    /**
     * Get all Competency in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);
        $lov = $this->competencyDao->getLov($request->data);

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one company based on Competency id
     * @param request
     */
    public function getOne(Request $request)
    {
        $dataCompetencyModel = array();
        $dataEmployee = array();
        $dataPosition = array();
        $a = 0; $b = 0; $c = 0;

        $this->validate($request, [
            "companyId" => "required|integer",
            'code' => 'required|max:20|alpha_num|exists:competencies,code']);

        $data = $this->competencyDao->getOne($request->code);
        $id = $data->id;

        $ratingScale = $this->ratingScaleDao->getOne($data->ratingScaleCode);
        $data->ratingScaleName = $ratingScale ? $ratingScale->name : '';

        $data->behaviours = $this->itemBehaviourDao->getAll($id);

        (count($data->behaviours) > 0) ? $data->behaviour = 'Yes' : $data->behaviour = 'No';
            Log::info($id);
        $data->proficiencies = $this->itemProficiencyDao->getAll($id);

        $data->usedByCompetencyGroup = $this->getUsedByCompetencyGroup($data->code);

        if(count($data->usedByCompetencyGroup) > 0) {
            for($i = 0 ; $i < count($data->usedByCompetencyGroup) ; $i++) {
                $getDataCompetencyModel = $this->getUsedByCompetencyModel($data->usedByCompetencyGroup[$i]['code']);
                if(!empty($getDataCompetencyModel)) {
                    for($x = 0 ; $x < count($getDataCompetencyModel) ; $x++){
                        $dataCompetencyModel[$a] = $getDataCompetencyModel[$x];
                        $a++;
                    }
                }
            }
        }
        $dataCollectCompetencyModel = collect($dataCompetencyModel)->unique('code')->all();
        $data->usedByCompetencyModel = $dataCollectCompetencyModel;

        if(count($data->usedByCompetencyModel) > 0) {
            for($i = 0 ; $i < count($data->usedByCompetencyModel) ; $i++) {
                $getDataEmployee = $this->getUsedByEmployee($dataCompetencyModel[$i]['code']);
                if(!empty($getDataEmployee)) {
                    for($x = 0 ; $x < count($getDataEmployee) ; $x++) {
                        $dataEmployee[$b] = $getDataEmployee[$x];
                        $b++;
                    }
                }

                $getDataPosition = $this->getUsedByPosition($dataCompetencyModel[$i]['code']);
                if(!empty($getDataPosition)) {
                    for($x = 0 ; $x < count($getDataPosition) ; $x++) {
                        $dataPosition[$c] = $getDataPosition[$x];
                        $c++;
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
     * Get one company based on Competency id
     * @param request
     */
    public function getOneById(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            'id' => 'required|integer|exists:competencies,id']);

        $data = $this->competencyDao->getOneById($request->id);
        $id = $request->id;

        $ratingScale = $this->ratingScaleDao->getOne($data->ratingScaleCode);
        $data->ratingScaleName = $ratingScale->name;

        $data->behaviours = $this->itemBehaviourDao->getAll($id);

        if(count($data->behaviours) > 0) {
            $data->behaviour = 'Yes';
        } else {
            $data->behaviour = 'No';
        }

        $data->proficiencies = $this->itemProficiencyDao->getAll($id);

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one company based on Competency id
     * @param request
     */
    public function getAllByCode(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            'id' => 'required|integer|exists:competencies,id',
            'code' => 'required|max:20|alpha_num|exists:competencies,code']);

        $data = $this->competencyDao->getAllByCode($request->code, $request->id);
        $data->behaviours = $this->itemBehaviourDao->getAll($request->id);
        $data->proficiencies = $this->itemProficiencyDao->getAll($request->id);

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Competency to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkCompetencyRequest($request);
        $data = array();

        if ($request->inputType === 'SAVE') {
            //code must be unique
            if ($this->competencyDao->checkDuplicateCompetencyCode($request->code) > 0) {
                throw new AppException(trans('messages.duplicateCode'));
            }
        }


        DB::transaction(function () use (&$request, &$data) {

            // Save Competencies
            $competency = $this->constructCompetency($request);
            $competency['id'] = $this->competencyDao->save($competency);

            if ($request->inputType !== 'SAVE') {
                $update = ["eff_end" => Carbon::now(),];
                $this->competencyDao->update($request->id, $update);
            }

            //Save Behaviourss
            $behaviours = $this->constructItemBehaviour($request, $competency['id']);
            $this->itemBehaviourDao->save($behaviours);
            //Save Proficiencies
            $proficiencies = $this->constructItemProficiency($request, $competency['id']);
            $this->itemProficiencyDao->save($proficiencies);

        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update Competency request.
     * @param request
     */
    private function checkCompetencyRequest(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|max:20|alpha_num',
            'name' => 'required|max:50',
            'description' => 'present|max:255',
            'type' => 'required|max:50',
            'ratingScaleCode' => 'required|max:20|exists:rating_scales,code',
            'coreCompetency' => 'required|boolean',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date'
        ]);
    }

    /**
     * Construct an Competency object (array).
     * @param request
     */
    private function constructCompetency(Request $request)
    {
        $competency = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "code" => $request->code,
            "name" => $request->name,
            "description" => $request->description,
            "type" => $request->type,
            "provider_id" => $request->provider,
            "renewal_cycle" => $request->renewalCycle,
            "send_notification" => $request->sendNotification,
            "rating_scale_code" => $request->ratingScaleCode,
            "core_competency" => $request->coreCompetency,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
        ];
        return $competency;
    }

    /**
     * Construct an Item Proficiency object (array).
     * @param request
     */
    private function constructItemProficiency(Request $request, $id)
    {
        $itemProficiencies = [];
        foreach ($request->proficiencies as $proficiency) {

            if(!array_key_exists('proficiency', $proficiency)){
                $proficiency['proficiency'] = null;
            }

            array_push($itemProficiencies, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'rating_scale_detail_id' => $proficiency['ratingScaleDetailId'],
                'proficiency' => $proficiency['proficiency'],
                'type_item' => 'competency',
                'item_id' => $id,
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now()
            ]);
        }

        return $itemProficiencies;
    }

    /**
     * Construct an Item Behaviours object (array).
     * @param request
     */
    private function constructItemBehaviour(Request $request, $id)
    {
        $itemBehaviours = [];
        foreach ($request->behaviours as $behaviour) {
            if($behaviour['behaviour']) {
                array_push($itemBehaviours, [
                    'tenant_id' => $this->requester->getTenantId(),
                    'company_id' => $this->requester->getCompanyId(),
                    'behaviour' => $behaviour['behaviour'],
                    'item_id' => $id,
                    'created_by' => $this->requester->getUserId(),
                    'created_at' => Carbon::now()
                ]);
            }
        }

        return $itemBehaviours;
    }

    private function getUsedByCompetencyGroup($competencyCode) {
        $data = array();

        $getCompetencyGroup = $this->competencyDao->getCompetencyGroupByCompetencyCode($competencyCode);

        $dataCollect = collect($getCompetencyGroup);
        $dataCollect->values()->all();

        if (count($dataCollect) > 0) {
            foreach ($dataCollect as $competencyGroup) {
                array_push($data, [
                    'id' => $competencyGroup->id,
                    'code' => $competencyGroup->code,
                    'name' => $competencyGroup->name,
                    'description' => $competencyGroup->description
                ]);
            }
        }

        return $data;
    }

    private function getUsedByCompetencyModel($competencyGroupCode) {
        $data = array();

        $getCompetencyModel = $this->competencyGroupDao->getCompetencyModelByCompetencyGroupCode($competencyGroupCode);

        $dataCollect = collect($getCompetencyModel);
        $dataCollect->values()->all();

        if (count($dataCollect) > 0) {
            foreach ($dataCollect as $competencyModel) {
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

    private function getUsedByPosition($competencyModelCode) {
        $data = array();

        $getDataPositionCompetencyModel = $this->competencyModelDao->getAllPositionCodeByCompetencyModelCode($competencyModelCode);
        if (count($getDataPositionCompetencyModel) > 0) {
            for($i = 0 ; $i < count($getDataPositionCompetencyModel); $i++) {

                $getPosition = $this->positionDao->getOnePositionByCode($getDataPositionCompetencyModel[$i]->positionCode);
                if($getPosition){
                    $getJob        = $this->jobDao->getOneJobByCode($getPosition->jobCode);
                    $getUnit       = $this->unitDao->getOneUnitByCode($getPosition->unitCode);
                    $getJobName  = $getJob ? $getJob->name : '';
                    $getUnitName = $getUnit ? $getUnit->name : '';

                    array_push($data, [
                        'positionCode' => $getPosition->code,
                        'description' => $getPosition->description,
                        'jobName' => $getJobName,
                        'unitName' => $getUnitName
                    ]);
                } else {
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
