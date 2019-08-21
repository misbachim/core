<?php

namespace App\Http\Controllers;

use App\Business\Dao\AutonumberDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling Autonumber process
 */
class AutonumberController extends Controller
{
    public function __construct(Requester $requester, AutonumberDao $autonumberDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->autonumberDao = $autonumberDao;
        $this->autonumberFields = array('name');
    }

    /**
     * Get all autonumbers
     * @param request
     */


    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required|integer"]);

        $units = $this->autonumberDao->getAll();

        $resp = new AppResponse($units, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all countries (lib+countries) in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $lov = $this->autonumberDao->getLov();

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one company based on Autonumber id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $autonumber = $this->autonumberDao->getOne(
            $request->id
        );

        $data = array();
        if (count($autonumber)>0) {
            $data['id'] = $autonumber->id;
            foreach ($this->autonumberFields as $field) {
                $data[$field] = $autonumber->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Autonumber to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkAutonumberRequest($request);
        $data = array();

        //code must be unique
        if ($this->autonumberDao->checkDuplicateAutonumberName($request->name) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $autonumber = $this->constructAutonumber($request);
            $data['id'] = $this->autonumberDao->save($autonumber);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Autonumber to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer'
        ]);
        $this->checkAutonumberRequest($request);

        //code must be unique
        if ($this->autonumberDao->checkDuplicateEditAutonumberName($request->name,$request->id) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        //code check starting sequence < last sequence
        if ($this->autonumberDao->checkMaxStartingNumber($request->startingSequence,$request->id) > 0) {
            throw new AppException(trans('messages.invalidStartingNumber'));
        }

        DB::transaction(function () use (&$request) {
            $autonumber = $this->constructAutonumber($request);
            $this->autonumberDao->update($request->id, $autonumber);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Autonumber Last Sequence to DB
     * @param request
     */
    public function updateLastSequence(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|integer',
            'lastSequence' => 'required|integer'
        ]);

        DB::transaction(function () use (&$request) {
            $autonumber = [
                "tenant_id"          => $this->requester->getTenantId(),
                "company_id"         => $this->requester->getCompanyId(),
                "last_sequence"      => $request->lastSequence
            ];
            $this->autonumberDao->update($request->id, $autonumber);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete Autonumber Data from DB
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);

//        if ($this->autonumberDao->getTotalUsage($request->id) > 0) {
//            throw new AppException(trans('messages.dataInUse'));
//        }

        DB::transaction(function () use (&$request) {
            $this->autonumberDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update autonumber request.
     * @param request
     */
    private function checkAutonumberRequest(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'startingSequence' => 'required|integer'
        ]);
    }

    /**
     * Construct an autonumber object (array).
     * @param request
     */
    private function constructAutonumber(Request $request)
    {
        $autonumber = [
            "tenant_id"          => $this->requester->getTenantId(),
            "company_id"         => $this->requester->getCompanyId(),
            "name"               => $request->name,
            "starting_sequence"  => $request->startingSequence
        ];
        return $autonumber;
    }
}
