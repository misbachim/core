<?php
namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;
use App\Business\Model\Requester;

class LocationGroupDetailDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }
    /**
     * Get all location group detail
     * @param  locationGroupId
     */
    public function getAll($locationGroupId)
    {
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();

        return
            DB::table('location_group_details')
                ->select(
                    'location_code as code',
                    'locations.name as name',
                    'countries.name as countryName',
                    'provinces.name as provinceName',
                    'cities.name as cityName',
                    'locations.address as address'
                )
                ->join('locations', function ($join) use($companyId, $tenantId)  {
                    $join->on( 'locations.code', '=', 'location_group_details.location_code')
                        ->where([
                            ['locations.tenant_id', $tenantId],
                            ['locations.company_id', $companyId]
                        ]);
                })
                ->join('cities', function ($join) use($companyId, $tenantId)  {
                    $join->on( 'cities.code', '=', 'locations.city_code')
                        ->where([
                            ['cities.tenant_id', $tenantId],
                            ['cities.company_id', $companyId]
                        ]);
                })
                ->join('provinces', function ($join) use($companyId, $tenantId)  {
                    $join->on( 'provinces.code', '=', 'cities.province_code')
                        ->where([
                            ['provinces.tenant_id', $tenantId],
                            ['provinces.company_id', $companyId]
                        ]);
                })
                ->join('countries', function ($join) use($companyId, $tenantId)  {
                    $join->on( 'countries.code', '=', 'provinces.country_code')
                        ->where([
                            ['countries.tenant_id', $tenantId],
                            ['countries.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['location_group_id', $locationGroupId]
                ])
                ->get();
    }

    /**
     * Insert data Location group detail to DB
     * @param  array $obj
     */
    public function save($obj)
    {
        LogDao::insertLogImpact($this->requester->getLogId(), 'location_group_details', $obj);

        DB::table('location_group_details')-> insert($obj);
    }

    /**
     * Delete data location group detail from DB
     * @param $locationGroupId
     */
    public function delete($locationGroupId)
    {
        DB::table('location_group_details')
            ->where('location_group_id', $locationGroupId)
            ->delete();
    }
}
