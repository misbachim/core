<?php

namespace App\Http\Controllers;

use App\Business\Helper\HttpClient;
use App\Business\Model\Requester;

/**
 * Controller for communication with core microservice
 * @package App\Http\Controllers
 */
class ExternalTravelController extends Controller
{
    public static $GET_TRAVEL_REQUEST_URI = 'travelRequest/getOne';
    public static $UPDATE_TRAVEL_REQUEST_URI = 'travelRequest/update';
    public static $GET_TRAVEL_EXPENSE_URI = 'travelExpense/getAllByTravelRequestId';
    public static $GET_TRAVEL_EXPENSE_ONE_URI = 'travelExpense/getOne';
    public static $UPDATE_TRAVEL_EXPENSE_URI = 'travelExpense/updateStatus';

    private $travelServiceUrl;
    private $requester;

    public function __construct(Requester $requester)
    {
        $this->travelServiceUrl = env('TRAVEL_SERVICE_API');
        $this->requester = $requester;
    }

    public function getTravelRequest($requestId, $companyId, $applicationId)
    {
        $url = $this->travelServiceUrl . ExternalTravelController::$GET_TRAVEL_REQUEST_URI;
        $body = array('id' => $requestId, 'companyId' => $companyId, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateTravelRequest($requestId, $status, $applicationId)
    {
        $url = $this->travelServiceUrl . ExternalTravelController::$UPDATE_TRAVEL_REQUEST_URI;
        $body = array('id' => $requestId, 'status' => $status, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateTravelRequestCompanyId($requestId, $status, $applicationId, $companyId)
    {
        $url = $this->travelServiceUrl . ExternalTravelController::$UPDATE_TRAVEL_REQUEST_URI;
        $body = array('id' => $requestId, 'status' => $status, 'applicationId' => $applicationId, 'companyId' => $companyId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function getTravelExpense($requestId, $companyId, $applicationId)
    {
        $url = $this->travelServiceUrl . ExternalTravelController::$GET_TRAVEL_EXPENSE_URI;
        $body = array('travelRequestId' => $requestId, 'companyId' => $companyId, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function getTravelExpenseById($requestId, $companyId, $applicationId)
    {
        $url = $this->travelServiceUrl . ExternalTravelController::$GET_TRAVEL_EXPENSE_ONE_URI;
        $body = array('id' => $requestId, 'companyId' => $companyId, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateTravelExpense($requestId, $status, $applicationId)
    {
        $url = $this->travelServiceUrl . ExternalTravelController::$UPDATE_TRAVEL_EXPENSE_URI;
        $body = array('id' => $requestId, 'status' => $status, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateTravelExpenseCompanyId($requestId, $status, $applicationId, $companyId)
    {
        $url = $this->travelServiceUrl . ExternalTravelController::$UPDATE_TRAVEL_EXPENSE_URI;
        $body = array('id' => $requestId, 'status' => $status, 'applicationId' => $applicationId, 'companyId' => $companyId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }
}
