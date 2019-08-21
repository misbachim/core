<?php

namespace App\Http\Controllers;

use App\Business\Dao\CustomFieldEmployeeProjectDao;
use App\Business\Dao\AutonumberDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;

class CustomFieldEmployeeProjectController extends Controller {
    public function __construct(
        Requester $requester,
        CustomFieldEmployeeProjectDao $customFieldEmployeeProjectDao,
        AutonumberDao $autonumberDao
    ) {
        parent::__construct();
        $this->requester = $requester;
        $this->customFieldEmployeeProjectDao = $customFieldEmployeeProjectDao;
        $this->autonumberDao = $autonumberDao;
    }

    public function getOneByEmployeeProjectId(Request $request) {
        $data = $this->customFieldEmployeeProjectDao->getOneByEmployeeProjectId($request->employeeProjectId, $request->lovNbft);
        $object = null;

        // check if data is exist
        if($data) {
            //c3 column increment
            if($data->c3){
                intval($data->c3);
                $newc3 = $data->c3;
                $data->c3 = $newc3 + 1;
                strval($data->c3);
            }else{
                $data->c3 = '1';
            }
            // retrieve data from db, and use $resp for report param
            $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
            return $this->renderResponse($resp);
        } else {
            // used data from UI instead
            $cfEmployeeProject = $this->constructCfEmployeeProject($request);

            $object = $cfEmployeeProject;

            //c3 column increment
            if($object['c3']){
                intval($object['c3']);
                $newc3 = $object['c3'];
                $object['c3'] = $newc3 + 1;
                strval($object['c3']);
                // Log::info(print_r('$data kosong dan c3 ada', true));
                // Log::info(print_r($object['c3'], true));
            }else{
                $object['c3'] = '1';
            }
            $resp = new AppResponse($object, trans('messages.allDataRetrieved'));
            return $this->renderResponse($resp);
        }
    }

    public function saveAndUpdateCfEmployeeProject(Request $request) {

        // if do not use existing data and use another 'noSurat'
        if ($request->has('saveOnly')) {
            if ($request->saveOnly) {
                DB::transaction(function () use (&$request, &$object) {
                    $cfEmployeeProject = $this->constructCfEmployeeProject($request);
                    $object = $cfEmployeeProject;
                    $this->customFieldEmployeeProjectDao->save($cfEmployeeProject);
                });

                // update autonumbers
                DB::transaction(function () use (&$request, &$object) {
                    $temp = ['last_sequence' => $request->lastSequence];
                    $this->autonumberDao->update($request->autonumbersId, $temp);
                });

                $resp = new AppResponse($object, trans('messages.allDataRetrieved'));
                return $this->renderResponse($resp);
            }
        }

        $data = $this->customFieldEmployeeProjectDao->getOneByEmployeeProjectId($request->employeeProjectId, $request->lovNbft);
        // Log::info(print_r($data, true));
        $object = null;
        // check if data is exist
        if($data) {
            //c3 column increment
            if($data->c3){
                intval($data->c3);
                $newc3 = $data->c3;
                $data->c3 = $newc3 + 1;
                strval($data->c3);
            }else{
                $data->c3 = '1';
            }
            // retrieve data from db, and use $resp for report param
            $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
            return $this->renderResponse($resp);
        } else {
            // save data to db, and use $request for report param
            DB::transaction(function () use (&$request, &$object) {
                log::info(print_r('$request', true));
                log::info(print_r($request, true));
                $cfEmployeeProject = $this->constructCfEmployeeProject($request);
                $object = $cfEmployeeProject;
                    intval($object['c3']);
                    $newc3 = $object['c3'];
                    $object['c3'] = $newc3 + 1;
                    strval($object['c3']);
                $this->customFieldEmployeeProjectDao->save($cfEmployeeProject);
            });

            // update autonumbers
            DB::transaction(function () use (&$request, &$object) {
                $temp = ['last_sequence' => $request->lastSequence];
                $this->autonumberDao->update($request->autonumbersId, $temp);
            });


            $resp = new AppResponse($object, trans('messages.allDataRetrieved'));
            return $this->renderResponse($resp);
        }
    }


    public function countCustomNumber($data){
        if($data->c3){
            intval($data->c3);
            $newc3 = $data->c3++;
            $data->c3 = $newc3;
            return strval($data->c3);
        }else{
            return $data->c3 = '1';
        }

    }

    /**
     * Construct an cf employee project object (array).
     * @param request
     */
    private function constructCfEmployeeProject(Request $request)
    {
        $data = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "employee_project_id" => $request->employeeProjectId,
            "c1" => $request->c1,
            "c2" => $request->c2,
            "c3" => $request->c3,
            "c4" => $request->c4,
            "c5" => $request->c5,
            "c6" => $request->c6,
            "c7" => $request->c7,
            "c8" => $request->c8,
            "c9" => $request->c9,
            "c10" => $request->c10,
            "lov_nbft" => $request->lovNbft
        ];
        return $data;
    }
}
