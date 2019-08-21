<?php

namespace App\Http\Controllers;

use App\Business\Dao\CustomFieldDao;
use App\Business\Dao\MigrationToolDao;
use App\Business\Dao\PositionSlotDao;
use App\Business\Model\AppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;

/**
 * Class for handling Lov Type process
 */
class MigrationToolController extends Controller
{
    private $requester;
    private $migrationToolDao;
    private $customFieldDao;
    private $externalCDNController;

    public function __construct(
        Requester $requester,
        MigrationToolDao $migrationToolDao,
        CustomFieldDao $customFieldDao,
        ExternalCDNController $externalCDNController,
        PositionSlotDao $positionSlotDao
    )
    {
        parent::__construct();

        $this->requester = $requester;
        $this->migrationToolDao = $migrationToolDao;
        $this->customFieldDao = $customFieldDao;
        $this->externalCDNController = $externalCDNController;
        $this->positionSlotDao = $positionSlotDao;
    }

    /**
     * Get all Migration tool module
     * @param Request $request
     * @return AppResponse
     */
    public function getAllMtModule(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
        ]);

        $mt = $this->migrationToolDao->getAllMtModule();

        $count = count($mt);
        $data = array();

        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $data[$i] = $mt[$i];
                $data[$i]->record = count($this->migrationToolDao->getTemp($mt[$i]->code));
            }
        }

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function generateTemplate(Request $request)
    {
        $this->validate($request, [
            'mtModuleCode' => 'required|exists:mt_modules,code'
        ]);

        $module = $this->migrationToolDao->getOneModule($request->mtModuleCode);
        if (!$module) {
            throw new AppException(trans('messages.migrationModuleDoesNotExist'));
        }
        $moduleAttributes = $this->migrationToolDao->getModuleAttributesWithoutNullFieldName($request->mtModuleCode);
        if (count($moduleAttributes->toArray()) === 0) {
            throw new AppException(trans('messages.migrationModuleAttributesDoNotExist'));
        }
        $customFields = $module->custom ? $this->customFieldDao->getAllForModule($module->custom) : [];

        $csvHeader = [];
        foreach ($moduleAttributes as $moduleAttribute) {
            if ($moduleAttribute->isMandatory === true) {
                $mandatory = '*';
            } else {
                $mandatory = '';
            }
            if ($moduleAttribute->isLookup === true && $moduleAttribute->dataType != 'DATE') {
                $lookup = 'O';
            } else if ($moduleAttribute->isLookup === false && $moduleAttribute->dataType === 'DATE') {
                $lookup = 'DD-MM-YYYY';
            } else {
                $lookup = 'F';
            }
            $moduleName = $moduleAttribute->name . ' ' . $mandatory . ' (' . $lookup . ')';
            array_push($csvHeader, $moduleName);
        }
        foreach ($customFields as $customField) {
            array_push($csvHeader, $customField->name);
        }

        $csvContent = join($csvHeader, ',') . "\n";
        return $this->renderResponse(new AppResponse($csvContent, trans('messages.dataRetrieved')));
    }

    /**
     * Get mt module's attributes lov.
     * @param Request $request
     * @return AppResponse
     */
    public function lovModuleAttributes(Request $request)
    {
        $this->validate($request, ['mtModuleCode' => 'required']);

        $module = $this->migrationToolDao->getOneModule($request->mtModuleCode);
        if (!$module) {
            throw new AppException(trans('messages.migrationModuleDoesNotExist'));
        }
        $attributes = $this->migrationToolDao->lovModuleAttributes($request->mtModuleCode)->toArray();
        $customFields = $module->custom ? $this->customFieldDao->getAllForModule($module->custom) : [];
        foreach ($customFields as $customField) {
            array_push($attributes, [
                'name' => $customField->name,
                'tempFieldName' => $customField->fieldName,
                'regex' => ''
            ]);
        }

        return $this->renderResponse(new AppResponse($attributes, trans('messages.allDataRetrieved')));
    }

    /**
     * Get all mt module's attributes.
     * @param Request $request
     * @return AppResponse
     */
    public function getModuleAttributes(Request $request)
    {
        $this->validate($request, ['mtModuleCode' => 'required']);

        $attributes = $this->migrationToolDao->getModuleAttributes($request->mtModuleCode);

        return $this->renderResponse(new AppResponse($attributes, trans('messages.allDataRetrieved')));
    }

    public function importMigrationData(Request $request)
    {
        info('request', array($request));
        $this->validate($request, [
            'mtModuleCode' => 'required',
            'fileId' => 'required'
        ]);

        $module = $this->migrationToolDao->getOneModule($request->mtModuleCode);
        if (!$module) {
            throw new AppException(trans('messages.migrationModuleDoesNotExist'));
        }
        $moduleAttributes = $this->migrationToolDao->getModuleAttributes($request->mtModuleCode);
        if (count($moduleAttributes->toArray()) === 0) {
            throw new AppException(trans('messages.migrationModuleAttributesDoNotExist'));
        }
        $customFields = $this->customFieldDao->getAllForModule($module->custom);
        $fileContent = $this->externalCDNController->doc($request->mtModuleCode, $request->fileId);
        $reader = Reader::createFromString($fileContent);
        $reader->setHeaderOffset(0);

        $tempRecords = [];
        $now = Carbon::now();
        foreach ($reader->getRecords() as $record) {

            $tempRecord = [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'mt_module_code' => $request->mtModuleCode,
                'created_by' => $this->requester->getUserId(),
                'created_at' => $now
            ];
            foreach ($moduleAttributes as $moduleAttribute) {
                if (array_key_exists($moduleAttribute->tempFieldName, $record)) {
                    $tempRecord[$moduleAttribute->tempFieldName] = $record[$moduleAttribute->tempFieldName];
                }
            }
            $nextTempFieldNumber = intval($moduleAttributes->last()->tempFieldName) + 1;
            foreach ($customFields as $customField) {
                $tempFieldName = 'f' . strval($nextTempFieldNumber);
                if (array_key_exists($customField->fieldName, $record)) {
                    $tempRecord[$tempFieldName] = $record[$customField->fieldName];
                }
            }
            array_push($tempRecords, $tempRecord);
        }

        DB::transaction(function () use (&$tempRecords) {
            $this->migrationToolDao->saveTempRecords($tempRecords);
        });
        return $this->renderResponse(new AppResponse(null, trans('messages.dataSaved')));
    }

    /**
     * Delete mt temporary from DB.
     * @param Request $request
     * @return AppResponse
     */
    public function deleteAllTempRecord(Request $request)
    {
        $this->validate($request, ["code" => "required|alpha_num"]);

        DB::transaction(function () use (&$request) {
            $this->migrationToolDao->deleteAllTempRecord(
                $request->code
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete mt temporary from DB.
     * @param Request $request
     * @return AppResponse
     */
    public function deleteAllTempRecordWithUserId(Request $request)
    {
        $this->validate($request, ["code" => "required|alpha_num"]);

        DB::transaction(function () use (&$request) {
            $this->migrationToolDao->deleteAllTempRecordWithUserId(
                $request->code
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }


    public function getAllListCleanse(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
        ]);

        $mAttributes = $this->migrationToolDao->getModuleAttributesWithoutNullFieldName($request->code);

        $count = count($mAttributes);
        $attr = array();
        $fieldName = array();
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $attr[$i] = $mAttributes[$i];
                $fieldName[] = str_replace($mAttributes[$i]->tempFieldName, $mAttributes[$i]->tempFieldName . ' as "' . $i . '"', $mAttributes[$i]->tempFieldName);
            }
            $data = $this->migrationToolDao->getAllTemp($request->code, $fieldName);
            $data['attributes'] = $attr;
            $data['record'] = count($this->migrationToolDao->getAllTemp($request->code, $fieldName));
        }
        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function updateCleansing(Request $request)
    {
        info('requets', array($request));
        DB::transaction(function () use (&$request) {
            foreach ($request->obj as $cleansing) {
                $data = [
                    $cleansing['fieldName'] => $cleansing['value']
                ];
                $this->migrationToolDao->updateMtTemps($cleansing['id'], $data);
            }
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    public function getErrorValue(Request $request)
    {
        $this->validate($request, ['companyId' => 'integer|required', 'mtModuleCode' => 'required|max:20']);

        $dataError = [];
        $mAttributes = $this->migrationToolDao->getModuleAttributesWithoutNullFieldName($request->mtModuleCode);

        if (count($mAttributes) > 0) {
            for ($i = 0; $i < count($mAttributes); $i++) {
                $fieldName[] = $mAttributes[$i]->tempFieldName;
            }

            $mtTemps = $this->migrationToolDao->getTemp($request->mtModuleCode);

            for ($j = 0; $j < count($mtTemps); $j++) {
                $error = [];
                $regex = $this->migrationToolDao->getFieldNameWithRegex($request->mtModuleCode, $mtTemps[$j]->id, $fieldName);

                $countError = 0;
                for ($k = 0; $k < count($regex); $k++) {
                    $arrayDataTemp = (array)$regex[$k];
                    if (preg_match("/" . $regex[$k]->regex . "/", $arrayDataTemp[$regex[$k]->tempFieldName])) {
                        array_push($error, null);
                    } else {
                        $countError ++;
                        array_push($error, [
                            'id' => $mtTemps[$j]->id,
                            'type' => 'Wrong Format',
                            'tempFieldName' => $regex[$k]->tempFieldName,
                            'fieldName' => $regex[$k]->fieldName,
                            'message' => 'Wrong Format',
                            'fix' => $arrayDataTemp[$regex[$k]->tempFieldName]
                        ]);
                    }
                }

                if($countError > 0) {
                    array_push($dataError, [
                        'rowIndex' => $j+1,
                        'errors' => $error,
                        'isView' => true
                    ]);
                }
            }
        }

        return $this->renderResponse(new AppResponse($dataError, trans('messages.dataRetrieved')));
    }

    public function updateAttachment(Request $request)
    {
        DB::transaction(function () use (&$request) {
            $attachment = array();
            $column = '';
            if ($request->ref === 'EMDOC') {
                $attachment['f5'] = $request->fileUri;
                $column = 'f5';
            } else if ($request->ref === 'EMPL') {
                $attachment['f30'] = $request->fileUri;
                $column = 'f30';
            }

            $this->migrationToolDao->updateAttachment(
                $column,
                $request->fileName,
                $attachment
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    public function moveTemporaryToTable(Request $request)
    {
        $this->validate($request, [
            'obj' => 'required',
            'companyId' => 'required|integer'
        ]);

        $fieldTemp = $this->migrationToolDao->getFieldNameTempSchema($request->obj);
        $fieldTempNullValue = $this->migrationToolDao->getFieldNameTempSchemaNullValue($request->obj);

        $dataCollect = collect($fieldTemp)->unique('desttable');
        $dataCollect->values()->all();

        $dataCollectNullValue = collect($fieldTempNullValue)->unique('desttable');
        $dataCollectNullValue->values()->all();

        $countDataTable = count($dataCollect);

        if ($countDataTable > 1) {
            //INSERT EMPLOYEE
            if ($request->obj === 'EMPL') {
                //INSERT PERSON
                $fieldAttr = $this->migrationToolDao->getMtAttributes($request->obj, 'persons');
                $fieldAssignment = $this->migrationToolDao->getMtAttributes($request->obj, 'assignments');

                if (count($fieldAttr) > 0) {
                    DB::transaction(function () use ($request, $fieldAttr, $fieldAssignment) {
                        try {
                            $getTemp = $this->migrationToolDao->getAllTempField($request->obj);

                            foreach ($getTemp as $dataTemp) {
                                $arrayDataTemp = (array)$dataTemp;

                                $dataRecord = [
                                    'tenant_id' => $this->requester->getTenantId(),
                                    'created_by' => $this->requester->getUserId(),
                                    'created_at' => Carbon::now()
                                ];

                                //=================================INSERT DATA PERSON=====================================//
                                foreach ($fieldAttr as $fieldDataAttr) {
                                    //FIELD NAME NOT NULL
                                    if ($fieldDataAttr->tempFieldName != null) {
                                        if (array_key_exists($fieldDataAttr->tempFieldName, $arrayDataTemp)) {
                                            if ($fieldDataAttr->dataType === 'DATE') {
                                                $changeFieldData = date('Y-m-d', strtotime($arrayDataTemp[$fieldDataAttr->tempFieldName]));
                                            } else {
                                                $changeFieldData = $arrayDataTemp[$fieldDataAttr->tempFieldName];
                                            }
                                            $dataRecord[$fieldDataAttr->destField] = $changeFieldData;
                                        }
                                        //FIELD NAME NULL
                                    } else {
                                        $fieldName = preg_replace('/[0-9]+/', '', $fieldDataAttr->defaultValue);

                                        if ($fieldDataAttr->defaultValue != null && $fieldName != 'f') {
                                            $changeFieldDataNull = $fieldDataAttr->defaultValue;
                                        } else if ($fieldDataAttr->defaultValue != null && $fieldName === 'f') {
                                            if ($fieldDataAttr->dataType === 'DATE') {
                                                $changeFieldDataNull = date('Y-m-d', strtotime($arrayDataTemp[$fieldDataAttr->defaultValue]));
                                            } else {
                                                $changeFieldDataNull = $arrayDataTemp[$fieldDataAttr->defaultValue];
                                            }
                                        }
                                        $dataRecord[$fieldDataAttr->destField] = $changeFieldDataNull;
                                    }
                                }


                                //================== SAVE PERSON ==================================//
                                $person['id'] = $this->migrationToolDao->saveActRecordsManyTable('persons', $dataRecord);


                                //=====================================INSERT ASSIGNMENT=========================================//
                                $dataRecordAssignment = [
                                    'tenant_id' => $this->requester->getTenantId(),
                                    'company_id' => $this->requester->getCompanyId(),
                                    'created_by' => $this->requester->getUserId(),
                                    'created_at' => Carbon::now()
                                ];

                                foreach ($fieldAssignment as $fieldDataAssignment) {
                                    //FIELD NAME NOT NULL
                                    if ($fieldDataAssignment->tempFieldName != null) {
                                        if (array_key_exists($fieldDataAssignment->tempFieldName, $arrayDataTemp)) {
                                            if ($fieldDataAssignment->dataType === 'DATE') {
                                                $changeFieldDataAss = date('Y-m-d', strtotime($arrayDataTemp[$fieldDataAssignment->tempFieldName]));
                                            } else {
                                                $changeFieldDataAss = $arrayDataTemp[$fieldDataAssignment->tempFieldName];
                                            }
                                            $dataRecordAssignment[$fieldDataAssignment->destField] = $changeFieldDataAss;
                                        }
                                        //FIELD NAME NULL
                                    } else {
                                        $fieldName = preg_replace('/[0-9]+/', '', $fieldDataAssignment->defaultValue);
                                        $getChar = strpos($fieldDataAssignment->defaultValue, 'F-');

                                        if ($fieldDataAssignment->defaultValue != 'function') {
                                            if ($fieldDataAssignment->defaultValue != null && $fieldName != 'f' && $getChar === 0) {
                                                $fieldName = preg_replace('/F-/', '', $fieldDataAssignment->defaultValue);
                                                $dataDefaultValueId = $fieldName;
                                                $changeFieldDataNullAss = '';

                                                //CONSTRAINT DATA ID
                                                if ($dataDefaultValueId != null) {
                                                    $changeFieldDataNullAss = $person['id'];
                                                }
                                            } else if ($fieldDataAssignment->defaultValue != null && $fieldName != 'f' && $getChar === false) {
                                                $changeFieldDataNullAss = $fieldDataAssignment->defaultValue;
                                            } else if ($fieldDataAssignment->defaultValue != null && $fieldName === 'f') {
                                                if ($fieldDataAssignment->dataType === 'DATE') {
                                                    $changeFieldDataNullAss = date('Y-m-d', strtotime($arrayDataTemp[$fieldDataAssignment->defaultValue]));
                                                } else {
                                                    $changeFieldDataNullAss = $arrayDataTemp[$fieldDataAssignment->defaultValue];
                                                }
                                            }
                                        } else {
                                            //GET TEMP NAME
                                            //POSITION SLOT OBJECT
                                            $positionCode = $arrayDataTemp['f25'];
                                            $effBegin = date('Y-m-d', strtotime($arrayDataTemp['f32']));
                                            $effEnd = date('Y-m-d', strtotime($arrayDataTemp['f33']));
                                            $count = $this->positionSlotDao->countAllRows($positionCode);
                                            $positionSlot = [
                                                "tenant_id" => $this->requester->getTenantId(),
                                                "company_id" => $this->requester->getCompanyId(),
                                                "position_code" => $positionCode,
                                                "code" => $positionCode . '-' . $count,
                                                "eff_begin" => $effBegin,
                                                "eff_end" => $effEnd
                                            ];
                                            $changeFieldDataNullAss = $positionCode . '-' . $count;
                                            $dataDefaultValueId = null;

                                            //INSERT POSITION SLOT
                                            if ($positionSlot != null) {
                                                $this->positionSlotDao->save($positionSlot);
                                            }
                                        }
                                        $dataRecordAssignment[$fieldDataAssignment->destField] = $changeFieldDataNullAss;
                                    }
                                }

                                $this->migrationToolDao->saveActRecordsManyTable('assignments', $dataRecordAssignment);
                                $this->migrationToolDao->deleteAllTempRecord($request->obj);
                            }
                        } catch (\Exception $e) {
                            info('error', array($e));
                            throw new AppException(trans('messages.invalidData'));
                        }
                    });
                }
            } else {
//                info('masuk lookup');
                //Migration Lookup From Any Table
                if (count($dataCollectNullValue) > 0) {
                    $mtModule = $this->migrationToolDao->getOneModule($request->obj);
                    
                    $fieldAttribute = $this->migrationToolDao->getMtAttributesWithoutDestTable($request->obj);
                    $getTemp = $this->migrationToolDao->getAllTempField($request->obj);

                    $dataRecords = [];
                    foreach ($getTemp as $dataTemp) {
                        $arrayDataTemp = (array)$dataTemp;
                        if ($mtModule->isCompany === true) {
                            $dataRecord = [
                                'tenant_id' => $this->requester->getTenantId(),
                                'company_id' => $this->requester->getCompanyId(),
                                'created_by' => $this->requester->getUserId(),
                                'created_at' => Carbon::now()
                            ];
                        } else {
                            $dataRecord = [
                                'tenant_id' => $this->requester->getTenantId(),
                                'created_by' => $this->requester->getUserId(),
                                'created_at' => Carbon::now()
                            ];
                        }
                        foreach ($fieldAttribute as $fieldData) {
                            if ($fieldData->tempFieldName === null) {
//                                info('employee id', array($arrayDataTemp[$fieldData->defaultValue]));
                                $dataLookup = $this->migrationToolDao->getDataFromTable($fieldData->lookupService, $fieldData->lookupTable, $fieldData->lookupField, $fieldData->defaultValueType, $arrayDataTemp[$fieldData->defaultValue]);

                                info('data lookup person', array($dataLookup));
                                info('data lookup employee', array($arrayDataTemp[$fieldData->defaultValue]));
                                if (count($dataLookup) < 1) {
                                    throw new AppException(trans('messages.dataLookupNotAvailable'));
                                }
                                $arrayDataLookup = (array)$dataLookup;
                                if ($fieldData->dataType === 'DATE') {
                                    $changeFieldData = $newDateFormat = date('Y-m-d', strtotime($arrayDataLookup[$fieldData->defaultValueType]));
                                } else {
                                    $changeFieldData = $arrayDataLookup[$fieldData->defaultValueType];
                                }
                                $dataRecord[$fieldData->destField] = $changeFieldData;
                            } else if ($fieldData->tempFieldName != null && $fieldData->destField != null) {
                                if (array_key_exists($fieldData->tempFieldName, $arrayDataTemp)) {
                                    if ($fieldData->dataType === 'DATE') {
                                        $changeFieldData = $newDateFormat = date('Y-m-d', strtotime($arrayDataTemp[$fieldData->tempFieldName]));
                                    } else {
                                        $changeFieldData = $arrayDataTemp[$fieldData->tempFieldName];
                                    }
                                    $dataRecord[$fieldData->destField] = $changeFieldData;
                                }
                            }
                        }
                        array_push($dataRecords, $dataRecord);
                    }
//                    info('data recidr', array($dataRecords));

                    //get destination table
                    foreach ($dataCollect as $datatable) {
                        if ($datatable->desttable) {
                            $destService = $datatable->destservice;
                            $destTable = $datatable->desttable;
                        }
                    }

//                    info('dest table', array($destTable));

                    DB::transaction(function () use (&$dataRecords, $destTable, $destService, $request) {
                        try {
                            $this->migrationToolDao->saveActRecords($destTable, $destService, $dataRecords);
                            $this->migrationToolDao->deleteAllTempRecord($request->obj);

                        } catch (\Exception $e) {
                            info('error', array($e));
                            throw new AppException(trans('messages.invalidData'));
                        }
                    });
                }
            }
        } else {
            $getTemp = $this->migrationToolDao->getAllTempField($request->obj);

            $dataRecords = [];
            foreach ($getTemp as $dataTemp) {
                $arrayDataTemp = (array)$dataTemp;
                $dataRecord = [
                    'tenant_id' => $this->requester->getTenantId(),
                    'company_id' => $this->requester->getCompanyId(),
                    'created_by' => $this->requester->getUserId(),
                    'created_at' => Carbon::now()
                ];
                foreach ($fieldTemp as $fieldData) {
                    if (array_key_exists($fieldData->tempfieldname, $arrayDataTemp)) {
                        if ($fieldData->datatype === 'DATE') {
                            $changeFieldData = $newDateFormat = date('Y-m-d', strtotime($arrayDataTemp[$fieldData->tempfieldname]));
                        } else {
                            $changeFieldData = $arrayDataTemp[$fieldData->tempfieldname];
                        }
                        $dataRecord[$fieldData->destfield] = $changeFieldData;
                    }
                }
                array_push($dataRecords, $dataRecord);
            }

            //get destination table
            foreach ($dataCollect as $datatable) {
                $destService = $datatable->destservice;
                $destTable = $datatable->desttable;
            }

            DB::transaction(function () use (&$dataRecords, $destTable, $destService, $request) {
                try {
                    $this->migrationToolDao->saveActRecords($destTable, $destService, $dataRecords);
                    $this->migrationToolDao->deleteAllTempRecord($request->obj);

                } catch (\Exception $e) {
                    info('error', array($e));
                    throw new AppException(trans('messages.invalidData'));
                }
            });
        }

        return $this->renderResponse(new AppResponse(null, trans('messages.dataSaved')));
    }

    /**
     * Search Batch from Mt Batches.
     * @param Request $request
     * @param array $data
     * @return array
     */
    public function searchBatches(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'searchQuery' => 'present|string|max:255',
            'mtModule' => 'required|string|max:20'
        ]);

        $batches = $this->migrationToolDao->search($request->searchQuery, $request->mtModule);

        return $this->renderResponse(new AppResponse($batches, trans('messages.allDataRetrieved')));
    }

    /**
     * Get all uploaded file URIs from File service.
     * @param Request $request
     * @param array $data
     * @return array
     */
    private function getFileUris(Request $request, array & $data)
    {
        $guzzle = new \GuzzleHttp\Client();
        try {
            $response = $guzzle->request(
                'POST',
                env('CDN_SERVICE_SAVE_API'),
                [
                    'multipart' => $this->constructPayload($request),
                    'headers' => [
                        'Authorization' => $request->headers->get('authorization'),
                        'Origin' => $request->headers->get('origin')
                    ]
                ]
            );
            $body = json_decode($response->getBody()->getContents());
            if ($body->status === 200) {
                $data['file'] = (array)$body;
                return (array)$body->data->fileUris;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $data['file'] = [];
            if ($e->hasResponse()) {
                $body = $e->getResponse()->getBody();
                $data['file'] = (array)json_decode($body->getContents());
            } else {
                $data['file']['status'] = 500;
                $data['file']['message'] = $e->getMessage();
                $data['file']['data'] = null;
            }
        }

        return [];
    }

    private function deleteFile($request, $fileUri)
    {
        $guzzle = new \GuzzleHttp\Client();
        try {
            $response = $guzzle->request('POST', env('CDN_SERVICE_DELETE_API'), [
                'form_params' => ['fileUri' => $fileUri, 'companyId' => $request->companyId],
                'headers' => [
                    'Authorization' => $request->headers->get('authorization'),
                    'Origin' => $request->headers->get('origin')
                ]
            ]);
            $body = json_decode($response->getBody()->getContents());
            return $body->status === 200;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return false;
        }
        // should never reach here
    }

    /**
     * Construct a multipart payload for uploading file to File service.
     * @param Request $request
     * @return array
     */
    private function constructPayload(Request $request)
    {
        $payload = array([
            'name' => 'data',
            'contents' => $request->data
        ], [
            'name' => 'ref',
            'contents' => $request->ref
        ], [
            'name' => 'companyId',
            'contents' => $request->companyId
        ]);
        foreach ($request->docTypes as $i => $docType) {
            array_push($payload, [
                'name' => "docTypes[$i]",
                'contents' => $docType
            ]);
        }
        foreach ($request->fileContents as $i => $file) {
            array_push($payload, [
                'name' => "fileContents[$i]",
                'contents' => file_get_contents($file),
                'filename' => $file->getClientOriginalName()
            ]);
        }

        return $payload;
    }
}
