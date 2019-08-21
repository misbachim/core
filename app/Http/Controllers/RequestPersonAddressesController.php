<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonAddressDao;
use App\Business\Dao\RequestAddressesDao;
use App\Business\Dao\RequestPersonAddressesDao;
use App\Business\Model\Requester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Business\Model\AppResponse;

class RequestPersonAddressesController extends Controller
{
    public function __construct(
        Requester $requester,
        RequestPersonAddressesDao $requestPersonAddressesDao,
        PersonAddressDao $personAddressDao,
        PersonAddressController $personAddressController,
        PersonController $personController
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->requestPersonAddressesDao = $requestPersonAddressesDao;
        $this->personAddressDao = $personAddressDao;
        $this->personAddressController = $personAddressController;
        $this->personController = $personController;
    }

    public function getAll(Request $request)
    {
        $this->validate($request, ["profileRequestId" => "required"]);

        $person = $this->requestPersonAddressesDao->getMany($request->profileRequestId);
        $resp = new AppResponse($person, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getOne(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);

        $person = $this->requestPersonAddressesDao->getOne(
            $request->id
        );

        $resp = new AppResponse($person, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function save(Request $request)
    {
        $data = array();
        $person = array();
        $this->checkRequestPersonAddress($request);

        DB::transaction(function () use (&$request, &$data) {
            $person = $this->constructRequestPersonAddress($request);
            if ($request->has('postalCode')) {
                $person['postal_code'] = $request->postalCode;
            }
            if ($request->has('mapLocation')) {
                $person['map_location'] = $request->mapLocation;
            }
            if ($request->has('phone')) {
                $person['phone'] = $request->phone;
            }
            if ($request->has('fax')) {
                $person['fax'] = $request->fax;
            }
            $data['id'] = $this->requestPersonAddressesDao->save($person);
        });
        info('data', $person);
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update request person address
     * @param Request $request
     */
    private function checkRequestPersonAddress(Request $request)
    {
        $this->validate($request, [
            'crudType' => 'required|max:1',
            'profileRequestId' => 'required|integer',
            'personAddressId' => 'nullable|integer'
        ]);
    }

    private function constructRequestPersonAddress(Request $request)
    {
        $person = [
            'tenant_id' => $this->requester->getTenantId(),
            'crud_type' => $request->crudType,
            'profile_request_id' => $request->profileRequestId,
            'person_address_id' => $request->personAddressId,
            'address' => $request->address,
            'lov_rsty' => $request->lovRsty,
            'lov_rsow' => $request->lovRsow,
            'city_code' => $request->cityCode,
        ];
        return $person;
    }
}