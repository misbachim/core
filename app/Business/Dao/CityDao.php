<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CityDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all City in One Company
     * @param offset,limit
     */
    public function getAll($offset,$limit,$provinceCode)
    {
        return
            DB::table('cities')
                ->select(
                    'cities.id',
                    'cities.code',
                    'cities.name',
                    'provinces.id as provinceId',
                    'provinces.name as provinceName'
                )
                ->leftjoin('provinces', function ($join) {
                    $join
                        ->on('provinces.code', '=', 'cities.province_code')
                        ->on('provinces.tenant_id', '=', 'cities.tenant_id')
                        ->on('provinces.company_id', '=', 'cities.company_id');
                })
                ->where([
                    ['cities.province_code', $provinceCode],
                    ['cities.tenant_id', $this->requester->getTenantId()],
                    ['cities.company_id', $this->requester->getCompanyId()]
                ])
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all cities (lib+cities) in ONE company
     * @param $provinceId
     */
    public function getLov($provinceCode)
    {
        return
            DB::table('cities')
                ->select(
                    'id',
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', '=', $this->requester->getTenantId()],
                    ['company_id', '=', $this->requester->getCompanyId()],
                    ['province_code', '=', $provinceCode]
                ])
                ->get();
    }

    /**
     * @param
     * @return
     */
    public function getTotalRow()
    {
        return DB::table('cities')
            ->leftjoin('provinces', function ($join) {
                $join
                    ->on('provinces.code', '=', 'cities.province_code');
            })
            ->where([
                ['cities.tenant_id', $this->requester->getTenantId()],
                ['cities.company_id', $this->requester->getCompanyId()]
            ])
            ->count();
    }

    /**
     * Get one City in One Company based on city id
     * @param cityId
     */
    public function getOne($cityId)
    {
        return
            DB::table('cities')
                ->select(
                    'cities.id',
                    'cities.code',
                    'cities.name',
                    'provinces.id as provinceId',
                    'provinces.name as provinceName'
                )
                ->join('provinces', function ($join) {
                    $join
                        ->on('provinces.code', '=', 'cities.province_code');
                })
                ->where([
                    ['cities.tenant_id', $this->requester->getTenantId()],
                    ['cities.company_id', $this->requester->getCompanyId()],
                    ['cities.id', $cityId]
                ])
                ->first();
    }

    /**
     * Insert data City to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'cities', $obj);

        return DB::table('cities')->insertGetId($obj);
    }

    /**
     * Update data City to DB
     * @param  cityId, array obj
     */
    public function update($cityId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'cities', $obj);

        DB::table('cities')
        ->where([
            ['id', $cityId]
        ])
        ->update($obj);
    }

    /**
     * Delete data City from DB
     * @param  cityId
     */
    public function delete($cityId)
    {
        DB::table('cities')->where('id', $cityId)->delete();
    }

    /**
     * @param string $code
     * @return
     */
    public function checkDuplicateCityCode(string $code)
    {
        return DB::table('cities')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

    /**
     * @param string $code
     * @param $id if update data, then check duplicate code beside current user id
     * @return
     */
    public function checkDuplicateEditCityCode(string $code, $id)
    {
        $result = DB::table('cities')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ]);

        if (!is_null($id)) {
            $result->where('id', '!=', $id);
        }

        return $result->count();
    }


    /**
     * Check usage. If result > 0 it means there is a dependency.
     * @param int $cityId
     * @return mixed
     */
    public function getTotalUsage(int $cityId)
    {
        return DB::table('cities')
            ->join('districts', function ($join) {
                $join
                    ->on('cities.id', '=', 'districts.city_id');
            })
            ->where('cities.id', $cityId)
            ->where('cities.tenant_id', $this->requester->getTenantId())
            ->count();
    }

     /*
     |----------------------------------------------------
     | sarch data city lengkap dengan province dan ountry
     |----------------------------------------------------
     |
     |
     */
     public function getSearch ($param, $column) {

        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        $searchString = strtolower("%$param%");

           $sql = DB::table('cities')
            ->select(
                'cities.id',
                'cities.name as cityName',
                'cities.code as cityCode',
                'provinces.code as provinceCode',
                'provinces.name as provinceName',
                'countries.code as countryCode',
                'countries.name as countryName'
            )
            ->join('provinces',  function ($join) use($tenantId, $companyId) {
                $join
                    ->on('provinces.code','cities.province_code')
                    ->where([
                        ['provinces.tenant_id', $tenantId],
                        ['provinces.company_id', $companyId]
                    ]);
            })
            ->join('countries',  function ($join) use($tenantId, $companyId) {
                $join
                    ->on('countries.code','provinces.country_code')
                    ->where([
                        ['countries.tenant_id', $tenantId],
                        ['countries.company_id', $companyId]
                    ]);
            })
            ->where([
                ['cities.tenant_id', $tenantId],
                ['cities.company_id', $companyId]
            ]);
            if ($column == 'name') {
                $sql->whereRaw('LOWER(cities.name) like ?', [$searchString]);
            } else {
                $sql->whereRaw('LOWER(cities.code) like ?', [$searchString]);
            }
            return $sql->get();
    }


}
