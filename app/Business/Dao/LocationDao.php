<?php
namespace App\Business\Dao;
use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class LocationDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }
    /**
     * Get all locations for ONE company
     * @param  offset , limit
     */
    public function getAll($offset, $limit)
    {
        return
            DB::table('locations')
                ->select(
                    'locations.id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'locations.name',
                    'locations.code',
                    'locations.latitude',
                    'locations.longitude',
                    'v_reg_mapping.country_name as country',
                    'v_reg_mapping.province_name as province',
                    'v_reg_mapping.city_name as city',
                    'address',
                    'phone',
                    'fax'
                )
                ->join('v_reg_mapping', function ($join) {
                    $join
                        ->on('v_reg_mapping.city_code', '=', 'locations.city_code');
                })
                ->where([
                    ['locations.tenant_id', $this->requester->getTenantId()],
                    ['locations.company_id', $this->requester->getCompanyId()]
                ])
                ->offset($offset)
                ->limit($limit)
                ->distinct()
                ->orderBy('name', 'asc')
//                ->groupBy('locations.id','v_reg_mapping.country_name','v_reg_mapping.province_name','v_reg_mapping.city_name')
                ->get();
    }

    /**
     * Get all Active Location in ONE company
     */
    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('locations')
            ->select(
                'locations.id',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'locations.name',
                'locations.code',
                'locations.latitude',
                'locations.longitude',
                'v_reg_mapping.country_name as country',
                'v_reg_mapping.province_name as province',
                'v_reg_mapping.city_name as city',
                'address',
                'phone',
                'fax'
            )
            ->join('v_reg_mapping', function ($join) {
                    $join
                        ->on('v_reg_mapping.city_code', '=', 'locations.city_code');
            })
            ->where([
                ['locations.tenant_id', $this->requester->getTenantId()],
                ['locations.company_id', $this->requester->getCompanyId()],
                ['locations.eff_begin', '<=', Carbon::now()],
                ['locations.eff_end', '>=', Carbon::now()]
            ])
            ->orderByRaw('locations.eff_end DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Get all InActive Location in ONE company
     */
    public function getAllInActive()
    {
        return
            DB::table('locations')
            ->select(
                'locations.id',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'locations.name',
                'locations.code',
                'locations.latitude',
                'locations.longitude',
                'v_reg_mapping.country_name as country',
                'v_reg_mapping.province_name as province',
                'v_reg_mapping.city_name as city',
                'address',
                'phone',
                'fax'
            )
            ->join('v_reg_mapping', function ($join) {
                    $join
                        ->on('v_reg_mapping.city_code', '=', 'locations.city_code');
            })
            ->where([
                ['locations.tenant_id', $this->requester->getTenantId()],
                ['locations.company_id', $this->requester->getCompanyId()],
                ['locations.eff_end', '<', Carbon::now()]
            ])
            ->get();
    }

    /**
     * @param
     * @return
     */
    public function getClockingLocations($fLat, $fLon)
    {
        return
            DB::raw('SELECT id, name,
            ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $fLat ) ) + COS( RADIANS( latitude ) )
                * COS( RADIANS( $fLat )) * COS( RADIANS( longitude ) - RADIANS( $fLon )) ) * 6380 AS distance
            FROM locations
            WHERE
            ACOS( SIN( RADIANS( latitude ) ) * SIN( RADIANS( $fLat ) ) + COS( RADIANS( latitude ) )
                * COS( RADIANS( $fLat )) * COS( RADIANS( longitude ) - RADIANS( $fLon )) ) * 6380 < 10
            ORDER BY distance');
    }
    /**
     * @param
     * @return
     */
    public function getTotalRow()
    {
        return DB::table('locations')
            ->where([
                ['locations.tenant_id', $this->requester->getTenantId()],
                ['locations.company_id', $this->requester->getCompanyId()]
            ])
            ->count();
    }
    //
    /**
     * Get all location in ONE company
     * @param
     */
    public function getLov()
    {
        return
            DB::table('locations')
                ->select(
                    'locations.code',
                    'locations.name',
                    'countries.name as countryName',
                    'provinces.name as provinceName',
                    'cities.name as cityName',
                    'locations.address',
                    'locations.phone',
                    'locations.fax',
                    'locations.postal_code as postalCode',
                    'locations.tax_office_code as taxOfficeCode'
                )
                ->leftjoin('cities', 'cities.code', '=', 'locations.city_code')
                ->leftjoin('provinces', 'provinces.code', '=', 'cities.province_code')
                ->leftjoin('countries', 'countries.code', '=', 'provinces.country_code')
                ->where([
                    ['locations.tenant_id', '=', $this->requester->getTenantId()],
                    ['locations.company_id', '=', $this->requester->getCompanyId()],
                    ['cities.tenant_id', '=', $this->requester->getTenantId()],
                    ['cities.company_id', '=', $this->requester->getCompanyId()],
                    ['provinces.tenant_id', '=', $this->requester->getTenantId()],
                    ['provinces.company_id', '=', $this->requester->getCompanyId()],
                    ['countries.tenant_id', '=', $this->requester->getTenantId()],
                    ['countries.company_id', '=', $this->requester->getCompanyId()],
                    ['locations.eff_begin', '<=', Carbon::now()],
                    ['locations.eff_end', '>=', Carbon::now()]
                ])
                ->get();
    }
    /**
     * Get one location in ONE company based on location code
     * @param  company_id ,locationCode
     */
    public function getOne($locationId)
    {
        return
            DB::table('locations')
                ->select(
                    'locations.id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'locations.name',
                    'locations.code',
                    'description',
                    'tax_office_code as taxOfficeCode',
                    'v_reg_mapping.country_code as countryCode',
                    'v_reg_mapping.province_code as provinceCode',
                    'locations.city_code as cityCode',
                    'v_reg_mapping.country_name as countryName',
                    'v_reg_mapping.province_name as provinceName',
                    'v_reg_mapping.city_name as cityName',
                    'address',
                    'postal_code as postalCode',
                    'phone',
                    'fax',
                    'longitude',
                    'latitude'
                )
                ->leftJoin('v_reg_mapping', function ($join) {
                    $join
                        ->on('v_reg_mapping.city_code', '=', 'locations.city_code');
                })
                ->where([
                    ['locations.id', $locationId],
                    ['locations.tenant_id', $this->requester->getTenantId()]
                ])
                ->orderBy('name', 'asc')
                ->first();
    }
    /**
     * Get one location in ONE company based on location code
     * @param  company_id ,locationCode
     */
    public function getOneByCode($locationCode)
    {
        return
            DB::table('locations')
                ->select(
                    'locations.id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'locations.name',
                    'locations.code',
                    'description',
                    'v_reg_mapping.country_name as countryName',
                    'v_reg_mapping.province_name as provinceName',
                    'v_reg_mapping.city_name as cityName',
                    'tax_office_code as taxOfficeCode',
                    'v_reg_mapping.country_code as countryCode',
                    'v_reg_mapping.province_code as provinceCode',
                    'locations.city_code as cityCode',
                    'address',
                    'postal_code as postalCode',
                    'phone',
                    'fax',
                    'longitude',
                    'latitude'
                )
                ->leftJoin('v_reg_mapping', function ($join) {
                    $join
                        ->on('v_reg_mapping.city_code', '=', 'locations.city_code');
                })
                ->where([
                    ['locations.code', $locationCode],
                    ['locations.company_id', $this->requester->getCompanyId()],
                    ['locations.tenant_id', $this->requester->getTenantId()]
                ])
                ->orderBy('name', 'asc')
                ->first();
    }
    public function getDefault()
    {
        return DB::table('locations')
            ->select('locations.code', 'locations.name')
            ->join('companies', 'companies.location_code', '=', 'locations.code')
            ->where([
                ['locations.tenant_id', $this->requester->getTenantId()],
                ['locations.company_id', $this->requester->getCompanyId()]
            ])
            ->first();
    }
    /**
     * get one data for a location
     * this function is used in education institution and
     * specialization Used By Employee
     * @param  string $locationCode
     */
    public function getOneLocationByCode($locationCode)
    {
        return
            DB::table('locations')
                ->select(
                    'locations.id',
                    'locations.name',
                    'locations.code'
                )
                ->where([
                    ['locations.tenant_id', $this->requester->getTenantId()],
                    ['locations.company_id', $this->requester->getCompanyId()],
                    ['locations.code', $locationCode]
                ])
                ->first();
    }

    /**
     * Insert data location to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'locations', $obj);

        return DB::table('locations')->insertGetId($obj);
    }

    /**
     * Update data location to DB
     * @param array obj, location id
     */
    public function update($locationId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'locations', $obj);

        DB::table('locations')
            ->where([
                ['locations.tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $locationId]
            ])
            ->update($obj);
    }

    /**
     * Delete data location from DB
     * @param id
     */
    public function delete(string $id)
    {
        DB::table('locations')
            ->where([
                ['company_id', $this->requester->getCompanyId()],
                ['id', $id]
            ])->delete();
    }
    /**
     * @param string name
     * @return
     */
    public function checkDuplicateLocationName(string $name)
    {
        return DB::table('locations')->where([
            ['name', $name],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }
    /**
     * @param string code
     * @return
     */
    public function checkDuplicateLocationCode(string $code)
    {
        return DB::table('locations')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }
    /**
     * @param string $name
     * @param $id if update data, then check duplicate code beside current location id
     * @return
     */
    public function checkDuplicateEditLocationName(string $name, string $id)
    {
        $result = DB::table('locations')->where([
            ['name', $name],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ]);
        if (!is_null($id)) {
            $result->where('id', '!=', $id);
        }
        return $result->count();
    }
    public function search($query, $offset, $limit)
    {
        $now = Carbon::now();
        $searchString = strtolower("%$query%");
        return
            DB::table('locations')
                ->select('code', 'name')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', $now],
                    ['eff_end', '>=', $now]
                ])
                ->whereRaw('LOWER(name) like ?', [$searchString])
                ->offset($offset)
                ->limit($limit)
                ->get();
    }
}
