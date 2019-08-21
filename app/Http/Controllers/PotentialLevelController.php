<?php

namespace App\Http\Controllers;

use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB, Log;

use App\Business\Dao\PotentialLevelDao;


/**
 * Class for handling asset process
 */
class PotentialLevelController extends Controller
{
    public function __construct(
        Requester $requester,
        PotentialLevelDao $potentialLevelDao
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->potentialLevelDao = $potentialLevelDao;
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
        $data = $this->potentialLevelDao->getOne($request->companyId);
        if ($data) {
            $data->items = $this->potentialLevelDao->getAllItem($request->companyId, $data->id);
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
            $oldData = $this->potentialLevelDao->getOne($request->companyId);
            if ($oldData) {
                $id = $oldData->id;
                $this->potentialLevelDao->deleteItems($id);

                // TODO tambah column eff_begin eff_end jika menggunakan mode history
                // TODO ganti fungsi diatas dengan update eff_end = Today()

                $this->potentialLevelDao->update($obj, $id);
            } else {
                $id = $this->potentialLevelDao->saveGetId($obj);
            }
            $objItems = $this->constructDataItems($request, $id);
            $this->potentialLevelDao->saveItems($objItems);

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
            'level' => 'integer|max:5',
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
            "level" => $request->potentialLevel
        ];
    }


    /*
    |-----------------------------------------------
    | construct object data items yang akan di save
    |-----------------------------------------------
    |
    |
    */
    public function constructDataItems($request, $potentialLevelId) {
        $items = [];
        foreach ($request->items as $item) {
            array_push($items, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'potential_level_id' => $potentialLevelId,
                'level' => $item['level'],
                'description' => $item['description'],
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now()
                ]);
            }
        return $items;
    }
}
