<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonDocumentDao;
use App\Business\Model\Requester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Business\Model\AppResponse;
use App\Business\Dao\RequestDocumentsDao;

class RequestDocumentsController extends Controller {
    public function  __construct(
        Requester $requester,
        PersonDocumentController $personDocumentController,
        PersonController $personController,
        RequestDocumentsDao $requestDocumentsDao,
        PersonDocumentDao $personDocumentDao
    ){
        parent::__construct();
        $this->requester = $requester;
        $this->personDocumentController = $personDocumentController;
        $this->personController = $personController;
        $this->requestDocumentsDao = $requestDocumentsDao;
        $this->personDocumentDao = $personDocumentDao;
    }

    public function getAll(Request $request)
    {
        $this->validate($request, ["personId" => "required"]);

        $person = $this->requestDocumentsDao->getAll($request->personId);
        $person->person = $this->personController->getOneEmployee($person->employeeId);
        $resp = new AppResponse($person, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getOne($employeeId, $id)
    {

        $person = $this->requestDocumentsDao->getOne(
            $id
        );
        $req = new \Illuminate\Http\Request();
        $req->replace([
            'id' => $employeeId
        ]);
        $person->person = $this->personController->getOneEmployeeForWorklist($req);

        return $person;
    }

    public function checkIfRequestIsPending(Request $request){
        $this->validate($request, [
            "employeeId" => "required",
            "status" => "required"
        ]);

        $person = $this->requestDocumentsDao->checkIfRequestIsPending($request->employeeId, $request->status);
        $resp = new AppResponse($person, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonDocumentRequest($request);

        if ($request->crudType == 'U' && $request->upload == 0){
            $person = $this->personDocumentDao->getOne($request->personId, $request->personDocumentId);
            $personDocument = $this->constructRequestPersonDocument($request);
            $personDocument['file_document'] = $person->fileDocument;
            $data['id'] = $this->requestDocumentsDao->save($personDocument);
        } else if ($request->crudType == 'D' && $request->upload == 0) {
            $personDocument = $this->constructRequestPersonDocument($request);
            $personDocument['file_document'] = $request->fileDocument;
            $data['id'] = $this->requestDocumentsDao->save($personDocument);
        } else
         {
            DB::transaction(function () use (&$request, &$data) {
                $personDocument = $this->constructRequestPersonDocument($request);

                if ($request->upload == true) {
                    $fileUris = $this->getFileUris($request, $data);
                    if (! empty($fileUris)) {
                        $personDocument['file_document'] = $fileUris['DOC'];
                    }
                }

                $data['id'] = $this->requestDocumentsDao->save($personDocument);
            });
        }

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    public function update($employeeId, $id, $origin, $auth)
    {
        $person = $this->getOne($employeeId, $id);
        $req = new \Illuminate\Http\Request();
        $req->replace([
            'tenantId' => $person->tenantId,
            'companyId' => $person->companyId,
            'id' => $person->personDocumentId,
            'personId' => $person->personId,
            'lovDcty' => $person->lovDcty,
            'name' => $person->name,
            'expired' => $person->expired,
            'fileDocument' => $person->fileDocument
        ]);
        $req->headers->set('Authorization', $auth);
        if ($person->crudType === 'U') {
            $this->personDocumentController->update($req);
        } else if ($person->crudType === 'C') {
            $this->personDocumentController->saveEss($req);
        } else if ($person->crudType === 'D') {
            $this->personDocumentController->delete($req);
        }

        DB::transaction(function () use (&$person) {
            $person = [
                'id' => $person->id,
                'tenant_id' => $person->tenantId,
                'company_id' => $person->companyId,
                'crud_type' => $person->crudType,
                'person_id' => $person->personId,
                'employee_id' => $person->employeeId,
                'person_document_id' => $person->personDocumentId,
                'lov_dcty' => $person->lovDcty,
                'file_document' => $person->fileDocument,
                'name' => $person->name,
                'expired' => $person->expired,
                'status' => 'A',
                'request_date' => $person->requestDate,
            ];

            $this->requestDocumentsDao->update(
                $person['id'],
                $person
            );
        });
    }

    public function delete($employeeId, $id)
    {
        $person = $this->getOne($employeeId, $id);

        DB::transaction(function () use (&$person) {
            $person = [
                'id' => $person->id,
                'tenant_id' => $person->tenantId,
                'company_id' => $person->companyId,
                'crud_type' => $person->crudType,
                'person_id' => $person->personId,
                'employee_id' => $person->employeeId,
                'person_document_id' => $person->personDocumentId,
                'lov_dcty' => $person->lovDcty,
                'file_document' => $person->fileDocument,
                'name' => $person->name,
                'expired' => $person->expired,
                'status' => 'R',
                'request_date' => $person->requestDate,
            ];

            $this->requestDocumentsDao->update(
                $person['id'],
                $person
            );
        });
    }

    private function constructRequestPersonDocument(Request $request){
        $person = [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $this->requester->getCompanyId(),
            'crud_type' => $request->crudType,
            'person_id' => $request->personId,
            'employee_id' => $request->employeeId,
            'person_document_id' => $request->personDocumentId,
            'lov_dcty' => $request->lovDcty,
            'name' => $request->name,
            'expired' => $request->expired,
            'status' => $request->status,
            'request_date' => $request->requestDate
        ];
        return $person;
    }

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

        $reqData = (array) json_decode($request->data);
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
     * Get all uploaded file URIs from File service.
     * @param Request $request
     * @param array $data
     * @return array
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
