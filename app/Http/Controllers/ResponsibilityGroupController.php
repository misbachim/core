<?php

namespace App\Http\Controllers;

use App\Business\Dao\JobFamilyDao;
use App\Business\Dao\ResponsibilityDao;
use App\Business\Dao\ResponsibilityGroupDao;
use App\Business\Dao\WorkingConditionDao;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling responsibility group process
 * @property Requester requester
 * @property ResponsibilityGroupDao responsibilityGroupDao
 */
class ResponsibilityGroupController extends Controller
{
    public function __construct(
        Requester $requester, 
        ResponsibilityGroupDao $responsibilityGroupDao, 
        ResponsibilityDao $responsibilityDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->responsibilityDao = $responsibilityDao;
        $this->responsibilityGroupDao = $responsibilityGroupDao;
    }

    /**
     * Get all responsibility group in one company
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $responsibilityGroup = $this->responsibilityGroupDao->getAll();

        $resp = new AppResponse($responsibilityGroup, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getLov(Request $request)
    {
        $this->validate($request, ['companyId' => 'required']);

        $activeResponsibilityGroup = $this->responsibilityGroupDao->getAllActive();

        $resp = new AppResponse($activeResponsibilityGroup, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one responsibility group based on responsibility group code
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required",
            "code" => "required"
        ]);

        $responsibilityGroup = $this->responsibilityGroupDao->getOne($request->code);

        if (count($responsibilityGroup) > 0) {
            info('code',[$request->code]);
            $responsibilityGroup->responsibilities = $this->responsibilityDao->getAllByResponsibilityGroup($request->code);
            info('wkkwk',[$responsibilityGroup->responsibilities]);
        }

        $resp = new AppResponse($responsibilityGroup, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }



    /**
     * Save responsibility group to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkResponsibilityGroupRequest($request);
        if ($this->responsibilityGroupDao->isCodeDuplicate($request->code)) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $responsibilityGroup = $this->constructResponsibilityGroup($request);
            $data['id'] = $this->responsibilityGroupDao->save($responsibilityGroup);
            if ($request->has('responsibilities')) {
                for ($i = 0; $i < count($request->responsibilities); $i++) {
                    $this->validate($request, [
                        "responsibilities.$i.code" => 'required|max:20'
                    ]);

                    $responsibility = [
                        "responsibility_group_code" => $request->code,
                    ];

                    $this->responsibilityDao->updateByCode($request->responsibilities[$i]['code'], $responsibility);
                }
            }

        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update responsibility group to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkResponsibilityGroupRequest($request);

        DB::transaction(function () use (&$request) {
            $responsibilityGroupCode = [
                "responsibility_group_code" => null,
            ];
            $this->responsibilityDao->updateByResponsibilityGroupCode($request->code, $responsibilityGroupCode);

            $responsibilityGroup = $this->constructResponsibilityGroup($request);
            unset($responsibilityGroup['code']);
            $this->responsibilityGroupDao->update($request->id, $responsibilityGroup);
            if ($request->has('responsibilities')) {
                for ($i = 0; $i < count($request->responsibilities); $i++) {
                    $this->validate($request, [
                        "responsibilities.$i.code" => 'required|max:20'
                    ]);

                    $responsibility = [
                        "responsibility_group_code" => $request->code,
                    ];

                    $this->responsibilityDao->updateByCode($request->responsibilities[$i]['code'], $responsibility);
                }
            }
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete a responsibility group.
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "companyId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $responsibilityGroup = [
                "eff_end" => Carbon::now()
            ];
            $this->responsibilityGroupDao->update($request->id, $responsibilityGroup);

            $responsibilityGroupCode = [
                "responsibility_group_code" => null,
            ];
            $this->responsibilityDao->updateByResponsibilityGroupCode($request->code, $responsibilityGroupCode);

        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update responsibility group request.
     * @param Request $request
     */
    private function checkResponsibilityGroupRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer|exists:companies,id',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'description' => 'present|max:255',
            'code' => 'required|max:20|alpha_num',
            'name' => 'required|max:50'
        ]);
    }

    /**
     * Construct a responsibility group object (array).
     * @param Request $request
     * @return array
     */
    private function constructResponsibilityGroup(Request $request)
    {
        $responsibilityGroup = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "description" => $request->description,
            "name" => $request->name,
            "code" => $request->code
        ];
        return $responsibilityGroup;
    }

    /**
     * Get all Active responsibility group in one company
     * @param Request $request
     * @return AppResponse
     */
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

        $data = $this->responsibilityGroupDao->getAllActive($offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->responsibilityGroupDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
     * Get all Inactive responsibility group in one company
     * @param Request $request
     * @return AppResponse
     */
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

        $data = $this->responsibilityGroupDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->responsibilityGroupDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }
}
