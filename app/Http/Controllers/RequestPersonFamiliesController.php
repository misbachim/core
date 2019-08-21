<?php

namespace App\Http\Controllers;

use App\Business\Dao\RequestPersonFamiliesDao;
use App\Business\Model\Requester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Business\Model\AppResponse;

class RequestPersonFamiliesController extends Controller
{
    public function __construct(
        Requester $requester,
        RequestPersonFamiliesDao $requestPersonFamiliesDao,
        PersonController $personController
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->requestPersonFamiliesDao = $requestPersonFamiliesDao;
        $this->personController = $personController;
    }

    public function getAll(Request $request)
    {
        $this->validate($request, ["profileRequestId" => "required"]);

        $person = $this->requestPersonFamiliesDao->getMany($request->profileRequestId);
        $resp = new AppResponse($person, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getOne(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);

        $person = $this->requestPersonFamiliesDao->getOne(
            $request->id
        );

        $resp = new AppResponse($person, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function save(Request $request)
    {
        $data = array();
        $person = array();
        $this->checkRequestPersonFamily($request);

        DB::transaction(function () use (&$request, &$data) {
            $person = $this->constructRequestPersonFamily($request);
            if ($request->has('occupation')) {
                $person['occupation'] = $request->occupation;
            }
            if ($request->has('description')) {
                $person['description'] = $request->description;
            }
            if ($request->has('address')) {
                $person['address'] = $request->address;
            }
            if ($request->has('phone')) {
                $person['phone'] = $request->phone;
            }
            if ($request->has('isEmergency')) {
                $person['is_emergency'] = $request->isEmergency;
            }
            $data['id'] = $this->requestPersonFamiliesDao->save($person);
        });
        info('data', $person);
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update request person family
     * @param Request $request
     */
    private function checkRequestPersonFamily(Request $request)
    {
        $this->validate($request, [
            'crudType' => 'required|max:1',
            'profileRequestId' => 'required|integer',
            'personFamilyId' => 'nullable|integer'
        ]);
    }

    private function constructRequestPersonFamily(Request $request)
    {
        $person = [
            'tenant_id' => $this->requester->getTenantId(),
            'crud_type' => $request->crudType,
            'profile_request_id' => $request->profileRequestId,
            'person_family_id' => $request->personFamilyId,
            'lov_famr' => $request->lovFamr,
            'name' => $request->name,
            'lov_gndr' => $request->lovGndr,
            'birth_date' => $request->birthDate,
            'lov_edul' => $request->lovEdul,

        ];
        return $person;


    }
}