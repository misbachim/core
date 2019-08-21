<?php

namespace App\Http\Controllers;
use App\Business\Dao\PositionCompetencyDao;
use App\Business\Dao\PositionCompetencyListDao;
use App\Business\Dao\RatingScaleDetailDao;

use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;

/**
 * Class for handling position process
 * @property Requester requester
 */
class PositionCompetencyController extends Controller
{
    public function __construct(
        Requester $requester,
        PositionCompetencyDao $positionCompetencyDao,
        RatingScaleDetailDao $ratingScaleDetailDao,
        PositionCompetencyListDao $positionCompetencyListDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->positionCompetencyDao = $positionCompetencyDao;
        $this->ratingScaleDetailDao = $ratingScaleDetailDao;
        $this->positionCompetencyListDao = $positionCompetencyListDao;
    }


    public function getAllPositionCompetency(Request $request)
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

        $data = $this->positionCompetencyListDao->getAllByPositionCode($request->code, $offset, $pageLimit);

        /**
         * TODO foreach $data untuk mengambil competency model untuk menampilkan button delete di ui
         **/

        foreach ($data as $key => $value) {
            $competency = $data[$key];
            if ($value->positionCompetencyId) {
                $competency->positionCompetencyModel = $this->positionCompetencyDao->getCompetencyCode($value->positionCompetencyId)->name;
            } else{
                $competency->positionCompetencyModel = '';
            }

            $competency->ratingScale = $this->ratingScaleDetailDao->getOne($value->ratingScaleDetailId);
            $competency->ratingScale->rows = $this->ratingScaleDetailDao->getNumberOfLevels($competency->ratingScale->ratingScaleId);
        }
        $totalRows = $this->positionCompetencyListDao->getTotalRows($request->code);

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
     * save position campetency
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

            $positionCompetencyModel = $this->positionCompetencyDao->getActive($request->positionCode);
            if ($positionCompetencyModel) {
                // update effEnd position competency list and position competency
                $listCompetencies = $this->positionCompetencyListDao->getAllByCompetencyId($positionCompetencyModel->id);
                $this->updateData($listCompetencies);
                $objPositionCompetency = ['eff_end' => Carbon::today()];
                $this->positionCompetencyDao->update($objPositionCompetency, $positionCompetencyModel->id);
            } else {
                // update effEnd position competency list
                $listCompetencies = $this->positionCompetencyListDao->getAllIdByCompetencyPositionCode($request->positionCode);
                $this->updateData($listCompetencies);
            }
            $id = null;
            if ($request->has('competencyModel') && $request->competencyModel !== []) {
                $data = $this->constructDataModel($request);
                $id = $this->positionCompetencyDao->save($data);
                $list = $this->constructDataCompetenciesList($request->listCompetency, $id, $request->companyId, $request->competencyModel['jobCode']);
                $this->positionCompetencyListDao->save($list);
            } else {
                $list = $this->constructDataCompetency($request->listCompetency, $id, $request->companyId);
                $this->positionCompetencyListDao->save($list);
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
                $this->positionCompetencyListDao->update($obj, $competency->id);
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
            'position_code' => $request->competencyModel['jobCode'],
            'competency_model_code' => $request->competencyModel['competencyCode']
        ];
        return $data;
    }

    public function constructDataCompetenciesList($list, $id, $companyId, $positionCode)
    {
        $data = [];
        foreach ($list as $key => $value) {
            $obj = [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $companyId,
                'position_competency_id' => $id,
                'position_code' => $positionCode,
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
                'position_competency_id' => $id,
                'position_code' => $value['positionCode'],
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
