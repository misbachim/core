<?php

namespace App\Http\Controllers;

use App\Business\Dao\AssetDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Exceptions\AppException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling asset process
 */
class AssetController extends Controller
{
    public function __construct(Requester $requester, AssetDao $assetDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->assetDao = $assetDao;
        $this->assetFields = array('effBegin', 'effEnd', 'code',
            'name', 'description', 'type');
    }

    /**
     * Get all assets for one company
     * @param request
     */
    public function getAll(Request $request)
    {
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
        $limit  = PagingAppResponse::getPageLimit($request->pageInfo);
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);

        $assets = $this->assetDao->getAll(
            $offset,
            $limit
        );

        $totalRow = count($assets);

        return $this->renderResponse(new PagingAppResponse($assets, trans('messages.allDataRetrieved'),$limit,$totalRow,$pageNo));
    }

    /**
    * Get all Active Assets in one company
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
        $data = $this->assetDao->getAllActive($offset, $pageLimit);
        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->assetDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
    * Get All InActive Assets in one company
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

        $data = $this->assetDao->getAllInActive();

        $resp = new PagingAppResponse(
            $data,
            trans('messages.allDataRetrieved'),
            $pageLimit,
            $this->assetDao->getTotalRows(),
            PagingAppResponse::getPageNo($request->pageInfo)
        );
        return $this->renderResponse($resp);
    }

    /**
     * Get all asset in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $lov = $this->assetDao->getLov();

//        info('log:',$lov);
        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one asset based on asset code
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $asset = $this->assetDao->getOne(
            $request->id
        );

        $data = array();
        if (count($asset) > 0) {
            foreach ($this->assetFields as $field) {
                $data[$field] = $asset->$field;
            }
        }

        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save asset to Database
     * @param request
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkAssetRequest($request);

        //code must be unique
        if ($this->assetDao->checkDuplicateAssetCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $asset = $this->constructAsset($request);
            $this->assetDao->save($asset);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update asset to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->validate($request, ['code' => 'required|alpha_num']);
        $this->checkAssetRequest($request);

        DB::transaction(function () use (&$request) {
            $asset = $this->constructAsset($request);
            $this->assetDao->update(
                $request->code,
                $asset
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete asset by code
     * @param request
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "code" => "required|alpha_num",
            "companyId" => "required|integer"
        ]);

        DB::transaction(function () use (&$request) {
            $this->assetDao->delete(
                $request->code
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update asset request.
     * @param request
     */
    private function checkAssetRequest(Request $request)
    {
        $this->validate($request, [
            'effBegin' => 'required|date',
            'effEnd' => 'required|date|after_or_equal:effBegin',
            'code' => 'required|max:20|alpha_num',
            'name' => 'required|max:50',
            'price' => 'present',
            'description' => 'max:255',
            'type' => 'max:50'
        ]);
    }

    /**
     * Construct a asset object (array).
     * @param request
     */
    private function constructAsset(Request $request)
    {
        $asset = [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $this->requester->getCompanyId(),
            'eff_begin' => $request->effBegin,
            'eff_end' => $request->effEnd,
            'code' => $request->code,
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'type' => $request->type
        ];
        return $asset;
    }
}
