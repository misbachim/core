<?php

namespace App\Http\Controllers;

use App\Business\Dao\DashboardDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;

/**
 * Class for handling asset process
 */
class DashboardController extends Controller
{
    public function __construct(Requester $requester, DashboardDao $dashboardDao)
    {
        parent::__construct();
        $this->requester = $requester;
        $this->dashboardDao = $dashboardDao;
    }


    /**
     * get all data for chart
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function getAll(Request $request)
    {
      // return ['data' => $request->all()];
      $this->validate($request, [
          "companyId" => "required|integer"
      ]);
      if ($request->unitCode != null) {
        $data = $this->generateChildChart($request);
      }else{
        $data = $this->generateMainChart($request);
      }

      return $this->renderResponse(new AppResponse($data, trans('messages.dataRetrieved')));
    }


    /**
     * Generate main chart
     * @param  [type] $request [description]
     * @return [type]          [description]
     */
    public function generateMainChart($request)
    {
      $org = $this->dashboardDao->getOrgStructure();
      
      $data = [];

      foreach ($org as $key => $value) {
        $data = $this->dashboardDao->getOrgStructureHierarchies($request, $value->id);
      }

      foreach ($data as $key => $value) {
        $total = $this->dashboardDao->getTotalEmployee($request, $value->unitCode, $value->orgId)[0]->total;
        $child = $this->dashboardDao->getChildOrgStructureHierarchies($value->unitCode, $value->orgId)[0]->child;
        $data[$key]->total = $total;
        $data[$key]->child = $child;
      }

      return $data;
    }

    /**
     * Generate chart by unit code
     * @param string $value [description]
     */
    public function generateChildChart($request)
    {
      $data = $this->dashboardDao->getOrgStructureHierarchiesChild($request);
      foreach ($data as $key => $value) {
        $total = $this->dashboardDao->getTotalEmployee($request, $value->unitCode, $value->orgId)[0]->total;
        $child = $this->dashboardDao->getChildOrgStructureHierarchies($value->unitCode, $value->orgId)[0]->child;
        $data[$key]->total = $total;
        $data[$key]->child = $child;
      }

      return $data;
    }

    public function getActiveStructureHierarchies(Request $request)
    {
      $this->validate($request, [
          "companyId" => "required|integer"
      ]);
      $data = $this->dashboardDao->getActiveStructureHierarchies();
      return $this->renderResponse(new AppResponse($data, trans('messages.dataRetrieved')));
    }

    public function getOneForPieChart(Request $request)
    {
      $this->validate($request, [
          "companyId" => "required|integer"
      ]);
      // return ['data' => $request->all()];
      $data = $this->dashboardDao->getOneForPieChart($request);
      return $this->renderResponse(new AppResponse($data, trans('messages.dataRetrieved')));
    }
}
