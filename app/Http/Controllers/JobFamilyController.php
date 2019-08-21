<?php

namespace App\Http\Controllers;

use App\Business\Dao\JobFamilyDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling jobFamily process
 * @property Requester requester
 * @property JobFamilyDao jobFamilyDao
 */
class JobFamilyController extends Controller
{
    public function __construct(Requester $requester, JobFamilyDao $jobFamilyDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->jobFamilyDao = $jobFamilyDao;
    }

    /**
     * Get all jobFamilies in one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $jobFamilies = $this->jobFamilyDao->getAll();

        $resp = new AppResponse($jobFamilies, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }
    
    /**
     * Get all Active Job Family in one company
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
        $data = $this->jobFamilyDao->getAllActive($offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->jobFamilyDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
     * Get All InActive Job Family in one company
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

        $data = $this->jobFamilyDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->jobFamilyDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, ['companyId' => 'required']);

        $activeJobFamilies = $this->jobFamilyDao->getAllActive();

        $resp = new AppResponse($activeJobFamilies, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one jobFamily based on jobFamily code
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "code" => "required"
        ]);

        $jobFamily = $this->jobFamilyDao->getOne($request->code);

        $resp = new AppResponse($jobFamily, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save jobFamily to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkJobFamilyRequest($request);
        if ($this->jobFamilyDao->isCodeDuplicate($request->code)) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $jobFamily = $this->constructJobFamily($request);
            $data['id'] = $this->jobFamilyDao->save($jobFamily);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update jobFamily to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkJobFamilyRequest($request);

        DB::transaction(function () use (&$request) {
            $jobFamily = $this->constructJobFamily($request);
            unset($jobFamily['code']);
            $this->jobFamilyDao->update($request->id, $jobFamily);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete a job family.
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $jobFamily = [
                "eff_end" => Carbon::now()
            ];
            $this->jobFamilyDao->update($request->id, $jobFamily);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update jobFamily request.
     * @param Request $request
     */
    private function checkJobFamilyRequest(Request $request)
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
     * Construct a jobFamily object (array).
     * @param Request $request
     * @return array
     */
    private function constructJobFamily(Request $request)
    {
        $jobFamily = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "description" => $request->description,
            "name" => $request->name,
            "code" => $request->code
        ];
        return $jobFamily;
    }
}
