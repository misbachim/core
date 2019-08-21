<?php

namespace App\Http\Controllers;

use App\Business\Dao\AssignmentDao;
use App\Business\Dao\JobDao;
use App\Business\Dao\LocationDao;
use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonEducationDao;
use App\Business\Dao\PositionDao;
use App\Business\Dao\UnitDao;
use App\Business\Dao\EducationSpecializationDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class for handling Education Specialization process
 */
class EducationSpecializationController extends Controller
{
    public function __construct(Requester $requester
        , EducationSpecializationDao $educationSpecializationDao
        , PersonEducationDao $personEducationDao
        , AssignmentDao $assignmentDao
        , JobDao $jobDao
        , UnitDao $unitDao
        , LocationDao $locationDao
        , PositionDao $positionDao
        , PersonDao $personDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->educationSpecializationDao = $educationSpecializationDao;
        $this->personEducationDao = $personEducationDao;
        $this->assignmentDao = $assignmentDao;
        $this->jobDao = $jobDao;
        $this->unitDao = $unitDao;
        $this->locationDao = $locationDao;
        $this->positionDao = $positionDao;
        $this->personDao = $personDao;
    }

    /**
     * Get all Education Specialization
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer"
        ]);

        $getEducationSpecialization = $this->educationSpecializationDao->getAll();

        $resp = new AppResponse($getEducationSpecialization, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all Education Specialization
     * @param request
     */
    public function getAllActive(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer"
        ]);

        $getEducationSpecialization = $this->educationSpecializationDao->getAllActive();

        $resp = new AppResponse($getEducationSpecialization, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all inActive Education Specialization
     * @param request
     */
    public function getAllInactive(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer"
        ]);

        $getEducationSpecialization = $this->educationSpecializationDao->getAllInactive();

        $resp = new AppResponse($getEducationSpecialization, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get lov effective date Education Specialization in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $getEducationSpecialization = $this->educationSpecializationDao->getAllActive(
            null , null
        );

        $resp = new AppResponse($getEducationSpecialization, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one effective date Education Specialization based on Education Specialization id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $educationSpecialization = $this->educationSpecializationDao->getOne(
            $request->id
        );

        $educationSpecialization->usedByEmployee = $this->getUsedByEmployee($educationSpecialization->code);

        $resp = new AppResponse($educationSpecialization, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Education Specialization to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkEducationSpecializationRequest($request);
        $data = array();


            if ($this->educationSpecializationDao->checkDuplicateEducationSpecializationCode($request->code) > 0) {
                throw new AppException(trans('messages.duplicateCode'));
            }

        DB::transaction(function () use (&$request, &$data) {
            $educationSpecialization = $this->constructEducationSpecialization($request);

            $data['id'] = $this->educationSpecializationDao->save($educationSpecialization);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Education Specialization to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->checkEducationSpecializationRequest($request);
        $this->validate($request, [
            'id' => 'required|integer|exists:education_specializations,id']);

        DB::transaction(function () use (&$request, &$data) {
            $educationSpecialization = $this->constructEducationSpecialization($request);

            $this->educationSpecializationDao->update($request->id, $educationSpecialization);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * get History Education Specialization from DB
     * @param request
     */
    public function getHistory(Request $request) {

        $this->validate($request, [
            "companyId" => "required|integer",
            'code' => 'required|max:50|exists:education_specializations,code',
            'id' => 'required|integer|exists:education_specializations,id']);

        $data = $this->educationSpecializationDao->getHistory($request->code, $request->id);

        info('history', array($data));
        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update Education Specialization request.
     * @param request
     */
    private function checkEducationSpecializationRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'code' => 'required|max:50|alpha_num',
            'name' => 'present|max:255',
            'description' => 'present|max:255',
            'lovCategoryEducation' => 'required',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
        ]);
    }

    /**
     * Construct an Education Specialization object (array).
     * @param request
     */
    private function constructEducationSpecialization(Request $request)
    {
        $educationSpecialization = [
            "tenant_id"              => $this->requester->getTenantId(),
            "company_id"             => $this->requester->getCompanyId(),
            "code"                   => $request->code,
            "name"                   => $request->name,
            "description"            => $request->description,
            "lov_category_education" => $request->lovCategoryEducation,
            "eff_begin"              => $request->effBegin,
            "eff_end"                => $request->effEnd
        ];
        return $educationSpecialization;
    }

    private function getDataCollect($getEducationSpecialization) {
        $data = array();

        $dataCollect = collect($getEducationSpecialization)->unique('code');
        $dataCollect->values()->all();

        foreach ($dataCollect as $educationSpecialization) {
            array_push($data, [
                'id' => $educationSpecialization->id,
                'code' => $educationSpecialization->code,
                'name' => $educationSpecialization->name,
                'description' => $educationSpecialization->description,
                'categoryName' => $educationSpecialization->categoryName,
                'lovCategoryEducation' => $educationSpecialization->lovCategoryEducation,
                'effBegin' => $educationSpecialization->effBegin,
                'effEnd' => $educationSpecialization->effEnd
            ]);
        }

        return $data;
    }

    private
    function getUsedByEmployee($specializationCode) {
        $data = array();
        $getDataPersonEdu = $this->personEducationDao->getAllPersonIdBySpecialization($specializationCode);

        if(count($getDataPersonEdu) > 0) {
            for($i = 0 ; $i < count($getDataPersonEdu) ; $i ++) {

                //get assignment by person id
                $getAssignment = $this->assignmentDao->getOneLastAssignmentByPersonId($getDataPersonEdu[$i]->personId);

                $getJob        = $this->jobDao->getOneJobByCode($getAssignment->jobCode);
                $getUnit       = $this->unitDao->getOneUnitByCode($getAssignment->unitCode);
                $getPosition   = $this->positionDao->getOnePositionByCode($getAssignment->positionCode);
                $getLocation   = $this->locationDao->getOneLocationByCode($getAssignment->locationCode);

                $getDataPerson = $this->personDao->getOnePersonById($getDataPersonEdu[$i]->personId);

                array_push($data, [
                    'employeeName' => $getDataPerson->firstName.' '.$getDataPerson->lastName,
                    'employeeId' => $getAssignment->employeeId,
                    'email' => $getDataPerson->email,
                    'jobName' => $getJob->name,
                    'unitName' => $getUnit->name,
                    'positionName' => $getPosition->name,
                    'locationName' => $getLocation->name,
                ]);
            }
        }

        return $data;
    }
}
