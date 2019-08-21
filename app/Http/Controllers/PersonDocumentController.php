<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonDocumentDao;
use App\Business\Dao\WorkflowDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;

/**
 * Class for handling personDocument process
 * @property Requester requester
 * @property PersonDocumentDao personDocumentDao
 * @property WorkflowDao workflowDao
 */
class PersonDocumentController extends Controller
{
    public function __construct(Requester $requester
        , PersonDocumentDao $personDocumentDao
        , WorkflowDao $workflowDao
    )
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personDocumentDao = $personDocumentDao;
        $this->workflowDao = $workflowDao;
    }

    /**
     * Get all personDocuments for one tenant
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            'personId' => 'required'
        ]);

        $personDocuments = $this->personDocumentDao->getAll($request->personId);

        $resp = new AppResponse($personDocuments, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all personDocuments with true flag and expiring months for one tenant
     * @param Request $request
     * @return AppResponse
     */
    public function getAllByFlag(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer'
        ]);

        $personDocuments = $this->personDocumentDao->getAllByFlagAndExpiredInThreeMonths();

        $now = Carbon::now();
        $oneMonth = Carbon::parse($now)->addMonths(1);
        $twoMonth = Carbon::parse($now)->addMonths(2);
        $threeMonth = Carbon::parse($now)->addMonths(3);

        if (count($personDocuments) > 0) {
            for ($i = 0; $i < count($personDocuments); $i++) {
                $expired = $personDocuments[$i]->expired;

                if ($expired < $now) {
                    $personDocuments[$i]->expStatus = 'expired';
                } else if ($expired >= $now && $expired < $oneMonth) {
                    $personDocuments[$i]->expStatus = 'expire in 1 month';
                } else if ($expired >= $oneMonth && $expired < $twoMonth) {
                    $personDocuments[$i]->expStatus = 'expire in 2 months';
                } else if ($expired >= $twoMonth && $expired < $threeMonth) {
                    $personDocuments[$i]->expStatus = 'expire in 3 months';
                }
            }
        }

        $resp = new AppResponse($personDocuments, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one personDocument based on personDocument id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "personId" => "required",
            "id" => "required"
        ]);

        $personDocument = $this->personDocumentDao->getOne($request->personId, $request->id);

        $resp = new AppResponse($personDocument, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personDocument to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonDocumentRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $personDocument = $this->constructPersonDocument($request);

            if ($request->upload == true) {
                $fileUris = $this->getFileUris($request, $data);
                if (!empty($fileUris)) {
                    $personDocument['file_document'] = $fileUris['DOC'];
                }
            }

            $data['id'] = $this->personDocumentDao->save($personDocument);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personDocument from ESS change request
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $data = array();
        $this->checkPersonDocumentRequest($request);
        $this->validate($request, ['id' => 'required']);

        DB::transaction(function () use (&$request, &$data) {
            $personDocument = $this->constructPersonDocument($request);

            if ($request->upload) {
                $document = $this->personDocumentDao->getOne($request->personId, $request->id);

                if ($document->fileDocument) {
                    if (!$this->deleteFile($request, $document->fileDocument)) {
                        throw new AppException(trans('messages.updateFail'));
                    }
                }
                $fileUris = $this->getFileUris($request, $data);
                if (!empty($fileUris)) {
                    $personDocument['file_document'] = $fileUris['DOC'];
                }
            }

            $this->personDocumentDao->update(
                $request->personId,
                $request->id,
                $personDocument
            );
        });

        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personDocument from ESS change request
     * @param Request $request
     * @return AppResponse
     */
    public function saveEss(Request $request)
    {
        $data = array();
        $this->validate($request, [
            'tenantId' => 'required',
            'personId' => 'required',
            'name' => 'required',
            'expired' => 'required',
            'lovDcty' => 'required',
            'fileDocument' => 'required',
        ]);

        DB::transaction(function () use (&$request, &$data) {
            $person = [
                'tenant_id' => $this->requester->getTenantId(),
                'person_id' => $request->personId,
                'name' => $request->name,
                'lov_dcty' => $request->lovDcty,
                'expired' => $request->expired,
                'file_document' => $request->fileDocument
            ];
            $data['id'] = $this->personDocumentDao->save($person);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personDocument to DB
     * @param Request $request
     * @return AppResponse
     */
    public function updateEss(Request $request)
    {
        $workflow = $this->workflowDao->getOne("PROF");
        $data = array();

        if (!$workflow->isActive) {
            $this->checkPersonDocumentRequest($request);
            $this->validate($request, ['id' => 'required']);

            DB::transaction(function () use (&$request, &$data) {
                $personDocument = [
                    'tenant_id' => $this->requester->getTenantId(),
                    'person_id' => $request->personId,
                    'name' => $request->name,
                    'lov_dcty' => $request->lovDcty,
                    'expired' => $request->expired,
                    'file_document' => $request->fileDocument
                ];

                if ($request->upload) {
                    $document = $this->personDocumentDao->getOne($request->personId, $request->id);

                    if ($document->fileDocument) {
                        if (!$this->deleteFile($request, $document->fileDocument)) {
                            throw new AppException(trans('messages.updateFail'));
                        }
                    }
                    $fileUris = $this->getFileUris($request, $data);
                    if (!empty($fileUris)) {
                        $personDocument['file_document'] = $fileUris['DOC'];
                    }
                }

                $this->personDocumentDao->update(
                    $request->personId,
                    $request->id,
                    $personDocument
                );
            });
        } else {
            $data['tenantId'] = $this->requester->getTenantId();
        }

        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete personDocument from DB.
     * @param Request $request
     * @return AppResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "personId" => "required",
            "id" => "required"
        ]);
        $data = [];

        DB::transaction(function () use (&$request, &$data) {
            $document = $this->personDocumentDao->getOne($request->personId, $request->id);
            if ($document && $document->fileDocument) {
                if (!$this->deleteFile($request, $document->fileDocument)) {
                    throw new AppException(trans('messages.deleteFail'));
                }
            }
            $this->personDocumentDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update personDocument request.
     * @param Request $request
     */
    private function checkPersonDocumentRequest(Request $request)
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
            'personId' => 'required|exists:persons,id',
            'name' => 'required|max:50',
            'lovDcty' => 'required|max:10|exists:lovs,key_data',
            'expired' => 'required|date'
        ]);
    }

    /**
     * Construct a personDocument object (array).
     * @param Request $request
     * @return array
     */
    private function constructPersonDocument(Request $request)
    {
        $personDocument = [
            'tenant_id' => $this->requester->getTenantId(),
            'person_id' => $request->personId,
            'name' => $request->name,
            'lov_dcty' => $request->lovDcty,
            'expired' => $request->expired
        ];
        return $personDocument;
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

    private function deleteFile(Request $request, $fileUri)
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
            info('file', ['file' => $file]);
            array_push($payload, [
                'name' => "fileContents[$i]",
                'contents' => file_get_contents($file),
                'filename' => $file->getClientOriginalName()
            ]);
        }

        return $payload;
    }
}
