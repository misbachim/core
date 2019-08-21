<?php

namespace App\Http\Controllers;

use App\Business\Dao\JobDao;
use App\Business\Dao\JobGradeDao;
use App\Business\Dao\JobResponsibilityDao;
use App\Business\Dao\JobWorkingConditionDao;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Business\Dao\UM\MenuDataAccessDao;

/**
 * Class for handling job process
 * @property Requester requester
 * @property JobDao jobDao
 * @property JobGradeDao jobGradeDao
 * @property JobResponsibilityDao jobResponsibilityDao
 * @property JobWorkingConditionDao jobWorkingConditionDao
 */
class JobController extends Controller
{
    public function __construct(
        Requester $requester,
        JobDao $jobDao,
        JobGradeDao $jobGradeDao,
        JobResponsibilityDao $jobResponsibilityDao,
        JobWorkingConditionDao $jobWorkingConditionDao,
        MenuDataAccessDao $menuDataAccessDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->jobDao = $jobDao;
        $this->jobGradeDao = $jobGradeDao;
        $this->jobResponsibilityDao = $jobResponsibilityDao;
        $this->jobWorkingConditionDao = $jobWorkingConditionDao;
        $this->menuDataAccessDao = $menuDataAccessDao;
    }

    /**
     * Get all jobs in one company
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

        $data = $this->jobDao->getAll($offset, $pageLimit);

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->jobDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

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

        $data = $this->jobDao->getAllActive($offset, $pageLimit);

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->jobDao->getTotalRows(),
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

        $data = $this->jobDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->jobDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, ['companyId' => 'required']);

        $activeJobs = $this->jobDao->getAllActive();

        $resp = new AppResponse($activeJobs, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getSLov(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'menuCode' => 'present'
        ]);

        $prsentInDataAccess = $this->menuDataAccessDao->getMenuDataAccessByMenuCode($request->menuCode);

        // if not Super Admin and menuCode exist in menu data access
        if (!$this->requester->getIsUserSA() && count($prsentInDataAccess)) {
            $lov = $this->jobDao->getSLov($request->menuCode);
        } else {
            $lov = $this->jobDao->getAllActive();
        }

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one job based on job id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        $job = $this->jobDao->getOne($request->id);

        $resp = new AppResponse($job, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getOneCode(Request $request)
    {
        $this->validate($request, [
            "code" => "required",
            "companyId" => "required"
        ]);

        $job = $this->jobDao->getOneCode($request->code);
        $job->grades = $this->jobGradeDao->getAll($job->code);
        $job->workingConditions = $this->jobWorkingConditionDao->getAll($job->code);
        $job->responsibilities = $this->jobResponsibilityDao->getAll($job->code);

        $resp = new AppResponse($job, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getJobWorkingCondition(Request $request)
    {
        $this->validate($request, [
            "jobCode" => "required",
            "companyId" => "required"
        ]);

        $workingConditions = $this->jobWorkingConditionDao->getAll($request->jobCode);

        $resp = new AppResponse($workingConditions, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getJobResponsibility(Request $request)
    {
        $this->validate($request, [
            "jobCode" => "required",
            "companyId" => "required"
        ]);

        $responsibilities = $this->jobResponsibilityDao->getAll($request->jobCode);

        $resp = new AppResponse($responsibilities, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save job to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkJobRequest($request);
        if ($this->jobDao->isCodeDuplicate($request->code)) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $job = $this->constructJob($request);
            $job['id'] = $this->jobDao->save($job);

            $this->jobGradeDao->delete($job['code']);
            $this->saveJobGrades($request);
            $this->jobResponsibilityDao->delete($job['code']);
            $this->saveJobResponsibilities($request);
            $this->jobWorkingConditionDao->delete($job['code']);
            $this->saveJobWorkingConditions($request);

            $data['id'] = $job['id'];
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update job to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer|exists:jobs,id']);
        $this->checkJobRequest($request);

        DB::transaction(function () use (&$request) {
            $job = $this->constructJob($request);
            $job['id'] = $request->id;
            unset($job['code']);
            $this->jobDao->update($request->id, $job);

            $this->jobGradeDao->delete($request->code);
            $this->saveJobGrades($request);
            $this->jobResponsibilityDao->delete($request->code);
            $this->saveJobResponsibilities($request);
            $this->jobWorkingConditionDao->delete($request->code);
            $this->saveJobWorkingConditions($request);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Update is_deleted = 1 job to DB
     * @param Request $request
     * @return AppResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "id" => "required"
        ]);

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
        $data = $this->jobDao->search($request->searchQuery, $offset, $limit);

        return $this->renderResponse(new AppResponse($data, trans('messages.allDataRetrieved')));
    }

    /**
     * Validate save/update job request.
     * @param Request $request
     */
    private function checkJobRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'code' => 'required|max:20|alpha_dash',
            'name' => 'required|max:50',
            'description' => 'present|max:255',
            'jobFamilyCode' => 'present|max:20',
            'jobCategoryCode' => 'present|max:20',
            'ordinal' => 'required|integer',
            'grades' => 'present|array',
            'responsibilities' => 'present|array',
            'workingConditions' => 'present|array'
        ]);
    }

    /**
     * Construct a job object (array).
     * @param Request $request
     * @return array
     */
    private function constructJob(Request $request)
    {
        $job = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "code" => $request->code,
            "name" => $request->name,
            "description" => $request->description,
            "job_family_code" => $request->jobFamilyCode,
            "job_category_code" => $request->jobCategoryCode,
            "ordinal" => $request->ordinal
        ];
        return $job;
    }

    /**
     * Save job's grades.
     * @param Request $request
     * @param $job
     */
    private function saveJobGrades(Request $request)
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
                'job_code' => $request->code,
                'grade_code' => $grade['code'],
                'bottom_rate' => round($bottomRate),
                'mid_rate' => array_key_exists('midRate', $grade) ? $grade['midRate'] : null,
                'top_rate' => round($topRate)
            ]);
            $this->jobGradeDao->save($data);
        }
    }

    /**
     * Save job's responsibilities.
     * @param Request $request
     * @param $job
     */
    private function saveJobResponsibilities(Request $request)
    {
        for ($i = 0; $i < count($request->responsibilities); $i++) {
            $this->validate($request, [
                "responsibilities.$i.description" => 'required'
            ]);

            $data = array();
            $jobResponsibilities = $request->responsibilities[$i];
            array_push($data, [
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now(),
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'job_code' => $request->code,
                'is_appraisal' => $jobResponsibilities['isAppraisal'],
                'description' => $jobResponsibilities['description'],
                'eff_begin' => $jobResponsibilities['effBegin'],
                'eff_end' => $jobResponsibilities['effEnd']
            ]);
            $this->jobResponsibilityDao->save($data);
        }
    }

    /**
     * Save job's working conditions.
     * @param Request $request
     * @param $job
     */
    private function saveJobWorkingConditions(Request $request)
    {
        for ($i = 0; $i < count($request->workingConditions); $i++) {
            $this->validate($request, [
                "workingConditions.$i.description" => 'required'
            ]);
            $data = array();
            $jobWorkingConditions = $request->workingConditions[$i];
            array_push($data, [
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now(),
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'is_essential' => $jobWorkingConditions['isEssential'],
                'job_code' => $request->code,
                'description' => $jobWorkingConditions['description'],
                'eff_begin' => $jobWorkingConditions['effBegin'],
                'eff_end' => $jobWorkingConditions['effEnd']
            ]);
            $this->jobWorkingConditionDao->save($data);
        }
    }
}
