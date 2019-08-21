<?php

namespace App\Http\Controllers;

use App\Business\Dao\CustomObjectDao;
use App\Business\Dao\CustomObjectFieldDao;
use App\Business\Dao\LovTypeDao;
use App\Business\Dao\LovDao;
use App\Business\Dao\PersonDao;
use App\Business\Model\AppResponse;
use App\Business\Model\Requester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 * @property CustomObjectDao customObjectDao
 * @property CustomObjectFieldDao customObjectFieldDao
 */
class CustomObjectController extends Controller
{
    public function __construct(
        Requester $requester,
        CustomObjectDao $customObjectDao,
        CustomObjectFieldDao $customObjectFieldDao,
        LovTypeDao $lovTypeDao,
        LovDao $lovDao,
        PersonDao $personDao
    )
    {
        $this->requester = $requester;
        $this->customObjectDao = $customObjectDao;
        $this->customObjectFieldDao = $customObjectFieldDao;
        $this->lovTypeDao = $lovTypeDao;
        $this->lovDao = $lovDao;
        $this->personDao = $personDao;
    }

    public function getPersonIdForView(Request $request){
        $companyId = $this->requester->getCompanyId();
        $data = $this->personDao->getOneEmployeeByCompanyId($companyId);

        return $this->renderResponse(new AppResponse($data, trans('messages.allDataRetrieved')));
    }

    public function getAll(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required'
        ]);

        $customObjects = $this->customObjectDao->getAll();

        return $this->renderResponse(new AppResponse($customObjects, trans('messages.allDataRetrieved')));
    }

    public function getAllByLovCusobj(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'lovCusobj' => 'required'
        ]);

        $customObjects = $this->customObjectDao->getAllByLovCusobj($request->lovCusobj);

        return $this->renderResponse(new AppResponse($customObjects, trans('messages.allDataRetrieved')));
    }

    public function getOne(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'id' => 'required'
        ]);

        $customObject = $this->customObjectDao->getOne($request->id);
        $customObject->fields = $this->customObjectFieldDao->getAll($request->id);

        return $this->renderResponse(new AppResponse($customObject, trans('messages.dataRetrieved')));
    }

    public function save(Request $request)
    {
        $this->checkRequest($request);

        $data = [];
        DB::transaction(function () use (&$request, &$data) {
            $customObject = $this->constructCustomObject($request);
            $data['id'] = $this->customObjectDao->save($customObject);

            $customObjectFields = $this->constructCustomObjectFields($request, $data['id']);
            $this->customObjectFieldDao->saveAll($customObjectFields);

            $lovTypes = $this->constructLovTypes($request);
            foreach ($lovTypes as $lovType) {
                $this->lovTypeDao->upsert($lovType);
                $this->lovDao->deleteByType($lovType['code']);
            }

            $lovs = $this->constructLovs($request);
            if (count($lovs) > 0) {
                $this->lovDao->saveAll($lovs);
            }
        });

        return $this->renderResponse(new AppResponse($data, trans('messages.dataSaved')));
    }

    public function update(Request $request)
    {
        $this->checkRequest($request);
        $this->validate($request, ['id' => 'required']);

        DB::transaction(function () use (&$request) {
            $customObject = $this->constructCustomObject($request);
            $this->customObjectDao->update($request->id, $customObject);

            $this->customObjectFieldDao->deleteAll($request->id);
            $customObjectFields = $this->constructCustomObjectFields($request, $request->id);
            $this->customObjectFieldDao->saveAll($customObjectFields);

            $lovTypes = $this->constructLovTypes($request);
            foreach ($lovTypes as $lovType) {
                $this->lovTypeDao->upsert($lovType);
                $this->lovDao->deleteByType($lovType['code']);
            }

            $lovs = $this->constructLovs($request);
            if (count($lovs) > 0) {
                $this->lovDao->saveAll($lovs);
            }
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    public function delete(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'id' => 'required'
        ]);

        DB::transaction(function () use (&$request) {
            $this->customObjectFieldDao->deleteAll($request->id);
            $this->customObjectDao->delete($request->id);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataDeleted')));
    }

    private function checkRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'lovCusobj' => 'required|alpha_num|max:10|exists:lovs,key_data',
            'name' => 'required|string|max:50',
            'description' => 'present|string|max:255',
            'isDisabled' => 'required|boolean',
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:50',
            'fields.*.lovCdtype' => 'required|alpha_num|max:20|exists:lovs,key_data',
            'fields.*.lovTypeCode' => 'present|nullable|max:10',
            'fields.*.isDisabled' => 'required|boolean',
            'fields.*.isNewLov' => 'required|boolean'
        ]);
    }

    private function constructCustomObject(Request $request)
    {
        return [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $this->requester->getCompanyId(),
            'lov_cusobj' => $request->lovCusobj,
            'name' => $request->name,
            'description' => $request->description,
            'is_disabled' => $request->isDisabled
        ];
    }

    private function constructCustomObjectFields(Request $request, $customObjectId)
    {
        $customObjectFields = [];

        foreach ($request->fields as $field) {
            $lovTypeCode = $field['lovTypeCode'];
            if ($lovTypeCode) {
                if (! $this->endsWith($field['lovTypeCode'], '|C') && $field['isNewLov']) {
                    $lovTypeCode .= '|C';
                }
            }

            array_push($customObjectFields, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'co_id' => $customObjectId,
                'name' => $field['name'],
                'lov_cdtype' => $field['lovCdtype'],
                'lov_type_code' => $lovTypeCode,
                'is_disabled' => $field['isDisabled']
            ]);
        }

        return $customObjectFields;
    }

    private function constructLovTypes(Request $request)
    {
        $lovTypes = [];
        foreach ($request->fields as $field) {
            if (! $field['isNewLov']) {
                continue;
            }
            $lovTypeReq = new \Illuminate\Http\Request();
            $lovTypeReq->replace([
                'code' => $field['lovTypeCode'],
                'name' => $field['name']
            ]);
            $this->validate($lovTypeReq, [
                'code' => 'required|max:20',
                'name' => 'required|max:50'
            ]);

            array_push($lovTypes, [
                'code' => $this->endsWith($lovTypeReq->code, '|C') ? $lovTypeReq->code : $lovTypeReq->code.'|C',
                'name' => $lovTypeReq->name
            ]);
        }
        return $lovTypes;
    }

    private function constructLovs(Request $request)
    {
        $lovs = [];
        foreach ($request->fields as $field) {
            if (! $field['isNewLov']) {
                continue;
            }
            foreach ($field['lovItems'] as $lov) {
                $lovReq = new \Illuminate\Http\Request();
                $lovReq->replace([
                    'keyData' => $lov['keyData'],
                    'valData' => $lov['valData'],
                    'isActive' => $lov['isActive']
                ]);
                $this->validate($lovReq, [
                    "keyData" => 'required|max:10',
                    "valData" => 'required|max:50',
                    "isActive" => 'required|boolean'
                ]);
                array_push($lovs, [
                    'tenant_id' => $this->requester->getTenantId(),
                    'company_id' => $this->requester->getCompanyId(),
                    'lov_type_code'=> $this->endsWith($field['lovTypeCode'], '|C') ? $field['lovTypeCode'] : $field['lovTypeCode'].'|C',
                    'key_data' => $lov['keyData'],
                    'val_data' => $lov['valData'],
                    'is_disableable' => true,
                    'is_active' => $lov['isActive'],
                    'created_at' => Carbon::now(),
                    'created_by' => $this->requester->getUserId()
                ]);
            }
        }
        return $lovs;
    }

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return $length === 0 || (substr($haystack, -$length) === $needle);
    }
}
