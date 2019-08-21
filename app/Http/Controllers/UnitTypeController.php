<?php

namespace App\Http\Controllers;

use App\Business\Dao\UnitTypeDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling unitType process
 */
class UnitTypeController extends Controller
{
    public function __construct(Requester $requester, UnitTypeDao $unitTypeDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->unitTypeDao = $unitTypeDao;
        $this->unitTypeFields = array('code', 'name', 'unitLevel');
    }

    /**
     * Get all unitTypes in one company
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required|integer"]);

        $unitTypes = $this->unitTypeDao->getAll();

        $resp = new AppResponse($unitTypes, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all unitType in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $lov = $this->unitTypeDao->getLov();

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one unitType based on unitType id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "code" => "required"
        ]);

        $unitType = $this->unitTypeDao->getOne(
            $request->code
        );

        $data = array();
        if (count($unitType) > 0) {
            foreach ($this->unitTypeFields as $field) {
                $data[$field] = $unitType->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save unitType to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkUnitTypeRequest($request);

        //code must be unique
        if ($this->unitTypeDao->checkDuplicateUnitTypeCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request) {
            $unitType = $this->constructUnitType($request);
            $this->unitTypeDao->save($unitType);
        });

        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update unitType to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->checkUnitTypeRequest($request);

        DB::transaction(function () use (&$request) {
            $unitType = $this->constructUnitType($request);
            unset($unitType['code']);
            $this->unitTypeDao->update(
                $request->code,
                $unitType
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete Unit Type Data from DB
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, ['code' => 'required|alpha_num']);

//        if ($this->unitTypeDao->getTotalUsage($request->code) > 0) {
//            throw new AppException(trans('messages.dataInUse'));
//        }

        DB::transaction(function () use (&$request) {
            $this->unitTypeDao->delete($request->code);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update unitType request.
     * @param request
     */
    private function checkUnitTypeRequest(Request $request)
    {
        $this->validate($request, [
            'unitLevel' => 'required|integer',
            'name' => 'required|max:50',
            'code' => 'required|max:20|alpha_num'
        ]);
    }

    /**
     * Construct a unitType object (array).
     * @param request
     */
    private function constructUnitType(Request $request)
    {
        $unitType = [
            "tenant_id"  => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "name"       => $request->name,
            "code"       => $request->code,
            "unit_level"  => $request->unitLevel

        ];
        return $unitType;
    }
}
