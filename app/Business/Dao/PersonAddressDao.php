<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PersonAddressDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all person addresses for one person
     * @param $personId
     */
    public function getAll($personId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('person_addresses')
                ->select(
                    'person_addresses.id',
                    'person_addresses.is_default as isDefault',
                    'person_addresses.eff_begin as effBegin',
                    'person_addresses.eff_end as effEnd',
                    'address',
                    'lov_rsty as lovRsty',
                    'lov_rsow as lovRsow',
                    'cities.name as cityName',
                    'person_addresses.postal_code as postalCode',
                    'provinces.name as provinceName',
                    'countries.name as countryName',
                    'countries.code as countryCode',
                    'provinces.code as provinceCode',
                    'cities.code as cityCode',
                    'person_addresses.map_location as mapLocation'
                )
                ->leftJoin('cities', function ($join) use($companyId, $tenantId)  {
                    $join->on('cities.code', '=', 'person_addresses.city_code')
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
                    ['person_addresses.tenant_id', $tenantId],
                    ['person_addresses.person_id', $personId]
                ])
                ->get();
    }

    /**
     * Get one person address based on personAddressId
     * @param $personId
     * @param $personAddressId
     * @return
     */
    public function getOne($personId, $personAddressId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('person_addresses')
                ->select(
                    'person_addresses.id',
                    'person_addresses.eff_begin as effBegin',
                    'person_addresses.eff_end as effEnd',
                    'person_addresses.is_default as isDefault',
                    'lov_rsty as lovRsty',
                    'lov_rsow as lovRsow',
                    'person_addresses.address',
                    'person_addresses.postal_code as postalCode',
                    'countries.code as countryCode',
                    'provinces.code as provinceCode',
                    'cities.name as cityName',
                    'person_addresses.city_code as cityCode',
                    'person_addresses.map_location as mapLocation',
                    'person_addresses.phone',
                    'person_addresses.fax'
                )
                ->leftJoin('cities', function ($join) use($companyId, $tenantId)  {
                    $join->on('cities.code', '=', 'person_addresses.city_code')
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
                    ['person_addresses.tenant_id',$tenantId],
                    ['person_addresses.person_id', $personId],
                    ['person_addresses.id', $personAddressId]
                ])
                ->first();
    }

    /**
     * Insert data person address to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_addresses')->insertGetId($obj);
    }

    /**
     * Update data person address to DB
     * @param $personId
     * @param $personAddressId
     * @param $obj
     */
    public function update($personId, $personAddressId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_addresses')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $personAddressId]
        ])
        ->update($obj);
    }

    /**
     * Delete data person address from DB.
     * @param $personId
     * @param $personAddressId
     */
    public function delete($personId, $personAddressId)
    {
        DB::table('person_addresses')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $personAddressId]
        ])
        ->delete();
    }
}
