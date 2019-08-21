<?php

namespace App\Http\Controllers;

use App\Business\Helper\HttpClient;
use App\Business\Model\Requester;

/**
 * Controller for communication with core microservice
 * @package App\Http\Controllers
 */
class ExternalPayrollController extends Controller
{
    public static $GET_BENEFIT_CLAIM_URI = 'benefitClaim/getOne';
    public static $UPDATE_BENEFIT_CLAIM_URI = 'benefitClaim/updateRequest';
    public static $GET_LOAN_REQUEST_URI = 'loanRequest/getOne';
    public static $UPDATE_LOAN_REQUEST_URI = 'loanRequest/update';

    private $payrollServiceUrl;
    private $requester;

    public function __construct(Requester $requester)
    {
        $this->payrollServiceUrl = env('PAYROLL_SERVICE_API');
        $this->requester = $requester;
    }

    public function getBenefitClaim($requestId, $companyId, $applicationId)
    {
        $url = $this->payrollServiceUrl . ExternalPayrollController::$GET_BENEFIT_CLAIM_URI;
        $body = array('id' => $requestId,'companyId' => $companyId,'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateBenefitClaim($requestId, $status, $companyId, $applicationId)
    {
        $url = $this->payrollServiceUrl . ExternalPayrollController::$UPDATE_BENEFIT_CLAIM_URI;
        $body = array('id' => $requestId,'status' => $status,'companyId' => $companyId,'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function getLoanRequest($requestId, $companyId, $applicationId) {
        $url = $this->payrollServiceUrl . ExternalPayrollController::$GET_LOAN_REQUEST_URI;
        $body = array('id' => $requestId,'companyId' => $companyId,'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateLoanRequest($requestId, $requestStatus, $companyId, $applicationId) {
        $url = $this->payrollServiceUrl . ExternalPayrollController::$UPDATE_LOAN_REQUEST_URI;
        $body = array('id' => $requestId,'requestStatus' => $requestStatus,'companyId' => $companyId,'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }
}
