<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Helper\SearchQueryBuilder;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestPersonAddressesDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * @param $profileRequestId
     */
    public function getMany($profileRequestId)
    {
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        return
            DB::table('request_person_addresses')
                ->select(
                    'request_person_addresses.id',
                    'request_person_addresses.address',
                    'request_person_addresses.phone',
                    'request_person_addresses.fax',
                    'request_person_addresses.postal_code as postalCode',
                    'request_person_addresses.profile_request_id as profileRequestId',
                    'request_person_addresses.crud_type as crudType',
                    'request_person_addresses.map_location as mapLocation',
                    'residence_types.val_data as residenceType',
                    'residence_ownerships.val_data as residenceOwnership',
                    'cities.name as cityName',
                    'provinces.name as provinceName',
                    'countries.name as countryName'
                )
                ->leftJoin('lovs as residence_types', function ($join) use ($companyId, $tenantId) {
                    $join->on('residence_types.key_data', '=', 'request_person_addresses.lov_rsty')
                        ->where([
                            ['residence_types.lov_type_code', 'RSTY'],
                            ['residence_types.tenant_id', $tenantId],
                            ['residence_types.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as residence_ownerships', function ($join) use ($companyId, $tenantId) {
                    $join->on('residence_ownerships.key_data', '=', 'request_person_addresses.lov_rsow')
                        ->where([
                            ['residence_ownerships.lov_type_code', 'RSOW'],
                            ['residence_ownerships.tenant_id', $tenantId],
                            ['residence_ownerships.company_id', $companyId]
                        ]);
                })
                ->leftJoin('cities', function ($join) use ($companyId, $tenantId) {
                    $join->on('cities.code', '=', 'request_person_addresses.city_code')
                        ->where([
                            ['cities.tenant_id', $tenantId],
                            ['cities.company_id', $companyId]
                        ]);
                })
                ->leftJoin('provinces', function ($join) use ($companyId, $tenantId) {
                    $join->on('provinces.code', '=', 'cities.province_code')
                        ->where([
                            ['provinces.tenant_id', $tenantId],
                            ['provinces.company_id', $companyId]
                        ]);
                })
                ->leftjoin('countries', function ($join) use ($companyId, $tenantId) {
                    $join->on('countries.code', '=', 'provinces.country_code')
                        ->where([
                            ['countries.tenant_id', $tenantId],
                            ['countries.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['request_person_addresses.tenant_id', $tenantId],
                    ['request_person_addresses.profile_request_id', $profileRequestId]
                ])
                ->get();
    }

    /**
     * Get one request person address based on personAddressId
     */
    public function getOne($personAddressId)
    {
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('request_person_addresses')
                ->select(
                    'id',
                    'person_address_id as personAddressId',
                    'profile_request_id as profileRequestId',
                    'address',
                    'crud_type as crudType',
                    'lov_rsty as lovRsty',
                    'lov_rsow as lovRsow',
                    'city_code as cityCode',
                    'phone',
                    'fax',
                    'map_location as mapLocation',
                    'postal_code as postalCode'
                )
                ->where([
                    ['tenant_id', $tenantId],
                    ['id', $personAddressId]
                ])
                ->first();
    }

    /**
     * Insert data Person Address Request to DB
     * @param  array obj
     */
    public function save($obj)
    {
        return DB::table('request_person_addresses')->insertGetId($obj);
    }

    /**
     * Update data Person Address Request to DB
     * @param array obj
     */
    public function update($personAddressId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('request_person_addresses')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['id', $personAddressId]
            ])
            ->update($obj);
    }
}
