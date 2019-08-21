<?php

namespace App\Http\Controllers;

use App\Business\Dao\PayRateDao;
use App\Business\Dao\PayRateDetailDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling pay rate process
 */
class PayRateController extends Controller
{
    public function __construct(
        Requester $requester,
        PayRateDao $payRateDao,
        PayRateDetailDao $payRateDetailDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->payRateDao = $payRateDao;
        $this->payRateDetailDao = $payRateDetailDao;
        $this->payRateFields = array('effBegin', 'effEnd', 'code', 'name');
    }

    /**
     * Get all pay rate in one company
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $data = $this->payRateDao->getAll(
            $this->requester->getTenantId(),
            $request->companyId
        );

        // $data = array();
        // for ($i=0; $i < count($payRates); $i++) {
        //     $data[$i]['id'] = $payRates[$i]['id'];
        //     foreach ($this->payRateFields as $field) {
        //         $data[$i][$field] = $payRates[$i][$field];
        //     }
        //     $data[$i]['payRateDetail'] = $this->payRateDetailDao->getAll(
        //         $data[$i]['id']
        //     );
        // }

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one pay rate based on payRateId
     * @param  Request companyId, payRateId
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "id" => "required"
        ]);

        $payRate = $this->payRateDao->getOne(
            $this->requester->getTenantId(),
            $request->companyId,
            $request->id
        );

        $data = array();
        if (count($payRate) > 0) {
            $data['id'] = $payRate->id;
            foreach ($this->payRateFields as $field) {
                $data[$field] = $payRate->$field;
            }
            $data['payRateDetail'] = $this->payRateDetailDao->getAll(
                $data['id']
            );
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save pay rate to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPayRateRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $payRate = $this->constructPayRate($request);
            $payRate['id'] = $this->payRateDao->save($payRate);
            $this->payRateDetailDao->delete($payRate['id']);
            $this->savePayRateDetail($request, $payRate);

            $data['id'] = $payRate['id'];
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update pay rate to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkPayRateRequest($request);

        DB::transaction(function () use (&$request) {
            $payRate = $this->constructPayRate($request);
            $payRate['id'] = $request->id;
            $this->payRateDao->update(
                $this->requester->getTenantId(),
                $request->companyId,
                $request->id,
                $payRate
            );
            $this->payRateDetailDao->delete($request->id);
            $this->savePayRateDetail($request, $payRate);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }


    /**
     * Set flag delete true to DB
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $this->payRateDao->update(
                $this->requester->getTenantId(),
                $request->companyId,
                $request->id,
                ['is_deleted' => true]
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update pay rate request.
     * @param request
     */
    private function checkPayRateRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'effBegin' => 'required',
            'effEnd' => 'required',
            'code' => 'required|max:20',
            'name' => 'required|max:50'
        ]);
    }

    /**
     * Construct a pay rate object (array).
     * @param request
     */
    private function constructPayRate(Request $request)
    {
        $payRate = [
            "tenant_id"  => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin"  => $request->effBegin,
            "eff_end"    => $request->effEnd,
            "code"       => $request->code,
            "name"       => $request->name,
            "is_deleted" => false
        ];
        return $payRate;
    }

    /**
     * Save pay rate's detailed information.
     * @param request, payRate
     */
    private function savePayRateDetail(Request $request, &$payRate)
    {
        if ($request->has('payRateDetail')) {
            $data = array();
            for ($i=0; $i < count($request->payRateDetail); $i++) {
                $this->validate($request, [
                    'payRateDetail.'.$i.'.gradeId' => 'required',
                    'payRateDetail.'.$i.'.bottomRate' => 'required',
                    'payRateDetail.'.$i.'.topRate' => 'required'
                ]);
                array_push($data, [
                    "tenant_id"   => $this->requester->getTenantId(),
                    "company_id"  => $request->companyId,
                    "pay_rate_id" => $payRate['id'],
                    "grade_id"    => $request->payRateDetail[$i]['gradeId'],
                    "bottom_rate"    => $request->payRateDetail[$i]['bottomRate'],
                    "top_rate"    => $request->payRateDetail[$i]['topRate'],
                ]);
            }
            $this->payRateDetailDao->save($data);
        }
    }
}
