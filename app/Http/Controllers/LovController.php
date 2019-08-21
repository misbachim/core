<?php

namespace App\Http\Controllers;

use App\Business\Dao\LovDao;
use App\Business\Model\AppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling Lov process
 */
class LovController extends Controller
{
    public function __construct(Requester $requester, LovDao $lovDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->lovDao = $lovDao;
        $this->lovFields = array('companyId', 'keyData', 'valData', 'lovTypeCode','isDisableable');
    }

    /**
     * Get all Lovs in one company
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "lovTypeCode" => "required|max:10"
        ]);

        $data = $this->lovDao->getAll(
            $request->lovTypeCode
        );

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one Lov in one company based on key data
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "lovTypeCode" => "required|max:10",
            "keyData" => "required|max:10"
        ]);

        $lov = $this->lovDao->getOne(
            $request->lovTypeCode,
            $request->keyData
        );

        $data = array();
        if (count($lov)>0) {
            $data['keyData']        = $lov->keyData;
            $data['valData']        = $lov->valData;
            $data['lovTypeCode']    = $lov->lovTypeCode;
            $data['lovTypeName']    = $lov->lovTypeName;
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Lov to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->validate($request, [
            'lovTypeCode' => 'required|max:10|alpha_num',
        ]);
        $this->checkLovRequest($request);

        //code must be unique
        if ($this->lovDao->checkDuplicateLovKeyData($request->keyData,$request->lovTypeCode) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request) {
            $lov = $this->constructLov($request);
            $this->lovDao->save($lov);
        });

        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Lov to DB
     * @param  Request
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'lovTypeCode' => 'required|max:10'
        ]);
        $this->checkLovRequest($request);

        DB::transaction(function () use (&$request) {
            $lov = $this->constructLov($request);
            unset($lov['isDeleteable']);
            $this->lovDao->update($request->keyData,$request->lovTypeCode, $lov);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete data from DB
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'keyData' => 'required|max:10',
            'lovTypeCode' => 'required|max:10'
     ]);

        if ($this->lovDao->checkIsDeleteable($request->keyData,$request->lovTypeCode) > 0) {
            throw new AppException(trans('messages.dataReserved'));
        }

        DB::transaction(function () use (&$request) {
            $this->lovDao->delete($request->keyData,$request->lovTypeCode);

        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update lov request.
     * @param request
     */
    private function checkLovRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'keyData' => 'required|max:10',
            'valData' => 'required|max:50'
        ]);
    }

    /**
     * Construct an lov object (array).
     * @param request
     */
    private function constructLov(Request $request)
    {
        $lov = [
            "tenant_id"       => $this->requester->getTenantId(),
            "company_id"      => $this->requester->getCompanyId(),
            "key_data"        => $request->keyData,
            "val_data"        => $request->valData,
            "lov_type_code"   => $request->lovTypeCode,
            "is_active"       => true,
            "is_disableable"   => true
        ];
        return $lov;
    }
}
