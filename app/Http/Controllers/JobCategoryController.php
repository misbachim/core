<?php

namespace App\Http\Controllers;

use App\Business\Dao\JobCategoryDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling jobCategory process
 * @property Requester requester
 * @property JobCategoryDao jobCategoryDao
 */
class JobCategoryController extends Controller
{
    public function __construct(Requester $requester, JobCategoryDao $jobCategoryDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->jobCategoryDao = $jobCategoryDao;
    }

    /**
     * Get all jobCategories in one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $jobCategories = $this->jobCategoryDao->getAll();

        $resp = new AppResponse($jobCategories, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all Active Job Category in one company
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
        $data = $this->jobCategoryDao->getAllActive($offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->jobCategoryDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
     * Get All InActive Job Category in one company
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

        $data = $this->jobCategoryDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->jobCategoryDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, ['companyId' => 'required']);

        $activeJobCategories = $this->jobCategoryDao->getAllActive();

        $resp = new AppResponse($activeJobCategories, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one jobCategory based on job category code
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "code" => "required"
        ]);

        $jobCategory = $this->jobCategoryDao->getOne($request->code);

        $resp = new AppResponse($jobCategory, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save jobCategory to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkJobCategoryRequest($request);
        if ($this->jobCategoryDao->isCodeDuplicate($request->code)) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $jobCategory = $this->constructJobCategory($request);
            $data['id'] = $this->jobCategoryDao->save($jobCategory);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update jobCategory to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkJobCategoryRequest($request);

        DB::transaction(function () use (&$request) {
            $jobCategory = $this->constructJobCategory($request);
            unset($jobCategory['code']);
            $this->jobCategoryDao->update($request->id, $jobCategory);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete a job category.
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $jobCategory = [
                "eff_end" => Carbon::now()
            ];
            $this->jobCategoryDao->update($request->id, $jobCategory);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update jobCategory request.
     * @param Request $request
     */
    private function checkJobCategoryRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'description' => 'present|max:255',
            'code' => 'required|max:20|alpha_num',
            'name' => 'required|max:50'
        ]);
    }

    /**
     * Construct a jobCategory object (array).
     * @param Request $request
     * @return array
     */
    private function constructJobCategory(Request $request)
    {
        $jobCategory = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "description" => $request->description,
            "name" => $request->name,
            "code" => $request->code
        ];
        return $jobCategory;
    }
}
