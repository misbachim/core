<?php

namespace App\Http\Controllers;

use App\Business\Dao\ProviderDao;
use App\Business\Dao\CredentialDao;
use App\Business\Dao\ProviderCredentialDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class for handling Provider process
 */
class ProviderController extends Controller
{
    public function __construct(
        Requester $requester,
        ProviderDao $provider,
        CredentialDao $credentialDao,
        ProviderCredentialDao $ProviderCredentialDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->providerDao = $provider;
        $this->credentialDao = $credentialDao;
        $this->providerCredentialDao = $ProviderCredentialDao;
    }

    /**
     * Get all Provider
     * @param request
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

        $getProvider = $this->providerDao->getAll($offset,$limit);
        $countRowProvider = count($getProvider);

        return $this->renderResponse(new PagingAppResponse($getProvider, trans('messages.allDataRetrieved'), $limit, $countRowProvider, $pageNo));
    }

    /**
     * Get all Provider
     * @param request
     */
    public function getAllActive(Request $request)
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

        $getProvider = $this->providerDao->getAllActive($offset,$limit);
        $countRowProvider = count($getProvider);

        return $this->renderResponse(new PagingAppResponse($getProvider, trans('messages.allDataRetrieved'), $limit, $countRowProvider, $pageNo));
    }

    /**
     * Get all Inactive Provider
     * @param request
     */
    public function getAllInactive(Request $request)
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

        $getProvider = $this->providerDao->getAllInactive($offset,$limit);
        $countRowProvider = count($getProvider);

        return $this->renderResponse(new PagingAppResponse($getProvider, trans('messages.allDataRetrieved'), $limit, $countRowProvider, $pageNo));
    }

    /**
     * Get one company based on Provider id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            'id' => 'required|max:20|alpha_num|exists:providers,id']);

        $provider = $this->providerDao->getOne($request->id);
        if($provider){
            $credentials = $this->providerCredentialDao->getAllByProvider($provider->name);

            for($i=0;$i<count($credentials);$i++){
                $provider->credentials[$i] = $this->credentialDao->getOne($credentials[$i]->code);
            }
        }

        $resp = new AppResponse($provider, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get all Provider in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $lov = $this->providerDao->getLov();

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Provider to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkProviderRequest($request);
        $data = array();

        //name must be unique
        if ($this->providerDao->checkDuplicateProviderName($request->name) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }

        DB::transaction(function () use (&$request, &$data) {
            $provider = $this->constructProvider($request);
            $data['id'] = $this->providerDao->save($provider);
        });

        if ($request->has('credentials')) {
            $this->saveProvidersCredential($request);
        }

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Provider to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->checkProviderRequest($request);
        $data = array();

        //id must exist for update
        $this->validate($request, [
            'id' => 'required|integer|exists:providers,id'
        ]);

        //update to DB
        DB::transaction(function () use (&$request, &$data) {
            $provider = $this->constructProvider($request);
            $this->providerDao->update($request->id, $provider);
        });

        if ($request->has('credentials')) {
            $this->providerCredentialDao->deleteAllByProvider($request->name);
            $this->saveProvidersCredential($request);
        }

        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update Provider request.
     * @param request
     */
    private function checkProviderRequest(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'address' => 'present|max:255',
            'description' => 'present|max:255',
            'countryCode' => 'present|max:20|exists:countries,code',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date'
        ]);
    }

    /**
     * Construct an Provider object (array).
     * @param request
     */
    private function constructProvider(Request $request)
    {
        $credential = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "name" => $request->name,
            "address" => $request->address,
            "description" => $request->description,
            "country_code" => $request->countryCode,
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
        ];
        return $credential;
    }

    /**
     * Save providers credentials realtion.
     * @param request, ratingScale
     */
    private function saveProvidersCredential(Request $request)
    {
            for ($i=0; $i < count($request->credentials); $i++) {
                $data = array();

                array_push($data, [
                    "tenant_id"         => $this->requester->getTenantId(),
                    "company_id"        => $this->requester->getCompanyId(),
                    "credential_code"   => $request->credentials[$i]['code'],
                    "provider_name"     => $request->name,
                    "created_at"        => Carbon::now(),
                    "created_by"        => $this->requester->getUserId()
                ]);

                $this->providerCredentialDao->save($data);
            }
    }
}
