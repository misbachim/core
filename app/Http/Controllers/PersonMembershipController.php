<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonMembershipDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling personMembership process
 * @property Requester requester
 * @property PersonMembershipDao personMembershipDao
 */
class PersonMembershipController extends Controller
{
    public function __construct(Requester $requester, PersonMembershipDao $personMembershipDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personMembershipDao = $personMembershipDao;
    }

    /**
     * Get all personMemberships for one person
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "personId" => "required"
        ]);

        $personMemberships = $this->personMembershipDao->getAll(
            $request->companyId,
            $request->personId
        );

        $resp = new AppResponse($personMemberships, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one personMembership based on personMembership id
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

        $personMembership = $this->personMembershipDao->getOne(
            $request->companyId,
            $request->personId,
            $request->id
        );

        $resp = new AppResponse($personMembership, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personMembership to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonMembershipRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $personMembership = $this->constructPersonMembership($request);
            $data['id'] = $this->personMembershipDao->save($personMembership);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personMembership to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkPersonMembershipRequest($request);

        DB::transaction(function () use (&$request) {
            $personMembership = $this->constructPersonMembership($request);
            $this->personMembershipDao->update(
                $request->companyId,
                $request->personId,
                $request->id,
                $personMembership
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete person membership by id.
     * @param Request $request
     * @return AppResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "personId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $this->personMembershipDao->delete(
                $request->companyId,
                $request->personId,
                $request->id
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update personMembership request.
     * @param Request $request
     */
    private function checkPersonMembershipRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'personId' => 'required|integer|exists:persons,id',
            'lovMbty' => 'required|alpha_num|max:10|exists:lovs,key_data',
            'accNumber' => 'required|max:50',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'employmentCode' => 'nullable|',
            'effEnd' => 'required|date'
        ]);
    }

    /**
     * Construct a personMembership object (array).
     * @param Request $request
     * @return array
     */
    private function constructPersonMembership(Request $request)
    {
        $personMembership = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "person_id" => $request->personId,
            "lov_mbty" => $request->lovMbty,
            "employment_code" => $request->employmentCode,
            "acc_number" => $request->accNumber,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd
        ];
        return $personMembership;
    }
}
