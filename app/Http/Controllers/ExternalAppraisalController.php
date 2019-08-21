<?php

namespace App\Http\Controllers;

use App\Business\Helper\HttpClient;
use App\Business\Model\Requester;

/**
 * Controller for communication with core microservice
 * @package App\Http\Controllers
 */
class ExternalAppraisalController extends Controller
{
    public static $GET_APPRAISAL_REQUEST_URI = 'reviewRequest/getOne';
    public static $UPDATE_APPRAISAL_REQUEST_URI = 'reviewRequest/update';

    private $appraisalServiceUrl;
    private $requester;

    public function __construct(Requester $requester)
    {
        $this->appraisalServiceUrl = env('APPRAISAL_SERVICE_API');
        $this->requester = $requester;
    }

    public function getAppraisalRequest($requestId,$companyId, $applicationId)
    {
        $url = $this->appraisalServiceUrl . ExternalAppraisalController::$GET_APPRAISAL_REQUEST_URI;
        $body = array('id' => $requestId,'companyId' => $companyId,'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateAppraisalRequest($requestId,$status, $applicationId)
    {
        $url = $this->appraisalServiceUrl . ExternalAppraisalController::$UPDATE_APPRAISAL_REQUEST_URI;
        $body = array('id' => $requestId,'status' => $status, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }
}