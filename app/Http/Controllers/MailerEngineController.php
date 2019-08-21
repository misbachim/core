<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonDao;
use App\Business\Dao\UM\UserDao;
use App\Business\Dao\WorklistDao;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Controller for handle wofkflow engine on ESS
 *
 * @package App\Http\Controllers
 */
class MailerEngineController extends Controller
{

    public function __construct(
        PersonDao $personDao,
        UserDao $userDao,
        WorklistDao $worklistDao,
        ExternalTimeController $externalTimeController,
        ExternalTravelController $externalTravelController
    )
    {
        $this->personDao = $personDao;
        $this->userDao = $userDao;
        $this->worklistDao = $worklistDao;
        $this->externalTimeController = $externalTimeController;
        $this->externalTravelController = $externalTravelController;
    }

    function mailRequest($requesterId, $approverId, $requestType, $id, $companyId)
    {
        //get worklist
        $currentWorklist = $this->worklistDao->getOne($id);
        $requestId = null;
        if ($currentWorklist) {
            $requestId = $currentWorklist->requestId;
        }

        //get approver name
        $aprroverPerson = $this->personDao->getOneEmployee($approverId);
        $aprroverEmail = $this->userDao->getEmailByPersonId($aprroverPerson->id);
        $approverName = $aprroverPerson->firstName . ' ' . $aprroverPerson->lastName;

        info('$aprroverPerson', [$aprroverPerson]);
        info('$aprroverEmail', [$aprroverEmail]);
        //get requester name
        $requesterPerson = $this->personDao->getOneEmployee($requesterId);
        $requesterName = $requesterPerson->firstName . ' ' . $requesterPerson->lastName;

        //send email to approver
        if ($aprroverEmail && $requestId !== null) {
            if ($requestType === 'LEAV') {
                //leave request
                $leave = $this->externalTimeController->getLeaveRequest($requestId, $companyId, 2);
                if ($leave) {
                    $this->sendLeaveRequestEmailToApprover($aprroverEmail->email, $requesterName, $approverName,
                        Carbon::parse($leave['detail'][0]['date'])->format('d-M-Y'),
                        Carbon::parse($leave['detail'][count($leave['detail']) - 1]['date'])->format('d-M-Y'),
                        $leave['description']
                    );
                }
            } else if ($requestType === 'PERM') {
                //permit request
                $permit = $this->externalTimeController->getPermitRequest($requestId, $companyId, 2);
                if ($permit) {
                    $this->sendPermitRequestEmailToApprover($aprroverEmail->email,
                        $requesterName,
                        $approverName,
                        Carbon::parse($permit['permissionDate'])->format('d-M-Y'),
                        $permit['reason']);
                }
            } else if ($requestType === 'OVER') {
                //overtime request
                $overtime = $this->externalTimeController->getOvertimeRequest($requestId, $companyId, 2);
                if ($overtime) {
                    $this->sendOvertimeRequestEmailToApprover($aprroverEmail->email, $requesterName, $approverName,
                        Carbon::parse($overtime['timeStart'])->format('d-M-Y H:i'),
                        Carbon::parse($overtime['timeEnd'])->format('d-M-Y H:i'),
                        $overtime['description']
                    );
                }
            } else if ($requestType === 'TRV') {
                //travel request
                $travel = $this->externalTravelController->getTravelRequest($requestId, $companyId, 2);
                if ($travel) {
                    $this->sendTravelRequestEmailToApprover($aprroverEmail->email, $requesterName, $approverName,
                        Carbon::parse($travel['departDate'])->format('d-M-Y'),
                        Carbon::parse($travel['returnDate'])->format('d-M-Y'),
                        $travel['travelPurpose']
                    );
                }
            } else if ($requestType === 'TRVX') {
                //travel expense
                $travelX = $this->externalTravelController->getTravelExpenseById($requestId, $companyId, 2);
                if ($travelX) {
                    $this->sendTravelExpenseRequestEmailToApprover($aprroverEmail->email, $requesterName, $approverName,
                        $travelX['travelExpenseName'],
                        $travelX['expenseAmount']
                    );
                }
            }

            return $this->renderResponse(new AppResponse(null, trans('messages.mailSent')));
        } else {
            return $this->renderResponse(new AppResponse(null, trans('messages.mailFailed')));
        }
    }

    function sendLeaveRequestEmailToApprover(string $to, string $requesterName, string $approverName, $startDate, $endDate, $description)
    {
        $this->sendEmail(
            'mailer_engine/leave_request',
            [
                'requesterPerson' => $requesterName,
                'approverPerson' => $approverName,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'description' => $description
            ],
            $to,
            'Leave Request from ' . $requesterName
        );
    }

    function sendPermitRequestEmailToApprover(string $to, string $requesterName, string $approverName, $permissionDate, $reason)
    {
        $this->sendEmail(
            'mailer_engine/permit_request',
            [
                'requesterPerson' => $requesterName,
                'approverPerson' => $approverName,
                'permissionDate' => $permissionDate,
                'reason' => $reason
            ],
            $to,
            'Permit Request from ' . $requesterName
        );
    }

    function sendOvertimeRequestEmailToApprover(string $to, string $requesterName, string $approverName, $startDate, $endDate, $description)
    {
        $this->sendEmail(
            'mailer_engine/overtime_request',
            [
                'requesterPerson' => $requesterName,
                'approverPerson' => $approverName,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'description' => $description
            ],
            $to,
            'Overtime Request from ' . $requesterName
        );
    }

    function sendTravelRequestEmailToApprover(string $to, string $requesterName, string $approverName, $startDate, $endDate, $purpose)
    {
        $this->sendEmail(
            'mailer_engine/travel_request',
            [
                'requesterPerson' => $requesterName,
                'approverPerson' => $approverName,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'purpose' => $purpose
            ],
            $to,
            'Travel Request from ' . $requesterName
        );
    }

    function sendTravelExpenseRequestEmailToApprover(string $to, string $requesterName, string $approverName, $name, $expenseAmount)
    {
        $this->sendEmail(
            'mailer_engine/travel_expense',
            [
                'requesterPerson' => $requesterName,
                'approverPerson' => $approverName,
                'name' => $name,
                'expenseAmount' => $expenseAmount
            ],
            $to,
            'Travel Expense Request from ' . $requesterName
        );
    }


    function sendEmail(string $templateViewName, array $data, string $to, string $subject)
    {
        Mail::send(
            $templateViewName,
            $data,
            function ($message) use ($to, $subject) {
                $message->from(
                    Config::get('mail.from.address'),
                    Config::get('mail.from.name')
                )
                    ->to($to)
                    ->subject($subject);
            }
        );
    }
}