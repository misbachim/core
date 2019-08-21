<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Dao\ReportParameter\PersonRptParamDao;
use App\Business\Dao\ReportParameter\JobRptParamDao;
use App\Business\Dao\ReportParameter\UnitRptParamDao;
use App\Business\Dao\ReportParameter\PositionRptParamDao;
use App\Business\Dao\ReportParameter\LocationRptParamDao;
use App\Business\Dao\ReportParameter\ProjectRptParamDao;
use Illuminate\Support\Facades\App;

class ReportParameterController extends Controller
{


    public function __construct(Requester $requester
                              , PersonRptParamDao $personRptParamDao
                              , JobRptParamDao $jobRptParamDao
                              , UnitRptParamDao $unitRptParamDao
                              , PositionRptParamDao $positionRptParamDao
                              , LocationRptParamDao $locationRptParamDao
                              , ProjectRptParamDao $projectRptParamDao
    )
    {
        parent::__construct();
        $this->requester = $requester;
        $this->personRptParamDao   = $personRptParamDao;
        $this->jobRptParamDao      = $jobRptParamDao;
        $this->unitRptParamDao     = $unitRptParamDao;
        $this->positionRptParamDao = $positionRptParamDao;
        $this->locationRptParamDao = $locationRptParamDao;
        $this->projectRptParamDao  = $projectRptParamDao;
    }


    /**
     * Get all Person First Name
     * @param request
     */
    public function getAllPersonFirstName(Request $request)
    {
        $dat = $this->personRptParamDao->getAllFirstName();

        $resp = new AppResponse($dat, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }
    /**
     * Get all Person Last Name
     * @param request
     */
    public function getAllPersonLastName(Request $request)
    {
        $dat = $this->personRptParamDao->getAllLastName();

        $resp = new AppResponse($dat, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /***
     * Get all Person Id
     * @param request
     */
    public function getAllPersonId(Request $request){
        $dat = $this->personRptParamDao->getAllId();

        $resp = new AppResponse($dat, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }


    /**
     * Get all Job Code & Name
     * @param request
     */
    public function getAllJobCodeName(Request $request)
    {
        $dat = $this->jobRptParamDao->getAllName();

        $resp = new AppResponse($dat, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }


    /**
     * Get all Unit Code & Name
     * @param request
     */
    public function getAllUnitCodeName(Request $request)
    {
        $dat = $this->unitRptParamDao->getAllName();

        $resp = new AppResponse($dat, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }


    /**
     * Get all Position Code & Name
     * @param request
     */
    public function getAllPositionCodeName(Request $request)
    {
        $dat = $this->positionRptParamDao->getAllName();

        $resp = new AppResponse($dat, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }


    /**
     * Get all Location Code & Name
     * @param request
     */
    public function getAllLocationCodeName(Request $request)
    {
        $dat = $this->locationRptParamDao->getAllName();

        $resp = new AppResponse($dat, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all Assignment Status Keydata & Valdata
     * @param request
     */
    public function getAllAssignmentStatus(Request $request)
    {
        $dat = $this->assignmentStatusRptParamDao->getAllName();

        $resp = new AppResponse($dat, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all Project Name
     * @param request
     */
    public function getAllProjectName(Request $request)
    {
        $dat = $this->projectRptParamDao->getAllName();

        $resp = new AppResponse($dat, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }
}
