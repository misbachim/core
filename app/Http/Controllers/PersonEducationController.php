<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonEducationDao;
use App\Business\Dao\LovDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling personEducation process
 */
class PersonEducationController extends Controller
{
    public function __construct(Requester $requester, PersonEducationDao $personEducationDao, LovDao $lovDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personEducationDao = $personEducationDao;
        $this->lovDao = $lovDao;
        $this->personEducationFields = array('id', 'lovEdul', 'institution', 'subject', 'grade', 'maxGrade',
            'yearBegin', 'yearEnd');
    }

    /**
     * Get all personEducations for one person
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["personId" => "required|integer"]);

        $personEducations = $this->personEducationDao->getAll(
            $request->personId
        );
        if(count($personEducations) > 0) {
            for($i = 0 ; $i < count($personEducations) ; $i++) {
                if(property_exists($personEducations[$i], 'lovEdul')) {
                    $EDUL = $this->lovDao->getOne('EDUL',$personEducations[$i]->lovEdul);
                    count($EDUL)>0 ?
                        $personEducations[$i]->education = $EDUL->valData
                        : $personEducations[$i]->education = '';
                }
                else {
                    $personEducations[$i]->education = '';
                }

                if(property_exists($personEducations[$i], 'specializationCode')) {
                    $personEducation = $this->personEducationDao->getOneEducationSpecialization($personEducations[$i]->specializationCode);
                    count($personEducation)>0 ?
                        $personEducations[$i]->subject = $personEducation->name
                        : $personEducations[$i]->subject = '';
                }
                else {
                    $personEducations[$i]->subject = '';
                }
            }
        }

        $resp = new AppResponse($personEducations, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one personEducation based on personEducation id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "personId" => "required|integer",
            "id" => "required|integer"
        ]);

        $personEducation = $this->personEducationDao->getOne(
            $request->personId,
            $request->id
        );

        $data = array();
        if (count($personEducation) > 0) {
            foreach ($this->personEducationFields as $field) {
                $data[$field] = $personEducation->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personEducation to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonEducationRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $personEducation = $this->constructPersonEducation($request);
            $data['id'] = $this->personEducationDao->save($personEducation);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personEducation to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);
        $this->checkPersonEducationRequest($request);

        DB::transaction(function () use (&$request) {
            $personEducation = $this->constructPersonEducation($request);
            $this->personEducationDao->update(
                $request->personId,
                $request->id,
                $personEducation
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete person education by id.
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required|integer",
            "personId" => "required|integer"
        ]);

        DB::transaction(function () use (&$request) {
            $this->personEducationDao->delete(
                $request->personId,
                $request->id
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update personEducation request.
     * @param request
     */
    private function checkPersonEducationRequest(Request $request)
    {
        $this->validate($request, [
            'lovEdul' => 'required|max:10|exists:lovs,key_data',
            'institution' => 'required|max:50',
            'subject' => 'required|max:50',
            'grade' => 'required|numeric|max_field:maxGrade',
            'maxGrade' => 'required|numeric|min_field:grade',
            'yearBegin' => 'required|integer',
            'yearEnd' => 'integer|min_field:yearBegin|nullable'
        ]);
    }

    /**
     * Construct a personEducation object (array).
     * @param request
     */
    private function constructPersonEducation(Request $request)
    {
        $personEducation = [
            'tenant_id' => $this->requester->getTenantId(),
            'person_id' => $request->personId,
            'lov_edul' => $request->lovEdul,
            'institution' => $request->institution,
            'subject' => $request->subject,
            'grade' => $request->grade,
            'max_grade' => $request->maxGrade,
            'year_begin' => $request->yearBegin,
            'year_end' => $request->yearEnd ? $request->yearEnd : null
        ];
        return $personEducation;
    }
}
