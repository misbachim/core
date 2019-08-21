<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonAddressDao;
use App\Business\Dao\WorkflowDao;
use App\Business\Dao\RequestAddressesDao;
use App\Business\Dao\LovDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling personAddress process
 */
class PersonAddressController extends Controller
{
    public function __construct(Requester $requester
                              , PersonAddressDao $personAddressDao
                              , WorkflowDao $workflowDao
                              , RequestAddressesDao $requestAddressesDao
                              , LovDao $lovDao
    )
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personAddressDao = $personAddressDao;
        $this->workflowDao = $workflowDao;
        $this->requestAddressesDao = $requestAddressesDao;
        $this->lovDao = $lovDao;
    }

    /**
     * Get all personAddresses for one person
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["personId" => "required"]);

        $personAddresses = $this->personAddressDao->getAll($request->personId);

        if(count($personAddresses) > 0) {
            for($i = 0 ; $i < count($personAddresses) ; $i++) {
                $getRsty = property_exists($personAddresses[$i], 'lovRsty')?
                    $this->lovDao->getOne('RSTY',$personAddresses[$i]->lovRsty)
                    : '';
                $getRsow = property_exists($personAddresses[$i], 'lovRsow')?
                    $this->lovDao->getOne('RSOW',$personAddresses[$i]->lovRsow)
                    : '';
                $personAddresses[$i]->residenceType =  count($getRsty) > 0 ? 
                    $getRsty->valData 
                    : '';
                $personAddresses[$i]->residenceOwnership = count($getRsow) > 0 ? 
                    $getRsow->valData 
                    : '';
            }
        }

        $resp = new AppResponse($personAddresses, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one personAddress based on personAddress id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "personId" => "required",
            "id" => "required"
        ]);

        $personAddress = $this->personAddressDao->getOne(
            $request->personId,
            $request->id
        );

        $resp = new AppResponse($personAddress, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personAddress to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonAddressRequest($request);

        DB::transaction(function () use (&$request, &$data) {

            if($request->isDefault) {
                $getAll = $this->personAddressDao->getAll($request->personId);
                $obj = ['is_default' => false];
                if (count($getAll) > 0) {
                    for($i = 0 ; $i < count($getAll) ; $i++) {
                        $this->personAddressDao->update($request->personId, $getAll[$i]->id, $obj);
                    }
                }
            }

            $personAddress = $this->constructPersonAddress($request);
            $data['id'] = $this->personAddressDao->save($personAddress);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personAddress to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkPersonAddressRequest($request);

        DB::transaction(function () use (&$request) {

            if($request->isDefault) {
                $getAll = $this->personAddressDao->getAll($request->personId);
                $obj = ['is_default' => false];
                if (count($getAll) > 0) {
                    for($i = 0 ; $i < count($getAll) ; $i++) {
                        $this->personAddressDao->update($request->personId, $getAll[$i]->id, $obj);
                    }
                }
            }

            $personAddress = $this->constructPersonAddress($request);
            $this->personAddressDao->update(
                $request->personId,
                $request->id,
                $personAddress
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }


    /**
     * Save personAddress from ESS change request
     * @param Request $request
     * @return AppResponse
     */
    public function saveEss(Request $request)
    {
        $this->checkPersonAddressRequest($request);
        $this->validate($request, [
            'companyId' => 'required|exists:companies,id',
            'employeeId' => 'required|exists:assignments,employee_id'
        ]);

        $workflow = $this->workflowDao->getOne("PROF", $request->companyId);
        $data = array();

        $req = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "employee_id" => $request->employeeId,
            "crud_type" => 'C',
            "lov_rsty" => $request->lovRsty,
            "lov_rsow" => $request->lovRsow,
            "city_code" => $request->cityCode,
            "address" => $request->address,
            "postal_code" => $request->postalCode,
            "phone" => $request->phone,
            "fax" => $request->fax,
        ];

        if(!$workflow->isActive) {

			$req['status'] = 'A';

            DB::transaction(function () use (&$request, &$req, &$data) {
                $personAddress = $this->constructPersonAddress($request);
                $data['personAddressId'] = $this->personAddressDao->save($personAddress);
                
                $req['person_address_id'] = $data['personAddressId'];
                
                $data['id'] = $this->requestAddressesDao->save($req);                          
            });
        }
        else {

			$req['status'] = 'P';

            DB::transaction(function () use (&$request, &$req, &$data) {
                $data['id'] = $this->requestAddressesDao->save($req);          
            });
        }
        
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personAddress from ESS change request
     * @param Request $request
     * @return AppResponse
     */
    public function updateEss(Request $request)
    {
        $this->checkPersonAddressRequest($request);
        $this->validate($request, [
            'companyId' => 'required|exists:companies,id',
            'employeeId' => 'required|exists:assignments,employee_id'
        ]);

        $workflow = $this->workflowDao->getOne("PROF", $request->companyId);
        $data = array();

        $req = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "employee_id" => $request->employeeId,
            "crud_type" => 'U',
            "person_address_id" => $request->id,
            "lov_rsty" => $request->lovRsty,
            "lov_rsow" => $request->lovRsow,
            "city_code" => $request->cityCode,
            "address" => $request->address,
            "postal_code" => $request->postalCode,
            "phone" => $request->phone,
            "fax" => $request->fax,
        ];

        if(!$workflow->isActive) {
            $this->validate($request, ['id' => 'required']);

			$req['status'] = 'A';

            DB::transaction(function () use (&$request, &$req, &$data) {
                $personAddress = $this->constructPersonAddress($request);
                $this->personAddressDao->update(
                    $request->personId,
                    $request->id,
                    $personAddress
                );
                
                $data['personAddressId'] = $request->id;
                $data['id'] = $this->requestAddressesDao->save($req);                                          
            });
        }
        else {
			$req['status'] = 'P';

            DB::transaction(function () use (&$request, &$req, &$data) {
                $data['id'] = $this->requestAddressesDao->save($req);          
            });
        }
			    
        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete person address by id.
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
            $this->personAddressDao->delete(
                $request->personId,
                $request->id
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update personAddress request.
     * @param Request $request
     */
    private function checkPersonAddressRequest(Request $request)
    {
        $this->validate($request, [
            'personId' => 'required|integer|exists:persons,id',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'lovRsty' => 'required|max:10|exists:lovs,key_data',
            'lovRsow' => 'required|max:10|exists:lovs,key_data',
            'cityCode' => 'required|string|exists:cities,code',
            'address' => 'required|max:255',
            'postalCode' => 'present|max:10',
            'mapLocation' => 'present|max:50',
            'phone' => 'present|max:50',
            'fax' => 'present|max:50',
            'isDefault' => 'required'
        ]);
    }

    /**
     * Construct a personAddress object (array).
     * @param Request $request
     * @return array
     */
    private function constructPersonAddress(Request $request)
    {
        $personAddress = [
            'tenant_id' => $this->requester->getTenantId(),
            'person_id' => $request->personId,
            'eff_begin' => $request->effBegin,
            'eff_end' => $request->effEnd,
            'lov_rsty' => $request->lovRsty,
            'lov_rsow' => $request->lovRsow,
            'city_code' => $request->cityCode,
            'address' => $request->address,
            'postal_code' => $request->postalCode,
            'map_location' => $request->mapLocation,
            'phone' => $request->phone,
            'fax' => $request->fax,
            'is_default' => $request->isDefault
        ];
        return $personAddress;
    }
}
