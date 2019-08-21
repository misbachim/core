<?php

namespace App\Http\Controllers;

use App\Business\Dao\RewardDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling reward process
 */
class RewardController extends Controller
{
    public function __construct(Requester $requester, RewardDao $rewardDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->rewardDao = $rewardDao;
        $this->rewardFields = array('id', 'effBegin', 'effEnd', 'code',
            'name', 'description', 'lovRwty');
    }

    /**
     * Get all rewards for one company
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $rewards = $this->rewardDao->getAll(
            $this->requester->getTenantId(),
            $request->companyId
        );

        $resp = new AppResponse($rewards, trans('messages.allDataRetrieved'));
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

        $data = $this->rewardDao->getAllActive($offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->rewardDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

        /**
     * Get All InActive Reward in one company
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

        $data = $this->rewardDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->rewardDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }


    public function getLov(Request $request)
    {
        $this->validate($request, ['companyId' => 'required']);

        $activeRewards = $this->rewardDao->getAllActive($request->companyId);

        $resp = new AppResponse($activeRewards, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one reward based on reward id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "id" => "required"
        ]);

        $reward = $this->rewardDao->getOne(
            $this->requester->getTenantId(),
            $request->companyId,
            $request->id
        );

        $data = array();
        if (count($reward) > 0) {
            foreach ($this->rewardFields as $field) {
                $data[$field] = $reward->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save reward to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkRewardRequest($request);

        if ($this->rewardDao->checkDuplicateCode($request->code)) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $reward = $this->constructReward($request);
            $data['id'] = $this->rewardDao->save($reward);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update reward to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkRewardRequest($request);

        DB::transaction(function () use (&$request) {
            $reward = $this->constructReward($request);
            $this->rewardDao->update(
                $this->requester->getTenantId(),
                $request->companyId,
                $request->id,
                $reward
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete reward by id.
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $this->rewardDao->delete(
                $this->requester->getTenantId(),
                $request->companyId,
                $request->id
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update reward request.
     * @param request
     */
    private function checkRewardRequest(Request $request)
    {
        $this->validate($request, [
            'effBegin' => 'required|date',
            'effEnd' => 'required|date',
            'code' => 'required|max:20',
            'name' => 'required|max:50',
            'description' => 'required|max:255',
            'lovRwty' => 'required|max:10'
        ]);
    }

    /**
     * Construct a reward object (array).
     * @param request
     */
    private function constructReward(Request $request)
    {
        $reward = [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $request->companyId,
            'eff_begin' => $request->effBegin,
            'eff_end' => $request->effEnd,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'lov_rwty' => $request->lovRwty
        ];
        return $reward;
    }
}
