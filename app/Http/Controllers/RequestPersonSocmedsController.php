<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonAddressDao;
use App\Business\Dao\RequestAddressesDao;
use App\Business\Dao\RequestPersonAddressesDao;
use App\Business\Dao\RequestPersonSocmedsDao;
use App\Business\Model\Requester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Business\Model\AppResponse;

class RequestPersonSocmedsController extends Controller
{
    public function __construct(
        Requester $requester,
        RequestPersonSocmedsDao $requestPersonSocmedsDao,
        PersonController $personController
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->requestPersonSocmedsDao = $requestPersonSocmedsDao;
        $this->personController = $personController;
    }

    public function getAll(Request $request)
    {
        $this->validate($request, ["profileRequestId" => "required"]);

        $person = $this->requestPersonSocmedsDao->getMany($request->profileRequestId);
        $resp = new AppResponse($person, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getOne(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);

        $person = $this->requestPersonSocmedsDao->getOne(
            $request->id
        );

        $resp = new AppResponse($person, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function save(Request $request)
    {
        $data = array();
        $person = array();
        $this->checkRequestPersonSocmed($request);

        DB::transaction(function () use (&$request, &$data) {
            $person = $this->constructRequestPersonSocmed($request);
            $data['id'] = $this->requestPersonSocmedsDao->save($person);
        });
        info('data', $person);
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update request person socmed
     * @param Request $request
     */
    private function checkRequestPersonSocmed(Request $request)
    {
        $this->validate($request, [
            'crudType' => 'required|max:1',
            'profileRequestId' => 'required|integer',
            'personSocmedId' => 'nullable|integer'
        ]);
    }

    private function constructRequestPersonSocmed(Request $request)
    {
        $person = [
            'tenant_id' => $this->requester->getTenantId(),
            'crud_type' => $request->crudType,
            'profile_request_id' => $request->profileRequestId,
            'person_socmed_id' => $request->personSocmedId,
            'lov_socm' => $request->lovSocm,
            'account' => $request->account
        ];
        return $person;
    }
}