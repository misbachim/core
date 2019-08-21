<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonAddressDao;
use App\Business\Dao\RequestAddressesDao;
use App\Business\Model\Requester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Business\Model\AppResponse;

class RequestAddressesController extends Controller {
    public function  __construct(
        Requester $requester,
        PersonAddressDao $personAddressDao,
        PersonAddressController $personAddressController,
        PersonController $personController,
        RequestAddressesDao $requestAddressesDao
    ){
        parent::__construct();
        $this->requester = $requester;
        $this->personAddressDao = $personAddressDao;
        $this->personAddressController = $personAddressController;
        $this->personController = $personController;
        $this->requestAddressesDao = $requestAddressesDao;
    }

    public function getAll(Request $request)
    {
        $this->validate($request, ["personId" => "required"]);

        $person = $this->requestAddressesDao->getAll($request->personId);
        $person->person = $this->personController->getOneEmployee($person->employeeId);
        $resp = new AppResponse($person, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getOne($employeeId, $id)
    {

        $person = $this->requestAddressesDao->getOne(
            $id
        );
        $req = new \Illuminate\Http\Request();
        $req->replace([
            'id' => $employeeId
        ]);
        $person->person = $this->personController->getOneEmployeeForWorklist($req);

        return $person;
    }

    public function checkIfRequestIsPending(Request $request){
        $this->validate($request, [
            "employeeId" => "required",
            "status" => "required"
        ]);

        $person = $this->requestAddressesDao->checkIfRequestIsPending($request->employeeId, $request->status);
        $resp = new AppResponse($person, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function save(Request $request)
    {
        $data = array();
        $person = array();

        DB::transaction(function () use (&$request, &$data) {
            $person = $this->constructRequestPersonAddress($request);
            $data['id'] = $this->requestAddressesDao->save($person);
        });
        info('data', $person);
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    public function update ($employeeId, $id) {
        $person = $this->getOne($employeeId, $id);
        $req = new \Illuminate\Http\Request();
        $req->replace([
            'id' => $person->personAddressId,
            'tenantId' => $person->tenantId,
            'personId' => $person->personId,
            'effBegin' => $person->effBegin,
            'effEnd' => $person->effEnd,
            'lovRsty' => $person->lovRsty,
            'lovRsow' => $person->lovRsow,
            'cityCode' => $person->cityCode,
            'address' => $person->address,
            'postalCode' => $person->postalCode,
            'mapLocation' => $person->mapLocation,
            'phone' => $person->phone,
            'fax' => $person->fax,
            'isDefault' => $person->isDefault
        ]);

        if ($person->crudType === 'U') {
            $this->personAddressController->update($req);
        } else if ($person->crudType === 'C') {
            $this->personAddressController->save($req);
        } else if ($person->crudType === 'D') {
            $this->personAddressController->delete($req);
        }

        DB::transaction(function () use (&$person) {
            $person = [
                'id' => $person->id,
                'tenant_id' => $person->tenantId,
                'company_id' => $person->companyId,
                'crud_type' => $person->crudType,
                'person_id' => $person->personId,
                'employee_id' => $person->employeeId,
                'person_address_id' => $person->personAddressId,
                'status' => 'A',
                'eff_begin' => $person->effBegin,
                'eff_end' => $person->effEnd,
                'address' => $person->address,
                'lov_rsty' => $person->lovRsty,
                'lov_rsow' => $person->lovRsow,
                'city_code' => $person->cityCode,
                'postal_code' => $person->postalCode,
                'map_location' => $person->mapLocation,
                'phone' => $person->phone,
                'fax' => $person->fax,
                'is_default' => $person->isDefault,
                'request_date' => $person->requestDate
            ];
            $this->requestAddressesDao->update(
                $person['person_id'],
                $person['id'],
                $person
            );
        });
    }

    public function delete($employeeId, $id) {
        $person = $this->getOne($employeeId, $id);

        DB::transaction(function () use (&$person) {
            $person = [
                'id' => $person->id,
                'tenant_id' => $person->tenantId,
                'company_id' => $person->companyId,
                'crud_type' => $person->crudType,
                'person_id' => $person->personId,
                'employee_id' => $person->employeeId,
                'person_address_id' => $person->personAddressId,
                'status' => 'R',
                'eff_begin' => $person->effBegin,
                'eff_end' => $person->effEnd,
                'address' => $person->address,
                'lov_rsty' => $person->lovRsty,
                'lov_rsow' => $person->lovRsow,
                'city_code' => $person->cityCode,
                'postal_code' => $person->postalCode,
                'map_location' => $person->mapLocation,
                'phone' => $person->phone,
                'fax' => $person->fax,
                'is_default' => $person->isDefault,
                'request_date' => $person->requestDate
            ];
            $this->requestAddressesDao->update(
                $person['person_id'],
                $person['id'],
                $person
            );
        });
    }

    private function constructRequestPersonAddress(Request $request) {
        $person = [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $this->requester->getCompanyId(),
            'crud_type' => $request->crudType,
            'person_id' => $request->personId,
            'employee_id' => $request->employeeId,
            'person_address_id' => $request->personAddressId,
            'status' => $request->status,
            'eff_begin' => $request->effBegin,
            'eff_end' => $request->effEnd,
            'address' => $request->address,
            'lov_rsty' => $request->lovRsty,
            'lov_rsow' => $request->lovRsow,
            'city_code' => $request->cityCode,
            'postal_code' => $request->postalCode,
            'map_location' => $request->mapLocation,
            'phone' => $request->phone,
            'fax' => $request->fax,
            'is_default' => $request->isDefault,
            'request_date' => $request->requestDate
        ];
        return $person;
    }
}