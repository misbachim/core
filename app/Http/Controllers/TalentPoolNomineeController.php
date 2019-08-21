<?php

namespace App\Http\Controllers;

use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB, Log;

use App\Business\Dao\TalentPoolNomineeDao;


/**
 * Class for handling asset process
 */
class TalentPoolNomineeController extends Controller
{
    public function __construct(
        Requester $requester,
        TalentPoolNomineeDao $talentPoolNomineeDao
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->talentPoolNomineeDao = $talentPoolNomineeDao;
    }


    /*
    |--------------------------------------------------
    | save data ke database
    |--------------------------------------------------
    |
    |
    */
    public function save(Request $request)
    {
        $this->checkRequest($request);
        $data = null;
        DB::transaction(function () use (&$request, &$data) {
            $obj = $this->constructData($request);
            $id = $this->talentPoolNomineeDao->saveGetId($obj);
            $data = $id;
        });
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }


    /*
    |-----------------------------
    | check request data dari ui
    |-----------------------------
    |
    |
    */
    public function checkRequest ($request) {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'name' => 'string',
            'readiness' => 'required',
            'status' => 'required',
            'year' => 'required',
            'talentPoolId' => 'required'
        ]);
    }


    /*
    |-----------------------------------------------
    | construct object data yang akan di save
    |-----------------------------------------------
    |
    |
    */
    public function constructData ($request) {
        return
        [
            'employee_id' => $request->employeeId,
            'note' => $request->note,
            'readiness' => $request->readiness,
            'status' => $request->status,
            'year' => $request->year,
            'talent_pool_id' => $request->talentPoolId,
        ];
    }


    /*
    |-----------------------------
    | delete data dari database
    |-----------------------------
    |
    | @param id<int>
    */
    public function delete(Request $request) {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $this->talentPoolNomineeDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }



    /*
    |-------------------------
    | update data nominee
    |-------------------------
    |
    |
    */
    public function update(Request $request) {
        $this->checkRequestUpdate($request);
         DB::transaction(function () use (&$request) {
            $obj = $this->constructDataUpdate($request);
            $this->talentPoolNomineeDao->update($obj, $request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }


    /*
    |-----------------------------
    | check request update
    |-----------------------------
    |
    |
    */
    public function checkRequestUpdate(Request $request) {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'id' => 'required',
            'readiness' => 'required',
            'year' => 'required'
        ]);
    }


    /*
    |-----------------------------
    | construct data untuk update
    |-----------------------------
    |
    |
    */
    public function constructDataUpdate(Request $request) {
        return
        [
            'note' => $request->note,
            'readiness' => $request->readiness,
            'year' => $request->year
        ];
    }
}
