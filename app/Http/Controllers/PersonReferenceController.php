<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonReferenceDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling personReference process
 * @property Requester requester
 * @property PersonReferenceDao personReferenceDao
 */
class PersonReferenceController extends Controller
{
    public function __construct(Requester $requester, PersonReferenceDao $personReferenceDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personReferenceDao = $personReferenceDao;
    }

    /**
     * Get all personReferences for one person
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["personId" => "required|integer"]);

        $personReferences = $this->personReferenceDao->getAll($request->personId);

        $resp = new AppResponse($personReferences, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one personReference based on personReference id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "personId" => "required|integer",
            "id" => "required|integer"
        ]);

        $personReference = $this->personReferenceDao->getOne(
            $request->personId,
            $request->id
        );

        $resp = new AppResponse($personReference, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personReference to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonReferenceRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $personReference = $this->constructPersonReference($request);
            $data['id'] = $this->personReferenceDao->save($personReference);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personReference to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);
        $this->checkPersonReferenceRequest($request);

        DB::transaction(function () use (&$request) {
            $personReference = $this->constructPersonReference($request);
            $this->personReferenceDao->update(
                $request->personId,
                $request->id,
                $personReference
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete person reference by id.
     * @param Request $request
     * @return AppResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required|integer",
            "personId" => "required|integer"
        ]);

        DB::transaction(function () use (&$request) {
            $this->personReferenceDao->delete(
                $request->personId,
                $request->id
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update personReference request.
     * @param Request $request
     */
    private function checkPersonReferenceRequest(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'relationship' => 'required|max:50',
            'description' => 'present|max:255',
            'phone' => 'required|max:50'
        ]);
    }

    /**
     * Construct a personReference object (array).
     * @param Request $request
     * @return array
     */
    private function constructPersonReference(Request $request)
    {
        $personReference = [
            "tenant_id" => $this->requester->getTenantId(),
            "person_id" => $request->personId,
            "name" => $request->name,
            "relationship" => $request->relationship,
            "description" => $request->description,
            "phone" => $request->phone
        ];
        return $personReference;
    }
}
