<?php

namespace App\Http\Controllers;

use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

use App\Business\Dao\CareerPathDao;


/**
 * Class for handling City process
 */
class CareerPathController extends Controller
{
    public function __construct(
        Requester $requester,
        CareerPathDao $careerPathDao
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->careerPathDao = $careerPathDao;
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
        $data = $this->careerPathDao->getAll();
        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }


    /*
    |-----------------------------
    | get data by caree path ID
    |-----------------------------
    | @param $request <object>
    |
    |
    */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer",
        ]);
        $data = $this->careerPathDao->getOne($request->id);
        if ($data) {
            $hierarchy = $this->careerPathDao->getHierarchies($data->id);
            $data->flatHierarchy = $hierarchy ;
            foreach ($hierarchy as $key => $value) {
               $hierarchy[$key]->children = [];
            }
            $datas = collect($hierarchy);
            $maxLevel = $datas->max('level');

            for ($i = (int)$maxLevel; $i > 0; $i--) {
			    for ($j = 0; $j < count($hierarchy); $j++) {
                    if ((int)$i === (int)$hierarchy[$j]->level) {
                        $newData = $hierarchy[$j];
                        foreach ($hierarchy as $keyHierarchy => $valueHierarchy) {
                            if ($valueHierarchy->positionCode == $newData->parentCode) {
                                array_push($hierarchy[$keyHierarchy]->children, $newData);
                            }
                        }
                    }
                }
            }
            $data->hierarchies = $hierarchy[0];
        }
        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }


    /*
    |-----------------------------
    | save data ke database
    |-----------------------------
    |
    |
    */
    public function save(Request $request) 
    {
        $this->checkRequest($request);
        if ($this->careerPathDao->checkDuplicate($request->name) > 0) {
            throw new AppException(trans('messages.duplicateName'));
        }
        $careerPathId = DB::transaction(function () use (&$request, &$data) {
            $obj = $this->constructData($request);
            $id = $this->careerPathDao->saveGetId($obj);
            $careerPathId = $id;
            $objItems = $this->constructDataItems($request, $id);
            $this->careerPathDao->saveItems($objItems);
            return $id;
        });
        $data = ['id' => $careerPathId];
        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }


    /*
    |-----------------------------
    | update data ke database
    |-----------------------------
    |
    |
    */
    public function update(Request $request) 
    {
        $this->checkRequest($request);
        if ($this->careerPathDao->checkDuplicateUpdate($request->name, $request->id) > 0) {
            throw new AppException(trans('messages.duplicateName'));
        }
        DB::transaction(function () use (&$request, &$data) {
            $obj = $this->constructData($request);
            $this->careerPathDao->update($obj, $request->id);
            $this->careerPathDao->deleteItem($request->id);
            $objItems = $this->constructDataItems($request, $request->id);
            $this->careerPathDao->saveItems($objItems);
        });
        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }



    /*
    |-----------------------------
    | save data ke database
    |-----------------------------
    |
    |
    */
    public function delete(Request $request) 
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer",
        ]);
        DB::transaction(function () use (&$request, &$data) {
            $this->careerPathDao->delete($request->id);
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
            'name' => 'required',
            'hierarchy.*.positionCode' => 'required'
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
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $request->companyId,
            'name' => $request->name,
            'description' => $request->description
        ];
    }


    /*
    |-----------------------------------------------
    | construct object data items yang akan di save
    |-----------------------------------------------
    |
    |
    */
    public function constructDataItems($request, $careerPatId) {
        $items = [];
        foreach ($request->hierarchy as $item) {
            array_push($items, [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'career_path_id' => $careerPatId,
                'level' => $item['level'],
                'position_code' => $item['positionCode'],
                'parent_position_code' => $item['parentCode'],
                'created_by' => $this->requester->getUserId(),
                'created_at' => Carbon::now()
                ]);
            }
        return $items;
    }


}
