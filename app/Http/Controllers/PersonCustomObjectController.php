<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonCustomObjectDao;
use App\Business\Dao\PersonCustomObjectFieldDao;
use App\Business\Dao\PersonDao;
use App\Business\Model\AppResponse;
use App\Business\Model\Requester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @property Requester requester
 * @property PersonCustomObjectDao personCustomObjectDao
 * @property PersonCustomObjectFieldDao personCustomObjectFieldDao
 * @property PersonDao personDao
 */
class PersonCustomObjectController extends Controller
{
    public function __construct(
        Requester $requester,
        PersonCustomObjectDao $personCustomObjectDao,
        PersonCustomObjectFieldDao $personCustomObjectFieldDao,
        PersonDao $personDao
    )
    {
        $this->requester = $requester;
        $this->personCustomObjectDao = $personCustomObjectDao;
        $this->personCustomObjectFieldDao = $personCustomObjectFieldDao;
        $this->personDao = $personDao;
    }

    public function getAll(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'personId' => 'required'
        ]);

        $personCustomObjects = $this->personCustomObjectDao->getAll($request->personId);

        return $this->renderResponse(new AppResponse($personCustomObjects, trans('messages.allDataRetrieved')));
    }

    public function getAllItems(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'personId' => 'required',
            'coId' => 'required'
        ]);

        $personCustomObjectItems = $this->personCustomObjectDao->getAllItems($request->personId, $request->coId);

        return $this->renderResponse(new AppResponse($personCustomObjectItems, trans('messages.allDataRetrieved')));
    }

    public function getOne(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'personId' => 'required',
            'coId' => 'required',
            'id' => 'required'
        ]);

        $personCustomObject = $this->personCustomObjectDao->getOne($request->personId, $request->coId, $request->id);
        $personCustomObject->fields = $this->personCustomObjectFieldDao->getAll($request->id);

        return $this->renderResponse(new AppResponse($personCustomObject, trans('messages.dataRetrieved')));
    }

    public function getAllFields(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'id' => 'required'
        ]);

        $fields = $this->personCustomObjectFieldDao->getAll($request->id);

        return $this->renderResponse(new AppResponse($fields, trans('messages.allDataRetrieved')));
    }

    public function save(Request $request)
    {
        $this->checkRequest($request);

        $data = [];
        DB::transaction(function () use (&$request, &$data) {
            $personCustomObject = $this->constructPersonCustomObject($request);
            $data['id'] = $this->personCustomObjectDao->save($personCustomObject);

            $personCustomObjectFields = $this->constructPersonCustomObjectFields($request, $data['id']);
            $this->personCustomObjectFieldDao->saveAll($personCustomObjectFields);
        });

        return $this->renderResponse(new AppResponse($data, trans('messages.dataSaved')));
    }

    public function update(Request $request)
    {
        $this->checkRequest($request);
        $this->validate($request, [
            'personId' => 'required',
            'coId' => 'required',
            'id' => 'required'
        ]);

        DB::transaction(function () use (&$request) {
            $personCustomObject = $this->constructPersonCustomObject($request);
            $this->personCustomObjectDao->update($request->personId, $request->coId, $request->id, $personCustomObject);

            $this->personCustomObjectFieldDao->deleteAll($request->id);
            $personCustomObjectFields = $this->constructPersonCustomObjectFields($request, $request->id);
            $this->personCustomObjectFieldDao->saveAll($personCustomObjectFields);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function updateFields(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'personCoId' => 'required|integer|exists:person_co,id',
            'personCoFields' => 'required|array|min:1',
            'personCoFields.*.coFieldId' => 'required|integer|exists:co_fields,id',
            'personCoFields.*.value' => 'present|nullable|string|max:255'
        ]);

        DB::transaction(function () use (&$request) {
            foreach ($request->personCoFields as $personCoField) {
                $this->personCustomObjectFieldDao->upsert(
                    $request->personCoId,
                    $personCoField['coFieldId'],
                    $personCoField['value']
                );
            }
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'personId' => 'required',
            'coId' => 'required',
            'id' => 'required'
        ]);

        DB::transaction(function () use (&$request) {
            $this->personCustomObjectFieldDao->deleteAll($request->id);
            $this->personCustomObjectDao->delete($request->personId, $request->coId, $request->id);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataDeleted')));
    }

    private function checkRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'personId' => 'required|integer|exists:persons,id',
            'coId' => 'required|integer|exists:co,id',
            'fields' => 'required|array|min:1',
            'fields.*.coFieldId' => 'required|integer|exists:co_fields,id',
            'fields.*.value' => 'present|nullable|string|max:255'
        ]);
    }

    private function constructPersonCustomObject(Request $request)
    {
        $person = $this->personDao->getBasicInfo($request->personId, $this->requester->getCompanyId());
        return [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $this->requester->getCompanyId(),
            'person_id' => $request->personId,
            'co_id' => $request->coId,
            'eff_begin' => $person->effBegin,
            'eff_end' => $person->effEnd
        ];
    }

    private function constructPersonCustomObjectFields(Request $request, $personCustomObjectId)
    {
        $personCustomObjectFields = [];

        foreach ($request->fields as $field) {
            array_push($personCustomObjectFields, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'person_co_id' => $personCustomObjectId,
                'co_field_id' => $field['coFieldId'],
                'value' => $field['value']
            ]);
        }

        return $personCustomObjectFields;
    }
}
