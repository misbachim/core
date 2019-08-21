<?php

namespace App\Http\Controllers;

use App\Business\Dao\LovTypeDao;
use App\Business\Model\AppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling Lov Type process
 */
class LovTypeController extends Controller
{
    public function __construct(Requester $requester, LovTypeDao $lovTypeDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->lovTypeDao = $lovTypeDao;
        $this->lovTypeFields = array('code', 'name');
    }

    /**
     * Get all Lov Type
     * @param request
     */
    public function getAll(Request $request)
    {
        $data = $this->lovTypeDao->getAll();

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one lov type based on code
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, ["code" => "required"]);
        $lovType = $this->lovTypeDao->getOne($request->code);

        $data = array();
        if (count($lovType)>0) {
            $data['name']           = $lovType->name;
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save lovType to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkLovTypeRequest($request);

        //code must be unique
        if ($this->lovTypeDao->checkDuplicateLovTypeCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request) {
            $lovType = $this->constructLovType($request);
            $this->lovTypeDao->save($lovType);
        });

        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update lovType to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|max:10|alpha_num'
        ]);
        $this->checkLovTypeRequest($request);

        DB::transaction(function () use (&$request) {
            $lovType = $this->constructLovType($request);
            unset($lovType['code']);
            $this->lovTypeDao->update($request->code, $lovType);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete lov type from DB.
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, ["code" => "required|alpha_num"]);

        DB::transaction(function () use (&$request) {
            $this->lovTypeDao->delete($request->code);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update lov type request.
     * @param request
     */
    private function checkLovTypeRequest(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|max:10|alpha_num',
            'name' => 'required|max:50'
        ]);
    }


    /**
     * Construct an lov type object (array).
     * @param request
     */
    private function constructLovType(Request $request)
    {
        $lovType = [
            "code"       => $request->code,
            "name"       => $request->name
        ];
        return $lovType;
    }
}
