<?php

namespace App\Http\Controllers;

use App\Business\Dao\JobFamilyDao;
use App\Business\Dao\ProjectDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling jobFamily process
 * @property Requester requester
 * @property ProjectDao projectDao
 */
class ProjectController extends Controller
{
    public function __construct(Requester $requester, ProjectDao $projectDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->projectDao = $projectDao;
    }

    /**
     * Get all project in one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $projects = $this->projectDao->getAll();

        $resp = new AppResponse($projects, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, ['companyId' => 'required']);

        $projects = $this->projectDao->getLov();

        $resp = new AppResponse($projects, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one project based on project code
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "code" => "required"
        ]);

        $projects = $this->projectDao->getOne($request->code);

        $resp = new AppResponse($projects, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save project to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkProjectRequest($request);
        if ($this->projectDao->isCodeDuplicate($request->code)) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $projects = $this->constructProject($request);
            $data['id'] = $this->projectDao->save($projects);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update project to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkProjectRequest($request);

        DB::transaction(function () use (&$request) {
            $projects = $this->constructProject($request);
            unset($projects['code']);
            $this->projectDao->update($request->id, $projects);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete a project.
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $projects = [
                "eff_end" => Carbon::now()
            ];
            $this->projectDao->update($request->id, $projects);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update employee project request.
     * @param Request $request
     */
    private function checkProjectRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'description' => 'present|max:255',
            'code' => 'required|max:20|alpha_num',
            'name' => 'required|max:50',
            'projectmanager' => 'max:20',
            'supervisor' => 'max:20',
            'location' => 'max:20'
        ]);
    }

    /**
     * Construct a employee project object (array).
     * @param Request $request
     * @return array
     */
    private function constructProject(Request $request)
    {
        $projects = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "description" => $request->description,
            "name" => $request->name,
            "code" => $request->code,
            "supervisor_id" => $request->supervisor,
            "projectmanager_id" => $request->projectmanager,
            "quota" => $request->quota,
            "location_code" => $request->location
        ];
        return $projects;
    }
}
