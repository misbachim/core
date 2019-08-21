<?php

namespace App\Http\Controllers;

use App\Business\Dao\ApprovalOrderDao;
use App\Business\Dao\AssignmentDao;
use App\Business\Dao\OrgStructureDao;
use App\Business\Dao\OrgStructureHierarchyDao;
use App\Business\Dao\PersonDao;
use App\Business\Dao\ProjectDao;
use App\Business\Dao\RequestAddressesDao;
use App\Business\Dao\RequestFamiliesDao;
use App\Business\Dao\RequestDocumentsDao;
use App\Business\Dao\UM\NotificationIdsDao;
use App\Business\Dao\UM\NotificationMessagesDao;
use App\Business\Dao\WorkflowDao;
use App\Business\Dao\WorklistDao;
use App\Business\Dao\EmployeeProjectDao;
use App\Business\Model\AppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;
use App\Business\Dao\Appraisal\ReviewProcessDao;

/**
 * Class for handling Autonumber process
 */
class WorklistController extends Controller
{
    public function __construct(
        Requester $requester,
        WorklistDao $worklistDao,
        WorkflowDao $workflowDao,
        ApprovalOrderDao $approvalOrderDao,
        AssignmentDao $assignmentDao,
        PersonDao $personDao,
        MailerEngineController $mailerEngineController,
        OrgStructureDao $orgStructureDao,
        OrgStructureHierarchyDao $orgStructureHierarchyDao,
        RequestFamiliesDao $requestFamiliesDao,
        RequestAddressesDao $requestAddressesDao,
        RequestAddressesController $requestAddressesController,
        RequestDocumentsController $requestDocumentsController,
        RequestDocumentsDao $requestDocumentsDao,
        ProjectDao $projectDao,
        ReviewProcessDao $reviewProcessDao,
        NotificationIdsDao $notificationIdsDao,
        NotificationMessagesDao $notificationMessagesDao,
        EmployeeProjectDao $employeeProjectDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->worklistDao = $worklistDao;
        $this->workflowDao = $workflowDao;
        $this->approvalOrderDao = $approvalOrderDao;
        $this->personDao = $personDao;
        $this->assignmentDao = $assignmentDao;
        $this->mailerEngineController = $mailerEngineController;
        $this->orgStructureDao = $orgStructureDao;
        $this->orgStructureHierarchyDao = $orgStructureHierarchyDao;
        $this->requestFamiliesDao = $requestFamiliesDao;
        $this->requestAddressesDao = $requestAddressesDao;
        $this->requestDocumentsDao = $requestDocumentsDao;
        $this->requestAddressesController = $requestAddressesController;
        $this->requestDocumentsController = $requestDocumentsController;
        $this->projectDao = $projectDao;
        $this->reviewProcessDao = $reviewProcessDao;
        $this->notificationIdsDao = $notificationIdsDao;
        $this->notificationMessagesDao = $notificationMessagesDao;
        $this->employeeProjectDao = $employeeProjectDao;
    }

