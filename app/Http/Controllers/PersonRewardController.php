<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonRewardDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling personReward process
 */
class PersonRewardController extends Controller
{
    public function __construct(Requester $requester, PersonRewardDao $personRewardDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personRewardDao = $personRewardDao;
    }

    /**
     * Get all personRewards for one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "personId" => "required"
        ]);

        $personRewards = $this->personRewardDao->getAll(
            $request->companyId,
            $request->personId
        );

        $resp = new AppResponse($personRewards, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one personReward based on personReward id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "personId" => "required",
            "id" => "required"
        ]);

        $personReward = $this->personRewardDao->getOne(
            $request->companyId,
            $request->personId,
            $request->id
        );

        $resp = new AppResponse($personReward, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personReward to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkRewardRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $personReward = $this->constructReward($request);
            $data['id'] = $this->personRewardDao->save($personReward);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personReward to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required'
        ]);
        $this->checkRewardRequest($request);

        DB::transaction(function () use (&$request) {
            $personReward = $this->constructReward($request);
            $this->personRewardDao->update(
                $request->companyId,
                $request->personId,
                $request->id,
                $personReward
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete personReward by id.
     * @param Request $request
     * @return AppResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required",
            "personId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $this->personRewardDao->delete(
                $request->companyId,
                $request->personId,
                $request->id
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update personReward request.
     * @param Request $request
     */
    private function checkRewardRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'personId' => 'required|integer|exists:persons,id',
            'rewardCode' => 'required|alpha_num|max:20|exists:rewards,code',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'description' => 'present|max:255'
        ]);
    }

    /**
     * Construct a personReward object (array).
     * @param Request $request
     * @return array
     */
    private function constructReward(Request $request)
    {
        $personReward = [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $request->companyId,
            'person_id' => $request->personId,
            'reward_code' => $request->rewardCode,
            'eff_begin' => $request->effBegin,
            'eff_end' => $request->effEnd,
            'description' => $request->description
        ];
        return $personReward;
    }
}
