<?php

namespace App\Http\Controllers;

use App\Business\Dao\AssignmentDao;
use App\Business\Dao\JobDao;
use App\Business\Dao\LocationDao;
use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonEducationDao;
use App\Business\Dao\PositionDao;
use App\Business\Dao\UnitDao;
use App\Business\Dao\EducationInstitutionDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling Education Institution process
 */
class EducationInstitutionController extends Controller
{
    public function __construct(Requester $requester
        , EducationInstitutionDao $educationInstitutionDao
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
        $this->educationInstitutionDao = $educationInstitutionDao;
        $this->personEducationDao = $personEducationDao;
        $this->assignmentDao = $assignmentDao;
        $this->jobDao = $jobDao;
        $this->unitDao = $unitDao;
        $this->locationDao = $locationDao;
        $this->positionDao = $positionDao;
        $this->personDao = $personDao;
    }

    /**
     * Get all Education Institution
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer"
        ]);

        $getEducationInstitution = $this->educationInstitutionDao->getAll();

        $resp = new AppResponse($getEducationInstitution, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all Education Institution
     * @param request
     */
    public function getAllActive(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer"
        ]);

        $getEducationInstitution = $this->educationInstitutionDao->getAllActive();

        $resp = new AppResponse($getEducationInstitution, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }
    
    /**
     * Get all inActive Education Institution
     * @param request
     */
    public function getAllInactive(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer"
        ]);

        $getEducationInstitution = $this->educationInstitutionDao->getAllInactive();

        $resp = new AppResponse($getEducationInstitution, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getDataCollect($getEducationInstitution) {
        $data = array();

        $dataCollect = collect($getEducationInstitution)->unique('name');
        $dataCollect->values()->all();

        foreach ($dataCollect as $educationInstitution) {
            array_push($data, [
                'id' => $educationInstitution->id,
                'name' => $educationInstitution->name,
                'address' => $educationInstitution->address,
                'countryName' => $educationInstitution->countryName,
                'countryCode' => $educationInstitution->countryCode,
                'lovAcreditation' => $educationInstitution->lovAcreditation,
                'effBegin' => $educationInstitution->effBegin,
                'effEnd' => $educationInstitution->effEnd,
                'linkWebsite' => $educationInstitution->linkWebsite,
            ]);
        }

        return $data;
    }

    /**
     * Get lov effective date Education Institution in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $getEducationInstitution = $this->educationInstitutionDao->getAllActive(null ,null);

        $resp = new AppResponse($getEducationInstitution, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one effective date Education Institution based on Education Institution id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $educationInstitution = $this->educationInstitutionDao->getOne(
            $request->id
        );

        $educationInstitution->usedByEmployee = $this->getUsedByEmployee($educationInstitution->name);

        $resp = new AppResponse($educationInstitution, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Education Institution to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkEducationInstitutionRequest($request);
        $data = array();

        //name must be unique
        if ($this->educationInstitutionDao->checkDuplicateEducationInstitutionName($request->name) > 0) {
            throw new AppException(trans('messages.duplicateName'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $educationInstitution = $this->constructEducationInstitution($request);

            $data['id'] = $this->educationInstitutionDao->save($educationInstitution);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Education Institution to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->checkEducationInstitutionRequest($request);
        $this->validate($request, [
            'id' => 'required|integer|exists:education_institutions,id']);

        DB::transaction(function () use (&$request, &$data) {
            $educationInstitution = $this->constructEducationInstitution($request);

            $this->educationInstitutionDao->update($request->id, $educationInstitution);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * get History Education Institution from DB
     * @param request
     */
    public function getHistory(Request $request) {

        $this->validate($request, [
            "companyId" => "required|integer",
            'name' => 'required|max:50|exists:education_institutions,name',
            'id' => 'required|integer|exists:education_institutions,id']);

        $data = $this->educationInstitutionDao->getHistory($request->name, $request->id);

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update Education Institution request.
     * @param request
     */
    private function checkEducationInstitutionRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'name' => 'required|max:50',
            'lovAcreditation' => 'required',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date'
        ]);
    }

    /**
     * Construct an Education Institution object (array).
     * @param request
     */
    private function constructEducationInstitution(Request $request)
    {
        $educationInstitution = [
            "tenant_id"          => $this->requester->getTenantId(),
            "company_id"         => $this->requester->getCompanyId(),
            "name"               => $request->name,
            "country_code"       => $request->countryCode,
            "lov_acreditation"   => $request->lovAcreditation,
            "address"            => $request->address,
            "eff_begin"          => $request->effBegin,
            "eff_end"            => $request->effEnd,
            "link_website"       => $request->linkWebsite
        ];
        return $educationInstitution;
    }

    private function getUsedByEmployee($institutionName) {
        $data = array();
        $getDataPersonEdu = $this->personEducationDao->getAllPersonIdByInstitution($institutionName);

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
