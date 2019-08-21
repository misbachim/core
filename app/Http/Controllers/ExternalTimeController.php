<?php

namespace App\Http\Controllers;

use App\Business\Helper\HttpClient;
use App\Business\Model\Requester;

/**
 * Controller for communication with core microservice
 * @package App\Http\Controllers
 */
class ExternalTimeController extends Controller
{
    public static $GET_LEAVE_REQUEST_URI = 'leaveRequest/getOne';
    public static $GET_PERMIT_REQUEST_URI = 'permissionRequest/getOne';
    public static $GET_OVERTIME_REQUEST_URI = 'overtimeRequest/getOne';
    public static $GET_ATTENDANCE_REQUEST_URI = 'requestRawTimesheet/getOne';
    public static $UPDATE_LEAVE_REQUEST_URI = 'leaveRequest/update';
    public static $UPDATE_PERMIT_REQUEST_URI = 'permissionRequest/update';
    public static $UPDATE_OVERTIME_REQUEST_URI = 'overtimeRequest/update';
    public static $UPDATE_ATTENDANCE_REQUEST_URI = 'requestRawTimesheet/update';
    public static $SAVE_WORKSHEET_URI = 'timeSheet/saveWorkSheet';
    public static $SAVE_RAWTIMESHEET_URI = 'timeSheet/saveRaw';

    private $timeServiceUrl;
    private $requester;

    public function __construct(Requester $requester)
    {
        $this->timeServiceUrl = env('TIME_SERVICE_API');
        $this->requester = $requester;
    }

    public function getLeaveRequest($requestId, $companyId, $applicationId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$GET_LEAVE_REQUEST_URI;
        $body = array('id' => $requestId, 'companyId' => $companyId, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function getPermitRequest($requestId, $companyId, $applicationId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$GET_PERMIT_REQUEST_URI;
        $body = array('id' => $requestId, 'companyId' => $companyId, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function getOvertimeRequest($requestId, $companyId, $applicationId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$GET_OVERTIME_REQUEST_URI;
        $body = array('id' => $requestId, 'companyId' => $companyId, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function getAttendanceRequest($requestId, $companyId, $applicationId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$GET_ATTENDANCE_REQUEST_URI;
        $body = array('id' => $requestId, 'companyId' => $companyId, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateLeaveRequest($requestId, $status, $applicationId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$UPDATE_LEAVE_REQUEST_URI;
        $body = array('id' => $requestId, 'status' => $status, 'upload' => false, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateLeaveRequestWithCompanyId($requestId, $status, $applicationId, $companyId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$UPDATE_LEAVE_REQUEST_URI;
        $body = array('id' => $requestId, 'status' => $status, 'upload' => false, 'applicationId' => $applicationId, 'companyId' => $companyId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }


    public function updatePermitRequest($requestId, $status, $applicationId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$UPDATE_PERMIT_REQUEST_URI;
        $body = array('id' => $requestId, 'status' => $status, 'upload' => false, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updatePermitRequestWithCompanyId($requestId, $status, $applicationId, $companyId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$UPDATE_PERMIT_REQUEST_URI;
        $body = array('id' => $requestId, 'status' => $status, 'upload' => false, 'applicationId' => $applicationId, 'companyId' => $companyId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateOvertimeRequest($requestId, $status, $applicationId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$UPDATE_OVERTIME_REQUEST_URI;
        $body = array('id' => $requestId, 'status' => $status, 'upload' => false, 'applicationId' => $applicationId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateOvertimeRequestWithCompanyId($requestId, $status, $applicationId, $companyId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$UPDATE_OVERTIME_REQUEST_URI;
        $body = array('id' => $requestId, 'status' => $status, 'upload' => false, 'applicationId' => $applicationId, 'companyId' => $companyId);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function updateAttendanceRequest($requestId, $status, $applicationId, $companyId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$UPDATE_ATTENDANCE_REQUEST_URI;
        $body = array('id' => $requestId, 'status' => $status, 'applicationId' => $applicationId, 'companyId' => $companyId);
        info('bodyAR', [$body]);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function saveWorksheet($attendance, $applicationId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$SAVE_WORKSHEET_URI;
        $body = array(
            'companyId' => $attendance['companyId'],
            'subEmployeeId' => $attendance['employeeId'],
            'date' => $attendance['date'],
            'timeStart' => $attendance['timeStart'],
            'timeEnd' => $attendance['timeEnd'],
            'description' => $attendance['description'],
            'description2' => $attendance['description2'],
            'activityValue1' => $attendance['value1'],
            'activityValue2' => $attendance['value2'],
            'activityCode' => $attendance['activityCode'],
            'applicationId' => $applicationId
        );
        info('bodyWS', [$body]);
        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }

    public function saveRawTimesheet($id, $rawTimesheet, $applicationId)
    {
        $url = $this->timeServiceUrl . ExternalTimeController::$SAVE_RAWTIMESHEET_URI;
        $body = array(
            'worksheetId' => $id,
            'companyId' => $rawTimesheet['companyId'],
            'subEmployeeId' => $rawTimesheet['employeeId'],
            'date' => $rawTimesheet['date'],
            'type' => $rawTimesheet['type'],
            'projectCode' => $rawTimesheet['projectCode'],
            'clockTimeLat' => $rawTimesheet['clockTimeLat'],
            'clockTimeLong' => $rawTimesheet['clockTimeLong'],
            'clockTime' => $rawTimesheet['clockTime'],
            'applicationId' => $applicationId
        );
        info('bodyRT', [$body]);

        $response = HttpClient::post($url, $body, $this->requester);

        return $response->data;
    }
}
