<?php

namespace App\Http\Controllers;

use App\Business\Dao\RatingScaleDetailDao;
use App\Business\Dao\RatingScaleDao;
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
class RatingScaleDetailController extends Controller
{
    public function __construct(
        Requester $requester, 
        RatingScaleDetailDao $ratingScaleDetailDao,
        RatingScaleDao $ratingScaleDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->ratingScaleDetailDao = $ratingScaleDetailDao;
        $this->ratingScaleDao = $ratingScaleDao;
    }

    public function getAllByRatingScaleId(Request $request)
    {
        // \Log::info(print_r($request, true));

        // $this->validate($request, [
        //     "companyId" => "required|integer",
        //     "id" => "required|integer"
        // ]);
        
        $ratingScaleId = $this->ratingScaleDao->getOne($request->code)->id;

        $data = $this->ratingScaleDetailDao->getAllByRatingScale($ratingScaleId);

        if($data == null){
            throw new AppException(trans('messages.dataNotFound'));
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

}

