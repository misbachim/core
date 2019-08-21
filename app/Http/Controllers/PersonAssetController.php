<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonAssetDao;
use App\Business\Dao\PersonAssetReceiptDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling personAsset process
 */
class PersonAssetController extends Controller
{
    public function __construct(Requester $requester, PersonAssetDao $personAssetDao, PersonAssetReceiptDao $personAssetReceiptDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personAssetDao = $personAssetDao;
        $this->personAssetReceiptDao = $personAssetReceiptDao;
        $this->personAssetFields = array('personAssetId', 'assetCode', 'isLost', 'getReceiptId', 'returnReceiptId');
        $this->personAssetReceiptFields = array('id', 'receiptNumber', 'type', 'date', 'fileReceipt', 'isCalculation');
    }

    /**
     * Get all personInventories for one tenant
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer',
            'personId' => 'required|integer'
        ]);

        $personInventories = $this->personAssetDao->getAll(
            $request->personId
        );

        $resp = new AppResponse($personInventories, trans('messages.allDataRetrieved'));

        return $this->renderResponse($resp);
    }

    public function getAllNearEndDate(Request $request)
    {
        $data = $this->personAssetDao->getAllNearEndDate();
        return $this->renderResponse(new AppResponse($data, trans('messages.allDataRetrieved')));
    }

    /**
     * Get all personInventories for one tenant
     * @param request
     */
    public function getAllNotReturned(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer',
            'personId' => 'required|integer'
        ]);

        $personInventories = $this->personAssetDao->getAllNotReturned(
            $request->personId
        );

        $resp = new AppResponse($personInventories, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all personInventoryReceipts for one tenant
     * @param request
     */
    public function getAllReceipt(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer',
            'personId' => 'required|integer'
        ]);

        $personAsset = $this->personAssetReceiptDao->getAll($request->personId);
        if ($personAsset !== null) {
            $temp = count($personAsset);
            for ($i = 0; $i < $temp; $i++) {
                $personAsset[$i]->assets = $this->personAssetReceiptDao->getAssetNameByReceipt($personAsset[$i]->id);
            }
        }

        $resp = new AppResponse($personAsset, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one personAsset based on personAsset id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "personId" => "required|integer",
            "id" => "required|integer"
        ]);

        $personAsset = $this->personAssetReceiptDao->getOne($request->personId, $request->id);
        if ($personAsset !== null) {
            $personAsset->assets = $this->personAssetReceiptDao->getAssetNameByReceipt($request->id);
        }

        $data = array();
        if (count($personAsset) > 0) {
            foreach ($this->personAssetReceiptFields as $field) {
                $data[$field] = $personAsset->$field;
            }
        }
        info('DATA', array($data));
        info('ASSET', array($personAsset));
        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personAsset to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonAssetReceiptRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $personAssetReceipt = $this->constructPersonAssetReceipt($request);

            if ($request->upload == true) {
                $fileUris = $this->getFileUris($request, $data);
                if (!empty($fileUris)) {
                    $personAssetReceipt['file_receipt'] = $fileUris['DOC'];
                }
            }

            $data['id'] = $this->personAssetReceiptDao->save($personAssetReceipt);
            if ($request->type === "g") {
                $request->getReceiptId = $data['id'];
                $this->savePersonAssets($request);
            }
            if ($request->type === "r") {
                $returnReceiptId = $data['id'];
                if ($request->assets) {
                    $this->updatePersonAssets($request,$returnReceiptId);
                }
            }
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personAsset to DB
     * @param request
     */
    public function update(Request $request)
    {
        $data = array();
        $this->checkPersonAssetReceiptRequest($request);
        $this->validate($request, ['id' => 'required|integer']);

        DB::transaction(function () use (&$request, &$data) {
            $personAssetReceipt = $this->constructPersonAssetReceipt($request);

            if ($request->upload) {
                $AssetReceipt = $this->personAssetReceiptDao->getOne($request->personId, $request->id);

                if ($AssetReceipt->fileReceipt) {
                    if (! $this->deleteFile($request, $AssetReceipt->fileReceipt)) {
                        throw new AppException(trans('messages.updateFail'));
                    }
                }
                $fileUris = $this->getFileUris($request, $data);
                if (! empty($fileUris)) {
                    $personAssetReceipt['file_receipt'] = $fileUris['DOC'];
                }
            }

            $this->personAssetReceiptDao->update(
                $request->personId,
                $request->id,
                $personAssetReceipt
            );

            if ($request->type === "g") {
                $request->getReceiptId = $request->id;
                $this->savePersonAssets($request);
            }
            if ($request->type === "r") {
                $request->returnReceiptId = $request->id;
                if ($request->assets) {
                    $this->updatePersonAssets($request);
                }
            }

        });

        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * update payroll calculation
     * @param request
     */
    // public function updatePayrollCalculation(Request $request)
    // {
    //     $data = array();
    //     $data = [
    //         'payroll_calculation' => $request->isCalculation,
    //     ];

    //     DB::transaction(function () use (&$request, &$data) {
    //         $this->personAssetDao->updatePayrollCalculation($request->companyId,$request->assets[$i]->id,
    //         $request->assets[$i]->getReceiptId, $data);
    //     });

    //     $resp = new AppResponse($data, trans('messages.dataUpdated'));
    //     return $this->renderResponse($resp);
    // }


    /**
     * Delete personAsset from DB.
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required|integer"
        ]);

        $returned = $this->personAssetDao->getReturnReceipt($request->id);
        if ($returned) {
            throw new AppException(trans('messages.dataInUse'));
        }

        DB::transaction(function () use (&$request) {
            $this->personAssetReceiptDao->delete($request->id);
            if ($request->type === "g") {
                $this->personAssetDao->deleteReceive($request->id);
            }
            if ($request->type === "r") {
                $obj = [
                    'return_receipt_id' => null,
                    'is_lost' => false
                ];
                $this->personAssetDao->updateReturn($request->id, $obj);
            }
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }


    /**
     * Validate save/update personAsset request.
     * @param request
     */
    private function checkPersonAssetReceiptRequest(Request $request)
    {
        $this->validate($request, [
            'data' => 'required',
            'upload' => 'required|boolean'
        ]);

        if ($request->upload == true) {
            $this->validate($request, [
                'docTypes' => 'required|array|min:1',
                'fileContents' => 'required|array|min:1',
                'ref' => 'required|string|max:255',
                'companyId' => 'required|integer|exists:companies,id'
            ]);
        }

        $reqData = (array)json_decode($request->data);
        if (null === $reqData) {
            throw new AppException(trans('messages.jsonInvalid'));
        }
        $request->merge($reqData);

        $this->validate($request, [
            'personId' => 'required|integer',
            'type' => 'required',
            'date' => 'required|date'
        ]);


    }

    /**
     * Construct a personAssetReceipt object (array).
     * @param request
     */
    private
    function constructPersonAssetReceipt(Request $request)
    {
        $personAssetReceipt = [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $request->companyId,
            'person_id' => $request->personId,
            'receipt_number' => $request->receiptNumber,
            'type' => $request->type,
            'date' => $request->date,
        ];
        return $personAssetReceipt;
    }

    /**
     * Get all uploaded file URIs from File service.
     * @param request , data
     */
    private function getFileUris(Request $request, array& $data)
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
                $data['file'] = (array) $body;
                return (array) $body->data->fileUris;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $data['file'] = [];
            if ($e->hasResponse()) {
                $body = $e->getResponse()->getBody();
                $data['file'] = (array) json_decode($body->getContents());
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
     * @param request
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

    private
    function savePersonAssets(Request $request)
    {
        for ($i = 0; $i < count($request->assets); $i++) {
            $assetReq = new \Illuminate\Http\Request();
            $assetReq->replace([
                'assetCode' => $request->assets[$i]->assetCode,
                'getReceiptId' => $request->getReceiptId,
            ]);
            $this->validate($assetReq, [
                'assetCode' => 'present|alpha_num',
                'getReceiptId' => 'present|integer',
            ]);

            $data = [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $request->companyId,
                'person_id' => $request->personId,
                'asset_code' => $request->assets[$i]->assetCode,
                'get_receipt_id' => $request->getReceiptId,
                'end_date' => $request->endDate,
                'is_payroll_calculation' => $request->isCalculation
            ];
            $this->personAssetDao->save($data);
        }
    }

    private function updatePersonAssets(Request $request,$returnReceiptId)
    {
        for ($i = 0; $i < count($request->assets); $i++) {
            $assetReq = new \Illuminate\Http\Request();
            $assetReq->replace([
                'isLost' => $request->assets[$i]->isLost,
                'returnReceiptId' => $returnReceiptId
            ]);
            $this->validate($assetReq, [
                'returnReceiptId' => 'present|integer',
                'isLost' => 'required|boolean',
            ]);

            $data = [
                'is_lost' => $request->assets[$i]->isLost,
                'return_receipt_id' => $returnReceiptId
            ];
            if ($request->assets[$i]->isReturned || $request->assets[$i]->isLost) {
                $this->personAssetDao->updateReceive(
                    $request->companyId,
                    $request->assets[$i]->id,
                    $request->assets[$i]->getReceiptId,
                    $data
                );
            }
        }
    }
}
