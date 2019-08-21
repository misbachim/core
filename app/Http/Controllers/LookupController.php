<?php

namespace App\Http\Controllers;

use App\Business\Dao\LookupDao;
use App\Business\Dao\LookupDetailDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling lookup process
 */
class LookupController extends Controller
{
    public function __construct(
        Requester $requester,
        LookupDao $lookupDao,
        LookupDetailDao $lookupDetailDao
    )
    {
        parent::__construct();

        $this->requester = $requester;
        $this->lookupDao = $lookupDao;
        $this->lookupDetailDao = $lookupDetailDao;
    }

    /**
     * Get all lookup in one company
     * @param request
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required|integer"]);

        $data = $this->lookupDao->getAll();

        return $this->renderResponse(new AppResponse($data, trans('messages.allDataRetrieved')));

    }

    /**
     * Get one lookup on lookup code
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "code" => "required|max:20"
        ]);

        $lookup = $this->lookupDao->getOne(
            $request->code
        );

        if (count($lookup) > 0) {
            $lookup->lookupDetail = $this->lookupDetailDao->getAll(array($lookup->id));
        }

        $resp = new AppResponse($lookup, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'lovLtyp' => 'required'
        ]);

        $lookup = $this->lookupDao->getAllActive($request->lovLtyp);

        $resp = new AppResponse($lookup, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Lookup to DB
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkLookupRequest($request);

        //codes must be unique
        if ($this->lookupDao->checkDuplicateLookupCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $lookup = $this->constructLookup($request);
            $lookup['id'] = $this->lookupDao->save($lookup);
            $this->lookupDetailDao->delete($lookup['id']);
            $this->saveLookupDetail($request, $lookup);

            $data['id'] = $lookup['id'];
        });


        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Lookup to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer']);
        $this->checkLookupRequest($request);

        DB::transaction(function () use (&$request) {
            $lookup = $this->constructLookup($request);
            $lookup['id'] = $request->id;
            unset($lookup['code']);
            $this->lookupDao->update(
                $request->id,
                $lookup
            );
            $this->lookupDetailDao->delete($request->id);
            $this->saveLookupDetail($request, $lookup);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete Location Group Data from DB
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required|integer",
            "companyId" => "required|integer"
        ]);

        DB::transaction(function () use (&$request) {
            $this->lookupDao->delete($request->id);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }


    /**
     * Validate lookup save/update request.
     * @param request
     */
    private function checkLookupRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date',
            'effEnd' => 'required|date|after_or_equal:effBegin',
            'lovLtyp' => 'required|max:10',
            'lovLook1' => 'required|max:20',
            'lovLook2' => 'nullable|max:20',
            'lovLook3' => 'nullable|max:20',
            'lovLook4' => 'nullable|max:20',
            'lovLook5' => 'nullable|max:20',
            'description' => 'nullable|max:255',
            'name' => 'required|max:50',
            'code' => 'required|max:20|alpha_dash'
        ]);
    }

    /**
     * Construct a lookup object for storage (array).
     * @param request
     */
    private function constructLookup(Request $request)
    {
        $lookup = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "lov_ltyp" => $request->lovLtyp,
            "lov_look_1" => $request->lovLook1,
            "lov_look_2" => $request->lovLook2,
            "lov_look_3" => $request->lovLook3,
            "lov_look_4" => $request->lovLook4,
            "lov_look_5" => $request->lovLook5,
            "code" => $request->code,
            "name" => $request->name,
            "description" => $request->description
        ];
        return $lookup;
    }

    /**
     * Save lookup's detailed information.
     * @param request , lookup
     */
    private function saveLookupDetail(Request $request, &$lookup)
    {
        if ($request->has('lookupDetail')) {
            $data = array();
            for ($i = 0; $i < count($request->lookupDetail); $i++) {
                $this->validate($request, [
                    "lookupDetail.$i.amount" => 'required',
                    "lookupDetail.$i.look1code" => 'required|max:20'
                ]);

                array_push($data, [
                    "tenant_id" => $this->requester->getTenantId(),
                    "company_id" => $this->requester->getCompanyId(),
                    "look_1_code" => $request->lookupDetail[$i]['look1code'],
                    "look_2_code" => $request->lookupDetail[$i]['look2code'],
                    "look_3_code" => $request->lookupDetail[$i]['look3code'],
                    "look_4_code" => $request->lookupDetail[$i]['look4code'],
                    "look_5_code" => $request->lookupDetail[$i]['look5code'],
                    "amount" => $request->lookupDetail[$i]['amount'],
                    "lookup_id" => $lookup['id']
                ]);
            }
            $this->lookupDetailDao->save($data);
        }
    }
}
