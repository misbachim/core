<?php

namespace App\Http\Controllers;

use App\Business\Dao\AssignmentReasonDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling assignmentReason process
 * @property Requester requester
 * @property AssignmentReasonDao assignmentReasonDao
 */
class AssignmentReasonController extends Controller
{
    public function __construct(Requester $requester, AssignmentReasonDao $assignmentReasonDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->assignmentReasonDao = $assignmentReasonDao;
    }

    /**
     * Get all assignmentReasons in one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $assignmentReasons = $this->assignmentReasonDao->getAll($request->companyId);

        $resp = new AppResponse($assignmentReasons, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, ['companyId' => 'required']);

        $activeAssignmentReasons = $this->assignmentReasonDao->getAllActive($request->companyId);

        $resp = new AppResponse($activeAssignmentReasons, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one assignmentReason based on assignmentReason id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "id" => "required"
        ]);

        $assignmentReason = $this->assignmentReasonDao->getOne(
            $request->companyId,
            $request->id
        );

        $resp = new AppResponse($assignmentReason, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save assignmentReason to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkAssignmentReasonRequest($request);
        if ($this->assignmentReasonDao->isCodeDuplicate($request->code)) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $assignmentReason = $this->constructAssignmentReason($request);
            $data['id'] = $this->assignmentReasonDao->save($assignmentReason);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update assignmentReason to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkAssignmentReasonRequest($request);

        DB::transaction(function () use (&$request) {
            $assignmentReason = $this->constructAssignmentReason($request);
            unset($assignmentReason['code']);
            $this->assignmentReasonDao->update(
                $request->companyId,
                $request->id,
                $assignmentReason
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete an assignment reason.
     * @param Request $request
     * @return AppResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        $this->assignmentReasonDao->delete($request->companyId, $request->id);

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update assignmentReason request.
     * @param Request $request
     */
    private function checkAssignmentReasonRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'code' => 'required|max:20|alpha_num',
            'description' => 'present|max:255',
            'lovActy' => 'required|max:10|exists:lovs,key_data'
        ]);
    }

    /**
     * Construct an assignmentReason object (array).
     * @param Request $request
     * @return array
     */
    private function constructAssignmentReason(Request $request)
    {
        $assignmentReason = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "code" => $request->code,
            "description" => $request->description,
            "lov_acty" => $request->lovActy
        ];
        return $assignmentReason;
    }
}
