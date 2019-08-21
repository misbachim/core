<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonFamilyDao;
use App\Business\Dao\WorkflowDao;
use App\Business\Dao\RequestFamiliesDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestFamiliesController extends Controller {
    public function __construct
    (
        Requester $requester,
        PersonFamilyDao $personFamilyDao,
        WorkflowDao $workflowDao,
        RequestFamiliesDao $requestFamiliesDao,
        PersonController $personController,
        PersonFamilyController $personFamilyController
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->personFamilyDao = $personFamilyDao;
        $this->workflowDao = $workflowDao;
        $this->requestFamiliesDao = $requestFamiliesDao;
        $this->personController = $personController;
        $this->personFamilyController = $personFamilyController;
    }

    /**
     * Get all request personFamilies for one person
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request, PersonController $personController)
    {
        $this->validate($request, ["personId" => "required"]);

        $personFamilies = $this->requestFamiliesDao->getAll($request->employeeId, $request->companyId);
        $personFamilies->person = $personController->getOneEmployee($personFamilies->employeeId);
        $resp = new AppResponse($personFamilies, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one request personFamily based on personFamily id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne($employeeId, $id)
    {

        $personFamily = $this->requestFamiliesDao->getOne(
            $id
        );
        $req = new \Illuminate\Http\Request();
        $req->replace([
            'id' => $employeeId
        ]);
        $personFamily->person = $this->personController->getOneEmployeeForWorklist($req);

        return $personFamily;
    }

    public function checkIfRequestIsPending(Request $request){
        $this->validate($request, [
            "employeeId" => "required",
            "status" => "required"
        ]);

        $personFamilies = $this->requestFamiliesDao->checkIfRequestIsPending($request->employeeId, $request->status);
        $resp = new AppResponse($personFamilies, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }


    /**
     * Save request personFamily to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $personFamily = array();

        DB::transaction(function () use (&$request, &$data) {
            $personFamily = $this->constructRequestPersonFamily($request);
            $data['id'] = $this->requestFamiliesDao->save($personFamily);
        });
        info('data', $personFamily);
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update request personFamily to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update($employeeId, $id)
    {
        $personFamily = $this->getOne($employeeId, $id);
        $req = new \Illuminate\Http\Request();
        $req->replace([
            'id' => $personFamily->personFamilyId,
            'tenantId' => $this->requester->getTenantId(),
            'personId' => $personFamily->person_id,
            'effBegin' => $personFamily->effBegin,
            'effEnd' => $personFamily->effEnd,
            'name' => $personFamily->name,
            'lovFamr' => $personFamily->lovFamr,
            'lovEdul' => $personFamily->lovEdul,
            'lovGndr' => $personFamily->lovGndr,
            'birthDate' => $personFamily->birthDate,
            'occupation' => $personFamily->occupation,
            'description' => $personFamily->description
        ]);
        if ($personFamily->crudType === 'U') {
            $this->personFamilyController->update($req);
        } else if ($personFamily->crudType === 'C') {
            $this->personFamilyController->save($req);
        } else if ($personFamily->crudType === 'D') {
            $this->personFamilyController->delete($req);
        }

        DB::transaction(function () use (&$personFamily) {
            $personFamily = [
                'id' => $personFamily->id,
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $personFamily->companyId,
                'crud_type' => $personFamily->crudType,
                'person_id' => $personFamily->person_id,
                'employee_id' => $personFamily->employee_id,
                'person_family_id' => $personFamily->personFamilyId,
                'status' => 'A',
                'name' => $personFamily->name,
                'lov_famr' => $personFamily->lovFamr,
                'lov_edul' => $personFamily->lovEdul,
                'lov_gndr' => $personFamily->lovGndr,
                'birth_date' => $personFamily->birthDate,
                'occupation' => $personFamily->occupation,
                'description' => $personFamily->description,
                'is_emergency' => $personFamily->isEmergency,
                'request_date' => $personFamily->requestDate
            ];
            $this->requestFamiliesDao->update(
                $personFamily['person_id'],
                $personFamily['id'],
                $personFamily
            );
        });
    }

    /**
     * Delete request person family by id.
     * @param Request $request
     * @return AppResponse
     */
    public function delete($employeeId, $id)
    {
        $personFamily = $this->getOne($employeeId, $id);

        DB::transaction(function () use (&$personFamily) {
            $personFamily = [
                'id' => $personFamily->id,
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $personFamily->companyId,
                'crud_type' => $personFamily->crudType,
                'person_id' => $personFamily->person_id,
                'employee_id' => $personFamily->employee_id,
                'person_family_id' => $personFamily->personFamilyId,
                'status' => 'R',
                'name' => $personFamily->name,
                'lov_famr' => $personFamily->lovFamr,
                'lov_edul' => $personFamily->lovEdul,
                'lov_gndr' => $personFamily->lovGndr,
                'birth_date' => $personFamily->birthDate,
                'occupation' => $personFamily->occupation,
                'description' => $personFamily->description,
                'is_emergency' => $personFamily->isEmergency,
                'request_date' => $personFamily->requestDate
            ];
            $this->requestFamiliesDao->update(
                $personFamily['person_id'],
                $personFamily['id'],
                $personFamily
            );
        });
    }

    /**
     * Construct request a personFamily object (array).
     * @param Request $request
     * @return array
     */
    private
    function constructRequestPersonFamily(Request $request)
    {
        $personFamily = [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $request->companyId,
            'crud_type' => $request->crudType,
            'person_id' => $request->personId,
            'employee_id' => $request->employeeId,
            'person_family_id' => $request->personFamilyId,
            'status' => $request->status,
            'name' => $request->name,
            'lov_famr' => $request->lovFamr,
            'lov_edul' => $request->lovEdul,
            'lov_gndr' => $request->lovGndr,
            'birth_date' => $request->birthDate,
            'eff_begin' => $request->effBegin,
            'eff_end' => $request->effEnd,
            'occupation' => $request->occupation,
            'description' => $request->description,
            'is_emergency' => $request->isEmergency,
            'request_date' => $request->requestDate
        ];
        return $personFamily;
    }
}