<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Helper\SearchQueryBuilder;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RequestAddressesDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all request person addresses for one person
     * @param $personId
     */
    public function getAll($personId, $companyId){
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('request_addresses')
                ->select(
                    'request_addresses.id',
                    'address',
                    'lov_rsty as lovRsty',
                    'lov_rsow as lovRsow',
                    'cities.name as cityName',
                    'request_addresses.postal_code as postalCode',
                    'provinces.name as provinceName',
                    'countries.name as countryName'
                )
                ->leftJoin('cities', function ($join) use($companyId, $tenantId)  {
                    $join->on('cities.code', '=', 'request_addresses.city_code')
                        ->where([
                            ['cities.tenant_id', $tenantId],
                            ['cities.company_id', $companyId]
                        ]);
                })
                ->leftJoin('provinces', function ($join) use($companyId, $tenantId)  {
                    $join->on('provinces.code', '=', 'cities.province_code')
                        ->where([
                            ['provinces.tenant_id', $tenantId],
                            ['provinces.company_id', $companyId]
                        ]);
                })
                ->leftjoin('countries', function ($join) use($companyId, $tenantId)  {
                    $join->on('countries.code', '=', 'provinces.country_code')
                        ->where([
                            ['countries.tenant_id', $tenantId],
                            ['countries.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['request_addresses.tenant_id', $tenantId],
                    ['request_addresses.person_id', $personId]
                ])
                ->get();
    }

    public function checkIfRequestIsPending($employeeId, $status){
        return
            DB::table('request_addresses')
                ->select(
                    'id',
                    'employee_id',
                    'person_id',
                    'person_address_id as personAddressId',
                    'crud_type as crudType',
                    'status'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['employee_id', $employeeId],
                    ['status', $status]
                ])
                ->get();
    }

    /**
     * Get one request person address based on personAddressId
     */
    public function getOne($personAddressId){
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        return
            DB::table('request_addresses')
                ->select(
                    'request_addresses.id',
                    'request_addresses.tenant_id as tenantId',
                    'request_addresses.company_id as companyId',
                    'request_addresses.person_id as personId',
                    'request_addresses.employee_id as employeeId',
                    'request_addresses.person_address_id as personAddressId',
                    'address',
                    'crud_type as crudType',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'lov_rsty as lovRsty',
                    'lov_rsow as lovRsow',
                    'cities.name as cityName',
                    'request_addresses.city_code as cityCode',
                    'phone',
                    'fax',
                    'map_location as mapLocation',
                    'is_default as isDefault',
                    'request_addresses.postal_code as postalCode',
                    'provinces.name as provinceName',
                    'countries.name as countryName',
                    'status',
                    'request_date as requestDate'
                )
                ->leftJoin('cities', function ($join) use($companyId, $tenantId)  {
                    $join->on('cities.code', '=', 'request_addresses.city_code')
                        ->where([
                            ['cities.tenant_id', $tenantId],
                            ['cities.company_id', $companyId]
                        ]);
                })
                ->leftJoin('provinces', function ($join) use($companyId, $tenantId)  {
                    $join->on('provinces.code', '=', 'cities.province_code')
                        ->where([
                            ['provinces.tenant_id', $tenantId],
                            ['provinces.company_id', $companyId]
                        ]);
                })
                ->leftjoin('countries', function ($join) use($companyId, $tenantId)  {
                    $join->on('countries.code', '=', 'provinces.country_code')
                        ->where([
                            ['countries.tenant_id', $tenantId],
                            ['countries.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['request_addresses.tenant_id',$tenantId],
                    ['request_addresses.id', $personAddressId]
                ])
                ->first();
    }

    /**
     * Insert data Person Address Request to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('request_addresses')->insertGetId($obj);
    }

    /**
     * Update data Person Address Request to DB
     * @param array obj
     */
    public function update($personId, $personAddressId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('request_addresses')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['person_id', $personId],
                ['id', $personAddressId]
            ])
            ->update($obj);
    }
}
