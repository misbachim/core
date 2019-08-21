<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonFamilyDao;
use App\Business\Dao\WorkflowDao;
use App\Business\Dao\RequestFamiliesDao;
use App\Business\Dao\Payroll\FamilyBeneficiariesDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling personFamily process
 */
class PersonFamilyController extends Controller
{
    public function __construct(Requester $requester
                              , PersonFamilyDao $personFamilyDao
                              , WorkflowDao $workflowDao
                              , RequestFamiliesDao $requestFamiliesDao
                              , FamilyBeneficiariesDao $familyBeneficiariesDao
    )
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personFamilyDao = $personFamilyDao;
        $this->workflowDao = $workflowDao;
        $this->requestFamiliesDao = $requestFamiliesDao;
        $this->familyBeneficiariesDao = $familyBeneficiariesDao;
    }

    /**
     * Get all personFamilies for one person
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["personId" => "required"]);

        $personFamilies = $this->personFamilyDao->getAll($request->personId);

        $resp = new AppResponse($personFamilies, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one personFamily based on personFamily id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "personId" => "required",
            "id" => "required"
        ]);

        $personFamily = $this->personFamilyDao->getOne(
            $request->personId,
            $request->id
        );

        $resp = new AppResponse($personFamily, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, ["personId" => "required"]);

        $lov = $this->personFamilyDao->getLov(
            $request->personId
        );

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getLovCustomBenefit(Request $request)
    {
        $this->validate($request, [
            "personId" => "required",
            "benefitGroupsBenefitsId" => "required"
        ]);

        $finalData    = array();

        $getFamilyBeneficiaries = $this->familyBeneficiariesDao->getAllFamilyRelationship($request->benefitGroupsBenefitsId);
        $getData = $this->personFamilyDao->getLovCustomBenefit($request->personId);
        foreach ($getData as $data) {
            $data->age = explode(" ", $data->age)[0];
        }

        info('$getFamilyBeneficiaries', [$getFamilyBeneficiaries]);
        info('$getData', [$getData]);

        if($getFamilyBeneficiaries) {
            foreach ($getFamilyBeneficiaries as $rule) {
                foreach ($getData as $idx => $datum) {
                    if ($rule->lovFamr === $datum->lovfamr && $datum->age <= $rule->ageLimit) {
                        array_push($finalData, $datum);
                    }
                }
            }
        }

        info('$finalData', [$finalData]);

        $resp = new AppResponse($finalData, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function search(Request $request)
    {
        $this->validate($request, [
            'searchQuery' => 'present',
            'personId' => 'required|numeric'
        ]);

        $families = $this->personFamilyDao->search($request->searchQuery, $request->personId);

        return $this->renderResponse(new AppResponse($families, trans('messages.allDataRetrieved')));
    }

    /**
     * Save personFamily to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonFamilyRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $personFamily = $this->constructPersonFamily($request);
            $data['id'] = $this->personFamilyDao->save($personFamily);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personFamily to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkPersonFamilyRequest($request);

        DB::transaction(function () use (&$request) {
            $personFamily = $this->constructPersonFamily($request);
            $this->personFamilyDao->update(
                $request->personId,
                $request->id,
                $personFamily
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personFamily from ESS change request
     * @param Request $request
     * @return AppResponse
     */
    public function saveEss(Request $request)
    {
//        $workflow = $this->workflowDao->getOne("PROF");
        $data = array();

        $req = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "employee_id" => $request->employeeId,
            "crud_type" => 'C',
            "lov_famr" => $request->lovFamr,
            "name" => $request->name,
            "lov_gndr" => $request->lovGndr,
            "birth_date" => $request->birthDate,
            "lov_edul" => $request->lovEdul,
            "occupation" => $request->occupation,
            "address" => $request->address,
            "phone" => $request->phone,
//            "is_emergency" => $request->is_emergency,
            "description" => $request->description,
        ];

