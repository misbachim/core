<?php

namespace App\Http\Controllers;

use App\Business\Helper\HttpClient;
use App\Business\Model\Requester;

/**
 * Controller for communication with core microservice
 * @package App\Http\Controllers
 */
class ExternalTalentController extends Controller
{
    public static $GET_MPP_URI = 'mppRequest/getOneById';
    public static $UPDATE_MPP_URI = 'mppRequest/cancelRequest';

    private $talentServiceUrl;
    private $requester;

    public function __construct(Requester $requester)
    {
        $this->talentServiceUrl = env('TALENT_SERVICE_API');
        $this->requester = $requester;
    }

    public function getMppRequest($requestId, $companyId, $applicationId)
    {
        $url = $this->talentServiceUrl . ExternalTalentController::$GET_MPP_URI;
        $body = array('id' => $requestId,'companyId' => $companyId,'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateMppRequest($requestId, $status, $companyId, $applicationId)
    {
        $url = $this->talentServiceUrl . ExternalTalentController::$UPDATE_MPP_URI;
        $body = array('id' => $requestId,'status' => $status,'companyId' => $companyId,'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }
}
