<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonWorkExpDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling personWorkExp process
 */
class PersonWorkExpController extends Controller
{
    public function __construct(Requester $requester, PersonWorkExpDao $personWorkExpDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personWorkExpDao = $personWorkExpDao;
        $this->personWorkExpFields = array('id', 'dateBegin', 'dateEnd',
            'company', 'jobPos', 'jobDesc', 'location', 'benefit',
            'lastSalary', 'reason');
    }

    /**
     * Get all personWorkExps for one person
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["personId" => "required|integer"]);

        $personWorkExps = $this->personWorkExpDao->getAll(
            $request->personId
        );

        $resp = new AppResponse($personWorkExps, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one personWorkExp based on personWorkExp id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "personId" => "required|integer",
            "id" => "required|integer"
        ]);

        $personWorkExp = $this->personWorkExpDao->getOne(
            $request->personId,
            $request->id
        );

        $data = array();
        if (count($personWorkExp) > 0) {
            foreach ($this->personWorkExpFields as $field) {
                $data[$field] = $personWorkExp->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personWorkExp to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonWorkExpRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $personWorkExp = $this->constructPersonWorkExp($request);
            $data['id'] = $this->personWorkExpDao->save($personWorkExp);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personWorkExp to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);
        $this->checkPersonWorkExpRequest($request);

        DB::transaction(function () use (&$request) {
            $personWorkExp = $this->constructPersonWorkExp($request);
            $this->personWorkExpDao->update(
                $request->personId,
                $request->id,
                $personWorkExp
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete person work exp by id.
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required|integer",
            "personId" => "required|integer"
        ]);

        DB::transaction(function () use (&$request) {
            $this->personWorkExpDao->delete(
                $request->personId,
                $request->id
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update personWorkExp request.
     * @param request
     */
    private function checkPersonWorkExpRequest(Request $request)
    {
        $this->validate($request, [
            'dateBegin' => 'required|date',
            'dateEnd' => 'required|date|after_or_equal:dateBegin',
            'company' => 'required|max:50',
            'jobPos' => 'required|max:50',
            'jobDesc' => 'required|max:255',
            'location' => 'required|max:255',
            'benefit' => 'max:255',
            'lastSalary' => 'required|integer',
            'reason' => 'max:255'
        ]);
    }

    /**
     * Construct a personWorkExp object (array).
     * @param request
     */
    private function constructPersonWorkExp(Request $request)
    {
        $personWorkExp = [
            "tenant_id" => $this->requester->getTenantId(),
            "person_id" => $request->personId,
            "date_begin" => $request->dateBegin,
            "date_end" => $request->dateEnd,
            "company" => $request->company,
            "job_pos" => $request->jobPos,
            "job_desc" => $request->jobDesc,
            "location" => $request->location,
            "benefit" => $request->benefit,
            "last_salary" => $request->lastSalary,
            "reason" => $request->reason
        ];
        return $personWorkExp;
    }
}
