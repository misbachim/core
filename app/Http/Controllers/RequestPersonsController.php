<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonAddressDao;
use App\Business\Dao\RequestAddressesDao;
use App\Business\Dao\RequestPersonAddressesDao;
use App\Business\Dao\RequestPersonsDao;
use App\Business\Dao\RequestPersonSocmedsDao;
use App\Business\Model\Requester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Business\Model\AppResponse;

class RequestPersonsController extends Controller
{
    public function __construct(
        Requester $requester,
        RequestPersonsDao $requestPersonsDao,
        PersonController $personController
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->requestPersonsDao = $requestPersonsDao;
        $this->personController = $personController;
    }

    public function getOne(Request $request)
    {
        $this->validate($request, [
            "profileRequestId" => "required"
        ]);

        $person = $this->requestPersonsDao->getOneByProfileRequestId(
            $request->profileRequestId
        );

        $resp = new AppResponse($person, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function save(Request $request)
    {
        $data = array();
        $person = array();
        $this->checkRequestPerson($request);

        DB::transaction(function () use (&$request, &$data) {
            $person = $this->constructRequestPerson($request);
            $data['id'] = $this->requestPersonsDao->save($person);
        });
        info('data', $person);
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update request person socmed
     * @param Request $request
     */
    private function checkRequestPerson(Request $request)
    {
        $this->validate($request, [
            'profileRequestId' => 'required|integer',
            'personSocmedId' => 'integer'
        ]);
    }

    private function constructRequestPerson(Request $request)
    {
        $person = [
            'tenant_id' => $this->requester->getTenantId(),
            'id_card' => $request->idCard,
            'profile_request_id' => $request->profileRequestId,
            'first_name' => $request->firstName,
            'last_name' => $request->lastName,
            'birth_place' => $request->birthPlace,
            'birth_date' => $request->birthDate,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'hobbies' => $request->hobbies,
            'strength' => $request->strength,
            'weakness' => $request->weakness,
            'country_code' => $request->countryCode,
            'lov_blod' => $request->lovBlod,
            'lov_rlgn' => $request->lovRlgn,
            'lov_gndr' => $request->lovGndr,
            'lov_mars' => $request->lovMars,
            'file_photo' => $request->filePhoto
        ];
        return $person;
    }
}