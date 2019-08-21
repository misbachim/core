<?php

namespace App\Http\Controllers;

use App\Business\Dao\AssignmentDao;
use App\Business\Dao\AssignmentTransactionDao;
use App\Business\Dao\PersonDao;
use App\Business\Dao\PositionSlotDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use App\Business\Model\PagingAppResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;

/**
 * Class for handling assignment process
 * @property Requester requester
 * @property AssignmentDao assignmentDao
 * @property AssignmentTransactionDao assignmentTransactionDao
 * @property PositionSlotDao positionSlotDao
 * @property PersonDao personDao
 */
class AssignmentController extends Controller
{
    public function __construct(
        Requester $requester,
        AssignmentDao $assignmentDao,
        AssignmentTransactionDao $assignmentTransactionDao,
        PositionSlotDao $positionSlotDao,
        PersonDao $personDao
    )
    {
        parent::__construct();

        $this->requester = $requester;
        $this->assignmentDao = $assignmentDao;
        $this->assignmentTransactionDao = $assignmentTransactionDao;
        $this->positionSlotDao = $positionSlotDao;
        $this->personDao = $personDao;
    }

    /**
     * Get all assignments for one tenant
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'personId' => 'required'
        ]);
        $assignments = $this->assignmentDao->getAll(
            $request->companyId,
            $request->personId
        );
        $assignments->firstAssignment = $this->assignmentDao->getFirstAssignment($request->personId);

        $resp = new AppResponse($assignments, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /*
    |------------------------------------------------------------
    | ambil semua employee person fit bedasarkan position code
    |------------------------------------------------------------
    |
    |
    */
    public function getPercentFitByPositionCode(Request $request) {
         $this->validate($request, [
            "companyId" => "required",
            'pageInfo' => 'required|array'
        ]);
        $request->merge($request->pageInfo);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1'
        ]);
        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $pageLimit = PagingAppResponse::getPageLimit($request->pageInfo);
        $data = $this->assignmentDao->getAllEmployeePercentFitByPositionCode($request->code, $offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->assignmentDao->getTotalRowsAllEmployeePercentFitByPositionCode($request->code),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }



    /**
     * Get all assignment transactions for one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAllTransactions(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required'
        ]);
        $assignmentTransactions = $this->assignmentTransactionDao->getAll(
            $request->companyId
        );

        $resp = new AppResponse($assignmentTransactions, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one assignment based on assignment id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "personId" => "required",
            "id" => "required"
        ]);

        $assignment = $this->assignmentDao->getOne(
            $request->companyId,
            $request->personId,
            $request->id
        );

        $resp = new AppResponse($assignment, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }





    /**
     * Get one assignment transaction based on person id and assignment transaction id
     * @param Request $request
     * @return AppResponse
     */
    public function getOneTransaction(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "personId" => "required",
            "id" => "required"
        ]);

        $assignmentTransaction = $this->assignmentTransactionDao->getOne(
            $request->companyId,
            $request->personId,
            $request->id
        );

        $resp = new AppResponse($assignmentTransaction, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all lov for one tenant
     * @param Request $request
     * @return AppResponse
     */
    public function getLov(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'personId' => 'required'
        ]);
        $assignments = $this->assignmentDao->getLov(
            $request->personId
        );

        $resp = new AppResponse($assignments, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getFirstAssignment(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'personId' => 'required'
        ]);

        $firstAssignment = $this->assignmentDao->getFirstAssignment($request->personId);

        $resp = new AppResponse($firstAssignment, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getLastPrimary(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'personId' => 'required'
        ]);

        $lastPrimaryAssignment = $this->assignmentDao->getLastPrimaryAssignment(
            $request->companyId,
            $request->personId
        );

        $resp = new AppResponse($lastPrimaryAssignment, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getHistoryEmployeeBenefit(Request $request)
    {

        $this->validate($request, [
            'companyId' => 'required',
            'personId' => 'required'
        ]);

        $getEmployeeId = $this->assignmentDao->getHistoryEmployeeBenefit($request->personId);

        $resp = new AppResponse($getEmployeeId, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get fixed effective dates. NULL if there is no fix.
     * @param Request $request
     * @return AppResponse
     */
    public function fix(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'personId' => 'required',
            'id' => 'present|nullable',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date'
        ]);

        $data = [];

        // Only fix effBegin if the previous primary assignment DOES NOT HAVE the default effEnd.
        $prevPrimaryAssignment = $this->assignmentDao->getPrevPrimaryAssignment(
            $request->companyId,
            $request->personId,
            $request->effBegin
        );
        $prevHasDefaultEndDate = $prevPrimaryAssignment && Carbon::parse($prevPrimaryAssignment->effEnd)->year === 9999;
        if ($prevHasDefaultEndDate) {
            $data['prevAssignment'] = null;
        } else {
            $data['prevAssignment'] = $this->isBadEffBegin($request);
        }
        $data['nextAssignment'] = $this->isBadEffEnd($request);

        // Adjust effective begin date.
        $data['fixedEffBegin'] = $data['prevAssignment'] ?
            // fixedEffBegin = Previous Assignment's effEnd + 1
            Carbon::parse($data['prevAssignment']->effEnd)->addDay()->format('Y-m-d') :
            // Do NOT fix effBegin.
            Carbon::parse($request->effBegin)->format('Y-m-d');

        // Adjust effective end date.
        $diffInDays = Carbon::parse($request->effBegin)->diffInDays(Carbon::parse($request->effEnd));

        if ($data['nextAssignment']) {
            // fixedEffEnd = Next Assignment's effBegin - 1
            $data['fixedEffEnd'] = Carbon::parse($data['nextAssignment']->effBegin)->subDay()->format('Y-m-d');
        } else {
            // Maintain assignment length if assignment DOES NOT HAVE default effEnd.
            if (Carbon::parse($request->effEnd)->year === 9999) {
                $data['fixedEffEnd'] = Carbon::parse(config('constant.defaultEndDate'))->format('Y-m-d');
            } else {
                $data['fixedEffEnd'] = Carbon::parse($data['fixedEffBegin'])->addDays($diffInDays)->format('Y-m-d');
            }
        }

        // Check for effective dates validity.
        if (Carbon::parse($data['fixedEffBegin'])->gte(Carbon::parse($data['fixedEffEnd']))) {
            $data['fixedEffBegin'] = null;
            $data['fixedEffEnd'] = null;
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save assignment to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkAssignmentRequest($request);
        if ($this->assignmentTransactionDao->hasUnapprovedAssignments($request->companyId, $request->personId)) {
            throw new AppException(trans('messages.hasUnapprovedAssignments'));
        }
        if ($this->areBadEffDates($request)) {
            throw new AppException(trans('messages.invalidEffDates'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $assignment = $this->constructAssignment($request);
            $assignmentTransaction = $this->constructAssignmentTransaction($request);
            $positionSlot = $this->constructPositionSlot($request);
            $this->positionSlotDao->save($positionSlot);

            if ($request->upload) {
                $fileUris = $this->getFileUris($request, $data);
                if (!empty($fileUris)) {
                    $assignment['file_assignment_doc'] = $fileUris['DOC'];
                    $assignmentTransaction['n_file_assignment_doc'] = $fileUris['DOC'];
                }
            }

            if ($request->isPrimary) {
                $this->assignmentTransactionDao->clearPrimary(
                    $request->companyId,
                    $request->personId,
                    $request->effBegin,
                    $request->effEnd
                );
            }
            $this->assignmentTransactionDao->save($assignmentTransaction);

            if ($request->isApproved) {
                if ($request->isPrimary) {
                    $this->endPrevPrimaryAssignment($request);
                    $this->assignmentDao->clearPrimary(
                        $request->companyId,
                        $request->personId,
                        $request->effBegin,
                        $request->effEnd
                    );
                }
                $data['id'] = $this->assignmentDao->save($assignment);
            }
        });

        info('assign', [$data]);
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update assignment to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $data = array();
        $this->checkAssignmentRequest($request);
        $this->validate($request, ['id' => 'required']);
        if ($this->areBadEffDates($request)) {
            throw new AppException(trans('messages.invalidEffDates'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $assignment = $this->constructAssignment($request);

            if ($request->upload) {
                $document = $this->assignmentDao->getOne(
                    $request->companyId,
                    $request->personId,
                    $request->id
                );

                if ($document->fileAssignmentDoc) {
                    if (!$this->deleteFile($request, $document->fileAssignmentDoc)) {
                        throw new AppException(trans('messages.updateFail'));
                    }
                }
                $fileUris = $this->getFileUris($request, $data);
                if (!empty($fileUris)) {
                    $assignment['file_assignment_doc'] = $fileUris['DOC'];
                }
            }

            if ($request->isPrimary) {
                $this->assignmentDao->clearPrimary(
                    $request->companyId,
                    $request->personId,
                    $request->effBegin,
                    $request->effEnd
                );
            }

            $this->assignmentDao->update(
                $request->companyId,
                $request->personId,
                $request->id,
                $assignment
            );
        });

        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    public function approve(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'personId' => 'required',
            'id' => 'required'
        ]);

        $this->assignmentTransactionDao->update(
            $request->companyId,
            $request->personId,
            $request->id,
            ['is_approved' => true]
        );

        $assignmentTransaction = $this->assignmentTransactionDao->getOne(
            $request->companyId,
            $request->personId,
            $request->id
        );
        if ($assignmentTransaction->isPrimary) {
            $this->endPrevPrimaryAssignment($request, $assignmentTransaction->effBegin);
            $this->assignmentDao->clearPrimary(
                $request->companyId,
                $request->personId,
                $assignmentTransaction->effBegin,
                $assignmentTransaction->effEnd
            );

            if ($assignmentTransaction->lovActy === 'TERM') {
                $this->assignmentDao->setPrimaryAssignmentsStatus(
                    $request->companyId,
                    $request->personId,
                    'TMT'
                );
                $this->assignmentDao->endSecondaryAssignments(
                    $request->companyId,
                    $request->personId,
                    $assignmentTransaction->effBegin
                );
                $person = $this->personDao->getOne($request->personId);
                $this->personDao->update(
                    $request->personId,
                    $person->effBegin,
                    [
                        'lov_ptyp' => 'EXE'
                    ]
                );
            }
        }

        $this->assignmentDao->cloneFromTransaction($request->id);

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    public function terminate(Request $request)
    {
        $this->validate($request, [
            'data' => 'required',
            'upload' => 'required|boolean'
        ]);

        if ($request->upload) {
            $this->validate($request, [
                'docTypes' => 'required|array|min:1',
                'fileContents' => 'required|array|min:1'
            ]);
        }

        $reqData = (array)json_decode($request->data);
        if (null === $reqData) {
            throw new AppException(trans('messages.jsonInvalid'));
        }
        $request->merge($reqData);

        $this->validate($request, [
            'companyId' => 'required|integer',
            'personId' => 'required|integer',
            'effBegin' => 'required|date',
            'assignmentReasonCode' => 'required|max:20|exists:assignment_reasons,code',
            'finalProcessDate' => 'present|nullable|date',
            'assignmentDocNumber' => 'present|max:50',
            'note' => 'present|max:255'
        ]);

        if ($this->assignmentTransactionDao->hasUnapprovedAssignments($request->companyId, $request->personId)) {
            throw new AppException(trans('messages.hasUnapprovedAssignments'));
        }

        $data = [];

        $lastPrimaryAssignment = $this->assignmentDao->getLastPrimaryAssignment(
            $request->companyId,
            $request->personId
        );
        if ($lastPrimaryAssignment) {
            DB::transaction(function () use (&$request, &$data, &$lastPrimaryAssignment) {
                $terminationTransaction = [
                    'tenant_id' => $this->requester->getTenantId(),
                    'company_id' => $request->companyId,
                    'n_person_id' => $request->personId,
                    'n_eff_begin' => $request->effBegin,
                    'n_eff_end' => Carbon::parse(config('constant.defaultEndDate'))->format('Y-m-d'),
                    'n_assignment_reason_code' => $request->assignmentReasonCode,
                    'n_final_process_date' => $request->finalProcessDate,
                    'n_assignment_doc_number' => $request->assignmentDocNumber,
                    'n_lov_asta' => 'TMT',
                    'n_lov_acty' => 'TERM',
                    'n_file_assignment_doc' => null,
                    'n_is_primary' => true,
                    'n_employee_id' => $lastPrimaryAssignment->employeeId,
                    'n_employee_status_code' => $lastPrimaryAssignment->employeeStatusCode,
                    'is_approved' => $request->isApproved,
                    "n_unit_code" => $lastPrimaryAssignment->unitCode,
                    "n_job_code" => $lastPrimaryAssignment->jobCode,
                    "n_position_code" => $lastPrimaryAssignment->positionCode,
                    "n_cost_center_code" => $lastPrimaryAssignment->costCenterCode,
                    "n_grade_code" => $lastPrimaryAssignment->gradeCode,
                    "n_location_code" => $lastPrimaryAssignment->locationCode,
                    "n_supervisor_id" => $lastPrimaryAssignment->supervisorId,
                    "n_note" => $request->note,
                    "o_assignment_id" => $lastPrimaryAssignment->id
                ];
                $termination = [
                    'tenant_id' => $this->requester->getTenantId(),
                    'company_id' => $request->companyId,
                    'person_id' => $request->personId,
                    'eff_begin' => $request->effBegin,
                    'eff_end' => Carbon::parse(config('constant.defaultEndDate'))->format('Y-m-d'),
                    'assignment_reason_code' => $request->assignmentReasonCode,
                    'final_process_date' => $request->finalProcessDate,
                    'assignment_doc_number' => $request->assignmentDocNumber,
                    'lov_asta' => 'TMT',
                    'lov_acty' => 'TERM',
                    'file_assignment_doc' => null,
                    'is_primary' => true,
                    'employee_id' => $lastPrimaryAssignment->employeeId,
                    'employee_status_code' => $lastPrimaryAssignment->employeeStatusCode,
                    "unit_code" => $lastPrimaryAssignment->unitCode,
                    "job_code" => $lastPrimaryAssignment->jobCode,
                    "position_code" => $lastPrimaryAssignment->positionCode,
                    "cost_center_code" => $lastPrimaryAssignment->costCenterCode,
                    "grade_code" => $lastPrimaryAssignment->gradeCode,
                    "location_code" => $lastPrimaryAssignment->locationCode,
                    "supervisor_id" => $lastPrimaryAssignment->supervisorId,
                    "note" => $request->note
                ];

                if ($request->upload) {
                    $fileUris = $this->getFileUris($request, $data);
                    if (!empty($fileUris)) {
                        $termination['file_assignment_doc'] = $fileUris['DOC'];
                    }
                }
                $this->assignmentTransactionDao->save($terminationTransaction);
                if ($request->isApproved) {
                    $this->endPrevPrimaryAssignment($request);
                    $this->assignmentDao->save($termination);
                }
            });
        } else {
            throw new AppException(trans('messages.lastPrimaryAssignmentNotFound'));
        }

        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    public function cancelTermination(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|exists:companies,id',
            'personId' => 'required|exists:persons,id',
            'newEffEnd' => 'required|date'
        ]);

        DB::transaction(function () use (&$request) {
            $this->assignmentDao->deleteLastTerminationAssignment(
                $request->companyId,
                $request->personId
            );

            $lastPrimaryAssignment = $this->assignmentDao->getLastPrimaryAssignment(
                $request->companyId,
                $request->personId
            );

            if ($lastPrimaryAssignment) {
                $this->assignmentDao->update(
                    $request->companyId,
                    $request->personId,
                    $lastPrimaryAssignment->id,
                    [
                        'eff_end' => $request->newEffEnd
                    ]
                );
            } else {
                throw new AppException(trans('messages.lastPrimaryAssignmentNotFound'));
            }
        });

        return $this->renderResponse(new AppResponse(null, trans('messages.dataUpdated')));
    }

    /**
     * Validate save/update assignment request.
     * @param Request $request
     */
    private function checkAssignmentRequest(Request $request)
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
            'companyId' => 'required|integer|exists:companies,id',
            'personId' => 'required|integer|exists:persons,id',
            'effBegin' => 'required|date',
            'effEnd' => 'required|date|after_or_equal:effBegin',
            'isPrimary' => 'required|boolean',
            'employeeId' => 'required|max:20',
            'employeeStatusCode' => 'required|max:20|exists:employee_statuses,code',
            'assignmentReasonCode' => 'required|max:20|exists:assignment_reasons,code',
            'locationCode' => 'present|nullable|max:20|exists:locations,code',
            'positionCode' => 'required|max:20|exists:positions,code',
            'jobCode' => 'required|max:20|exists:jobs,code',
            'unitCode' => 'required|max:20|exists:units,code',
            'costCenterCode' => 'present|nullable|max:20|exists:cost_centers,code',
            'gradeCode' => 'present|nullable|max:20|exists:grades,code',
            'lovAsta' => 'required|max:10|exists:lovs,key_data',
            'lovActy' => 'required|max:10|exists:lovs,key_data',
            'supervisorId' => 'present|nullable|integer|exists:persons,id',
            'note' => 'present|max:500',
            'finalProcessDate' => 'present|nullable|date',
            'assignmentDocNumber' => 'present|max:50',
            'oldAssignmentId' => 'present|nullable|integer|exists:assignments,id',
            'isApproved' => 'present|boolean'
        ]);
    }

    /**
     * Construct a assignment object (array).
     * @param Request $request
     * @return array
     */
    private function constructAssignment(Request $request)
    {
        $count = $this->positionSlotDao->countAllRows($request->positionCode);
        $assignment = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "person_id" => $request->personId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "is_primary" => $request->isPrimary,
            "employee_id" => $request->employeeId,
            "employee_status_code" => $request->employeeStatusCode,
            "assignment_reason_code" => $request->assignmentReasonCode,
            "unit_code" => $request->unitCode,
            "job_code" => $request->jobCode,
            "position_code" => $request->positionCode,
            "position_slot_code" => $request->positionCode . '-' . $count,
            "cost_center_code" => $request->costCenterCode,
            "grade_code" => $request->gradeCode,
            "location_code" => $request->locationCode,
            "lov_asta" => $request->lovAsta,
            "lov_acty" => $request->lovActy,
            "supervisor_id" => $request->supervisorId,
            "note" => $request->note,
            "final_process_date" => $request->finalProcessDate,
            "assignment_doc_number" => $request->assignmentDocNumber
        ];
        return $assignment;
    }

    private function constructAssignmentTransaction(Request $request)
    {
        $count = $this->positionSlotDao->countAllRows($request->positionCode);
        $assignmentTransaction = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "is_approved" => $request->isApproved,
            "n_person_id" => $request->personId,
            "n_eff_begin" => $request->effBegin,
            "n_eff_end" => $request->effEnd,
            "n_is_primary" => $request->isPrimary,
            "n_employee_id" => $request->employeeId,
            "n_employee_status_code" => $request->employeeStatusCode,
            "n_assignment_reason_code" => $request->assignmentReasonCode,
            "n_unit_code" => $request->unitCode,
            "n_job_code" => $request->jobCode,
            "n_position_code" => $request->positionCode,
            "n_position_slot_code" => $request->positionCode . '-' . $count,
            "n_cost_center_code" => $request->costCenterCode,
            "n_grade_code" => $request->gradeCode,
            "n_location_code" => $request->locationCode,
            "n_lov_asta" => $request->lovAsta,
            "n_lov_acty" => $request->lovActy,
            "n_supervisor_id" => $request->supervisorId,
            "n_note" => $request->note,
            "n_assignment_doc_number" => $request->assignmentDocNumber,
            "o_assignment_id" => $request->oldAssignmentId
        ];
        return $assignmentTransaction;
    }

    /**
     * Construct a position slot object (array).
     * @param Request $request
     * @return array
     */
    private function constructPositionSlot(Request $request)
    {
        $count = $this->positionSlotDao->countAllRows($request->positionCode);
        $positionSlot = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "position_code" => $request->positionCode,
            "code" => $request->positionCode . '-' . $count,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd
        ];
        return $positionSlot;
    }

    /**
     * Get all uploaded file URIs from File service.
     * @param Request $request
     * @param array $data
     * @return array
     */
    private function getFileUris(Request $request, array &$data)
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
        foreach ($request->fileContents as $i => $file) {
            array_push($payload, [
                'name' => "fileContents[$i]",
                'contents' => file_get_contents($file),
                'filename' => $file->getClientOriginalName()
            ]);
        }

        return $payload;
    }

    private function areBadEffDates(Request $request)
    {
        if (!$request->isPrimary) {
            return false;
        }
        return $this->isBadEffBegin($request) || $this->isBadEffEnd($request);
    }

    private function isBadEffBegin(Request $request)
    {
        $prevAssignment = $this->assignmentDao->getPrevPrimaryAssignment(
            $request->companyId,
            $request->personId,
            $request->effBegin,
            $request->id
        );

        if (!$prevAssignment || Carbon::parse($prevAssignment->effEnd)->year === 9999) {
            return null;
        }

        $pastEnd = Carbon::parse($prevAssignment->effEnd);
        $currentBegin = Carbon::parse($request->effBegin);
        if ($pastEnd->gte($currentBegin) || $pastEnd->diffInDays($currentBegin) !== 1) {
            return $prevAssignment;
        }
    }

    private function isBadEffEnd(Request $request)
    {
        $nextAssignment = $this->assignmentDao->getNextPrimaryAssignment(
            $request->companyId,
            $request->personId,
            $request->effBegin,
            $request->id
        );

        if (!$nextAssignment) {
            return null;
        }

        $futureBegin = Carbon::parse($nextAssignment->effBegin);
        $currentEnd = Carbon::parse($request->effEnd);
        if ($futureBegin->lte($currentEnd) || $futureBegin->diffInDays($currentEnd) !== 1) {
            return $nextAssignment;
        }
    }

    private function endPrevPrimaryAssignment(Request $request, $effBegin = null)
    {
        $effBegin = $effBegin ? $effBegin : $request->effBegin;
        if (!$effBegin) {
            throw new \InvalidArgumentException('effBegin is required');
        }

        $prevPrimaryAssignment = $this->assignmentDao->getPrevPrimaryAssignment(
            $request->companyId,
            $request->personId,
            $effBegin
        );
        if ($prevPrimaryAssignment) {
            $this->assignmentDao->update(
                $request->companyId,
                $request->personId,
                $prevPrimaryAssignment->id,
                [
                    'eff_end' => Carbon::parse($effBegin)->subDay()
                ]
            );
        }
    }


    public function checkEmployeeId(Request $request)
    {

        $this->validate($request, [
            "employeeId" => "required|max:20"
        ]);

        $data = $this->assignmentDao->getOneEmployeeId(
            $request->employeeId
        );

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function checkPositionVacant(Request $request)
    {
        $this->validate($request, [
            "positionCode" => "required",
            "beginDate" => "required",
            "endDate" => "required",
        ]);

        $data = $this->assignmentDao->checkPositionVacant(
            $request->positionCode,
            $request->beginDate,
            $request->endDate,
            $request->employeeId
        );

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

}
