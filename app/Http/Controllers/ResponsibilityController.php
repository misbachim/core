<?php

namespace App\Http\Controllers;

use App\Business\Dao\DutiesDao;
use App\Business\Dao\ResponsibilityDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling responsibility process
 * @property Requester requester
 * @property ResponsibilityDao responsibilityDao
 */
class ResponsibilityController extends Controller
{
    public function __construct(Requester $requester, ResponsibilityDao $responsibilityDao, DutiesDao $dutiesDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->responsibilityDao = $responsibilityDao;
        $this->dutiesDao = $dutiesDao;
    }

    /**
     * Get all responsibility in one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $responsibility = $this->responsibilityDao->getAll();

        $resp = new AppResponse($responsibility, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all responsibility in one company by responsibility group code
     * @param Request $request
     * @return AppResponse
     */
    public function getAllByResponsibilityGroup(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "responsibilityGroupCode" => "required|max:20|exists:responsibility_groups,code"
        ]);

        $responsibility = $this->responsibilityDao->getAllByResponsibilityGroup($request->responsibilityGroupCode);

        $resp = new AppResponse($responsibility, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'isResponsibilityGroup' => 'required'
        ]);

        if ($request->isResponsibilityGroup) {
            $activeResponsibility = $this->responsibilityDao->getAllActiveWithoutRespGroup();
        } else {
            $activeResponsibility = $this->responsibilityDao->getAllActive();
        }

        $resp = new AppResponse($activeResponsibility, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one responsibility based on responsibility code
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "code" => "required"
        ]);

        $responsibility = $this->responsibilityDao->getOne($request->code);

        if (count($responsibility) > 0) {
            $responsibility->duties = $this->dutiesDao->getAll($request->code);
        }

        $resp = new AppResponse($responsibility, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getAllActive(Request $request){
        $this->validate($request, [
            "companyId" => "required",
            'pageInfo' => 'required|array'
        ]);
        $request->merge($request->pageInfo);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1'
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $pageLimit = PagingAppResponse::getPageLimit($request->pageInfo);

        $data = $this->responsibilityDao->getAllActive($offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->responsibilityDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    public function getAllInActive(Request $request){
        $this->validate($request, [
            "companyId" => "required",
            'pageInfo' => 'required|array'
        ]);
        $request->merge($request->pageInfo);
        $this->validate($request, [
            'pageLimit' => 'required|integer|min:0',
            'pageNo' => 'required|integer|min:1'
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $pageLimit = PagingAppResponse::getPageLimit($request->pageInfo);

        $data = $this->responsibilityDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->responsibilityDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
     * Save responsibility to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkResponsibilityRequest($request);
        if ($this->responsibilityDao->isCodeDuplicate($request->code)) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $responsibility = $this->constructResponsibility($request);
            $data['id'] = $this->responsibilityDao->save($responsibility);

//            $lookup = $this->constructLookup($request);
//            $lookup['id'] = $this->lookupDao->save($lookup);
            $this->dutiesDao->delete($request->code);
            $this->saveDuties($request);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update responsibility to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkResponsibilityRequest($request);

        DB::transaction(function () use (&$request) {
            $responsibility = $this->constructResponsibility($request);
            unset($responsibility['code']);
            $this->responsibilityDao->update($request->id, $responsibility);
            $this->dutiesDao->delete($request->code);
            $this->saveDuties($request);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete a responsibility.
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $responsibility = [
                "eff_end" => Carbon::now()
            ];
            $this->responsibilityDao->update($request->id, $responsibility);
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update responsibility request.
     * @param Request $request
     */
    private function checkResponsibilityRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'description' => 'present|max:255',
            'usedFor' => 'max:2',
            'usedForValue' => 'max:20',
            'code' => 'required|max:20|alpha_num',
            'name' => 'required|max:50'
        ]);
    }

    /**
     * Construct a responsibility object (array).
     * @param Request $request
     * @return array
     */
    private function constructResponsibility(Request $request)
    {
        $this->checkResponsibilityRequest($request);
        $responsibility = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "description" => $request->description,
            "used_for" => $request->usedFor,
            "used_for_value" => $request->usedForValue,
            "name" => $request->name,
            "code" => $request->code
        ];
        return $responsibility;
    }

    /**
     * Save duties.
     * @param request
     */
    private function saveDuties(Request $request)
    {
        if ($request->has('duties')) {
            $data = array();
            for ($i = 0; $i < count($request->duties); $i++) {
                $this->validate($request, [
                    "duties.$i.effBegin" => 'required|date',
                    "duties.$i.effEnd" => 'required|date',
                    "duties.$i.description" => 'required|max:255'
                ]);

                array_push($data, [
                    "tenant_id" => $this->requester->getTenantId(),
                    "company_id" => $this->requester->getCompanyId(),
                    "description" => $request->duties[$i]['description'],
                    "eff_begin" => $request->duties[$i]['effBegin'],
                    "eff_end" => $request->duties[$i]['effEnd'],
                    "responsibility_code" => $request->code,
                    "created_by" => $this->requester->getUserId(),
                    "created_at" => Carbon::now()
                ]);
            }
            $this->dutiesDao->save($data);
        }
    }
}
