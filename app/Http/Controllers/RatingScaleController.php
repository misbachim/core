<?php

namespace App\Http\Controllers;

use App\Business\Dao\RatingScaleDao;
use App\Business\Dao\RatingScaleDetailDao;
use App\Business\Dao\ItemProficiencyDao;
use App\Business\Dao\PositionCompetencyModelDao;
use App\Business\Dao\PersonCompetencyModelDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class for handling rating_scale process
 */
class RatingScaleController extends Controller
{
    public function __construct(
        Requester $requester,
        RatingScaleDao $ratingScaleDao,
        RatingScaleDetailDao $ratingScaleDetailDao,
        ItemProficiencyDao $itemProficiencyDao,
        PositionCompetencyModelDao $positionCompetencyModelDao,
        PersonCompetencyModelDao $personCompetencyModelDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->ratingScaleDao = $ratingScaleDao;
        $this->ratingScaleDetailDao = $ratingScaleDetailDao;
        $this->itemProficiencyDao = $itemProficiencyDao;
        $this->positionCompetencyModelDao = $positionCompetencyModelDao;
        $this->personCompetencyModelDao = $personCompetencyModelDao;
        $this->ratingScaleFields = array('code', 'name', 'description');
    }

    /**
     * Get all rating_scales
     * @param request
     */
    public function getAll(Request $request)
    {
        $data = array();
        $this->validate($request, [
            "companyId" => "required|integer",
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

        $getRatingScale = $this->ratingScaleDao->getAll(
            $offset,
            $limit
        );

        $countRowRatingScale = count($getRatingScale);

        if ($countRowRatingScale > 0) {
            foreach ($getRatingScale as $data) {
                $data->numberOfLevels = $this->ratingScaleDetailDao->getNumberOfLevels($data->id);
            }
        }

        return $this->renderResponse(new PagingAppResponse($getRatingScale, trans('messages.allDataRetrieved'), $limit, $countRowRatingScale, $pageNo));
    }

    /**
     * Get all rating_scales
     * @param request
     */
    public function getAllActive(Request $request)
    {
        $data = array();
        $this->validate($request, [
            "companyId" => "required|integer",
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

        $getRatingScale = $this->ratingScaleDao->getAllActive(
            $offset,
            $limit
        );
        $countRowRatingScale = count($getRatingScale);

        if ($countRowRatingScale > 0) {
            foreach ($getRatingScale as $data) {
                $data->numberOfLevels = $this->ratingScaleDetailDao->getNumberOfLevels($data->id);
            }
        }

        return $this->renderResponse(new PagingAppResponse($getRatingScale, trans('messages.allDataRetrieved'), $limit, $countRowRatingScale, $pageNo));
    }

    /**
     * Get all rating_scales
     * @param request
     */
    public function getAllInactive(Request $request)
    {
        $data = array();
        $this->validate($request, [
            "companyId" => "required|integer",
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

        $getRatingScale = $this->ratingScaleDao->getAllInactive(
            $offset,
            $limit
        );

        $countRowRatingScale = count($getRatingScale);

        if ($countRowRatingScale > 0) {
            foreach ($getRatingScale as $data) {
                $data->numberOfLevels = $this->ratingScaleDetailDao->getNumberOfLevels($data->id);
            }
        }

        return $this->renderResponse(new PagingAppResponse($getRatingScale, trans('messages.allDataRetrieved'), $limit, $countRowRatingScale, $pageNo));
    }

    /**
     * get History Rating Scale from DB
     * @param request
     */
    public function getHistory(Request $request)
    {

        $this->validate($request, [
            "companyId" => "required|integer",
            'code' => 'required|max:50|exists:rating_scales,code',
            'id' => 'required|integer|exists:rating_scales,id'
        ]);

        $data = $this->ratingScaleDao->getHistory($request->code, $request->id);
        $countRowRatingScale = count($data);

        if ($countRowRatingScale > 0) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]->numberOfLevels = $this->ratingScaleDetailDao->getNumberOfLevels($data[$i]->id);
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get One rating_scale in one tenant
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, ['code' => 'required|string']);

        $rating_scale = $this->ratingScaleDao->getOne($request->code);
        $rating_scale->ratingScaleLevels = $this->ratingScaleDetailDao->getAllByRatingScale($rating_scale->id);

        $resp = new AppResponse($rating_scale, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all rating_scales (lib+rating_scales) in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $lov = $this->ratingScaleDao->getLov();

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all rating_scales (lib+rating_scales) in one company
     * @param request
     */
    public function getDetailLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $data = $this->RatingScaleDetailDao->getLov();

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Location Group to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkRatingScaleRequest($request);

        if ($request->flag === 'SAVE') {
            //codes must be unique
            if ($this->ratingScaleDao->checkDuplicateRatingScaleCode($request->code) > 0) {
                throw new AppException(trans('messages.duplicateCode'));
            }
        }
        if ($request->flag === 'EDIT') {
            $this->validate($request, [
                'id' => 'required|integer|exists:rating_scales,id'
            ]);
        }

        DB::transaction(function () use (&$request, &$data) {
            $rating_scale = $this->constructRatingScale($request);
            $getId = null;

            if ($request->flag === 'EDIT') {
                // delete anak
                $this->ratingScaleDetailDao->delete($request->id);
                $this->ratingScaleDao->update($request->id, $rating_scale);
                $getId = $request->id;

            } else {
                $data['id'] = $this->ratingScaleDao->save($rating_scale);
                $getId = $data['id'];
            }

            $this->saveRatingScaleDetail($request, $getId);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    public function checkCondition(Request $request)
    {

        $this->validate($request, [
            'id' => 'required|integer|exists:rating_scales,id'
        ]);

        $condition = ['condition' => false];

        $getRatingScaleDetail = $this->ratingScaleDetailDao->getAllByRatingScale($request->id);

        if ($getRatingScaleDetail) {
            foreach ($getRatingScaleDetail as $getData) {
                $getItemProficiencies = $this->itemProficiencyDao->getAllByRatingScaleDetailId($getData->id);
                $getPositionCompetency = $this->positionCompetencyModelDao->getAllDetailsByRatingScaleDetailId($getData->id);
                $getPersonCompetency = $this->personCompetencyModelDao->getAllDetailsByRatingScaleDetailId($getData->id);

                if (count($getItemProficiencies) > 0 ||
                    count($getPositionCompetency) > 0 ||
                    count($getPersonCompetency) > 0) {
                    $condition = ['condition' => true];
                    break;
                }
            }
        }

        $resp = new AppResponse($condition, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update rating_scale request.
     * @param request
     */
    private function checkRatingScaleRequest(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|max:20',
            'name' => 'required|max:50',
            'description' => 'present|max:255'
        ]);
    }

    /**
     * Construct an rating_scale object (array).
     * @param request
     */
    private function constructRatingScale(Request $request)
    {
        $rating_scale = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "code" => $request->code,
            "name" => $request->name,
            "description" => $request->description,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
        ];
        return $rating_scale;
    }

    /**
     * Save rating scale's detailed information.
     * @param request, ratingScale
     */
    private function saveRatingScaleDetail(Request $request, $ratingScaleId)
    {
        if ($request->has('ratingScaleLevels')) {
            for ($i = 0; $i < count($request->ratingScaleLevels); $i++) {
                $data = array();
                
                //level must be unique
                $countLevel = count($request->ratingScaleLevels);
                $countUniqueLevel = count(collect($request->ratingScaleLevels)->unique('level'));
                if ($countLevel !== $countUniqueLevel) {
                    throw new AppException(trans('messages.levelMustUnique'));
                }

                array_push($data, [
                    "tenant_id" => $this->requester->getTenantId(),
                    "company_id" => $this->requester->getCompanyId(),
                    "label" => $request->ratingScaleLevels[$i]['label'],
                    "level" => $request->ratingScaleLevels[$i]['level'],
                    "rating_scale_id" => $ratingScaleId,
                    "created_at" => Carbon::now(),
                    "created_by" => $this->requester->getUserId()
                ]);

                $this->ratingScaleDetailDao->save($data);
            }
        }
    }
}
