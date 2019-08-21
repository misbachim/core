<?php

namespace App\Http\Controllers;

use App\Business\Dao\GradeDao;
use App\Business\Model\Requester;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling grade process
 */
class GradeController extends Controller
{
    public function __construct(Requester $requester, GradeDao $gradeDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->gradeDao = $gradeDao;
        $this->gradeFields = array('effBegin', 'effEnd', 'code',
            'name', 'ordinal', 'workMonth', 'bottomRate', 'midRate', 'topRate');
    }

    /**
     * Get all grades in one company
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required|integer"]);

        $grades = $this->gradeDao->getAll();

        $resp = new AppResponse($grades, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all Active Grade in one company
     */
    public function getAllActive(Request $request){
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

        $data = $this->gradeDao->getAllActive($offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->gradeDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
     * Get All InActive Grade in one company
     */
    public function getAllInActive(Request $request){
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

        $data = $this->gradeDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->gradeDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }


    /**
     * Get all active grades in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $lov = $this->gradeDao->getLov();

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }


    /**
     * Get one grade based on grade id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $grade = $this->gradeDao->getOne(
            $request->id
        );

        $data = array();
        if (count($grade) > 0) {
            $data['id'] = $grade->id;
            foreach ($this->gradeFields as $field) {
                $data[$field] = $grade->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }


    /**
     * Get one grade based on grade code
     * @param request
     */
    public function getOneByCode(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "code" => "required|integer"
        ]);

        $grade = $this->gradeDao->getOneByCode(
            $request->code
        );

        $data = array();
        if (count($grade) > 0) {
            $data['id'] = $grade->id;
            foreach ($this->gradeFields as $field) {
                $data[$field] = $grade->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save grade to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkGradeRequest($request);

        //codes must be unique
        if ($this->gradeDao->checkDuplicateGradeCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $grade = $this->constructGrade($request);
            $data['id'] = $this->gradeDao->save($grade);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update grade to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);
        $this->checkGradeRequest($request);

        //codes must be unique
        if ($this->gradeDao->checkDuplicateEditGradeCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request) {
            $grade = $this->constructGrade($request);
            unset($grade['code']);
            $this->gradeDao->update(
                $request->id,
                $grade
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update grade request.
     * @param request
     */
    private function checkGradeRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date',
            'effEnd' => 'required|date|after_or_equal:effBegin',
            'ordinal' => 'required|integer',
            'name' => 'required|max:50',
            'code' => 'required|max:20|alpha_num',
            'workMonth' => 'present|integer',
            'bottomRate' => 'present|nullable|integer|max_field:topRate',
            //'midRate' => 'present|nullable|integer|max_field:topRate|min_field:bottomRate',
            'topRate' => 'present|nullable|integer|min_field:bottomRate'
        ]);
    }

    /**
     * Construct a grade object (array).
     * @param request
     */
    private function constructGrade(Request $request)
    {
        $grade = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "name" => $request->name,
            "code" => $request->code,
            "ordinal" => $request->ordinal,
            "work_month" => $request->workMonth,
            "bottom_rate" => $request->bottomRate,
            "mid_rate" => $request->midRate,
            'top_rate' => $request->topRate,
        ];
        return $grade;
    }
}
