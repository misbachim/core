<?php

namespace App\Http\Controllers;

use App\Business\Dao\CustomFieldDao;
use App\Business\Dao\LovDao;
use App\Business\Dao\LovTypeDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Http\Controllers\Controller;
use function GuzzleHttp\Psr7\str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling custom field process
 */
class CustomFieldController extends Controller
{
    public function __construct(Requester $requester, CustomFieldDao $customFieldDao, LovDao $lovDao, LovTypeDao $lovTypeDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->customFieldDao = $customFieldDao;
        $this->lovDao = $lovDao;
        $this->lovTypeDao = $lovTypeDao;
    }

    /**
     * Get all custom fields in one company
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required|integer"]);

        $customField = $this->customFieldDao->getAll();

        $resp = new AppResponse($customField, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getAllByModule(Request $request)
    {
        $this->validate($request, [
            "lovCusmod" => "required"
        ]);

        $customField = $this->customFieldDao->getAllForModule($request->lovCusmod);
        if ($customField) {
            for($i=0;$i<count($customField);$i++){
                if ($customField[$i]->lovCdtype == 'OPT') {
                    $lovType = $this->lovDao->getAll($customField[$i]->lovTypeCode);
                    if($lovType){
                        $customField[$i]->option = $lovType;
                    }
                }
            }
        }

        $resp = new AppResponse($customField, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one custom field based on cf id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $customField = $this->customFieldDao->getOne(
            $request->id
        );

        $data = array();
        if (count($customField) > 0) {
            $data = $customField;
            $data->lovs = $this->lovDao->getAll($customField->lovTypeCode);
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save custom field to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $lastFieldName = $this->customFieldDao->getLastFieldName($request->lovCusmod);
        if (count($lastFieldName) > 0) {
            $newFieldName = explode("c", $lastFieldName->fieldName);
            $temp = "c" . strval(intval($newFieldName[1]) + 1);
        } else {
            $temp = "c" . strval(1);
        }

        $reqData = [
            "fieldName" => $temp
        ];
        $request->merge($reqData);

        $this->checkCustomFieldRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            if ($request->lovCdtype == 'OPT' && $request->isNewLov) {
                $customField = $this->constructCustomFieldCustom($request);
                $data['id'] = $this->customFieldDao->save($customField);
                $this->validate($request, [
                    'lovTypeCode' => 'required|max:10|alpha_num',
                    'lovTypeName' => 'required|max:50'
                ]);
                $lovType = [
                    "code" => $request->lovTypeCode . '|C',
                    "name" => $request->lovTypeName
                ];
                $this->lovTypeDao->save($lovType);
                $this->saveLov($request, $request->lovTypeCode . '|C');
            } else {
                $customField = $this->constructCustomField($request);
                $data['id'] = $this->customFieldDao->save($customField);
            }
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update grade to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);
        $this->checkCustomFieldRequest($request);

        DB::transaction(function () use (&$request) {
            $customField = $this->constructCustomField($request);
            $this->customFieldDao->update(
                $request->id,
                $customField
            );
            if ($request->lovCdtype == 'OPT' && $request->isNewLov) {
                $this->saveLov($request, $request->lovTypeCode);
            }
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update custom field request.
     * @param request
     */
    private function checkCustomFieldRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'lovCusmod' => 'required|exists:lovs,key_data|max:20',
            'name' => 'required|max:50',
            'fieldName' => 'required|max:10',
            'lovCdtype' => 'required|exists:lovs,key_data|max:20',
            'isActive' => 'required|boolean',
            'lovTypeCode' => 'present|max:10'
        ]);
    }

    /**
     * Construct a custom field object (array).
     * @param request
     */
    private function constructCustomField(Request $request)
    {
        $customField = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "name" => $request->name,
            "lov_cusmod" => $request->lovCusmod,
            "field_name" => $request->fieldName,
            "lov_cdtype" => $request->lovCdtype,
            "lov_type_code" => $request->lovTypeCode,
            "is_active" => $request->isActive
        ];
        return $customField;
    }

    /**
     * Construct a custom field object with C(array).
     * @param request
     */
    private function constructCustomFieldCustom(Request $request)
    {
        $customField = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "name" => $request->name,
            "lov_cusmod" => $request->lovCusmod,
            "field_name" => $request->fieldName,
            "lov_cdtype" => $request->lovCdtype,
            "lov_type_code" => $request->lovTypeCode . '|C',
            "is_active" => $request->isActive
        ];
        return $customField;
    }

    private function saveLov(Request $request, $lovTypeCode)
    {
        if ($request->has('lovs')) {
            $this->lovDao->deleteByType($request->lovTypeCode);
        }
        for ($i = 0; $i < count($request->lovs); $i++) {
            $lovReq = new \Illuminate\Http\Request();
            $lov = (array)$request->lovs[$i];
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
            $data = [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'lov_type_code' => $lovTypeCode,
                'key_data' => $lov['keyData'],
                'val_data' => $lov['valData'],
                'is_disableable' => true,
                'is_active' => $lov['isActive']
            ];
            $this->lovDao->save($data);
        }
    }
}
