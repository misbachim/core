<?php

namespace App\Http\Controllers;

use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB, Log;

use App\Business\Dao\PerformanceLevelDao;


/**
 * Class for handling asset process
 */
class PerformanceLevelController extends Controller
{
    public function __construct(
        Requester $requester,
        PerformanceLevelDao $performanceLevelDao
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->performanceLevelDao = $performanceLevelDao;
    }


    /*
    |-----------------------------
    | get all data dari database
    |-----------------------------
    | @param $request <object>
    |
    |
    */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "pageInfo" => "required|array"
        ]);
        $data = $this->performanceLevelDao->getOne($request->companyId);
        if ($data) {
            $data->items = $this->performanceLevelDao->getAllItem($request->companyId, $data->id);
        }
        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
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
            $obj = $this->constructData($request);
            $oldData = $this->performanceLevelDao->getOne($request->companyId);
            if ($oldData) {
                $id = $oldData->id;
                $this->performanceLevelDao->deleteItems($id);
                $this->performanceLevelDao->update($obj, $id);
            } else {
                $id = $this->performanceLevelDao->saveGetId($obj);
            }
            $objItems = $this->constructDataItems($request, $id);
            $this->performanceLevelDao->saveItems($objItems);

        });
        $resp = new AppResponse(null, trans('messages.dataSaved'));
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
            'defaultValue' => 'integer|max:5',
            'synchronize' => 'required',
            'items.*.level' => 'required',
            'items.*.description' => 'required'
        ]);
    }



    /*
    |-----------------------------------------------
    | construct object data yang akan di save
    |-----------------------------------------------
    |
    |
    */
    public function constructData ($request) {
        return
        [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "default_value" => $request->defaultValue,
            "sync_with_review" => $request->synchronize
        ];
    }


    /*
    |-----------------------------------------------
    | construct object data items yang akan di save
    |-----------------------------------------------
    |
    |
    */
    public function constructDataItems($request, $performanceLevelId) {
        $items = [];
        foreach ($request->items as $item) {
            array_push($items, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'performance_level_id' => $performanceLevelId,
                'level' => $item['level'],
                'description' => $item['description'],
                'equivalent' => $item['equivalent'],
                'value1' => $item['val1'],
                'value12' => $item['val2'],
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now()
                ]);
            }
        return $items;
    }
}
