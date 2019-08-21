<?php

namespace App\Http\Controllers;
use App\Business\Dao\JobCompetencyDao;
use App\Business\Dao\JobCompetencyListDao;
use App\Business\Dao\RatingScaleDetailDao;

use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;

/**
 * Class for handling job process
 * @property Requester requester
 */
class JobCompetencyController extends Controller
{
    public function __construct(
        Requester $requester,
        JobCompetencyDao $jobCompetencyDao,
        RatingScaleDetailDao $ratingScaleDetailDao,
        JobCompetencyListDao $jobCompetencyListDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->jobCompetencyDao = $jobCompetencyDao;
        $this->ratingScaleDetailDao = $ratingScaleDetailDao;
        $this->jobCompetencyListDao = $jobCompetencyListDao;
    }


    public function getAllJobCompetency(Request $request)
    {
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

        $data = $this->jobCompetencyListDao->getAllByJobCode($request->code, $offset, $pageLimit);

        /**
         * TODO foreach $data untuk mengambil competency model untuk menampilkan button delete di ui
         **/

        foreach ($data as $key => $value) {
            $competency = $data[$key];
            if ($value->jobCompetencyId) {
                $competency->jobCompetencyModel = $this->jobCompetencyDao->getCompetencyCode($value->jobCompetencyId)->name;
            } else{
                $competency->jobCompetencyModel = '';
            }

            $competency->ratingScale = $this->ratingScaleDetailDao->getOne($value->ratingScaleDetailId);
            $competency->ratingScale->rows = $this->ratingScaleDetailDao->getNumberOfLevels($competency->ratingScale->ratingScaleId);
        }
        $totalRows = $this->jobCompetencyListDao->getTotalRows($request->code);

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $totalRows,
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }


    /**
     * save job campetency
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required'
        ]);

        $this->checkCompetencyList($request);

        DB::transaction(function () use (&$request, &$data) {

            $jobCompetencyModel = $this->jobCompetencyDao->getActive($request->jobCode);
            if ($jobCompetencyModel) {
                // update effEnd job competency list and job competency
                $listCompetencies = $this->jobCompetencyListDao->getAllByCompetencyId($jobCompetencyModel->id);
                $this->updateData($listCompetencies);
                $objJobCompetency = ['eff_end' => Carbon::today()];
                $this->jobCompetencyDao->update($objJobCompetency, $jobCompetencyModel->id);
            } else {
                // update effEnd job competency list
                $listCompetencies = $this->jobCompetencyListDao->getAllIdByCompetencyJobCode($request->jobCode);
                $this->updateData($listCompetencies);
            }
            $id = null;
            if ($request->has('competencyModel') && $request->competencyModel !== []) {
                $data = $this->constructDataModel($request);
                $id = $this->jobCompetencyDao->save($data);
                $list = $this->constructDataCompetenciesList($request->listCompetency, $id, $request->companyId, $request->competencyModel['jobCode']);
                $this->jobCompetencyListDao->save($list);
            } else {
                $list = $this->constructDataCompetency($request->listCompetency, $id, $request->companyId);
                $this->jobCompetencyListDao->save($list);
            }
        });

        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }


    public function updateData($listCompetencies)
    {
        DB::transaction(function () use (&$listCompetencies) {
            foreach ($listCompetencies as $key => $competency) {
                $obj = ['eff_end' => Carbon::today()];
                $this->jobCompetencyListDao->update($obj, $competency->id);
            }
        });
    }



    public function checkCompetencyList($request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer',
            'listCompetency.*.id' => 'required|integer',
            'listCompetency.*.code' => 'required|string',
            'listCompetency.*.name' => 'required|string',
            'listCompetency.*.effBegin' => 'required|date',
            'listCompetency.*.effEnd' => 'required|date',
            'listCompetency.*.essential' => 'required|boolean',
            'listCompetency.*.ratingScaleDetailId' => 'required|integer',
            'listCompetency.*.valMargin' => 'required|integer',
            'listCompetency.*.levelMargin' => 'required|string',
            'listCompetency.*.useInReview' => 'required|boolean',
        ]);
    }


    public function constructDataModel($request)
    {
        $data = [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $request->companyId,
            'eff_begin' => $request->competencyModel['effBegin'],
            'eff_end' => $request->competencyModel['effEnd'],
            'job_code' => $request->competencyModel['jobCode'],
            'competency_model_code' => $request->competencyModel['competencyCode']
        ];
        return $data;
    }

    public function constructDataCompetenciesList($list, $id, $companyId, $jobCode)
    {
        $data = [];
        foreach ($list as $key => $value) {
            $obj = [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $companyId,
                'job_competency_id' => $id,
                'job_code' => $jobCode,
                'competency_code' => $value['code'],
                'eff_begin' => $value['effBegin'],
                'eff_end' => $value['effEnd'],
                'essential' => $value['essential'],
                'rating_scale_detail_id' => $value['ratingScaleDetailId'],
                'margin_value' => $value['valMargin'],
                'margin_level' => $value['levelMargin'],
                'use_in_review' => $value['useInReview'],
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now()
            ];

            array_push($data, $obj);
        }
        return $data;
    }


    public function constructDataCompetency($list, $id, $companyId)
    {
        $data = [];
        foreach ($list as $key => $value) {
            $obj = [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $companyId,
                'job_competency_id' => $id,
                'job_code' => $value['jobCode'],
                'competency_code' => $value['code'],
                'eff_begin' => $value['effBegin'],
                'eff_end' => $value['effEnd'],
                'essential' => $value['essential'],
                'rating_scale_detail_id' => $value['ratingScaleDetailId'],
                'margin_value' => $value['valMargin'],
                'margin_level' => $value['levelMargin'],
                'use_in_review' => $value['useInReview'],
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now()
            ];

            array_push($data, $obj);
        }
        return $data;
    }
}
