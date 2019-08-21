<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonCredentialDao;
use App\Business\Dao\CredentialDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonCredentialController extends Controller
{
    public function __construct(Requester $requester, PersonCredentialDao $personCredentialDao, CredentialDao $credentialDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personCredentialDao = $personCredentialDao;
        $this->credentialDao = $credentialDao;
    }

    /**
     * Get all Active Person Credential
     * @param request
     */
    public function getAll(Request $request)
    {
        $data = array();
        $this->validate($request, [
            "companyId" => "required|integer",
            "pageInfo" => "required|array",
            "personId" => "required"
        ]);

        $reqData = $request->pageInfo;
        $request->merge($reqData);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1',
        ]);
        
        $personId = $request->personId;
        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $data = $this->personCredentialDao->getAll(
            $offset, 
            $limit, 
            $personId
        );

        $countRowQS = count($data);

        if($countRowQS > 0) {
            for ($i = 0 ; $i < $countRowQS ; $i++) {
                $data[$i]->credentialDetail = $this->credentialDao->getOne($data[$i]->credentialCode);
            }
        }

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'), $limit, $countRowQS, $pageNo));
    }

    /**
     * Get all Inactive Person Credential
     * @param request
     */
    public function getAllInactive(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "pageInfo" => "required|array",
            "personId" => "required"
        ]);

        $reqData = $request->pageInfo;
        $request->merge($reqData);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1',
        ]);
        
        $personId = $request->personId;
        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $data = $this->personCredentialDao->getAllInactive(
            $offset, 
            $limit, 
            $personId
        );

        $countRowQS = count($data);

        if($countRowQS > 0) {
            for ($i = 0 ; $i < $countRowQS ; $i++) {
                $data[$i]->credentialDetail = $this->credentialDao->getOne($data[$i]->credentialCode);
            }
        }

        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'), $limit, $countRowQS, $pageNo));
    }

    /**
     * Get one Person Credential based on Person Credential id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            'id' => 'required|max:20|alpha_num|exists:person_credentials,id']);

        $credential = $this->personCredentialDao->getOne($request->id);
        $credential->credentialDetail = $this->credentialDao->getOne($credential->credentialCode);

        $resp = new AppResponse($credential, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonCredential($request);

        DB::transaction(function () use (&$request, &$data) {
            $personCredential = $this->constructPersonCredential($request);

            if ($request->upload == true) {
                $fileUris = $this->getFileUris($request, $data);
                if (!empty($fileUris)) {
                    $personCredential['document'] = $fileUris['DOC'];
                }
            }
            $data['id'] = $this->personCredentialDao->save($personCredential);
            $request->id = $data['id'];
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    public function update(Request $request)
    {
        $data = array();
        $this->checkPersonCredential($request);

        DB::transaction(function () use (&$request, &$data) {
            $personCredential = $this->constructPersonCredential($request);

            if ($request->upload == true) {
                $fileUris = $this->getFileUris($request, $data);
                if (!empty($fileUris)) {
                    $personCredential['document'] = $fileUris['DOC'];
                }
            }
            $data['id'] = $this->personCredentialDao->update($request->id, $personCredential);
        });

        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * delete (set flag_delete = 1) on Person Credential based id
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            'id' => 'required|max:20|alpha_num|exists:person_credentials,id']);

        $qualificationSource = $this->personCredentialDao->delete($request->id);

        $resp = new AppResponse($qualificationSource, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save Person Credential.
     * @param request
     */
    private
    function checkPersonCredential(Request $request)
    {
        $this->validate($request, [
            'data' => 'required',
            'upload' => 'required|boolean'
        ]);

        if ($request->upload == true) {
            $this->validate($request, [
                'docTypes' => 'required|array|min:1',
                'fileContents' => 'required|array|min:1',
                'ref' => 'required|string|max:255'
            ]);
        }

        $reqData = (array)json_decode($request->data);
        if (null === $reqData) {
            throw new AppException(trans('messages.jsonInvalid'));
        }
        $request->merge($reqData);

        $this->validate($request, [
            'companyId' => 'required|integer',
            'personId' => 'required',
            'credentialCode' => 'required',
            'credentialNumber'=> 'required',
            'beginDate' => 'required',
            'endDate' => 'required'
        ]);


    }

    /**
     * Construct a Person Credential object (array).
     * @param request
     */
    private
    function constructPersonCredential(Request $request)
    {
        $personCredential = [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $request->companyId,
            'person_id' => $request->personId,
            'credential_code' => $request->credentialCode,
            'no_credential' => $request->credentialNumber,
            'begin_date' => $request->beginDate,
            'end_date' => $request->endDate
        ];
        return $personCredential;
    }

    /**
     * Get all uploaded file URIs from File service.
     * @param request , data
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
        info('sini woi!');
        info('fileeee!', [$request->fileContents]);
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
