<?php

namespace App\Http\Controllers;

use App\Business\Dao\JobFamilyDao;
use App\Business\Dao\WorkingConditionDao;
use App\Business\Dao\WorkingConditionTypeDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling working condition type process
 * @property Requester requester
 * @property WorkingConditionTypeDao workingConditionTypeDao
 */
class WorkingConditionTypeController extends Controller
{
    public function __construct(Requester $requester, WorkingConditionTypeDao $workingConditionTypeDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->workingConditionTypeDao = $workingConditionTypeDao;
    }

    /**
     * Get all Working Condition Types in one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $workingConditionTypes = $this->workingConditionTypeDao->getAll();

        $resp = new AppResponse($workingConditionTypes, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all Active Working Condition Type in one company
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
        $data = $this->workingConditionTypeDao->getAllActive($offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->workingConditionTypeDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
     * Get All InActive Working Condition Type in one company
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

        $data = $this->workingConditionTypeDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->workingConditionTypeDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, ['companyId' => 'required']);

        $activeWorkingConditionTypes = $this->workingConditionTypeDao->getAllActive();

        $resp = new AppResponse($activeWorkingConditionTypes, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one workingConditionType based on workingCondition type code
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "code" => "required"
        ]);

        $workingConditions = $this->workingConditionTypeDao->getOne($request->code);

        $resp = new AppResponse($workingConditions, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save working condition type to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkWorkingConditionTypeRequest($request);
        if ($this->workingConditionTypeDao->isCodeDuplicate($request->code)) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $workingConditionTypes = $this->constructWorkingConditionType($request);
            $data['id'] = $this->workingConditionTypeDao->save($workingConditionTypes);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update working condition types to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkWorkingConditionTypeRequest($request);

        DB::transaction(function () use (&$request) {
            $workingConditionTypes = $this->constructWorkingConditionType($request);
            unset($workingConditionTypes['code']);
            $this->workingConditionTypeDao->update($request->id, $workingConditionTypes);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete a working condition type.
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $workingConditionTypes = [
                "eff_end" => Carbon::now()
            ];
            $this->workingConditionTypeDao->update($request->id, $workingConditionTypes);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update working Condition Type request.
     * @param Request $request
     */
    private function checkWorkingConditionTypeRequest(Request $request)
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
     * Construct a working condition type object (array).
     * @param Request $request
     * @return array
     */
    private function constructWorkingConditionType(Request $request)
    {
        $workingConditionTypes = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "description" => $request->description,
            "name" => $request->name,
            "code" => $request->code
        ];
        return $workingConditionTypes;
    }
}
