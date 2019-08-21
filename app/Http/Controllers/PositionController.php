<?php

namespace App\Http\Controllers;

use App\Business\Dao\PositionDao;
use App\Business\Dao\PositionGradeDao;
use App\Business\Dao\PositionResponsibilityDao;
use App\Business\Dao\PositionWorkingConditionDao;
use App\Business\Dao\PositionCredentialDao;
use App\Business\Dao\PositionCompetencyModelDao;
use App\Business\Dao\RatingScaleDetailDao;
use App\Business\Dao\CompetencyDao;
use App\Business\Dao\CompetencyModelDao;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Business\Dao\UM\MenuDataAccessDao;

/**
 * Class for handling position process
 * @property Requester requester
 * @property PositionDao positionDao
 * @property PositionGradeDao positionGradeDao
 * @property PositionResponsibilityDao positionResponsibilityDao
 * @property PositionWorkingConditionDao positionWorkingConditionDao
 */
class PositionController extends Controller
{
    public function __construct(
        Requester $requester,
        PositionDao $positionDao,
        PositionGradeDao $positionGradeDao,
        PositionResponsibilityDao $positionResponsibilityDao,
        PositionWorkingConditionDao $positionWorkingConditionDao,
        PositionCredentialDao $positionCredentialDao,
        PositionCompetencyModelDao $positionCompetencyModelDao,
        RatingScaleDetailDao $ratingScaleDetailDao,
        CompetencyDao $competencyDao,
        CompetencyModelDao $competencyModelDao,
        MenuDataAccessDao $menuDataAccessDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->positionDao = $positionDao;
        $this->positionGradeDao = $positionGradeDao;
        $this->positionResponsibilityDao = $positionResponsibilityDao;
        $this->positionWorkingConditionDao = $positionWorkingConditionDao;
        $this->positionCredentialDao = $positionCredentialDao;
        $this->positionCompetencyModelDao = $positionCompetencyModelDao;
        $this->ratingScaleDetailDao = $ratingScaleDetailDao;
        $this->competencyDao = $competencyDao;
        $this->competencyModelDao = $competencyModelDao;
        $this->menuDataAccessDao = $menuDataAccessDao;
    }

    /**
     * Get all positions in one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
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

        $data = $this->positionDao->getAll($offset, $pageLimit);

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->positionDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
     * Get all Active grade in one company
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

        $data = $this->positionDao->getAllActive($offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->positionDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
     * Get All InActive grade in one company
     */
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

