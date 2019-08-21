<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonExtTrainingDao;
use App\Business\Dao\WorkflowDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling personExtTraining process
 * @property Requester requester
 * @property PersonExtTrainingDao personExtTrainingDao
 * @property WorkflowDao workflowDao
 */
class PersonExtTrainingController extends Controller
{
    public function __construct(Requester $requester
                              , PersonExtTrainingDao $personExtTrainingDao
                              , WorkflowDao $workflowDao
    )
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personExtTrainingDao = $personExtTrainingDao;
        $this->personExtTrainingFields = array('id', 'institution', 'yearBegin',
            'yearEnd', 'description', 'fileCertificate');
        $this->workflowDao = $workflowDao;
    }

    /**
     * Get all personExtTrainings for one tenant
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            'personId' => 'required'
        ]);

        $personExtTrainings = $this->personExtTrainingDao->getAll(
            $this->requester->getTenantId(),
            $request->personId
        );

        $resp = new AppResponse($personExtTrainings, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one personExtTraining based on personExtTraining id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "personId" => "required",
            "id" => "required"
        ]);

        $personExtTraining = $this->personExtTrainingDao->getOne(
            $this->requester->getTenantId(),
            $request->personId,
            $request->id
        );

        $data = array();
        if (count($personExtTraining) > 0) {
            foreach ($this->personExtTrainingFields as $field) {
                $data[$field] = $personExtTraining->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personExtTraining to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonExtTrainingRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $personExtTraining = $this->constructPersonExtTraining($request);

            if ($request->upload == true) {
                $fileUris = $this->getFileUris($request, $data);
                if (! empty($fileUris)) {
                    $personExtTraining['file_certificate'] = $fileUris['DOC'];
                }
            }

            $data['id'] = $this->personExtTrainingDao->save($personExtTraining);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personExtTraining to DB
     * @param request
     */
    public function update(Request $request)
    {
        $data = array();
        $this->checkPersonExtTrainingRequest($request);
        $this->validate($request, ['id' => 'required']);

        DB::transaction(function () use (&$request, &$data) {
            $personExtTraining = $this->constructPersonExtTraining($request);

            if ($request->upload == true) {
                $fileUris = $this->getFileUris($request, $data);
                if (! empty($fileUris)) {
                    $personExtTraining['file_certificate'] = $fileUris['DOC'];
                }
            }

            $this->personExtTrainingDao->update(
                $this->requester->getTenantId(),
                $request->personId,
                $request->id,
                $personExtTraining
            );
        });

        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personExtTraining from ESS change request
     * @param Request $request
     * @return AppResponse
     */
    public function saveEss(Request $request)
    {
        $workflow = $this->workflowDao->getOne("PROF");
        $data = array();

        if(!$workflow->isActive) {
            $this->checkPersonExtTrainingRequest($request);			
			
            DB::transaction(function () use (&$request, &$data) {
                $personExtTraining = $this->constructPersonExtTraining($request);

                if ($request->upload) {
                    $fileUris = $this->getFileUris($request, $data);
                    if (! empty($fileUris)) {
                        $personExtTraining['file_certificate'] = $fileUris['DOC'];
                    }
                }

                $data['id'] = $this->personExtTrainingDao->save($personExtTraining);
            });
        }
        else { 
            $data['tenantId'] = $this->requester->getTenantId();
        }

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personExtTraining from ESS change request
     * @param request
     */
    public function updateEss(Request $request)
    {
        $workflow = $this->workflowDao->getOne("PROF");
        $data = array();
                
        if(!$workflow->isActive) {
            $this->checkPersonExtTrainingRequest($request);
            $this->validate($request, ['id' => 'required']);

            DB::transaction(function () use (&$request, &$data) {
                $personExtTraining = $this->constructPersonExtTraining($request);

                if ($request->upload == true) {
                    $fileUris = $this->getFileUris($request, $data);
                    if (! empty($fileUris)) {
                        $personExtTraining['file_certificate'] = $fileUris['DOC'];
                    }
                }

                $this->personExtTrainingDao->update(
                    $this->requester->getTenantId(),
                    $request->personId,
                    $request->id,
                    $personExtTraining
                );
            });
        }
        else { 
            $data['tenantId'] = $this->requester->getTenantId();
        }
        
        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete personExtTraining from DB.
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);


        DB::transaction(function () use (&$request) {
            $this->personExtTrainingDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update personExtTraining request.
     * @param request
     */
    private function checkPersonExtTrainingRequest(Request $request)
    {
        $this->validate($request, [
            'data' => 'required',
            'upload' => 'required|boolean'
        ]);

        if ($request->upload == true) {
            $this->validate($request, [
                'docTypes' => 'required|array|min:1',
                'fileContents' => 'required|array|min:1'
            ]);
        }

        $reqData = (array) json_decode($request->data);
        if (null === $reqData) {
            throw new AppException(trans('messages.jsonInvalid'));
        }
        $request->merge($reqData);

        $this->validate($request, [
            'personId' => 'required',
            'institution' => 'required',
            'yearBegin' => 'required|integer',
            'yearEnd' => 'required|integer',
            'description' => 'required'
        ]);
    }

    /**
     * Construct a personExtTraining object (array).
     * @param request
     */
    private function constructPersonExtTraining(Request $request)
    {
        $personExtTraining = [
            'tenant_id' => $this->requester->getTenantId(),
            'person_id' => $request->personId,
            'institution' => $request->institution,
            'year_begin' => $request->yearBegin,
            'year_end' => $request->yearEnd,
            'description' => $request->description
        ];
        return $personExtTraining;
    }

    /**
     * Get all uploaded file URIs from File service.
     * @param request, data
     */
    private function getFileUris(Request $request, array& $data)
    {
        $guzzle = new \GuzzleHttp\Client();
        try {
            $response = $guzzle->request(
                'POST',
                env('CDN_SERVICE_API'),
                ['multipart' => $this->constructPayload($request)]
            );
            $body = json_decode($response->getBody()->getContents());
            if ($body->data->uploaded === true) {
                $data['uploaded'] = true;
                return (array) $body->data->fileUris;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $data['uploaded'] = false;
            if ($e->hasResponse()) {
                $body = $e->getResponse()->getBody();
                $data['fileError'] = json_decode($body->getContents());
            } else {
                $data['fileError'] = $e->getMessage();
            }
        }

        return [];
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
