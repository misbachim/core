<?php

namespace App\Http\Controllers;

use App\Business\Dao\AssignmentDao;
use App\Business\Dao\CompanyDao;
use App\Business\Dao\EmployeeProjectDao;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;

/**
 * Class for handling personLanguage process
 * @property Requester requester
 * @property EmployeeProjectDao employeeProjectDao
 * @property AssignmentDao assignmentDao
 */
class EmployeeProjectController extends Controller
{
    public function __construct(Requester $requester, EmployeeProjectDao $employeeProjectDao, AssignmentDao $assignmentDao, CompanyDao $companyDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->employeeProjectDao = $employeeProjectDao;
        $this->assignmentDao = $assignmentDao;
        $this->companyDao = $companyDao;
    }

    /**
     * Get all employee Project for one employee id
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
//        $this->validate($request, ["employeeId" => "required"]);
//
//        $employeeProject = $this->employeeProjectDao->getAll($request->employeeId);
//
//        $resp = new AppResponse($employeeProject, trans('messages.allDataRetrieved'));
//        return $this->renderResponse($resp);

        $this->validate($request, [
            "companyId" => "required|integer",
            "employeeId" => "required|string",
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


        if ($request->has('search')) {
            $data = $this->employeeProjectDao->searchWithLimit(
                $offset,
                $limit,
                $request->employeeId,
                $request->search
            );
            $totalRow = $this->employeeProjectDao->getTotalRowWithQuery(
                $request->employeeId,
                $request->search
            );
        } else {
            $data = $this->employeeProjectDao->getAll(
                $offset,
                $limit,
                $request->employeeId
            );
            $totalRow = $this->employeeProjectDao->getTotalRow(
                $request->employeeId
            );
        }




        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'), $limit, $totalRow, $pageNo));

    }

    /**
     * Get all active employee Project for one employee id
     * @param Request $request
     * @return AppResponse
     */
    public function getLov(Request $request)
    {
//        $this->validate($request, ["employeeId" => "required"]);
        $employeeId = null;
        if ($request->has('employeeId')) {
            $employeeId = $request->employeeId;
        } else if ($request->has('subEmployeeId')) {
            $employeeId = $request->subEmployeeId;
        }
        $employeeProject = $this->employeeProjectDao->getAllActive($employeeId);

        $resp = new AppResponse($employeeProject, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all active employee Project for one employee id
     * @param Request $request
     * @return AppResponse
     */
    public function search(Request $request)
    {
        $this->validate($request, ["searchQuery" => "required"]);
        $employeeId = null;
        if ($request->has('employeeId')) {
            $employeeId = $request->employeeId;
        } else if ($request->has('subEmployeeId')) {
            $employeeId = $request->subEmployeeId;
        }
        $employeeProject = $this->employeeProjectDao->search($employeeId, $request->searchQuery);

        $resp = new AppResponse($employeeProject, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all employee Project for one employee id and assignment Date
     * @param Request $request
     * @return AppResponse
     */
    public function getAllByAssignment(Request $request)
    {
        $this->validate($request, [
            "employeeId" => "required"
        ]);

        $employeeProject = array();
        $assignment = $this->assignmentDao->getOneLastAssignmentByEmployeeId($request->employeeId);
        if ($assignment) {
            $setting = $this->companyDao->getSetting($this->requester->getTenantId(), $this->requester->getCompanyId());
            $project = 'MPRO';
            foreach ($setting as $set) {
                if ($set->lovTypeCode === 'PROJ') {
                    $project = $set->lovKeyData;
                }
            }
            if ($project === 'SPRO') {
                $employeeProject = $this->employeeProjectDao->getAllByAssignmentDate($request->employeeId, $assignment->effBegin, $assignment->effEnd);
            } else {
                $employeeProject = $this->employeeProjectDao->getAllActiveForAssignment($request->employeeId);
            }
        }

        $resp = new AppResponse($employeeProject, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one employee Project based on employee id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);

        $employeeProject = $this->employeeProjectDao->getOne(
            $request->id
        );

        $resp = new AppResponse($employeeProject, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save employee Project to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkEmployeeProjectRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $employeeProject = $this->constructEmployeeProject($request);
            $setting = $this->companyDao->getSetting($this->requester->getTenantId(), $this->requester->getCompanyId());
            $project = 'MPRO';
            foreach ($setting as $set) {
                if ($set->lovTypeCode === 'PROJ') {
                    $project = $set->lovKeyData;
                }
            }
            if ($project === 'SPRO') {
                $lastProject = $this->employeeProjectDao->getLastOne($request->previousEmployeeId);
                $newEffEnd = [
                    'eff_end' => $request->effBegin
                ];
                if ($lastProject) {
                    if ($lastProject->effEnd > $request->effBegin) {
                        $this->employeeProjectDao->update($request->previousEmployeeId, $lastProject->id, $newEffEnd);
                    }
                }
            }
            $data['id'] = $this->employeeProjectDao->save($employeeProject);

        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update employee Project to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkEmployeeProjectRequest($request);

        DB::transaction(function () use (&$request) {
            $employeeProject = $this->constructEmployeeProject($request);
            $this->employeeProjectDao->update(
                $request->employeeId,
                $request->id,
                $employeeProject
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete employee Project by id.
     * @param Request $request
     * @return AppResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "employeeId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $this->employeeProjectDao->deleteByEmployeeId(
                $request->employeeId
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update employee Project request.
     * @param Request $request
     */
    private function checkEmployeeProjectRequest(Request $request)
    {
        $this->validate($request, [
            'employeeId' => 'required|max:20|exists:assignments,employee_id',
            'projectCode' => 'required|max:20|exists:projects,code',
            'effBegin' => 'required|date',
            'effEnd' => 'required|date|after_or_equal:effBegin'
        ]);
    }

    /**
     * Construct a employee Project object (array).
     * @param Request $request
     * @return array
     */
    private function constructEmployeeProject(Request $request)
    {
        $employeeProject = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "employee_id" => $request->employeeId,
            "project_code" => $request->projectCode,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd
        ];

        if ($request->has('weight')) {
            $employeeProject['weight'] = $request->weight;
        }
        return $employeeProject;
    }


    /*
    |-----------------------------
    | search employee project
    |-----------------------------
    |
    |
    */
    public function searchEmployeeProject(Request $request) {
        $this->validate($request, [
            "companyId" => "required|integer",
            "param" => "required",
        ]);

        $data = $this->employeeProjectDao->getSearchBox($request->param, $request->employeeId);
        $resp = new AppResponse($data, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }
}
