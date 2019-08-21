<?php

namespace App\Http\Controllers;

use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB, Log;

use App\Business\Dao\ReadinessLevelDao;


/**
 * Class for handling asset process
 */
class ReadinessLevelController extends Controller
{
    public function __construct(
        Requester $requester,
        ReadinessLevelDao $readinessLevelDao
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->readinessLevelDao = $readinessLevelDao;
    }


    /*
    |-----------------------------
    | get all data dari database
    |-----------------------------
    | @param $request <object>
    |
    |
    */
    public function getAll(Request $request)
    {
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
        $limit  = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $datas = $this->readinessLevelDao->getAll($request->companyId);
        $totalRow = count($datas);
        return $this->renderResponse(new PagingAppResponse($datas, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));
    }


    /*
    |--------------------------------------------------
    | save data ke database
    |--------------------------------------------------
    |
    |
    */
    public function save(Request $request)
    {
        $this->checkRequest($request);
        DB::transaction(function () use (&$request, &$data) {
            $oldData = $this->readinessLevelDao->getOneActive();
            // TODO update data yang ready now true jadi false jika request ready now nya true
            // if ($oldData) {
            //     $objUpdate = ['eff_end' => Carbon::today()];
            //     $this->readinessLevelDao->update($objUpdate, $oldData->id);
            // }
            $obj = $this->constructData($request);
            $this->readinessLevelDao->save($obj);
        });
        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }


    /*
    |--------------------------------------------------
    | update data ke database
    |--------------------------------------------------
    |
    |
    */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkRequest($request);
        DB::transaction(function () use (&$request) {
            $objUpdate = $this->constructData($request);
            $this->readinessLevelDao->update($objUpdate, $request->id);
        });
        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }


    /*
    |-----------------------------
    | check request data dari ui
    |-----------------------------
    |
    |
    */
    public function checkRequest ($request) {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'description' => 'string',
            'name' => 'string|max:50',
            'color' => 'string:max:7'
        ]);
    }



    /*
    |-----------------------------------------------
    | construct object data yang akan di saveDuties
    |-----------------------------------------------
    |
    |
    */
    public function constructData ($request) {
        return
        [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "description" => $request->description,
            "name" => $request->name,
            "ready_now" => $request->readyNow,
            "color" => $request->color
        ];
    }



    /*
    |-----------------------------
    | lov readiness level
    |-----------------------------
    |
    |
    */
    public function lov(Request $request) {
        $this->validate($request, [
            "companyId" => "required|integer"
        ]);
        $datas = $this->readinessLevelDao->getLov($request->companyId);
        $resp = new AppResponse($datas, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }
}
