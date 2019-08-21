<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonOrganizationDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling personOrganization process
 * @property Requester requester
 * @property PersonOrganizationDao personOrganizationDao
 */
class PersonOrganizationController extends Controller
{
    public function __construct(Requester $requester, PersonOrganizationDao $personOrganizationDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personOrganizationDao = $personOrganizationDao;
    }

    /**
     * Get all personOrganizations for one person
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["personId" => "required|integer"]);

        $personOrganizations = $this->personOrganizationDao->getAll($request->personId);

        $resp = new AppResponse($personOrganizations, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one personOrganization based on personOrganization id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "personId" => "required|integer",
            "id" => "required|integer"
        ]);

        $personOrganization = $this->personOrganizationDao->getOne(
            $request->personId,
            $request->id
        );

        $resp = new AppResponse($personOrganization, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personOrganization to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonOrganizationRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $personOrganization = $this->constructPersonOrganization($request);
            $data['id'] = $this->personOrganizationDao->save($personOrganization);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personOrganization to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);
        $this->checkPersonOrganizationRequest($request);

        DB::transaction(function () use (&$request) {
            $personOrganization = $this->constructPersonOrganization($request);
            $this->personOrganizationDao->update(
                $request->personId,
                $request->id,
                $personOrganization
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete person organization by id.
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
            $this->personOrganizationDao->delete(
                $request->personId,
                $request->id
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update personOrganization request.
     * @param Request $request
     */
    private function checkPersonOrganizationRequest(Request $request)
    {
        $this->validate($request, [
            'institution' => 'required|max:50',
            'yearBegin' => 'required|integer|min:0|max_field:yearEnd',
            'yearEnd' => 'required|integer',
            'description' => 'required|max:255'
        ]);
    }

    /**
     * Construct a personOrganization object (array).
     * @param Request $request
     * @return array
     */
    private function constructPersonOrganization(Request $request)
    {
        $personOrganization = [
            "tenant_id" => $this->requester->getTenantId(),
            "person_id" => $request->personId,
            "institution" => $request->institution,
            "year_begin" => $request->yearBegin,
            "year_end" => $request->yearEnd,
            "description" => $request->description,
        ];
        return $personOrganization;
    }
}