//        if(!$workflow->isActive) {

			$req['status'] = 'A';

            $this->checkPersonFamilyRequest($request);

            DB::transaction(function () use (&$request, &$req, &$data) {
                $personFamily = $this->constructPersonFamily($request);
                $data['personFamilyId'] = $this->personFamilyDao->save($personFamily);
                
                $req['person_family_id'] = $data['personFamilyId'];
                
//                $data['id'] = $this->requestFamiliesDao->save($req);
            });

//        }
//        else {
//
//			$req['status'] = 'P';
//
//            DB::transaction(function () use (&$request, &$req, &$data) {
//                $data['id'] = $this->requestFamiliesDao->save($req);
//            });
//        }
        
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personFamily from ESS change request
     * @param Request $request
     * @return AppResponse
     */
    public function updateEss(Request $request)
    {
//        $workflow = $this->workflowDao->getOne("PROF");
        $data = array();

        $req = [
            "status" => "",        
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "employee_id" => $request->employeeId,
            "crud_type" => 'U',
            "person_family_id" => $request->id,
            "lov_famr" => $request->lovFamr,
            "name" => $request->name,
            "lov_gndr" => $request->lovGndr,
            "birth_date" => $request->birthDate,
            "lov_edul" => $request->lovEdul,
            "occupation" => $request->occupation,
            "address" => $request->address,
            "phone" => $request->phone,
            "is_emergency" => $request->isEmergency,
            "description" => $request->description,
        ];
		
//        if(!$workflow->isActive) {

			$req['status'] = 'A';
   
            $this->validate($request, ['id' => 'required']);
            $this->checkPersonFamilyRequest($request);

            DB::transaction(function () use (&$request, &$req, &$data) {
                $personFamily = $this->constructPersonFamily($request);
                $this->personFamilyDao->update(
                    $request->personId,
                    $request->id,
                    $personFamily
                );
                
                $data['personFamilyId'] = $request->id;
//                $data['id'] = $this->requestFamiliesDao->save($req);
            });

//        }
//        else {
//
//			$req['status'] = 'P';
//
//            DB::transaction(function () use (&$request, &$req, &$data) {
//                $data['id'] = $this->requestFamiliesDao->save($req);
//            });
//        }
			
        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personFamily to DB
     * @param Request $request
     * @return AppResponse
     */
    public function updateEmergencyContact(Request $request)
    {
        $this->validate($request, ['isEmergency' => 'required']);


        DB::transaction(function () use (&$request) {
            if ($request->isEmergency === false) {
                $this->personFamilyDao->setEmergencyContactFalse(
                    $request->personId
                );
            } else {
                for ($i = 0; $i < count($request->emergencyContact); $i++) {
                    $this->personFamilyDao->setEmergencyContactTrue(
                        $request->personId,
                        $request->emergencyContact[$i]['id']
                    );
                }
            }

        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete person family by id.
     * @param Request $request
     * @return AppResponse
     */
    public
    function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "personId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $this->personFamilyDao->delete(
                $request->personId,
                $request->id
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update personFamily request.
     * @param request
     */
    private
    function checkPersonFamilyRequest(Request $request)
    {
        $this->validate($request, [
            'personId' => 'required|integer|exists:persons,id',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'name' => 'required|max:50',
            'lovFamr' => 'required|max:10|exists:lovs,key_data',
            'lovEdul' => 'required|max:10|exists:lovs,key_data',
            'lovGndr' => 'required|max:10|exists:lovs,key_data',
            'birthDate' => 'required|date',
            'occupation' => 'present|max:50',
            'description' => 'present|max:255'
//            'isEmergency' => 'required|boolean'
        ]);
    }

    /**
     * Construct a personFamily object (array).
     * @param Request $request
     * @return array
     */
    private
    function constructPersonFamily(Request $request)
    {
        $personFamily = [
            'tenant_id' => $this->requester->getTenantId(),
            'person_id' => $request->personId,
            'eff_begin' => $request->effBegin,
            'eff_end' => $request->effEnd,
            'name' => $request->name,
            'lov_famr' => $request->lovFamr,
            'lov_edul' => $request->lovEdul,
            'lov_gndr' => $request->lovGndr,
            'birth_date' => $request->birthDate,
            'occupation' => $request->occupation,
            'description' => $request->description
        ];
        return $personFamily;
    }
}
