<?php

namespace App\Http\Controllers;

use App\Business\Dao\ApprovalOrderDao;
use App\Business\Dao\WorkflowDao;
use App\Business\Dao\PersonDao;
use App\Business\Dao\UnitDao;
use App\Business\Dao\PositionDao;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\AppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;

/**
 * Class for handling AutoNumber process
 */
class WorkflowController extends Controller
{
    public function __construct(Requester $requester, WorkflowDao $workflowDao, ApprovalOrderDao $approvalOrderDao, PersonDao $personDao, UnitDao $unitDao, PositionDao $positionDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->workflowDao = $workflowDao;
        $this->approvalOrderDao = $approvalOrderDao;
        $this->personDao = $personDao;
        $this->unitDao = $unitDao;
        $this->positionDao = $positionDao;
    }

    /**
     * Get all workflow default
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required|integer"]);

        $workflow = $this->workflowDao->getAllDefaultByWorkflowType($request->lovWfty);

        $count = count($workflow);
        $tempWorkflowId = array();

        if ($count > 0) {
            foreach ($workflow as $datum) {
                if($datum->employeeId) {
                    $person = $this->personDao->getOneEmployee($datum->employeeId);
                    $datum->employeeName = $person->firstName . ' ' . $person->lastName;
                } else {
                    $datum->employeeName = '';
                }

                if($datum->unitCode) {
                    $unit   = $this->unitDao->getOneUnitByCode($datum->unitCode);
                    $datum->unitName = $unit->name;
                } else {
                    $datum->unitName = '';
                }

                array_push($tempWorkflowId, $datum->id);
            }

            $getApprovalOrder = $this->approvalOrderDao->getManyApprovalOrder($tempWorkflowId, $request->companyId);

            foreach ($workflow as $datum) {
                $tempApprovalOrder = array();

                foreach ($getApprovalOrder as $pivot) {
                    if ($pivot->workflowId === $datum->id) {
                        array_push($tempApprovalOrder, $pivot);
                    }
                }
                $datum->approvalOrder = $tempApprovalOrder;
            }

            foreach ($workflow as $datum) {
                for ($j = 0; $j < count($datum->approvalOrder); $j++) {
                    if ($datum->approvalOrder[$j]->lovWapt === 'EMPL')
                    {
                        $person = $this->personDao->getOneEmployee($datum->approvalOrder[$j]->value);
                        $datum->approvalOrder[$j]->name = $person->firstName . ' ' . $person->lastName;
                    }
                    if($datum->approvalOrder[$j]->lovWapt === 'POST')
                    {
                        $position = $this->positionDao->getOnePositionByCode($datum->approvalOrder[$j]->value);
                        $datum->approvalOrder[$j]->name = $position->name;
                    }

                    $datum->approvalOrder[$j]->isAdd = false;
                    $datum->approvalOrder[$j]->disabled = true;
                    $j == count($datum->approvalOrder) - 1
                        ? $datum->approvalOrder[$j]->isDelete = true
                        : $datum->approvalOrder[$j]->isDelete = false;
                }
            }
        }

        $resp = new AppResponse($workflow, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all workflow unit
     * @param request
     */
    public function getAllUnit(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "lovWfty" => "required|string",
            "search" => "present|string",
            "pageInfo" => "required|array"
        ]);

        $reqData = $request->pageInfo;
        $request->merge($reqData);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1',
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit  = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $workflow = $this->workflowDao->getAllUnitByWorkflowType($offset, $limit, $request->lovWfty, $request->search);
        $totalRow = $this->workflowDao->totalRowUnitWorkflowType($request->lovWfty, $request->search);

        $count = count($workflow);
        $tempWorkflowId = array();
        if ($count > 0) {

            foreach ($workflow as $datum) {
                array_push($tempWorkflowId, $datum->id);
            }

            $getApprovalOrder = $this->approvalOrderDao->getManyApprovalOrder($tempWorkflowId, $request->companyId);

            foreach ($workflow as $datum) {
                $tempApprovalOrder = array();

                foreach ($getApprovalOrder as $pivot) {
                    if ($pivot->workflowId === $datum->id) {
                        array_push($tempApprovalOrder, $pivot);
                    }
                }
                $datum->approvalOrder = $tempApprovalOrder;
            }

            foreach ($workflow as $datum) {
                foreach ($datum->approvalOrder as $appOrder) {
                    if($appOrder->lovWapt === 'EMPL') {

                        $person = $this->personDao->getOneEmployee($appOrder->value);
                        $appOrder->name = $person->firstName . ' ' . $person->lastName;
                    }
                    if($appOrder->lovWapt === 'POST')
                    {
                        $position = $this->positionDao->getOnePositionByCode($appOrder->value);
                        $appOrder->name = $position->name;
                    }
                }
            }
        }

        return $this->renderResponse(new PagingAppResponse($workflow, trans('messages.allDataRetrieved'), $limit, $totalRow, $pageNo));
    }

    /**
     * Get all workflow employee
     * @param request
     */
    public function getAllEmployee(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "lovWfty" => "required|string",
            "search" => "present|string",
            "pageInfo" => "required|array"
        ]);

        $reqData = $request->pageInfo;
        $request->merge($reqData);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1',
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $workflow = $this->workflowDao->getAllEmployeeByWorkflowType($offset, $limit, $request->lovWfty, $request->search);
        $totalRow = $this->workflowDao->totalRowEmployeeWorkflowType($request->lovWfty, $request->search);

        $count = count($workflow);
        $tempWorkflowId = array();
        if ($count > 0) {

            foreach ($workflow as $datum) {
                array_push($tempWorkflowId, $datum->id);
            }

            $getApprovalOrder = $this->approvalOrderDao->getManyApprovalOrder($tempWorkflowId, $request->companyId);

            foreach ($workflow as $datum) {
                $tempApprovalOrder = array();

                foreach ($getApprovalOrder as $pivot) {
                    if ($pivot->workflowId === $datum->id) {
                        array_push($tempApprovalOrder, $pivot);
                    }
                }
                $datum->approvalOrder = $tempApprovalOrder;
            }

            foreach ($workflow as $datum) {
                foreach ($datum->approvalOrder as $appOrder) {
                    if($appOrder->lovWapt === 'EMPL')
                    {
                        $person = $this->personDao->getOneEmployee($appOrder->value);
                        $appOrder->name = $person->firstName . ' ' . $person->lastName;
                    }
                    if($appOrder->lovWapt === 'POST')
                    {
                        $position = $this->positionDao->getOnePositionByCode($appOrder->value);
                        $appOrder->name = $position->name;
                    }
                }
            }
        }

        return $this->renderResponse(new PagingAppResponse($workflow, trans('messages.allDataRetrieved'), $limit, $totalRow, $pageNo));
    }

    /**
     * Get all workflow Location
     * @param request
     */
    public function getAllLocation(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "lovWfty" => "required|string",
            "search" => "present|string",
            "pageInfo" => "required|array"
        ]);

        $reqData = $request->pageInfo;
        $request->merge($reqData);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1',
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit  = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $workflow = $this->workflowDao->getAllLocationByWorkflowType($offset, $limit, $request->lovWfty, $request->search);
        $totalRow = $this->workflowDao->totalRowLocationWorkflowType($request->lovWfty, $request->search);

        $count = count($workflow);
        $tempWorkflowId = array();
        if ($count > 0) {

            foreach ($workflow as $datum) {
                array_push($tempWorkflowId, $datum->id);
            }

            $getApprovalOrder = $this->approvalOrderDao->getManyApprovalOrder($tempWorkflowId, $request->companyId);

            foreach ($workflow as $datum) {
                $tempApprovalOrder = array();

                foreach ($getApprovalOrder as $pivot) {
                    if ($pivot->workflowId === $datum->id) {
                        array_push($tempApprovalOrder, $pivot);
                    }
                }
                $datum->approvalOrder = $tempApprovalOrder;
            }

            foreach ($workflow as $datum) {
                foreach ($datum->approvalOrder as $appOrder) {
                    if($appOrder->lovWapt === 'EMPL')
                    {
                        $person = $this->personDao->getOneEmployee($appOrder->value);
                        $appOrder->name = $person->firstName . ' ' . $person->lastName;
                    }
                    if($appOrder->lovWapt === 'POST')
                    {
                        $position = $this->positionDao->getOnePositionByCode($appOrder->value);
                        $appOrder->name = $position->name;
                    }
                }
            }
        }

        return $this->renderResponse(new PagingAppResponse($workflow, trans('messages.allDataRetrieved'), $limit, $totalRow, $pageNo));
    }

    /**
     * Get all workflow Project
     * @param request
     */
    public function getAllProject(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "lovWfty" => "required|string",
            "search" => "present|string",
            "pageInfo" => "required|array"
        ]);

        $reqData = $request->pageInfo;
        $request->merge($reqData);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1',
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit  = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $workflow = $this->workflowDao->getAllProjectByWorkflowType($offset, $limit, $request->lovWfty, $request->search);
        $totalRow = $this->workflowDao->totalRowProjectWorkflowType($request->lovWfty, $request->search);

        $count = count($workflow);
        $tempWorkflowId = array();
        if ($count > 0) {

            foreach ($workflow as $datum) {
                array_push($tempWorkflowId, $datum->id);
            }

            $getApprovalOrder = $this->approvalOrderDao->getManyApprovalOrder($tempWorkflowId, $request->companyId);

            foreach ($workflow as $datum) {
                $tempApprovalOrder = array();

                foreach ($getApprovalOrder as $pivot) {
                    if ($pivot->workflowId === $datum->id) {
                        array_push($tempApprovalOrder, $pivot);
                    }
                }
                $datum->approvalOrder = $tempApprovalOrder;
            }

            foreach ($workflow as $datum) {
                foreach ($datum->approvalOrder as $appOrder) {
                    if($appOrder->lovWapt === 'EMPL')
                    {
                        $person = $this->personDao->getOneEmployee($appOrder->value);
                        $appOrder->name = $person->firstName . ' ' . $person->lastName;
                    }
                    if($appOrder->lovWapt === 'POST')
                    {
                        $position = $this->positionDao->getOnePositionByCode($appOrder->value);
                        $appOrder->name = $position->name;
                    }
                }
            }
        }


        return $this->renderResponse(new PagingAppResponse($workflow, trans('messages.allDataRetrieved'), $limit, $totalRow, $pageNo));
    }

    /**
     * Get one company based on Autonumber id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $workflow = $this->workflowDao->getOne($request->id, $request->companyId);
        $workflow->approvalOrder = $this->approvalOrderDao->getAllApprovalOrder($request->id, $request->companyId);
        info('data', array($workflow->approvalOrder->value));

        return $this->renderResponse(new AppResponse($workflow, trans('messages.dataRetrieved')));
    }

    /**
     * Update Workflow to DB
     * @param request
     */

    public function update(Request $request)
    {
        $this->validate($request, [
            "employeeId" => 'present|nullable|max:20',
            "unitCode" => 'present|nullable|max:20',
            "isDefault" => 'present|boolean',
            "approvalOrder" => 'present|array',
            "lovWfty" => 'required|max:10|exists:lovs,key_data',
            "state" => 'required',
            "companyId" => "required|integer",
        ]);

        $data = array();

        if($request->state === 'default') {

            DB::transaction(function () use (&$request, &$data) {

                $getId = null;
                // Jika punya Id (punya data) maka delete approval order
                if($request->id) {
                    $getId = $request->id;
                    $this->approvalOrderDao->deleteApprovalOrder($getId, $request->companyId);
                } else {
                    $constructWorkflow = $this->constructWorkflow($request);
                    $getId      =  $this->workflowDao->save($constructWorkflow);
                    $data['id'] = $getId;
                }

                $newRequest = new \Illuminate\Http\Request();
                $newRequest->replace([
                    'id' => $getId,
                    'approvalOrder' => $request->approvalOrder,
                    'companyId' => $request->companyId
                ]);
                $this->saveApprovalOrder($newRequest);
            });

        } else {

            DB::transaction(function () use (&$request, &$data) {

                $getId = null;
                // Jika punya Id (punya data) maka delete approval order
                if($request->id) {
                    $getId = $request->id;
                    $this->approvalOrderDao->deleteApprovalOrder($getId, $request->companyId);
                    $constructWorkflow = $this->constructWorkflow($request);
                    $this->workflowDao->update($getId, $constructWorkflow);
                } else {
                    $constructWorkflow = $this->constructWorkflow($request);
                    $getId             =  $this->workflowDao->save($constructWorkflow);
                    $data['id']        = $getId;
                }
                $newRequest = new \Illuminate\Http\Request();
                $newRequest->replace([
                    'id' => $getId,
                    'approvalOrder' => $request->approvalOrder,
                    'companyId' => $request->companyId
                ]);
                $this->saveApprovalOrder($newRequest);
            });
        }

        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    private function saveApprovalOrder(Request $request) {

        if($request->state === '') {
            foreach ($request->approvalOrder as $approvalOrders) {
                $approvalOrderReq = new \Illuminate\Http\Request();
                $approvalOrderReq->replace([
                    'lovWapt' => $approvalOrders['lovWapt'],
                    'number' => $approvalOrders['number'],
                    'value' => $approvalOrders['value']
                ]);
                if ($approvalOrders['lovWapt'] !== 'HEAD' && $approvalOrders['lovWapt'] !== 'SELF' && $approvalOrders['lovWapt'] !== 'SPOST' && $approvalOrders['lovWapt'] !== 'SPR') {
                    $this->validate($approvalOrderReq, [
                        "lovWapt" => 'required|max:10|exists:lovs,key_data',
                        "number" => 'required|integer|max:4',
                        "value" => 'required'
                    ]);
                }

                $approvalFields = [
                    'tenant_id' => $this->requester->getTenantId(),
                    'company_id' => $request->companyId,
                    'workflow_id' => $request->id,
                    'lov_wapt' => $approvalOrders['lovWapt'],
                    'number' => $approvalOrders['number'],
                    'value' => $approvalOrders['value']
                ];
                $this->approvalOrderDao->saveApprovalOrder($approvalFields);
            }
        } else {
            $number = 1;
            foreach ($request->approvalOrder as $approvalOrders) {
                $approvalOrderReq = new \Illuminate\Http\Request();
                $approvalOrderReq->replace([
                    'lovWapt' => $approvalOrders['lovWapt'],
                    'number' => $number,
                    'value' => $approvalOrders['value']
                ]);
                if ($approvalOrders['lovWapt'] !== 'HEAD' && $approvalOrders['lovWapt'] !== 'SELF' && $approvalOrders['lovWapt'] !== 'SPOST' && $approvalOrders['lovWapt'] !== 'SPR') {
                    $this->validate($approvalOrderReq, [
                        "lovWapt" => 'required|max:10|exists:lovs,key_data',
                        "number" => 'required|integer|max:4',
                        "value" => 'required'
                    ]);
                }

                $approvalFields = [
                    'tenant_id' => $this->requester->getTenantId(),
                    'company_id' => $request->companyId,
                    'workflow_id' => $request->id,
                    'lov_wapt' => $approvalOrders['lovWapt'],
                    'number' => $number,
                    'value' => $approvalOrders['value']
                ];
                $this->approvalOrderDao->saveApprovalOrder($approvalFields);
                $number++;
            }
        }
    }

    /**
     * Delete workflow from DB.
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        DB::transaction(function () use (&$request) {
            $this->approvalOrderDao->deleteApprovalOrder($request->id, $request->companyId);
            $this->workflowDao->delete($request->id, $request->companyId);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate update workflow request.
     * @param request
     */
    private function checkWorkflowRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer',
            'lovWfty' => 'required|max:10'
        ]);
    }

    /**
     * Construct an workflow object (array).
     * @param request
     */
    private function constructWorkflow(Request $request)
    {
        $workflow = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "lov_wfty" => $request->lovWfty,
            "is_default" => $request->isDefault,
            "unit_code" => $request->unitCode,
            "employee_id" => $request->employeeId,
            "location_code" => $request->locationCode,
            "project_code" => $request->projectCode,
        ];
        return $workflow;
    }

    private function constructApprovalOrder(Request $request)
    {
        $approvalFields = [];
        foreach ($request->approvalOrder as $approvalOrders) {
            array_push($approvalFields, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'workflow_id' => $request->id,
                'lov_wapt' => $approvalOrders['lovWapt'],
                'number' => 1,
                'value' => $approvalOrders['value']
            ]);
        }

        return $approvalFields;
    }
}