        $data = $this->positionDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->positionDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    public function getNLov(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required'
        ]);

        $activePositions = $this->positionDao->getAllActive();

        $resp = new AppResponse($activePositions, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'unitCode' => 'required',
            'jobCode' => 'required'
        ]);

        $activePositions = $this->positionDao->getLov(
            $request->unitCode,
            $request->jobCode
        );

        $resp = new AppResponse($activePositions, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getSLov(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'menuCode' => 'present',
        ]);

        $lov = collect();

        // sorry for make this verbose validation
        // I need to make sure it is still compatible with
        // all API already define in Admin UI
        // Cheer ^-^/ - AWey
        if (($request->has('unitCode') && $request->has('jobCode'))
            && $request->filled('unitCode') && $request->filled('jobCode')) {

            $prsentInDataAccess = $this->menuDataAccessDao->getMenuDataAccessByMenuCode($request->menuCode);

            // if not Super Admin and menuCode exist in menu data access
            if (!$this->requester->getIsUserSA() && count($prsentInDataAccess)) {
                $lov = $this->positionDao->getSLov($request->menuCode, $request->unitCode, $request->jobCode);
            } else {
                $lov = $this->positionDao->getLov($request->unitCode, $request->jobCode);
            }
        } else if ($request->has('unitCode') && $request->filled('unitCode')) {
            $lov = $this->positionDao->getLov($request->unitCode);
        } else {
            $lov = $this->positionDao->getAllActive();
        }

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one position based on position id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "code" => "required",
            "companyId" => "required"
        ]);

        $position = $this->positionDao->getOne($request->code);
        if ($position) {
            $position->grades = $this->positionGradeDao->getAll($position->code);
            $position->workingConditions = $this->positionWorkingConditionDao->getAll($position->code);
            $position->responsibilities = $this->positionResponsibilityDao->getAll($position->code);
            $position->credentials = $this->positionCredentialDao->getAll($position->code);
            $position->competencyModel = $this->positionCompetencyModelDao->getOne($position->code);

            if ($position->competencyModel != null) {
                $position->competencyModel->competencyModelName = $this->competencyModelDao->getLastOne($position->competencyModel->competencyModelCode, $this->requester->getCompanyId())->name;
                $position->competencyModel->detail = $this->getCompetencyModelDetail($position->competencyModel->id);
            }
        }

        $resp = new AppResponse($position, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getPositionByUnit(Request $request)
    {
        $this->validate($request, [
            "unitCode" => "required",
        ]);

        $position = $this->positionDao->getQuantityByUnit($request->unitCode);

        $resp = new AppResponse($position, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getCompetencyModelHistory(Request $request)
    {
        $this->validate($request, [
            "positionCode" => "required",
            "positionCompetencyModelId" => "required"
        ]);

        $data = null;
        if ($request->positionCompetencyModelId) {
            $data = $this->positionCompetencyModelDao->getHistory($request->positionCompetencyModelId, $request->positionCode);

            if (count($data) > 0) {
                for ($i = 0; $i < count($data); $i++) {
                    $data[$i]->competencyModelName = $this->competencyModelDao->getLastOne($data[$i]->competencyModelCode, $this->requester->getCompanyId())->name;
                    $data[$i]->detail = $this->getCompetencyModelDetail($data[$i]->id);
                }
            }
        } else {
            $data = [];
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save position to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $this->checkPositionRequest($request);
        if ($this->positionDao->isCodeDuplicate($request->code)) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $position = $this->constructPosition($request);
            $this->positionDao->save($position);

            $this->positionGradeDao->delete($position['code']);
            $this->savePositionGrades($request);

            $this->positionResponsibilityDao->delete($position['code']);
            $this->savePositionResponsibilities($request);

            $this->positionWorkingConditionDao->delete($position['code']);
            $this->savePositionWorkingConditions($request);

            $this->positionCredentialDao->delete($position['code']);
            $this->savePositionCredentials($request);

            $this->saveCompetencyModel($request);
        });

        $resp = new AppResponse(['code' => $request->code], trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update position to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->checkPositionRequest($request);
        $this->validate($request, ['code' => 'exists:positions,code']);

        DB::transaction(function () use (&$request) {
            $position = $this->constructPosition($request);
            unset($position['code']);
            $this->positionDao->update($request->code, $position);

            $this->positionGradeDao->delete($request->code);
            $this->savePositionGrades($request);

            $this->positionResponsibilityDao->delete($request->code);
            $this->savePositionResponsibilities($request);

            $this->positionWorkingConditionDao->delete($request->code);
            $this->savePositionWorkingConditions($request);

            $this->positionCredentialDao->delete($request->code);
            $this->savePositionCredentials($request);

            if ($request->competencyModel['flagUpdate']) {
                $this->saveCompetencyModel($request);
            }
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Update is_deleted = 1 position to DB
     * @param Request $request
     * @return AppResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "code" => "required"
        ]);

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update position request.
     * @param Request $request
     */
    private function checkPositionRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'code' => 'required|max:20|alpha_dash',
            'name' => 'required|max:50',
            'description' => 'present|max:255',
            'unitCode' => 'required|max:20|exists:units,code',
            'jobCode' => 'required|max:20|exists:jobs,code',
            'isHead' => 'required|boolean',
            'grades' => 'present|array',
            'responsibilities' => 'present|array',
            'workingConditions' => 'present|array',
            'credentials' => 'present|array'
        ]);
    }

    /**
     * Construct a position object (array).
     * @param Request $request
     * @return array
     */
    private function constructPosition(Request $request)
    {
        $position = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "code" => $request->code,
            "name" => $request->name,
            "description" => $request->description,
            "unit_code" => $request->unitCode,
            "job_code" => $request->jobCode,
            "is_head" => $request->isHead
        ];
        return $position;
    }

    /**
     * Save position's grades.
     * @param Request $request
     */
    private function savePositionGrades(Request $request)
    {
        for ($i = 0; $i < count($request->grades); $i++) {
            $this->validate($request, [
                "grades.$i.code" => 'required|exists:grades,code|max:20',
                "grades.$i.midRate" => "required|integer|min:0"
            ]);

            $data = array();
            $grade = $request->grades[$i];
            $bottomRate = $grade['midRate'] - ($grade['midRate'] * 20 / 100);
            $topRate = $grade['midRate'] + ($grade['midRate'] * 20 / 100);
            array_push($data, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'position_code' => $request->code,
                'grade_code' => $grade['code'],
                'bottom_rate' => round($bottomRate),
                'mid_rate' => array_key_exists('midRate', $grade) ? $grade['midRate'] : null,
                'top_rate' => round($topRate)
            ]);
            $this->positionGradeDao->save($data);
        }
    }

    /**
     * Save position's responsibilities.
     * @param Request $request
     * @param $job
     */
    private function savePositionResponsibilities(Request $request)
    {
        for ($i = 0; $i < count($request->responsibilities); $i++) {
            $this->validate($request, [
                "responsibilities.$i.description" => 'required'
            ]);

            $data = array();
            $positionResponsibilities = $request->responsibilities[$i];
            array_push($data, [
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now(),
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'position_code' => $request->code,
                'is_appraisal' => $positionResponsibilities['isAppraisal'],
                'description' => $positionResponsibilities['description'],
                'eff_begin' => $positionResponsibilities['effBegin'],
                'eff_end' => $positionResponsibilities['effEnd']
            ]);
            $this->positionResponsibilityDao->save($data);
        }
    }

    /**
     * Save position's working conditions.
     * @param Request $request
     * @param $job
     */
    private function savePositionWorkingConditions(Request $request)
    {
        for ($i = 0; $i < count($request->workingConditions); $i++) {
            $this->validate($request, [
                "workingConditions.$i.description" => 'required'
            ]);
            $data = array();
            $positionWorkingConditions = $request->workingConditions[$i];
            array_push($data, [
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now(),
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'position_code' => $request->code,
                'is_essential' => $positionWorkingConditions['isEssential'],
                'description' => $positionWorkingConditions['description'],
                'eff_begin' => $positionWorkingConditions['effBegin'],
                'eff_end' => $positionWorkingConditions['effEnd']
            ]);
            $this->positionWorkingConditionDao->save($data);
        }
    }

    /**
     * Save position's credentials.
     * @param Request $request
     */
    private function savePositionCredentials(Request $request)
    {
        for ($i = 0; $i < count($request->credentials); $i++) {
            $this->validate($request, [
                "credentials.$i.credentialCode" => 'required'
            ]);
            $data = array();
            $positionCredentials = $request->credentials[$i];
            array_push($data, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'position_code' => $request->code,
                'credential_code' => $positionCredentials['credentialCode'],
                'eff_begin' => $positionCredentials['effBegin'],
                'eff_end' => $positionCredentials['effEnd'],
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now()
            ]);
            $this->positionCredentialDao->save($data);
        }
    }

    public function isFull(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'code' => 'required'
        ]);

        $isFull = $this->positionDao->isFull(
            $request->companyId,
            $request->code
        );

        $resp = new AppResponse($isFull, trans('messages.dataRetrieved'));
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
        $data = $this->positionDao->search($request->searchQuery, $offset, $limit);

        return $this->renderResponse(new AppResponse($data, trans('messages.allDataRetrieved')));
    }

    /**
     * Save position's competency model.
     * @param Request $request
     */
    private function saveCompetencyModel(Request $request)
    {
        if (count($request->competencyModel['detail']) > 0) {
            $reqData = $request->competencyModel;
            $request->merge($reqData);
            $this->validate($request, [
                'competencyModelCode' => 'required',
                'positionCode' => 'required',
                'effBegin' => 'required',
                'effEnd' => 'required',
            ]);

            $position_competency_model = [
                "tenant_id" => $this->requester->getTenantId(),
                "company_id" => $this->requester->getCompanyId(),
                "competency_model_code" => $request->competencyModel['competencyModelCode'],
                "position_code" => $request->competencyModel['positionCode'],
                "eff_begin" => $request->competencyModel['effBegin'],
                "eff_end" => $request->competencyModel['effEnd'],
                "created_at" => Carbon::now(),
                "created_by" => $this->requester->getUserId()
            ];

            if ($request->competencyModel['flagUpdate'] && $request->has('id')) {
                $objUpdate = [
                    'eff_end' => Carbon::now()
                ];
                $this->positionCompetencyModelDao->update($request->competencyModel['id'], $objUpdate);
            }

            $position_competency_model_id = $this->positionCompetencyModelDao->save($position_competency_model);
            $this->saveCompetencyModelDetails($request, $position_competency_model_id);
        }
    }

    /**
     * Save position's competency model.
     * @param Request $request
     */
    private function saveCompetencyModelDetails(Request $request, $position_competency_model_id)
    {
        for ($i = 0; $i < count($request->competencyModel['detail']); $i++) {
            $this->validate($request, [
                // "credentials.$i.credentialCode" => 'required'
            ]);

            $data = array();
            array_push($data, [
                "tenant_id" => $this->requester->getTenantId(),
                "company_id" => $this->requester->getCompanyId(),
                "position_competency_model_id" => $position_competency_model_id,
                "competency_id" => $request->competencyModel['detail'][$i]['competencyId'],
                "rating_scale_detail_id" => $request->competencyModel['detail'][$i]['ratingScaleDetailId'],
                "used_in_process_review" => $request->competencyModel['detail'][$i]['usedInProcessReview'],
                "created_at" => Carbon::now(),
                "created_by" => $this->requester->getUserId()
            ]);

            $this->positionCompetencyModelDao->saveDetail($data);
        }
    }

    private function getCompetencyModelDetail($competencyModelId)
    {
        $detail = $this->positionCompetencyModelDao->getAllDetails($competencyModelId);
        if (count($detail) > 0) {
            for ($i = 0; $i < count($detail); $i++) {
                $competency = $this->competencyDao->getOneById($detail[$i]->competencyId);
                $detail[$i]->competencyCode = $competency->code;
                $detail[$i]->competencyName = $competency->name;
                $detail[$i]->ratingScaleCode = $competency->ratingScaleCode;

                if ($detail[$i]->ratingScaleDetailId != null) {
                    $ratingScaleDetail = $this->ratingScaleDetailDao->getOne($detail[$i]->ratingScaleDetailId);
                    $detail[$i]->ratingScaleDetailLabel = $ratingScaleDetail->label;
                } else {
                    $detail[$i]->ratingScaleDetailLabel = $detail[$i]->ratingScaleDetailId;
                }

            }
        }

        return $detail;
    }

    public function checkHeadOfUnitOnUnit(Request $request)
    {
        $this->validate($request, [
            "unitCode" => "required",
        ]);

        $data = $this->positionDao->checkHeadOfUnitOnUnit(
            $request->unitCode
        );

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }


    /*
    |--------------------------------------------------------------------------------
    | ambil semua posisi untuk multipleselect terkecuali positionCode[]  di parameter
    |--------------------------------------------------------------------------------
    |
    |
    */
    public function getPositionForMultipleSelect(Request $request) {
        $this->validate($request, [
            'companyId' => 'required'
        ]);

        $activePositions = $this->positionDao->getPositionForMultipleSelect($request->param);

        $resp = new AppResponse($activePositions, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }
}
