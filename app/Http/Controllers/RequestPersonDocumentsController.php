<?php

namespace App\Http\Controllers;

use App\Business\Dao\RequestPersonDocumentsDao;
use App\Business\Model\Requester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Business\Model\AppResponse;

class RequestPersonDocumentsController extends Controller
{
    public function __construct(
        Requester $requester,
        RequestPersonDocumentsDao $requestPersonDocumentsDao,
        PersonController $personController
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->requestPersonDocumentDao = $requestPersonDocumentsDao;
        $this->personController = $personController;
    }

    public function getAll(Request $request)
    {
        $this->validate($request, ["profileRequestId" => "required"]);
        $person = $this->requestPersonDocumentDao->getMany($request->profileRequestId);
        $resp = new AppResponse($person, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getOne(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);

        $person = $this->requestPersonDocumentDao->getOne(
            $request->id
        );

        $resp = new AppResponse($person, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function save(Request $request)
    {
        $data = array();
        $person = array();
        $this->checkRequestPersonDocument($request);

        DB::transaction(function () use (&$request, &$data) {
            $person = $this->constructRequestPersonDocument($request);
            if ($request->upload == true) {
                $fileUris = $this->getFileUris($request, $data);
                if (!empty($fileUris)) {
                    $person['file_document'] = $fileUris['DOC'];
                }
            }
            $data['id'] = $this->requestPersonDocumentDao->save($person);
        });
        info('data', $person);
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update request person document
     * @param Request $request
     */
    private function checkRequestPersonDocument(Request $request)
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
            'crudType' => 'required|max:1',
            'profileRequestId' => 'required|integer',
            'personDocumentId' => 'nullable|integer'
        ]);
    }

    private function constructRequestPersonDocument(Request $request)
    {
        $person = [
            'tenant_id' => $this->requester->getTenantId(),
            'crud_type' => $request->crudType,
            'profile_request_id' => $request->profileRequestId,
            'person_document_id' => $request->personDocumentId,
            'lov_dcty' => $request->lovDcty,
            'name' => $request->name,
            'expired' => $request->expired
        ];
        return $person;
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