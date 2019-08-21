<?php

namespace App\Http\Controllers;

use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB, Log;

use App\Business\Dao\PotentialPerformanceMatrixDao;


/**
 * Class for handling asset process
 */
class PotentialPerformanceMatrixController extends Controller
{
    public function __construct(
        Requester $requester,
        PotentialPerformanceMatrixDao $potentialPerformanceMatrixDao
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->potentialPerformanceMatrixDao = $potentialPerformanceMatrixDao;
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
        $data = $this->potentialPerformanceMatrixDao->getAll($request->companyId);
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
            // $oldData = $this->potentialPerformanceMatrixDao->getAll($request->companyId);
            // if (count($oldData) > 0) {
            //     foreach ($oldData as $key => $value) {
            //         $this->potentialPerformanceMatrixDao->endActiveData($value->id);
            //     }
            // }
            $this->potentialPerformanceMatrixDao->delete();
            $this->potentialPerformanceMatrixDao->save($obj);

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
            'data.*.description' => 'required',
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
        $datas = [];
        foreach ($request->data as $key => $value) {
            array_push($datas, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'description' => $value['description'],
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now()
            ]);
        }
        return $datas;
    }

}
