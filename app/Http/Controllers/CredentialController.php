<?php

namespace App\Http\Controllers;

use App\Business\Dao\CredentialDao;
use App\Business\Dao\PositionCredentialDao;
use App\Business\Dao\ProviderCredentialDao;
use App\Business\Dao\ProviderDao;
use App\Business\Model\AppResponse;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling Credential process
 */
class CredentialController extends Controller
{
    public function __construct(
        Requester $requester,
        CredentialDao $credentialDao,
        PositionCredentialDao $positionCredentialDao,
        ProviderCredentialDao $providerCredentialDao,
        ProviderDao $providerDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->credentialDao = $credentialDao;
        $this->positionCredentialDao = $positionCredentialDao;
        $this->providerCredentialDao = $providerCredentialDao;
        $this->providerDao = $providerDao;
    }

    /**
     * Get all Credential
     * @param request
     */
    public function getAll(Request $request)
    {
        $dataCredential = array();
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

        $getCredential = $this->credentialDao->getAll(
            $offset,
            $limit
        );

        $countRowCredential = count($getCredential);

        return $this->renderResponse(new PagingAppResponse($getCredential, trans('messages.allDataRetrieved'), $limit, $countRowCredential, $pageNo));
    }

    /**
     * Get all Credential
     * @param request
     */
    public function getAllActive(Request $request)
    {
        $dataCredential = array();
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

        $getCredential = $this->credentialDao->getAllActive(
            $offset,
            $limit
        );

        $countRowCredential = count($getCredential);

        return $this->renderResponse(new PagingAppResponse($getCredential, trans('messages.allDataRetrieved'), $limit, $countRowCredential, $pageNo));
    }

    /**
     * Get all Inactive Credential
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

        $getCredential = $this->credentialDao->getAllInactive(
            $offset,
            $limit
        );

        $countRowCredential = count($getCredential);

        return $this->renderResponse(new PagingAppResponse($getCredential, trans('messages.allDataRetrieved'), $limit, $countRowCredential, $pageNo));
    }

    /**
     * Get all credential in one company
     * @param request
     */
    public function getLov(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $lov = $this->credentialDao->getLov();

        $dataLov = collect($lov)->unique('code');
        $dataLov->values()->all();

        $resp = new AppResponse($dataLov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one company based on Credential id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            'code' => 'required|max:20|alpha_num|exists:credentials,code']);

        $credential = $this->credentialDao->getOne($request->code);

        if($credential){
            $credential->positions = $this->positionCredentialDao->getAllByPosition($request->code);
            $credential->providers = array();

            $providers = $this->providerCredentialDao->getAllByCredential($credential->code);
            for($i=0;$i<count($providers);$i++){
                $credential->providers[$i] = $this->providerDao->getOneByName($providers[$i]->providerName);
            }
        }

        $resp = new AppResponse($credential, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one company based on Credential id
     * @param request
     */
    public function getAllByCode(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            'code' => 'required|max:20|alpha_num|exists:credentials,code',
            'id' => 'required|integer|exists:credentials,id']);

        $credential = $this->credentialDao->getAllByCode($request->code, $request->id);

        $resp = new AppResponse($credential, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save Credential to DB
     * @param request
     */
    public function save(Request $request)
    {
        $this->checkCredentialRequest($request);
        $data = array();

        //code must be unique
        if ($this->credentialDao->checkDuplicateCredentialCode($request->code) > 0) {
            throw new AppException(trans('messages.duplicateCode'));
        }
        //save to DB
        DB::transaction(function () use (&$request, &$data) {
            $credential = $this->constructCredential($request);
            $this->credentialDao->save($credential);
        });

        if ($request->has('providers')) {
            $this->saveProvidersCredential($request);
        }

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update Credential to DB
     * @param request
     */
    public function update(Request $request)
    {
        $this->checkCredentialRequest($request);
        $data = array();

        //id must exist for update
        $this->validate($request, [
            'id' => 'required|integer|exists:credentials,id'
        ]);

        //update to DB
        DB::transaction(function () use (&$request, &$data) {
            $credential = $this->constructCredential($request);
            $this->credentialDao->update($request->id, $credential);
        });

        if ($request->has('providers')) {
            $this->providerCredentialDao->deleteAllByCredential($request->code);
            $this->saveProvidersCredential($request);
        }

        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update credential request.
     * @param request
     */
    private function checkCredentialRequest(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|max:20|alpha_num',
            'name' => 'required|max:50',
            'description' => 'present|max:255',
            'renewalCycle' => 'present|nullable|integer',
            'notificationDays' => 'present|nullable|integer',
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date'
        ]);
    }

    /**
     * Construct an credential object (array).
     * @param request
     */
    private function constructCredential(Request $request)
    {
        $credential = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "code" => $request->code,
            "name" => $request->name,
            "description" => $request->description,
            "renewal_cycle" => $request->renewalCycle,
            "notification_days" => $request->notificationDays,
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
            for ($i=0; $i < count($request->providers); $i++) {
                $data = array();

                array_push($data, [
                    "tenant_id"         => $this->requester->getTenantId(),
                    "company_id"        => $this->requester->getCompanyId(),
                    "credential_code"   => $request->code,
                    "provider_name"     => $request->providers[$i]['name'],
                    "created_at"        => Carbon::now(),
                    "created_by"        => $this->requester->getUserId()
                ]);

                $this->providerCredentialDao->save($data);
            }
    }
}
