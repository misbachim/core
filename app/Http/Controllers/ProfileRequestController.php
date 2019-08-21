<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonAddressDao;
use App\Business\Dao\ProfileRequestsDao;
use App\Business\Dao\RequestAddressesDao;
use App\Business\Dao\RequestPersonAddressesDao;
use App\Business\Dao\RequestPersonDocumentsDao;
use App\Business\Dao\RequestPersonFamiliesDao;
use App\Business\Dao\RequestPersonsDao;
use App\Business\Dao\RequestPersonSocmedsDao;
use App\Business\Model\Requester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Business\Model\AppResponse;

class ProfileRequestController extends Controller
{
    public function __construct(
        Requester $requester,
        ProfileRequestsDao $profileRequestsDao,
        PersonController $personController
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->profileRequestDao = $profileRequestsDao;
        $this->personController = $personController;
    }

    public function getAll()
    {
        $person = $this->profileRequestDao->getAll();
        $resp = new AppResponse($person, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getOne(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);

        $person = $this->profileRequestDao->getOne(
            $request->id
        );

        $resp = new AppResponse($person, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function checkIfRequestIsPending(Request $request)
    {
        $this->validate($request, [
            "personId" => "required",
            "status" => "required"
        ]);

        $person = $this->profileRequestDao->checkIfRequestIsPending($request->personId, $request->status);
        $resp = new AppResponse($person, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function save(Request $request)
    {
        $data = array();
        $this->checkProfileRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $person = $this->constructProfileRequest($request);
            $data['id'] = $this->profileRequestDao->save($person);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);

        DB::transaction(function () use (&$request) {
            $person = [
                'status' => $request->status,
            ];
            $this->profileRequestDao->update(
                $request->id,
                $person
            );
        });
        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update profile request
     * @param Request $request
     */
    private function checkProfileRequest(Request $request)
    {
        $this->validate($request, [
            'status' => 'required|max:1',
            'personId' => 'required|integer'
        ]);
    }

    private function constructProfileRequest(Request $request)
    {
        $person = [
            'tenant_id' => $this->requester->getTenantId(),
            'person_id' => $request->personId,
            'status' => $request->status
        ];
        return $person;
    }
}