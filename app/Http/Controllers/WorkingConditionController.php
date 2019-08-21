<?php

namespace App\Http\Controllers;

use App\Business\Dao\WorkingConditionDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling working condition process
 * @property Requester requester
 * @property WorkingConditionDao workingConditionDao
 */
class WorkingConditionController extends Controller
{
    public function __construct(Requester $requester, WorkingConditionDao $workingConditionDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->workingConditionDao = $workingConditionDao;
    }

    /**
     * Get all Working Conditions in one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $workingConditions = $this->workingConditionDao->getAll();

        $resp = new AppResponse($workingConditions, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }
    
    /**
     * Get all Active Working Condition in one company
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
        $data = $this->workingConditionDao->getAllActive($offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->workingConditionDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
     * Get All InActive Working Condition in one company
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

        $data = $this->workingConditionDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->workingConditionDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, ['companyId' => 'required']);

        $activeWorkingConditions = $this->workingConditionDao->getAllActive();

        $resp = new AppResponse($activeWorkingConditions, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one workingCondition based on workingCondition code
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "code" => "required"
        ]);

        $workingConditions = $this->workingConditionDao->getOne($request->code);

        $resp = new AppResponse($workingConditions, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save working condition to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkWorkingConditionRequest($request);
        if ($this->workingConditionDao->isCodeDuplicate($request->code)) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $workingConditions = $this->constructWorkingCondition($request);
            $data['id'] = $this->workingConditionDao->save($workingConditions);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update working conditions to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkWorkingConditionRequest($request);

        DB::transaction(function () use (&$request) {
            $workingConditions = $this->constructWorkingCondition($request);
            unset($workingConditions['code']);
            $this->workingConditionDao->update($request->id, $workingConditions);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete a working condition.
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $workingConditions = [
                "eff_end" => Carbon::now()
            ];
            $this->workingConditionDao->update($request->id, $workingConditions);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update working Condition request.
     * @param Request $request
     */
    private function checkWorkingConditionRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'description' => 'present|max:255',
            'workingConditionTypeCode' => 'required|max:20|alpha_num',
            'code' => 'required|max:20|alpha_num',
            'name' => 'required|max:50'
        ]);
    }

    /**
     * Construct a working condition object (array).
     * @param Request $request
     * @return array
     */
    private function constructWorkingCondition(Request $request)
    {
        $workingConditions = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "description" => $request->description,
            "name" => $request->name,
            "working_condition_type_code" => $request->workingConditionTypeCode,
            "code" => $request->code
        ];
        return $workingConditions;
    }
}
