<?php

namespace App\Http\Controllers;

use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB, Log;

use App\Business\Dao\TalentPoolDao;
use App\Business\Dao\TalentPoolNomineeDao;
use App\Business\Dao\PersonDao;
use App\Business\Dao\UM\UserDao;


/**
 * Class for handling asset process
 */
class TalentPoolController extends Controller
{
    public function __construct(
        Requester $requester,
        TalentPoolDao $talentPoolDao,
        TalentPoolNomineeDao $talentPoolNomineeDao,
        PersonDao $personDao,
        UserDao $userDao
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->talentPoolNomineeDao = $talentPoolNomineeDao;
        $this->talentPoolDao = $talentPoolDao;
        $this->personDao = $personDao;
        $this->userDao = $userDao;
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

        $data = $this->talentPoolDao->getAll($offset, $limit);
        foreach ($data as $key => $value) {
            $data[$key]->employeeConfirm = $this->talentPoolNomineeDao->countNominee($value->id);
        }
        $countRowQS = count($data);
        return $this->renderResponse(new PagingAppResponse($data, trans('messages.allDataRetrieved'), $limit, $countRowQS, $pageNo));
    }


    /*
    |-----------------------------
    | get detail data talent pool
    |-----------------------------
    | @param id <int>
    |
    */
    public function getOne (Request $request) {
         $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);
        $data = $this->talentPoolDao->getOne($request->id);
        $dataEmployee = $this->talentPoolNomineeDao->getAllNomineeByTalentPoolId($data->id);
        $createdBy = $this->userDao->getPersonName($data->createdBy);
        $data->createdBy = $createdBy->personName;
        $data->employeeConfirm = $dataEmployee;

        foreach ($dataEmployee as $key => $value) {
            $person = $this->personDao->getFullNameAndPosition($value->employeeId);
            $data->employeeConfirm[$key]->employeeName = $person->fullName;
            $data->employeeConfirm[$key]->position = $person->position;
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
            $this->talentPoolDao->save($obj);
        });
        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }


   /*
    |--------------------------------------------------
    | update data di database
    |--------------------------------------------------
    |
    |
    */
    public function update(Request $request)
    {
        $this->checkRequest($request);
        DB::transaction(function () use (&$request, &$data) {
            $obj = $this->constructData($request);
            $this->talentPoolDao->update($obj, $request->id);
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
            'name' => 'string',
            'automatic' => 'boolean',
            'effBegin' => 'required|date',
            'effEnd' => 'required|date|after_or_equal:effBegin',
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
            'name' => $request->name,
            'automatic' => $request->automatic,
            'eff_begin' => $request->effBegin,
            'eff_end' => $request->effEnd,
            'description' => $request->description
        ];
    }


}
