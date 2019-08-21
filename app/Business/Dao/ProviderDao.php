<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProviderDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get LOV all Provider in ONE company
     * @param effBegin, effEnd
     */
    public function getLov()
    {
        return
            DB::table('providers')
            ->select(
                'id',
                'name'
            )
            ->where([
                ['tenant_id', '=', $this->requester->getTenantId()],
                ['company_id', '=', $this->requester->getCompanyId()],
                ['eff_begin', '<=', Carbon::now()],
                ['eff_end', '>=', Carbon::now()]
            ])
            ->get();
    }

    /**
     * Get all providers
     * @param  offset, limit, effBegin, effEnd
     */
    public function getAll($offset, $limit)
    {
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        return
            DB::table('providers')
            ->select(
                'providers.id',
                'providers.name',
                'providers.description',
                'providers.address',
                'providers.country_code as countryCode',
                'providers.eff_begin as effBegin',
                'providers.eff_end as effEnd',
                'countries.name as country'
            )
            ->join('countries', function ($join) use ($companyId, $tenantId) {
                $join->on('countries.code', '=', 'providers.country_code')
                    ->where([
                        ['countries.tenant_id', $tenantId],
                        ['countries.company_id', $companyId]
                    ]);
            })
            ->where([
                ['providers.tenant_id', $this->requester->getTenantId()],
                ['providers.company_id', $this->requester->getCompanyId()]
            ])
            ->orderByRaw('eff_end DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Get all providers
     * @param  offset, limit, effBegin, effEnd
     */
    public function getAllActive($offset, $limit)
    {
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        return
            DB::table('providers')
            ->select(
                'providers.id',
                'providers.name',
                'providers.description',
                'providers.address',
                'providers.country_code as countryCode',
                'providers.eff_begin as effBegin',
                'providers.eff_end as effEnd',
                'countries.name as country'
            )
            ->join('countries', function ($join) use ($companyId, $tenantId) {
                $join->on('countries.code', '=', 'providers.country_code')
                    ->where([
                        ['countries.tenant_id', $tenantId],
                        ['countries.company_id', $companyId]
                    ]);
            })
            ->where([
                ['providers.tenant_id', $tenantId],
                ['providers.company_id', $companyId],
                ['providers.eff_begin', '<=', Carbon::now()],
                ['providers.eff_end', '>=', Carbon::now()]
            ])
            ->orderByRaw('eff_end DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Get all Inactive providers Group By id
     * @param  offset, limit, effBegin, effEnd
     */
    public function getAllInactive($offset, $limit)
    {
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        return
            DB::table('providers')
            ->select(
                'providers.id',
                'providers.name',
                'providers.description',
                'providers.address',
                'providers.country_code as countryCode',
                'providers.eff_begin as effBegin',
                'providers.eff_end as effEnd',
                'countries.name as country'
            )
            ->join('countries', function ($join) use ($companyId, $tenantId) {
                $join->on('countries.code', '=', 'providers.country_code')
                    ->where([
                        ['countries.tenant_id', $tenantId],
                        ['countries.company_id', $companyId]
                    ]);
            })
            ->where([
                ['providers.tenant_id', $this->requester->getTenantId()],
                ['providers.company_id', $this->requester->getCompanyId()],
                ['providers.eff_end', '<', Carbon::now()]
            ])
            ->orderByRaw('eff_end DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Get one providers based on providers id
     * @param  $providerId
     */
    public function getOne($providerId)
    {
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        return
            DB::table('providers')
            ->select(
                'providers.id',
                'providers.name',
                'providers.description',
                'providers.address',
                'providers.country_code as countryCode',
                'providers.eff_begin as effBegin',
                'providers.eff_end as effEnd',
                'countries.name as country'
            )
            ->join('countries', function ($join) use ($companyId, $tenantId) {
                $join->on('countries.code', '=', 'providers.country_code')
                    ->where([
                        ['countries.tenant_id', $tenantId],
                        ['countries.company_id', $companyId]
                    ]);
            })
            ->where([
                ['providers.tenant_id', $this->requester->getTenantId()],
                ['providers.company_id', $this->requester->getCompanyId()],
                ['providers.id', '=', $providerId]
            ])
            ->orderBy('eff_end', 'DESC')
            ->first();
    }

    /**
     * Get one providers based on providers id
     * @param  $providerId
     */
    public function getOneByName($providerName)
    {
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        return
            DB::table('providers')
            ->select(
                'providers.id',
                'providers.name',
                'providers.description',
                'providers.address',
                'providers.country_code as countryCode',
                'providers.eff_begin as effBegin',
                'providers.eff_end as effEnd',
                'countries.name as country'
            )
            ->join('countries', function ($join) use ($companyId, $tenantId) {
                $join->on('countries.code', '=', 'providers.country_code')
                    ->where([
                        ['countries.tenant_id', $tenantId],
                        ['countries.company_id', $companyId]
                    ]);
            })
            ->where([
                ['providers.tenant_id', $this->requester->getTenantId()],
                ['providers.company_id', $this->requester->getCompanyId()],
                ['providers.name', '=', $providerName]
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
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'providers', $obj);

        return DB::table('providers')->insertGetId($obj);
    }

    /**
     * Update data providers to DB
     * @param  array obj, countryId
     */
    public function update($providerId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'providers', $obj);

        DB::table('providers')
            ->where([
                ['id', $providerId]
            ])
            ->update($obj);
    }

    /**
     * Check Duplicate Provider Name in data providers
     * @param string $code
     * @return
     */
    public function checkDuplicateProviderName(string $name)
    {
        return DB::table('providers')->where([
            ['name', $name],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

}
