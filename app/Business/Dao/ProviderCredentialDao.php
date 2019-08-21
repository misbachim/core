<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProviderCredentialDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all by provider name
     * @param  offset, limit, effBegin, effEnd
     */
    public function getAllByProvider($providerName)
    {
        return
            DB::table('providers_credentials')
                ->select(
                    'id',
                    'credential_code as code'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['provider_name', $providerName]
                ])
                ->get();
    }

    /**
     * Get all by credential code
     * @param  offset, limit, effBegin, effEnd
     */
    public function getAllByCredential($credentialCode)
    {
        return
            DB::table('providers_credentials')
                ->select(
                    'id',
                    'provider_name as providerName'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['credential_code', $credentialCode]
                ])
                ->get();
    }

    /**
     * Get one providers based on providers id
     * @param  $providerId
     */
    public function getOne($id)
    {
        return
            DB::table('providers_credentials')
                ->select(
                    'id',
                    'provider_name as providerName',
                    'credential_code as credentialCode'
                )
                ->join('countries', 'countries.code', '=', 'country_code')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['id', '=', $id]
                ])
                ->orderBy('eff_end', 'DESC')
                ->first();
    }


    /**
     * Insert data providers to DB
     * @param  array obj
     */
    public function save($obj)
    {
        DB::table('providers_credentials')->insert($obj);
    }

    /**
     * Delete all relation based on credential code
     * @param  $ratingScaleId
     */
    public function deleteAllByProvider($providerName)
    {
        DB::table('providers_credentials')
            ->where('provider_name', $providerName)
            ->delete();
    }

    /**
     * Delete all relation based on credential code
     * @param  $ratingScaleId
     */
    public function deleteAllByCredential($credentialCode)
    {
        DB::table('providers_credentials')
            ->where('credential_code', $credentialCode)
            ->delete();
    }

}