    /**
     * Get all worklist
     * @param request
     */
    public function getWorklist(
        Request $request,
        ExternalTimeController $externalTimeController,
        ExternalAppraisalController $externalAppraisalController,
        ExternalPayrollController $externalPayrollController,
        ExternalTalentController $externalTalentController,
        RequestFamiliesController $requestFamiliesController,
        ExternalTravelController $externalTravelController
    ) {
        $this->validate($request, [
            "companyId" => "required|integer",
            "approverId" => "required",
            "state" => "required|max:1"
        ]);

        if ($request->state === 'H') {
            $worklist = $this->worklistDao->getAllWorklistAnswered($request->approverId);
        } else if ($request->state === 'R') {
            $worklist = $this->worklistDao->getAllWorklistResponse($request->approverId);
        } else if ($request->state === 'E') {
            $worklist = $this->worklistDao->getAllWorklistEscalation($request->approverId);
        } else {
            $worklist = $this->worklistDao->getAllWorklist($request->approverId);
        }
        $count = count($worklist);
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                if ($worklist[$i]->lovWfty === 'LEAV') {
                    $worklist[$i]->request = $externalTimeController->getLeaveRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'PERM') {
                    $worklist[$i]->request = $externalTimeController->getPermitRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'OVER') {
                    $worklist[$i]->request = $externalTimeController->getOvertimeRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'BENF') {
                    $worklist[$i]->request = $externalPayrollController->getBenefitClaim($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'MPP') {
                    $worklist[$i]->request = $externalTalentController->getMppRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'PROF' && $worklist[$i]->subType === 'family') {
                    $worklist[$i]->request = $requestFamiliesController->getOne($worklist[$i]->requesterId, $worklist[$i]->requestId);
                } else if ($worklist[$i]->lovWfty === 'PROF' && $worklist[$i]->subType === 'address') {
                    $worklist[$i]->request = $this->requestAddressesController->getOne($worklist[$i]->requesterId, $worklist[$i]->requestId);
                } else if ($worklist[$i]->lovWfty === 'PROF' && $worklist[$i]->subType === 'document') {
                    $worklist[$i]->request = $this->requestDocumentsController->getOne($worklist[$i]->requesterId, $worklist[$i]->requestId);
                } else if ($worklist[$i]->lovWfty === 'RFAR') {
                    $worklist[$i]->request = $externalAppraisalController->getAppraisalRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'TRV') {
                    $worklist[$i]->request = $externalTravelController->getTravelRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'TRVX') {
                    $worklist[$i]->request = $externalTravelController->getTravelExpenseById($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'ATTD') {
                    $worklist[$i]->request = $externalTimeController->getAttendanceRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'LOAN') {
                    $worklist[$i]->request = $externalPayrollController->getLoanRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                }
            }
        }

        $resp = new AppResponse($worklist, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getWorklistSubordinate(
        Request $request,
        ExternalTimeController $externalTimeController,
        ExternalAppraisalController $externalAppraisalController,
        ExternalPayrollController $externalPayrollController,
        ExternalTalentController $externalTalentController,
        RequestFamiliesController $requestFamiliesController,
        ExternalTravelController $externalTravelController
    ) {
        $this->validate($request, [
            "companyId" => "required|integer",
            "approverId" => "required",
            "typeRequest" => "present|string",
            "searchInfo" => "present|array",
            "pageInfo" => "present|array"
        ]);

        $request->merge((array) $request->searchInfo);
        $this->validate($request, [
            'status' => 'present|string',
            'requesterId' => 'present|string',
        ]);

        $request->merge((array) $request->pageInfo);
        $this->validate($request, [
            'offset' => 'present|integer',
            'limit' => 'present|integer',
        ]);

        $worklist = $this->worklistDao->getAllWorklistSubordinate($request->approverId, $request->typeRequest, $request->offset, $request->limit, $request->status, $request->requesterId, $request->selectedStartDate, $request->selectedEndDate);
        $count = count($worklist);
        info(print_r($worklist, true));
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {

                // get request
                if ($worklist[$i]->lovWfty === 'LEAV') {
                    $worklist[$i]->request = $externalTimeController->getLeaveRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'PERM') {
                    $worklist[$i]->request = $externalTimeController->getPermitRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'OVER') {
                    $worklist[$i]->request = $externalTimeController->getOvertimeRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'BENF') {
                    $worklist[$i]->request = $externalPayrollController->getBenefitClaim($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'MPP') {
                    $worklist[$i]->request = $externalTalentController->getMppRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'PROF' && $worklist[$i]->subType === 'family') {
                    $worklist[$i]->request = $requestFamiliesController->getOne($worklist[$i]->requesterId, $worklist[$i]->requestId);
                } else if ($worklist[$i]->lovWfty === 'PROF' && $worklist[$i]->subType === 'address') {
                    $worklist[$i]->request = $this->requestAddressesController->getOne($worklist[$i]->requesterId, $worklist[$i]->requestId);
                } else if ($worklist[$i]->lovWfty === 'PROF' && $worklist[$i]->subType === 'document') {
                    $worklist[$i]->request = $this->requestDocumentsController->getOne($worklist[$i]->requesterId, $worklist[$i]->requestId);
                } else if ($worklist[$i]->lovWfty === 'RFAR') {
                    $worklist[$i]->request = $externalAppraisalController->getAppraisalRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'TRV') {
                    $worklist[$i]->request = $externalTravelController->getTravelRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'TRVX') {
                    $worklist[$i]->request = $externalTravelController->getTravelExpenseById($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'ATTD') {
                    $worklist[$i]->request = $externalTimeController->getAttendanceRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                } else if ($worklist[$i]->lovWfty === 'LOAN') {
                    $worklist[$i]->request = $externalPayrollController->getLoanRequest($worklist[$i]->requestId, $this->requester->getCompanyId(), $request->applicationId);
                }
            }
        }

        //filter date
        if ($request->selectFilter == 'DATE') {
            $newWorklist = array();
            if ($count > 0) {
                for ($i = 0; $i < $count; $i++) {
                    if (count($worklist[$i]->request) > 0) {
                        if ($worklist[$i]->lovWfty == 'LEAV') {
                            foreach ($worklist[$i]->request['detail'] as $key => $valueRequest) {
                                if ($valueRequest['date'] >= $request->selectedStartDate && $valueRequest['date'] <= $request->selectedEndDate) {
                                    array_push($newWorklist, $worklist[$i]);
                                    break;
                                }
                            }
                        } else if ($worklist[$i]->lovWfty == 'ATTD') {
                            if ($worklist[$i]->request['date'] >= $request->selectedStartDate && $worklist[$i]->request['date'] <= $request->selectedEndDate) {
                                array_push($newWorklist, $worklist[$i]);
                            }
                        } else if ($worklist[$i]->lovWfty == 'PERM') {
                            if ($worklist[$i]->request['permissionDate'] >= $request->selectedStartDate && $worklist[$i]->request['permissionDate'] <= $request->selectedEndDate) {
                                array_push($newWorklist, $worklist[$i]);
                            }
                        }
                    }
                }
            }
            $worklist = $newWorklist;
        }

        $resp = new AppResponse($worklist, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function countGetWorklistSubordinate(Request $request)
    {

        $this->validate($request, [
            "companyId" => "required|integer",
            "approverId" => "required",
            "typeRequest" => "present|string",
            "searchInfo" => "present|array",
        ]);

        $request->merge((array) $request->searchInfo);
        $this->validate($request, [
            'status' => 'present|string',
            'requesterId' => 'present|string',
        ]);

        $data = new class
        { };

        $totalApprove = 0;
        $totalPending = 0;
        $totalReject = 0;
        $totalCancel = 0;

        $countWorklist = $this->worklistDao->getAllWorklistSubordinate($request->approverId, $request->typeRequest, 0, 0, '', $request->requesterId, $request->selectedStartDate, $request->selectedEndDate);
        if (count($countWorklist) > 0) {
            for ($i = 0; $i < count($countWorklist); $i++) {

                // get count answer
                if ($countWorklist[$i]->answer == 'A') {
                    $totalApprove++;
                } else if ($countWorklist[$i]->answer == null && $countWorklist[$i]->isActive == 1) {
                    $totalPending++;
                } else if ($countWorklist[$i]->answer == 'R') {
                    $totalReject++;
                } else if ($countWorklist[$i]->answer == 'C') {
                    $totalCancel++;
                }
            }
        }

        $data->totalRows = $this->worklistDao->countGetWorklistSubordinate($request->approverId, $request->typeRequest, $request->status, $request->requesterId);
        $data->totalApprove = $totalApprove;
        $data->totalCancel = $totalCancel;
        $data->totalReject = $totalReject;
        $data->totalPending = $totalPending;

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all worklist
     * @param request
     */
    // triger
    public function getWorklistByRequestId(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "requestId" => "required|integer"
        ]);


        $worklist = $this->worklistDao->getAllByRequestId($request->requestId);

        $count = count($worklist);
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $worklist[$i]->person = $this->assignmentDao->getOneByEmployeeId($worklist[$i]->approverId);
            }
        }


        $resp = new AppResponse($worklist, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all worklist by Request Id and Lov Wfty
     * @param request
     */
    public function getAllByRequestIdAndLovWfty(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "requestId" => "required|integer"
        ]);


        $worklist = $this->worklistDao->getAllByRequestIdAndLovWfty($request->requestId, $request->lovWfty);

        $count = count($worklist);
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $worklist[$i]->person = $this->assignmentDao->getOneByEmployeeId($worklist[$i]->approverId);
            }
        }


        $resp = new AppResponse($worklist, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all worklist Request Id,Lov Wfty and Description
     * @param request
     */
    public function getAllByRequestIdLovWftyAndDesc(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "requestId" => "required|integer"
        ]);


        $worklist = $this->worklistDao->getAllByRequestIdLovWftyAndDesc($request->requestId, $request->lovWft, $request->description);

        $count = count($worklist);
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $worklist[$i]->person = $this->assignmentDao->getOneByEmployeeId($worklist[$i]->approverId);
            }
        }


        $resp = new AppResponse($worklist, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one worklist based on work  id
     * @param Request $request
     * @return AppResponse
     */
    public function getOneWorklist(Request $request, ExternalTimeController $externalTimeController, ExternalAppraisalController $externalAppraisalController, ExternalPayrollController $externalPayrollController, ExternalTalentController $externalTalentController, RequestFamiliesController $requestFamiliesController, ExternalTravelController $externalTravelController)
    {
        $this->validate($request, [
            "id" => "required"
        ]);
        $worklist = $this->worklistDao->getOne($request->id);
        info('worklist ty', array($worklist->lovWfty));
        if ($worklist->lovWfty === 'LEAV') {
            $worklist->request = $externalTimeController->getLeaveRequest($worklist->requestId, $this->requester->getCompanyId(), $request->applicationId);
        } else if ($worklist->lovWfty === 'PERM') {
            $worklist->request = $externalTimeController->getPermitRequest($worklist->requestId, $this->requester->getCompanyId(), $request->applicationId);
        } else if ($worklist->lovWfty === 'OVER') {
            $worklist->request = $externalTimeController->getOvertimeRequest($worklist->requestId, $this->requester->getCompanyId(), $request->applicationId);
        } else if ($worklist->lovWfty === 'BENF') {
            $worklist->request = $externalPayrollController->getBenefitClaim($worklist->requestId, $this->requester->getCompanyId(), $request->applicationId);
        } else if ($worklist->lovWfty === 'MPP') {
            $worklist->request = $externalTalentController->getMppRequest($worklist->requestId, $this->requester->getCompanyId(), $request->applicationId);
        } else if ($worklist->lovWfty === 'PROF' && $worklist->subType === 'family') {
            $worklist->request = $requestFamiliesController->getOne($worklist->requesterId, $worklist->requestId);
        } else if ($worklist->lovWfty === 'PROF' && $worklist->subType === 'address') {
            $worklist->request = $this->requestAddressesController->getOne($worklist->requesterId, $worklist->requestId);
        } else if ($worklist->lovWfty === 'PROF' && $worklist->subType === 'document') {
            $worklist->request = $this->requestDocumentsController->getOne($worklist->requesterId, $worklist->requestId);
        } else if ($worklist->lovWfty === 'RFAR') {
            $worklist->request = $externalAppraisalController->getAppraisalRequest($worklist->requestId, $this->requester->getCompanyId(), $request->applicationId);
        } else if ($worklist->lovWfty === 'TRV') {
            $worklist->request = $externalTravelController->getTravelRequest($worklist->requestId, $this->requester->getCompanyId(), $request->applicationId);
        } else if ($worklist->lovWfty === 'TRVX') {
            $worklist->request = $externalTravelController->getTravelExpenseById($worklist->requestId, $this->requester->getCompanyId(), $request->applicationId);
        } else if ($worklist->lovWfty === 'ATTD') {
            $worklist->request = $externalTimeController->getAttendanceRequest($worklist->requestId, $this->requester->getCompanyId(), $request->applicationId);
        } else if ($worklist->lovWfty === 'LOAN') {
            $worklist->request = $externalPayrollController->getLoanRequest($worklist->requestId, $this->requester->getCompanyId(), $request->applicationId);
        }
        $resp = new AppResponse($worklist, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }


    /**
     * Generate Worklist
     * @param request
     */
    public function generateWorklist(Request $request, ExternalTimeController $externalTimeController)
    {
        $this->checkWorklistRequest($request);
        $workflowList = array();

        // EMPLOYEE
        $customEmployee = $this->workflowDao->getOneByWorkflowTypeAndEmployee($request->companyId, $request->workflowType, $request->requesterId);
        $approvalOrder = [];

        if ($customEmployee) {
            info('Custom Employee');
            info(print_r($customEmployee, true));
            $approvalOrder = $this->approvalOrderDao->getAllApprovalOrder($customEmployee->id, $request->companyId);
        } else {

            // LOCATION
            $location = $this->assignmentDao->getOneByEmployeeId($request->requesterId);
            info('Location Is :');
            info(print_r($location, true));
            $customLocation = $this->workflowDao->getOneByWorkflowTypeAndLocation($request->companyId, $request->workflowType, $location->locationCode);

            if ($customLocation) {

                info('Custom Location');
                info(print_r($customLocation, true));
                $approvalOrder = $this->approvalOrderDao->getAllApprovalOrder($customLocation->id, $request->companyId);
            } else {

                $customProject = null;

                //PROJECT
                if ($request->has('projectCode') && $request->projectCode) {

                    info('Project Is :');
                    info(print_r($request->projectCode, true));

                    $customProject = $this->workflowDao->getOneByWorkflowTypeAndProject($request->companyId, $request->workflowType, $request->projectCode);
                    info('Custom Project');
                    info(print_r($customProject, true));
                    if ($customProject) {
                        $approvalOrder = $this->approvalOrderDao->getAllApprovalOrder($customProject->id, $request->companyId);
                    }
                }

                if (!$customProject) {

                    // UNIT
                    $unit = $this->assignmentDao->getOneByEmployeeId($request->requesterId);

                    info('Unit Is :');
                    info(print_r($unit, true));

                    $customUnit = $this->workflowDao->getOneByWorkflowTypeAndUnit($request->companyId, $request->workflowType, $unit->unitCode);
                    if ($customUnit) {
                        info('Custom Unit');
                        info(print_r($customUnit, true));
                        $approvalOrder = $this->approvalOrderDao->getAllApprovalOrder($customUnit->id, $request->companyId);
                    } else {
                        // info('Default Is :');
                        $default = $this->workflowDao->getOneByWorkflowTypeDefault($request->companyId, $request->workflowType);
                        info(print_r($default, true));

                        if (count($default)) {
                            $approvalOrder = $this->approvalOrderDao->getAllApprovalOrder($default->id, $request->companyId);
                        }
                    }
                }
            }
        }
        info('$approvalOrder');
        info(print_r($approvalOrder, true));
        if ($approvalOrder !== []) {
            for ($i = 0; $i < count($approvalOrder); $i++) {
                if ($approvalOrder[$i]->lovWapt == 'STRC') {
                    $unitCode = $this->assignmentDao->getOneByEmployeeId($request->requesterId);
                    $orgStructureId = $this->orgStructureDao->getOnePrimary($request->companyId);
                    $orgStructureList = $this->orgStructureHierarchyDao->getWorkflow($request->companyId, $orgStructureId->id, $unitCode->unitCode);

                    $level = $approvalOrder[$i]->value;
                    info('Start STRC!');
                    Log::info(print_r($orgStructureList, true));
                    foreach ($orgStructureList as $orgStructure) {
                        if ($orgStructure->employee_id === $request->requesterId) {

                            // Process Menghilangkan {} pada string dan menghasilkan array path
                            $path = $orgStructure->path;
                            $ltrim = ltrim($path, '{');
                            $newPath = rtrim($ltrim, '}');

                            $arrayPath = explode(',', $newPath);
                            $newArrayPath = array();

                            for ($AP = count($arrayPath) - 1; $AP >= 0; $AP--) {
                                array_push($newArrayPath, $arrayPath[$AP]);
                            }

                            info('$newArrayPath', [$newArrayPath]);

                            $flag = 1;
                            foreach ($newArrayPath as $path) { // path nya harus mulai dari kanan (length -1)

                                if ($flag <= $level && $level !== 0) {

                                    // mendapatkan head of unit sesuai index path(unit)
                                    $getHou = $this->personDao->getHeadOfUnit($path);
                                    if ($getHou) {
                                        // jika yg request itu bukan head of unit
                                        if ($request->requesterId !== $getHou->employeeId) {
                                            $personStatus = $this->assignmentDao->getActivePersonLastAssignmentStatus($this->requester->getCompanyId(), $getHou->employeeId);
                                            // if person active and isn't terminated
                                            if (count($personStatus) > 0) {
                                                if ($personStatus->status !== 'TMT') {
                                                    $workflow = [
                                                        "tenant_id" => $this->requester->getTenantId(),
                                                        "company_id" => $this->requester->getCompanyId(),
                                                        "lov_wfty" => $request->workflowType,
                                                        "request_id" => $request->requestId,
                                                        "approver_id" => $getHou->employeeId,
                                                        "requester_id" => $request->requesterId,
                                                        "sub_type" => $request->subType,
                                                        "description" => $request->description,
                                                    ];
                                                    array_push($workflowList, $workflow);
                                                    $flag++;
                                                }
                                            }
                                        }
                                    } else {
                                        $flag++;
                                    }
                                } else {
                                    break;
                                }
                            }
                        }
                    }
                } else if ($approvalOrder[$i]->lovWapt == 'HEAD') {
                    $unitCode = $this->assignmentDao->getOneByEmployeeId($request->requesterId);
                    info('$unitCode', [$unitCode]);
                    $orgStructureId = $this->orgStructureDao->getOnePrimary($request->companyId);
                    info('$orgStructureId', [$orgStructureId]);
                    $orgStructureList = $this->orgStructureHierarchyDao->getWorkflow($request->companyId, $orgStructureId->id, $unitCode->unitCode);

                    $level = $approvalOrder[$i]->value;

                    foreach ($orgStructureList as $orgStructure) {
                        if ($orgStructure->employee_id === $request->requesterId) {

                            // Process Menghilangkan {} pada string dan menghasilkan array path
                            $path = $orgStructure->path;
                            $ltrim = ltrim($path, '{');
                            $newPath = rtrim($ltrim, '}');

                            $arrayPath = explode(',', $newPath);
                            $newArrayPath = array();

                            for ($AP = count($arrayPath) - 1; $AP >= 0; $AP--) {
                                array_push($newArrayPath, $arrayPath[$AP]);
                            }

                            $flag = 1;
                            foreach ($newArrayPath as $path) { // path nya harus mulai dari kanan (length -1)

                                if ($flag <= $level && $level !== 0) {

                                    // mendapatkan head of unit sesuai index path(unit)
                                    $getHou = $this->personDao->getHeadOfUnit($path);
                                    if ($getHou) {
                                        // jika yg request itu bukan head of unit
                                        if ($request->requesterId !== $getHou->employeeId) {
                                            $personStatus = $this->assignmentDao->getActivePersonLastAssignmentStatus($this->requester->getCompanyId(), $getHou->employeeId);
                                            // if person active and isn't terminated
                                            if (count($personStatus) > 0) {
                                                if ($personStatus->status !== 'TMT') {
                                                    if($flag == $level) {
                                                        $workflow = [
                                                            "tenant_id" => $this->requester->getTenantId(),
                                                            "company_id" => $this->requester->getCompanyId(),
                                                            "lov_wfty" => $request->workflowType,
                                                            "request_id" => $request->requestId,
                                                            "approver_id" => $getHou->employeeId,
                                                            "requester_id" => $request->requesterId,
                                                            "sub_type" => $request->subType,
                                                            "description" => $request->description,
                                                            "number" => $approvalOrder[$i]->number

                                                        ];
                                                        array_push($workflowList, $workflow);
                                                    }
                                                    $flag++;
                                                }
                                            }
                                        }
                                    } else {
                                        $flag++;
                                    }

                                } else {
                                    break;
                                }
                            }
                        }
                    }
                } else if ($approvalOrder[$i]->lovWapt == 'POST') {
                    $positionList = $this->assignmentDao->getAllEmployeeByPositionCode($approvalOrder[$i]->value);
                    for ($h = 0; $h < count($positionList); $h++) {
                        if ($positionList[$h]->employeeId !== $request->requesterId) {
                            $personStatus = $this->assignmentDao->getActivePersonLastAssignmentStatus($this->requester->getCompanyId(), $positionList[$h]->employeeId);
                            // if person active and isn't terminated
                            if (count($personStatus) > 0) {
                                if ($personStatus->status !== 'TMT') {
                                    $workflow = [
                                        "tenant_id" => $this->requester->getTenantId(),
                                        "company_id" => $this->requester->getCompanyId(),
                                        "lov_wfty" => $request->workflowType,
                                        "request_id" => $request->requestId,
                                        "approver_id" => $positionList[$h]->employeeId,
                                        "requester_id" => $request->requesterId,
                                        "sub_type" => $request->subType,
                                        "description" => $request->description,
                                    ];
                                    array_push($workflowList, $workflow);
                                }
                            }
                        }
                    }
                } else if ($approvalOrder[$i]->lovWapt == 'PROJ') {
                    if ($request->has('projectCode')) {
                        $projectList = $this->projectDao->getOne($request->projectCode);
                        if ($projectList) {
                            info('project', [$projectList]);
                            $approverId = null;
                            if ($approvalOrder[$i]->value === 'SPV') {
                                $approverId = (string) $projectList->supervisorId;
                            } else if ($approvalOrder[$i]->value === 'PM') {
                                $approverId = (string) $projectList->projectManagerId;
                            }
                            if ($approverId) {
                                if ($approverId !== $request->requesterId) {
                                    $personStatus = $this->assignmentDao->getActivePersonLastAssignmentStatus($this->requester->getCompanyId(), $approverId);
                                    // if person active and isn't terminated
                                    if (count($personStatus) > 0) {
                                        if ($personStatus->status !== 'TMT') {
                                            $workflow = [
                                                "tenant_id" => $this->requester->getTenantId(),
                                                "company_id" => $this->requester->getCompanyId(),
                                                "lov_wfty" => $request->workflowType,
                                                "request_id" => $request->requestId,
                                                "approver_id" => $approverId,
                                                "requester_id" => $request->requesterId,
                                                "sub_type" => null,
                                                "description" => null
                                            ];
                                            array_push($workflowList, $workflow);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else if ($approvalOrder[$i]->lovWapt == 'SPR') {
                    $assignment = $this->assignmentDao->getOneByEmployeeId($request->requesterId);
                    if ($assignment) {
                        info('assignment', [$assignment]);
                        if ($assignment->supervisorId !== $request->requesterId && $assignment->supervisorId !== null) {
                            $personStatus = $this->assignmentDao->getActivePersonLastAssignmentStatus($this->requester->getCompanyId(), $assignment->supervisorId);
                            // if person active and isn't terminated
                            if (count($personStatus) > 0) {
                                if ($personStatus->status !== 'TMT') {
                                    $workflow = [
                                        "tenant_id" => $this->requester->getTenantId(),
                                        "company_id" => $this->requester->getCompanyId(),
                                        "lov_wfty" => $request->workflowType,
                                        "request_id" => $request->requestId,
                                        "approver_id" => $assignment->supervisorId,
                                        "requester_id" => $request->requesterId,
                                        "sub_type" => null,
                                        "description" => null
                                    ];
                                    array_push($workflowList, $workflow);
                                }
                            }
                        }
                    }
                } else if ($approvalOrder[$i]->lovWapt == 'HEAD_LAMA') {
                    $unitCode = $this->assignmentDao->getOneByEmployeeId($request->requesterId);
                    $approverId = $this->personDao->getHeadOfUnit($unitCode->unitCode);
                    $workflow = [
                        "tenant_id" => $this->requester->getTenantId(),
                        "company_id" => $this->requester->getCompanyId(),
                        "lov_wfty" => $request->workflowType,
                        "request_id" => $request->requestId,
                        "approver_id" => (string) $approverId->employeeId,
                        "requester_id" => $request->rsub_typeequesterId,
                        "sub_type" => $request->subType,
                        "description" => $request->description,
                        //for appraisal
                        "number" => $approvalOrder[$i]->number
                    ];
                    array_push($workflowList, $workflow);
                } else if ($approvalOrder[$i]->lovWapt == 'SELF') {
                    $workflow = [
                        "tenant_id" => $this->requester->getTenantId(),
                        "company_id" => $this->requester->getCompanyId(),
                        "lov_wfty" => $request->workflowType,
                        "request_id" => $request->requestId,
                        "requester_id" => $request->requesterId,
                        "approver_id" => $request->requesterId,
                        "sub_type" => $request->subType,
                        "description" => $request->description,
                        //for appraisal
                        "number" => $approvalOrder[$i]->number
                    ];
                    array_push($workflowList, $workflow);
                } else if($approvalOrder[$i]->lovWapt == 'DHEAD') {

                    // mendapatkan head of unit sesuai dengan Direct Head Of Unit Code
                    $getHou = $this->personDao->getHeadOfUnit($approvalOrder[$i]->value);
                    if ($getHou) {
                        // jika yg request itu bukan head of unit
                        if ($request->requesterId !== $getHou->employeeId) {
                            $personStatus = $this->assignmentDao->getActivePersonLastAssignmentStatus($this->requester->getCompanyId(), $getHou->employeeId);

                            // if person active and isn't terminated
                            if ($personStatus) {
                                if ($personStatus->status !== 'TMT') {
                                    $workflow = [
                                        "tenant_id" => $this->requester->getTenantId(),
                                        "company_id" => $this->requester->getCompanyId(),
                                        "lov_wfty" => $request->workflowType,
                                        "request_id" => $request->requestId,
                                        "approver_id" => $getHou->employeeId,
                                        "requester_id" => $request->requesterId,
                                        "sub_type" => $request->subType,
                                        "description" => $request->description,
                                        "number" => $approvalOrder[$i]->number

                                    ];
                                    array_push($workflowList, $workflow);
                                }
                            }
                        }
                    }
                } else {
                    $personStatus = $this->assignmentDao->getActivePersonLastAssignmentStatus($this->requester->getCompanyId(), $approvalOrder[$i]->value);
                    // if person active and isn't terminated
                    if (count($personStatus) > 0) {
                        if ($personStatus->status !== 'TMT') {
                            $workflow = [
                                "tenant_id" => $this->requester->getTenantId(),
                                "company_id" => $this->requester->getCompanyId(),
                                "lov_wfty" => $request->workflowType,
                                "request_id" => $request->requestId,
                                "requester_id" => $request->requesterId,
                                "approver_id" => $approvalOrder[$i]->value,
                                "sub_type" => $request->subType,
                                "description" => $request->description,
                                //for appraisal
                                "number" => $approvalOrder[$i]->number
                            ];
                            array_push($workflowList, $workflow);
                        }
                    }
                }
            }
        }

        $result = array_values(array_unique($workflowList, SORT_REGULAR));
        $workflowSimulation = array();
        $resultMultipleRequests = array();
        $resultMultipleRequestReviewProcess = array();
        $notif = array();
        if ($request->has('reviewProcess')) {
            if ($request->inputByEss == true) {
                DB::transaction(function () use (&$result, &$resultMultipleRequestReviewProcess) {
                    $ordinal = 0;
                    for ($x = 0; $x < count($result); $x++) {
                        $ordinal++;
                        if ($result[$x]['number']) {
                            $ordinal = $result[$x]['number'];
                        }
                        $finalProcesReview = [
                            "tenant_id" => $result[$x]['tenant_id'],
                            "company_id" => $result[$x]['company_id'],
                            "review_request_id" => $result[$x]['request_id'],
                            "appraise_id" => $result[$x]['requester_id'],
                            "appraiser_id" => $result[$x]['approver_id'],
                            "ordinal" => $ordinal,
                            "status_assessment" => 'NEW',
                            // "status_approval" => 'P',
                            "status_approval" => 'O',
                            "score" => 0
                        ];
                        array_push($resultMultipleRequests, $finalProcesReview);
                        // $this->reviewProcessDao->saveReviewProcess($finalProcesReview);
                        // if ($ordinal === 1) {
                        //     //                            $this->mailerEngineController->mailRequest($result[$x]['requester_id'], $result[$x]['approver_id'], $result[$x]['lov_wfty']);
                        // }
                    }
                });
            } else {
                DB::transaction(function () use (&$result, &$resultMultipleRequests) {
                    $ordinal = 0;
                    for ($x = 0; $x < count($result); $x++) {
                        // Log::info('data', ['data' => $result[$x]]);
                        $ordinal++;
                        if ($result[$x]['number']) {
                            $ordinal = $result[$x]['number'];
                        }
                        $finalProcesReview = [
                            "tenant_id" => $result[$x]['tenant_id'],
                            "company_id" => $result[$x]['company_id'],
                            "review_request_id" => $result[$x]['request_id'],
                            "appraise_id" => $result[$x]['requester_id'],
                            "appraiser_id" => $result[$x]['approver_id'],
                            "ordinal" => $ordinal,
                            "status_assessment" => 'DRAFT',
                            // "status_approval" => 'A',
                            "status_approval" => 'O',
                            "score" => 0
                        ];
                        array_push($resultMultipleRequests, $finalProcesReview);
                        // $id = $this->reviewProcessDao->saveReviewProcess($finalProcesReview);
                        // if ($ordinal === 1) {
                        //     $this->mailerEngineController->mailRequest($result[$x]['requester_id'], $result[$x]['approver_id'], $result[$x]['lov_wfty'], $id, $this->requester->getCompanyId());
                        // }
                    }
                });
            }
        } else {
            DB::transaction(function () use (&$result, &$request, &$externalTimeController, &$externalPayrollController, &$workflowSimulation, &$resultMultipleRequests, &$notif) {
                $ordinal = 0;
                $id = null;
                if (count($result) > 0) {
                    for ($x = 0; $x < count($result); $x++) {
                        $ordinal++;
                        if ((string) $result[$x]['requester_id'] !== $result[$x]['approver_id']) {
                            if ($ordinal === 1) {
                                if ($request->has('activeFalse')) {
                                    $finalWorkflow = [
                                        "tenant_id" => $result[$x]['tenant_id'],
                                        "company_id" => $result[$x]['company_id'],
                                        "lov_wfty" => $result[$x]['lov_wfty'],
                                        "request_id" => $result[$x]['request_id'],
                                        "requester_id" => $result[$x]['requester_id'],
                                        "approver_id" => $result[$x]['approver_id'],
                                        "ordinal" => $ordinal,
                                        "is_active" => false,
                                        "sub_type" => $result[$x]['sub_type'],
                                        "description" => $result[$x]['description']
                                    ];
                                } else {
                                    $finalWorkflow = [
                                        "tenant_id" => $result[$x]['tenant_id'],
                                        "company_id" => $result[$x]['company_id'],
                                        "lov_wfty" => $result[$x]['lov_wfty'],
                                        "request_id" => $result[$x]['request_id'],
                                        "requester_id" => $result[$x]['requester_id'],
                                        "approver_id" => $result[$x]['approver_id'],
                                        "ordinal" => $ordinal,
                                        "is_active" => true,
                                        "sub_type" => $result[$x]['sub_type'],
                                        "description" => $result[$x]['description']
                                    ];
                                }

                                if ($request->has('isSimulation') && $request->isSimulation) {

                                    info('$finalWorkflow', $finalWorkflow);

                                    //                                    if($finalWorkflow) {
                                    array_push($workflowSimulation, $finalWorkflow);
                                    //                                    }

                                } else if ($request->has('isMultipleRequest') && $request->isMultipleRequest) {
                                    array_push($resultMultipleRequests, $finalWorkflow);
                                } else {
                                    $id = $this->worklistDao->save($finalWorkflow);

                                    // notification
                                    $person = $this->assignmentDao->getOneByEmployeeId($result[$x]['approver_id']);
                                    $notificationIdsData = $this->notificationIdsDao->getNotifByPersonId(
                                        $person->personId
                                    );
                                    $userIds = array_column($notificationIdsData, 'user_id');
                                    $notifIds = array_column($notificationIdsData, 'notif_id');
                                    $type = '';
                                    if ($result[$x]['lov_wfty'] === 'LEAV') {
                                        $type = 'Leave';
                                    } else if ($result[$x]['lov_wfty'] === 'OVER') {
                                        $type = 'Overtime';
                                    } else if ($result[$x]['lov_wfty'] === 'PERM') {
                                        $type = 'Permit';
                                    } else if ($result[$x]['lov_wfty'] === 'ATTD') {
                                        $type = 'Attendance';
                                    } else if ($result[$x]['lov_wfty'] === 'TRV') {
                                        $type = 'Travel';
                                    } else if ($result[$x]['lov_wfty'] === 'TRVX') {
                                        $type = 'Travel Expense';
                                    } else if ($result[$x]['lov_wfty'] === 'RFAR') {
                                        $type = 'Review Form';
                                    }
                                    $notifMessageId = $this->notificationMessagesDao->save(
                                        'R',
                                        $type . ' Request ' . Carbon::now()->format('d M Y'),
                                        'You have pending approval ' . $type . ' request on ' . Carbon::now()->format('d M Y'),
                                        $userIds,
                                        'subordinate-request'
                                    );
                                    $notif['notifIds'] = $notifIds;
                                    $notif['notifMessageId'] = $notifMessageId;

                                    info('$notifIds', [$notifIds]);
                                    info('$notifMessageId', [$notifMessageId]);

                                    //email
                                    $this->mailerEngineController->mailRequest($result[$x]['requester_id'], $result[$x]['approver_id'], $result[$x]['lov_wfty'], $id, $this->requester->getCompanyId());
                                }
                            } else {
                                $finalWorkflow = [
                                    "tenant_id" => $result[$x]['tenant_id'],
                                    "company_id" => $result[$x]['company_id'],
                                    "lov_wfty" => $result[$x]['lov_wfty'],
                                    "request_id" => $result[$x]['request_id'],
                                    "requester_id" => $result[$x]['requester_id'],
                                    "approver_id" => $result[$x]['approver_id'],
                                    "ordinal" => $ordinal,
                                    "is_active" => false,
                                    "sub_type" => $result[$x]['sub_type'],
                                    "description" => $result[$x]['description']
                                ];
                                if ($request->has('isSimulation') && $request->isSimulation) {

                                    array_push($workflowSimulation, $finalWorkflow);
                                } else if ($request->has('isMultipleRequest') && $request->isMultipleRequest) {
                                    array_push($resultMultipleRequests, $finalWorkflow);
                                } else {
                                    $this->worklistDao->save($finalWorkflow);
                                }
                            }
                        }
                    }
                } else {
                    if ($ordinal > 0) {
                        $finalWorkflow = [
                            "tenant_id" => $this->requester->getTenantId(),
                            "company_id" => $this->requester->getCompanyId(),
                            "lov_wfty" => $request->workflowType,
                            "request_id" => $request->requestId,
                            "requester_id" => $request->requesterId,
                            "approver_id" => $request->requesterId,
                            "ordinal" => $ordinal,
                            "is_active" => false,
                            "answer" => 'A',
                            "sub_type" => $request->subType,
                            "description" => $request->description
                        ];

                        if ($request->has('isSimulation') && $request->isSimulation) {
                            array_push($workflowSimulation, $finalWorkflow);
                        } else if ($request->has('isMultipleRequest') && $request->isMultipleRequest) {
                            array_push($resultMultipleRequests, $finalWorkflow);
                        } else {
                            $id = $this->worklistDao->save($finalWorkflow);
                            $currentWorklist = $this->worklistDao->getOne($id);
                            $nextWorklist = $this->worklistDao->getNextWorklist($currentWorklist->ordinal, $currentWorklist->requestId);
                            if ($nextWorklist === null) {
                                if ($currentWorklist->lovWfty === 'LEAV') {
                                    $externalTimeController->updateLeaveRequestWithCompanyId($currentWorklist->requestId, 'A', 2, $this->requester->getCompanyId());
                                } else if ($currentWorklist->lovWfty === 'PERM') {
                                    $externalTimeController->updatePermitRequestWithCompanyId($currentWorklist->requestId, 'A', 2, $this->requester->getCompanyId());
                                } else if ($currentWorklist->lovWfty === 'OVER') {
                                    $externalTimeController->updateOvertimeRequestWithCompanyId($currentWorklist->requestId, 'A', 2, $this->requester->getCompanyId());
                                } else if ($currentWorklist->lovWfty === 'ATTD') {
                                    $externalTimeController->updateAttendanceRequest($currentWorklist->requestId, 'A', $request->applicationId, $this->requester->getCompanyId());
                                } else if ($currentWorklist->lovWfty === 'LOAN') {
                                    $externalPayrollController->updateLoanRequest($currentWorklist->requestId, 'A', $this->requester->getCompanyId(), $request->applicationId);
                                }
                            }
                        }
                    } else {
                        if ($request->has('isSimulation') && $request->isSimulation) {
                            // info('isSimulation in Ordinal > 0 Else',[]);
                        } else {
                            if ($request->workflowType === 'LEAV') {
                                $externalTimeController->updateLeaveRequestWithCompanyId($request->requestId, 'A', 2, $this->requester->getCompanyId());
                            } else if ($request->workflowType === 'PERM') {
                                $externalTimeController->updatePermitRequestWithCompanyId($request->requestId, 'A', 2, $this->requester->getCompanyId());
                            } else if ($request->workflowType === 'OVER') {
                                $externalTimeController->updateOvertimeRequestWithCompanyId($request->requestId, 'A', 2, $this->requester->getCompanyId());
                            } else if ($request->workflowType === 'ATTD') {
                                $externalTimeController->updateAttendanceRequest($request->requestId, 'A', $request->applicationId, $this->requester->getCompanyId());
                            } else if ($request->workflowType === 'LOAN') {
                                $externalPayrollController->updateLoanRequest($request->requestId, 'A', $this->requester->getCompanyId(), $request->applicationId);
                            }
                        }
                    }
                }
            });
        }

        if ($request->has('isSimulation') && $request->isSimulation) {

            $count = count($workflowSimulation);
            if ($count > 0) {
                for ($i = 0; $i < $count; $i++) {
                    $workflowSimulation[$i]['person'] = $this->assignmentDao->getOneByEmployeeId($workflowSimulation[$i]['approver_id']);
                }
            }

            info('workflowSimulation');
            info(print_r($workflowSimulation, true));

            $resp = new AppResponse($workflowSimulation, trans('messages.allDataRetrieved'));
        } else if ($request->has('isMultipleRequest') && $request->isMultipleRequest) {
            $resp = new AppResponse($resultMultipleRequests, trans('messages.allDataRetrieved'));
        } else {
            $resp = new AppResponse($notif, trans('messages.dataSaved'));
        }

        return $this->renderResponse($resp);
    }

    /**
     * Save Worklist
     * @param request
     */
    public
    function save(Request $request)
    {

        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id'
        ]);

        DB::transaction(function () use (&$request) {

            $constructWorkList = [
                "tenant_id" => $this->requester->getTenantId(),
                "company_id" => $request->companyId,
                "lov_wfty" => $request->lovWfty,
                "request_id" => $request->requestId,
                "ordinal" => $request->ordinal,
                "requester_id" => $request->requesterId,
                "approver_id" => $request->approverId,
                "answer" => $request->answer,
                "is_active" => $request->isActive
            ];

            $this->worklistDao->save($constructWorkList);
        });

        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Multiple Save Worklist
     * @param request
     */
    public
    function multipleSave(Request $request)
    {
        DB::transaction(function () use (&$request) {
            $constructData = array();
            foreach ($request->listOfWorklist as $key => $valueData) {
                $this->validate($request, [
                    'companyId' => 'required|integer|exists:companies,id'
                ]);
                // $constructWorkList = array();
                $constructWorkList = [
                    "tenant_id" => $this->requester->getTenantId(),
                    "company_id" => $request->companyId,
                    "lov_wfty" => $valueData['lov_wfty'],
                    "request_id" => $valueData['request_id'],
                    "ordinal" => $valueData['ordinal'],
                    "requester_id" => $valueData['requester_id'],
                    "approver_id" => $valueData['approver_id'],
                    "answer" => (isset($valueData['answer'])) ? $valueData['answer'] : "",
                    "is_active" => $valueData['is_active']
                ];
                array_push($constructData, $constructWorkList);
            }
            $this->worklistDao->multipleSave($constructData);
        });

        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Answer Request Worklist
     * @param request
     */

    public
    function answerRequest(Request $request, ExternalTimeController $externalTimeController, ExternalPayrollController $externalPayrollController, ExternalTalentController $externalTalentController, RequestFamiliesController $requestFamiliesController, ExternalAppraisalController $externalAppraisalController, ExternalTravelController $externalTravelController)
    {
        info('request', array($request));
        $auth = $request->headers->get('authorization');
        $origin = $request->headers->get('origin');
        $this->checkAnswerRequest($request);
        $notif = array();
        DB::transaction(function () use (&$request, &$externalAppraisalController, &$externalTimeController, &$externalPayrollController, &$externalTalentController, &$requestFamiliesController, &$externalTravelController, &$auth, &$origin) {
            $currentWorklist = $this->worklistDao->getOne($request->workflowId);
            $answer = [
                "answer" => $request->answer,
                "notes" => $request->notes,
                "is_active" => false
            ];
            $this->worklistDao->update($request->workflowId, $answer);
            $nextWorklist = $this->worklistDao->getNextWorklist($currentWorklist->ordinal, $currentWorklist->requestId);

            if ($request->answer === 'A' || $request->answer === 'AA') {
                if ($nextWorklist !== null) {
                    $active = [
                        "is_active" => true
                    ];
                    $this->worklistDao->update($nextWorklist->id, $active);

                    // notification
                    $person = $this->assignmentDao->getOneByEmployeeId($nextWorklist->approverId);
                    $notificationIdsData = $this->notificationIdsDao->getNotifByPersonId(
                        $person->personId
                    );
                    $userIds = array_column($notificationIdsData, 'user_id');
                    $notifIds = array_column($notificationIdsData, 'notif_id');
                    $type = '';
                    if ($nextWorklist->lovWfty === 'LEAV') {
                        $type = 'Leave';
                    } else if ($nextWorklist->lovWfty === 'OVER') {
                        $type = 'Overtime';
                    } else if ($nextWorklist->lovWfty === 'PERM') {
                        $type = 'Permit';
                    } else if ($nextWorklist->lovWfty === 'ATTD') {
                        $type = 'Attendance';
                    } else if ($nextWorklist->lovWfty === 'TRV') {
                        $type = 'Travel';
                    } else if ($nextWorklist->lovWfty === 'TRVX') {
                        $type = 'Travel Expense';
                    }
                    $notifMessageId = $this->notificationMessagesDao->save(
                        'R',
                        $type . ' Request ' . Carbon::now()->format('d M Y'),
                        'You have pending approval ' . $type . ' request on ' . Carbon::now()->format('d M Y'),
                        $userIds,
                        'subordinate-request'
                    );

                    $notif['notifIds'] = $notifIds;
                    $notif['notifMessageId'] = $notifMessageId;

                    //email
                    $this->mailerEngineController->mailRequest($nextWorklist->requesterId, $nextWorklist->approverId, $nextWorklist->lovWfty, $nextWorklist->id, $this->requester->getCompanyId());
                } else {
                    // when requests were approved
                    if ($currentWorklist->lovWfty === 'LEAV') {
                        $externalTimeController->updateLeaveRequestWithCompanyId($currentWorklist->requestId, 'A', $request->applicationId, $this->requester->getCompanyId());
                    } else if ($currentWorklist->lovWfty === 'PERM') {
                        $externalTimeController->updatePermitRequestWithCompanyId($currentWorklist->requestId, 'A', $request->applicationId, $this->requester->getCompanyId());
                    } else if ($currentWorklist->lovWfty === 'OVER') {
                        $externalTimeController->updateOvertimeRequestWithCompanyId($currentWorklist->requestId, 'A', $request->applicationId, $this->requester->getCompanyId());
                    } else if ($currentWorklist->lovWfty === 'BENF') {
                        $externalPayrollController->updateBenefitClaim($currentWorklist->requestId, 'A', $this->requester->getCompanyId(), $request->applicationId);
                    } else if ($currentWorklist->lovWfty === 'MPP') {
                        $externalTalentController->updateMppRequest($currentWorklist->requestId, 'A', $this->requester->getCompanyId(), $request->applicationId);
                    } else if ($currentWorklist->lovWfty === 'PROF' && $currentWorklist->subType === 'family') {
                        $requestFamiliesController->update($currentWorklist->requesterId, $currentWorklist->requestId);
                    } else if ($currentWorklist->lovWfty === 'PROF' && $currentWorklist->subType === 'address') {
                        $this->requestAddressesController->update($currentWorklist->requesterId, $currentWorklist->requestId);
                    } else if ($currentWorklist->lovWfty === 'PROF' && $currentWorklist->subType === 'document') {
                        $this->requestDocumentsController->update($currentWorklist->requesterId, $currentWorklist->requestId, $origin, $auth);
                    } else if ($currentWorklist->lovWfty === 'RFAR') {
                        $externalAppraisalController->updateAppraisalRequest($currentWorklist->requestId, 'A', $request->applicationId);
                    } else if ($currentWorklist->lovWfty === 'TRV') {
                        $externalTravelController->updateTravelRequestCompanyId($currentWorklist->requestId, 'A', $request->applicationId, $this->requester->getCompanyId());
                        $travelExpense = $externalTravelController->getTravelExpense($currentWorklist->requestId, $this->requester->getCompanyId(), $request->applicationId);

                        for ($i = 0; $i < count($travelExpense); $i++) {
                            //                            $request->workflowType = 'TRVX';
                            //                            $request->tenantId = $travelExpense[$i]['tenantId'];
                            //                            $request->requestId = $travelExpense[$i]['id'];
                            //                            $request->requesterId = $travelExpense[$i]['employeeId'];
                            //                            $request->subType = '';
                            //                            $request->description = '';

                            $request = new \Illuminate\Http\Request();
                            $request->setMethod('POST');
                            $request->request->add([
                                'workflowType' => 'TRVX',
                                'tenantId' => $travelExpense[$i]['tenantId'],
                                'companyId' => $travelExpense[$i]['companyId'],
                                'requestId' => $travelExpense[$i]['id'],
                                'requesterId' => $travelExpense[$i]['employeeId'],
                                'subType' => '',
                                'description' => ''
                            ]);

                            info('expensesss', array($request));

                            $this->generateWorklist($request, $externalTimeController);
                        }
                    } else if ($currentWorklist->lovWfty === 'TRVX') {
                        $externalTravelController->updateTravelExpenseCompanyId($currentWorklist->requestId, 'A', $request->applicationId, $this->requester->getCompanyId());
                    } else if ($currentWorklist->lovWfty === 'ATTD') {
                        //update status request
                        $externalTimeController->updateAttendanceRequest($currentWorklist->requestId, 'A', $request->applicationId, $this->requester->getCompanyId());
                        //insert worksheets dan rawtimesheets
                        $requestRawTimesheet = $externalTimeController->getAttendanceRequest($currentWorklist->requestId, $this->requester->getCompanyId(), $request->applicationId);
                        if ($requestRawTimesheet) {
                            $data = array();
                            info('rts', [$requestRawTimesheet]);
                            $attendance = [
                                'companyId' => $this->requester->getCompanyId(),
                                'employeeId' => $requestRawTimesheet['employeeId'],
                                'date' => $requestRawTimesheet['date'],
                                'timeStart' => $requestRawTimesheet['timeIn'],
                                'timeEnd' => $requestRawTimesheet['timeOut'],
                                'description' => $requestRawTimesheet['description'],
                                'description2' => $requestRawTimesheet['description2'],
                                'value1' => $requestRawTimesheet['value1'],
                                'value2' => $requestRawTimesheet['value2'],
                                'activityCode' => $requestRawTimesheet['activityCode'],
                            ];
                            $clockIn = [
                                'companyId' => $this->requester->getCompanyId(),
                                'employeeId' => $requestRawTimesheet['employeeId'],
                                'date' => $requestRawTimesheet['date'],
                                'type' => 'I',
                                'projectCode' => $requestRawTimesheet['projectCode'],
                                'clockTimeLat' => $requestRawTimesheet['timeInLat'],
                                'clockTimeLong' => $requestRawTimesheet['timeInLong'],
                                'clockTime' => $requestRawTimesheet['timeIn']
                            ];
                            $clockOut = [
                                'companyId' => $this->requester->getCompanyId(),
                                'employeeId' => $requestRawTimesheet['employeeId'],
                                'date' => $requestRawTimesheet['date'],
                                'type' => 'O',
                                'projectCode' => $requestRawTimesheet['projectCode'],
                                'clockTimeLat' => $requestRawTimesheet['timeOutLat'],
                                'clockTimeLong' => $requestRawTimesheet['timeOutLong'],
                                'clockTime' => $requestRawTimesheet['timeOut']
                            ];
                            $data = $externalTimeController->saveWorksheet($attendance, $request->applicationId);
                            $externalTimeController->saveRawTimesheet($data['id'], $clockIn, $request->applicationId);
                            $externalTimeController->saveRawTimesheet($data['id'], $clockOut, $request->applicationId);
                        }
                    } else if ($currentWorklist->lovWfty === 'LOAN') {
                        $externalPayrollController->updateLoanRequest($currentWorklist->requestId, 'A', $this->requester->getCompanyId(), $request->applicationId);
                    }
                }
            } else {
                // when requests were rejected
                if ($currentWorklist->lovWfty === 'LEAV') {
                    $externalTimeController->updateLeaveRequestWithCompanyId($currentWorklist->requestId, 'R', $request->applicationId, $this->requester->getCompanyId());
                } else if ($currentWorklist->lovWfty === 'PERM') {
                    $externalTimeController->updatePermitRequestWithCompanyId($currentWorklist->requestId, 'R', $request->applicationId, $this->requester->getCompanyId());
                } else if ($currentWorklist->lovWfty === 'OVER') {
                    $externalTimeController->updateOvertimeRequestWithCompanyId($currentWorklist->requestId, 'R', $request->applicationId, $this->requester->getCompanyId());
                } else if ($currentWorklist->lovWfty === 'BENF') {
                    $externalPayrollController->updateBenefitClaim($currentWorklist->requestId, 'R', $this->requester->getCompanyId(), $request->applicationId);
                } else if ($currentWorklist->lovWfty === 'MPP') {
                    $externalTalentController->updateMppRequest($currentWorklist->requestId, 'R', $this->requester->getCompanyId(), $request->applicationId);
                } else if ($currentWorklist->lovWfty === 'PROF' && $currentWorklist->subType === 'family') {
                    $requestFamiliesController->delete($currentWorklist->requesterId, $currentWorklist->requestId);
                } else if ($currentWorklist->lovWfty === 'PROF' && $currentWorklist->subType === 'address') {
                    $this->requestAddressesController->delete($currentWorklist->requesterId, $currentWorklist->requestId);
                } else if ($currentWorklist->lovWfty === 'PROF' && $currentWorklist->subType === 'document') {
                    $this->requestDocumentsController->delete($currentWorklist->requesterId, $currentWorklist->requestId);
                } else if ($currentWorklist->lovWfty === 'RFAR') {
                    $externalAppraisalController->updateAppraisalRequest($currentWorklist->requestId, 'R', $request->applicationId);
                } else if ($currentWorklist->lovWfty === 'TRV') {
                    $externalTravelController->updateTravelRequestCompanyId($currentWorklist->requestId, 'R', $request->applicationId, $this->requester->getCompanyId());
                } else if ($currentWorklist->lovWfty === 'TRVX') {
                    $externalTravelController->updateTravelExpenseCompanyId($currentWorklist->requestId, 'R', $request->applicationId, $this->requester->getCompanyId());
                } else if ($currentWorklist->lovWfty === 'ATTD') {
                    $externalTimeController->updateAttendanceRequest($currentWorklist->requestId, 'R', $request->applicationId, $this->requester->getCompanyId());
                } else if ($currentWorklist->lovWfty === 'LOAN') {
                    $externalPayrollController->updateLoanRequest($currentWorklist->requestId, 'R', $this->requester->getCompanyId(), $request->applicationId);
                }
            }
        });

        $resp = new AppResponse($notif, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Forward Request Worklist
     * @param request
     */

    public
    function forwardRequest(Request $request)
    {
        $this->checkForwardRequest($request);
        DB::transaction(function () use (&$request) {
            $currentWorklist = $this->worklistDao->getOne($request->workflowId);
            $answer = [
                "answer" => "F",
                "is_active" => false
            ];
            $this->worklistDao->update($request->workflowId, $answer);

            $newApprover = [
                "tenant_id" => $this->requester->getTenantId(),
                "company_id" => $this->requester->getCompanyId(),
                "lov_wfty" => $currentWorklist->lovWfty,
                "request_id" => $currentWorklist->requestId,
                "approver_id" => $request->approverId,
                "requester_id" => $currentWorklist->requesterId,
                "ordinal" => $currentWorklist->ordinal + 0.1,
                "is_active" => true
            ];
            $this->worklistDao->save($newApprover);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate check workflow request.
     * @param request
     */
    private
    function checkWorklistRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'workflowType' => 'required|max:10',
            'requestId' => 'required|integer',
            'requesterId' => 'required|max:20'
        ]);
    }

    /**
     * Validate check answer request.
     * @param request
     */
    private
    function checkAnswerRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'workflowId' => 'required|integer',
            'answer' => 'required|max:1'
        ]);
    }

    /**
     * Validate check forward request.
     * @param request
     */
    private
    function checkForwardRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'workflowId' => 'required|integer',
            'approverId' => 'required|max:20'
        ]);
    }

    /**
     * Change Requester
     * @param request
     */

    public
    function changeRequester($travelExpense)
    {
        for ($i = 0; $i < count($travelExpense); $i++) {
            //                            $request->workflowType = 'TRVX';
            //                            $dataReq = $request->all();
            //                            $dataReq['workflowType'] = 'TRVX';
            //                            $request->merge(['workflowType' => 'TRVX']);
            $expenseReq = new \Illuminate\Http\Request();
            $expenseReq->setMethod('POST');
            info('$expenseReq', array($expenseReq));

            $expenseReq->request->add([
                'workflowType' => 'TRVX',
                'tenantId' => $travelExpense[$i]['tenantId']
            ]);
            //            $expenseReq->replace([
            //                'workflowType' => 'TRVX',
            //                'tenantId' => $travelExpense[$i]['tenantId']
            //            ]);
            info('expensesss1', array($expenseReq));
            //                            $expenses = [
            //                                'workflowType' => 'TRVX',
            //                                'tenantId' => $travelExpense[$i]['tenantId'],
            //                                'companyId' => $travelExpense[$i]['companyId'],
            //                                'requestId' => $travelExpense[$i]['id'],
            //                                'requesterId' => $travelExpense[$i]['employeeId'],
            //                                'subType' => '',
            //                                'description' => ''
            //                            ];
            //
            //
            //                            $req = Request::create('/worklist/generateWorklist', 'POST', $expenses );
            //                            $res = app()->handle($req);
            //                            $respBody = json_decode($res->getContent(), true);
            //                            $response = Route::dispatch( $request );

            //                            $this->request = Request::create('/worklist/generateWorklist', 'POST', array(), null, array(), $server);
            //                            $this->request->headers->add($headers);
            info('expensesss', array($expenseReq));

            $this->generateWorklist($expenseReq, ExternalTimeController);
        }
    }

    /**
     * Update Status Worklist to DB
     * @param request
     */
    public function updateStatus(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer"
        ]);

        DB::transaction(function () use (&$request, &$data) {
            $worklist = $this->constructWorklistStatus($request);
            $this->worklistDao->updateStatus($request->id, $request->lovWfty, $worklist);
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    /**
     * Construct an Worklist object for Workflow & Worklist update (array).
     * @param request
     */
    private function constructWorklistStatus($request)
    {
        $data = [
            'is_active' => $request->isActive,
            'answer' => $request->answer,
            'notes' => $request->notes
        ];
        return $data;
    }
}
