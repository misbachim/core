<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonCompetencyModelDao;
use App\Business\Dao\CompetencyDao;
use App\Business\Dao\CompetencyModelDao;
use App\Business\Dao\PositionCompetencyModelDao;
use App\Business\Dao\AssignmentDao;
use App\Business\Dao\RatingScaleDetailDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Class for handling Person Competency Model process
 */
class PersonCompetencyModelController extends Controller
{
    public function __construct(Requester $requester
        , PersonCompetencyModelDao $personCompetencyModelDao
        , PositionCompetencyModelDao $positionCompetencyModelDao
        , AssignmentDao $assignmentDao
        , CompetencyDao $competencyDao
        , RatingScaleDetailDao $ratingScaleDetailDao
        , CompetencyModelDao $competencyModelDao)
    {
        parent::__construct();
        $this->requester = $requester;
        $this->personCompetencyModelDao = $personCompetencyModelDao;
        $this->positionCompetencyModelDao = $positionCompetencyModelDao;
        $this->assignmentDao = $assignmentDao;
        $this->competencyModelDao = $competencyModelDao;
        $this->competencyDao = $competencyDao;
        $this->ratingScaleDetailDao = $ratingScaleDetailDao;
    }

    /**
     * Get all Person Competency Model
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "employeeId" => "required"
        ]);

        $personCompetency = $this->structureGetPersonCompetencyModel($request->employeeId);

        return $this->renderResponse(new AppResponse($personCompetency, trans('messages.allDataRetrieved')));
    }

    public function getHistory(Request $request) {
        $this->validate($request, [
            "companyId" => "required|integer",
            "employeeId" => "required"
        ]);
        $data = null;
        if($request->personCompetencyModelId) {
            $data = $this->personCompetencyModelDao->getHistory($request->personCompetencyModelId, $request->employeeId);

            if(count($data) > 0) {
                for($i = 0 ; $i < count($data) ; $i++) {
                    $getCompModelLastOne = $this->competencyModelDao->getLastOne($data[$i]->competencyModelCode, $this->requester->getCompanyId());

                    $getName = $getCompModelLastOne ? $getCompModelLastOne->name : '';
                    $data[$i]->competencyModelName = $getName;
                    $data[$i]->detail = $this->structureGetDetail('person', $data[$i]->id);
                }
            }
        }else{
            $data = [];
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function save(Request $request) {
        $this->validate($request, [
            "companyId" => "required|integer"
        ]);

        $data = array();

        DB::transaction(function () use (&$request, &$data) {
            $personCompetencyModel = $this->constructPersonCompetencyModel($request);

            if($request->id) {
                $objUpdate = [
                   'eff_end' => Carbon::now()
                ];
                $this->personCompetencyModelDao->update($request->id, $objUpdate);
            }
            $data['id'] = $this->personCompetencyModelDao->save($personCompetencyModel);
            $this->savePersonCompetencyModelDetail($request, $data['id'], $request->employeeId);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    public function getTemporaryPersonCompetency(Request $request) {

        $positionCompetencyModel = $this->structureGetPositionCompetencyModel($request->personId);
        $personCompetencyModel = $this->structureGetPersonCompetencyModel($request->employeeId);

        info('position', [$positionCompetencyModel]);
        info('person', [$personCompetencyModel]);

        $data = (object)[
            'competencyModelCode' => $positionCompetencyModel->competencyModelCode,
            'competencyModelName' => $positionCompetencyModel->competencyModelName,
            'effBegin' => Carbon::now(),
            'effEnd' => '9999-12-31'
        ];
        $dataDetail = [];

        foreach ($positionCompetencyModel->detail as $detailPosition) {
            $cek = 0;
            foreach ($personCompetencyModel->detail as $detailPerson) {
                if($detailPosition->competencyCode === $detailPerson->competencyCode) {
                    array_push($dataDetail, [
                        'competencyCode' => $detailPosition->competencyCode,
                        'competencyName' => $detailPosition->competencyName,
                        'competencyId' => $detailPosition->competencyId,
                        'ratingScaleDetailId' => $detailPerson->ratingScaleDetailId,
                        'ratingScaleCode' => $this->competencyDao->getOneById($detailPosition->competencyId)->ratingScaleCode,
                        'label' => $detailPerson->label,
                        'usedInProcessReview' => $detailPerson->usedInProcessReview
                    ]);
                    $cek = 1;
                    break;
                }
            }
            if($cek == 0){
                array_push($dataDetail, [
                    'competencyCode' => $detailPosition->competencyCode,
                    'competencyName' => $detailPosition->competencyName,
                    'competencyId' => $detailPosition->competencyId,
                    'ratingScaleDetailId' => null,
                    'ratingScaleCode' => $this->competencyDao->getOneById($detailPosition->competencyId)->ratingScaleCode,
                    'label' => '',
                    'usedInProcessReview' => true
                ]);
            }
        }
        $data->detail = $dataDetail;

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function checkConditionDataCompetency(Request $request) {

        $this->validate($request, [
            'employeeId' => "required",
            'personId' => 'required|integer|exists:persons,id']);

        $positionCompetencyModel = $this->structureGetPositionCompetencyModel($request->personId);
        $personCompetencyModel = $this->structureGetPersonCompetencyModel($request->employeeId);

        info('position',[$positionCompetencyModel]);
        info('person',[$personCompetencyModel]);

        if($positionCompetencyModel){
            if($positionCompetencyModel->competencyModelCode === '') {
                $positionCompetencyModel = (object)['competencyModelCode' => ''];
            }
        }
        if($personCompetencyModel){
            if($personCompetencyModel->competencyModelCode === '') {
                $personCompetencyModel = (object)['competencyModelCode' => ''];
            }
        }

        $conditionCompareDate = false;
        $conditionDataPosition = false;

        if($positionCompetencyModel){
            if($positionCompetencyModel->competencyModelCode !== $personCompetencyModel->competencyModelCode){
                $conditionCompareDate = true;
            }else{ $conditionCompareDate = false; }
        }

        if($positionCompetencyModel){
            if($positionCompetencyModel->competencyModelCode !== '') {
                $conditionDataPosition = true;
            }else{ $conditionDataPosition = false; }
        }

        $condition = [
            'conditionCompareDate' => $conditionCompareDate,
            'conditionDataPosition' => $conditionDataPosition
        ];

        $resp = new AppResponse($condition, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function structureGetPositionCompetencyModel($personId) {
        $getAssignment = $this->assignmentDao->getOneLastAssignmentByPersonId($personId);
        $getPositionCompetencyModel = null;

        if($getAssignment) {
            $getPositionCompetencyModel = $this->positionCompetencyModelDao->getOne($getAssignment->positionCode);
            if(count($getPositionCompetencyModel) > 0) {

                $getCompModelLastOne = $this->competencyModelDao->getLastOne($getPositionCompetencyModel->competencyModelCode, $this->requester->getCompanyId());

                $getName = $getCompModelLastOne ? $getCompModelLastOne->name : '';
                $getDetail = $this->structureGetDetail('position', $getPositionCompetencyModel->id);

                $getPositionCompetencyModel->competencyModelName = $getName ? $getName : '';
                $getPositionCompetencyModel->detail = $getDetail;

            }else{
                $getPositionCompetencyModel = $this->constructGetParentNull('position');
            }
        }

        return $getPositionCompetencyModel;
    }

    public function structureGetPersonCompetencyModel($employeeId) {
        $getPersonCompetencyModel = $this->personCompetencyModelDao->getAll($employeeId);
        info('$getPersonCompetencyModel', [$getPersonCompetencyModel]);

        if(count($getPersonCompetencyModel) > 0) {
            info('getLastOne', [$this->competencyModelDao->getLastOne($getPersonCompetencyModel->competencyModelCode, $this->requester->getCompanyId())]);
            $getCompModelLastOne = $this->competencyModelDao->getLastOne($getPersonCompetencyModel->competencyModelCode, $this->requester->getCompanyId());
            $getName = $getCompModelLastOne ? $getCompModelLastOne->name : '';
            $getDetail = $this->structureGetDetail('person', $getPersonCompetencyModel->id);

            $getPersonCompetencyModel->competencyModelName = $getName ? $getName : '';
            $getPersonCompetencyModel->detail = $getDetail;

        }else{
            $getPersonCompetencyModel = $this->constructGetParentNull('person');
        }

        return $getPersonCompetencyModel;
    }

    public function structureGetDetail($flag, $competencyModelId) {
        info('$competencyModelId', [$competencyModelId]);
        $detail = $flag === 'position' ? $this->positionCompetencyModelDao->getAllDetails($competencyModelId) : $this->personCompetencyModelDao->getDetail($competencyModelId);
        if(count($detail) > 0) {
            for($i = 0 ; $i < count($detail) ; $i++) {

                $getCompetency = $this->competencyDao->getOneById($detail[$i]->competencyId);

                $detail[$i]->competencyCode = $getCompetency->code;
                $detail[$i]->competencyName = $getCompetency->name;
                $detail[$i]->ratingScaleCode = $getCompetency->ratingScaleCode;

                if($detail[$i]->ratingScaleDetailId != null){
                    $ratingScaleDetail = $this->ratingScaleDetailDao->getOne($detail[$i]->ratingScaleDetailId);
                    $detail[$i]->label = $ratingScaleDetail->label;
                }
                else {
                    $detail[$i]->label = $detail[$i]->ratingScaleDetailId;
                }
            }
        } else {
          $detail = $flag === 'position' ? $this->constructGetDetailNull('position') : $this->constructGetDetailNull('person');
        }

        return $detail;
    }

    public function savePersonCompetencyModelDetail(Request $request, $personCompetencyModelId, $employeeId) {

        for ($i = 0; $i < count($request->detail); $i++) {
            $data = array();
            $personCompetencyModelDetail = $request->detail[$i];
            array_push($data, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'employee_id' => $employeeId,
                'employee_competency_model_id' => $personCompetencyModelId,
                'competency_id' => $personCompetencyModelDetail['competencyId'],
                'rating_scale_detail_id' => $personCompetencyModelDetail['ratingScaleDetailId'],
                'used_in_process_review' => $personCompetencyModelDetail['usedInProcessReview'],
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now()
            ]);
            $this->personCompetencyModelDao->savePersonCompetencyModelDetails($data);
        }
    }

    public function constructPersonCompetencyModel(Request $request) {

        $personCompetencyModel = [
            "tenant_id"          => $this->requester->getTenantId(),
            "company_id"         => $this->requester->getCompanyId(),
            "competency_model_code" => $request->competencyModelCode,
            "employee_id"        => $request->employeeId,
            "eff_begin"          => $request->effBegin,
            "eff_end"            => $request->effEnd
        ];
        return $personCompetencyModel;
    }

    public function constructGetDetailNull($flag) {
        $array = null;

        if($flag === 'position') {
            $array = (object)[
                'id' => '',
                'positionCompetencyModelId' => '',
                'competencyId' => '',
                'ratingScaleDetailId' => '',
                'usedInProcessReview' => '',
                'competencyCode' => '',
                'competencyName' => '',
                'ratingScaleCode' => '',
                'label' => ''
            ];
        }else{
            $array = (object)[
                'id' => '',
                'employeeCompetencyModelId' => '',
                'competencyId' => '',
                'ratingScaleDetailId' => '',
                'usedInProcessReview' => '',
                'competencyCode' => '',
                'competencyName' => '',
                'ratingScaleCode' => '',
                'label' => ''
            ];
        }
        return $array;
    }

    public function constructGetParentNull($flag) {
        $array = null;

        if($flag === 'position') {
            $array = (object)[
                'id' => '',
                'competencyModelCode' => '',
                'competencyModelName' => '',
                'positionCode' => '',
                'detail' => []
            ];
        } else {
            $array = (object)[
                'id' => '',
                'competencyModelCode' => '',
                'competencyModelName' => '',
                'employeeId' => '',
                'detail' => []
            ];
        }
        return $array;
    }

}
