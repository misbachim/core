<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProvinceDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Provinces in One Company
     * @param offset,limit,countryId
     */
    public function getAll($offset,$limit,$countryCode)
    {
        return
            DB::table('provinces')
                ->select(
                    'provinces.id',
                    'provinces.code',
                    'provinces.name',
                    'countries.id as countryId',
                    'countries.name as countryName'
                )
                ->leftjoin('countries', function ($join) {
                    $join
                        ->on('countries.code', '=', 'provinces.country_code')
                        ->on('countries.tenant_id', '=', 'provinces.tenant_id')
                        ->on('countries.company_id', '=', 'provinces.company_id');
                })
                ->where([
                    ['provinces.country_code', $countryCode],
                    ['provinces.tenant_id', $this->requester->getTenantId()],
                    ['provinces.company_id', $this->requester->getCompanyId()]
                ])
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all provinces (lib+provinces) in ONE company
     * @param  $countryId
     */
    public function getLov($countryCode)
    {
        return
            DB::table('provinces')
                ->select(
                    'id',
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', '=', $this->requester->getTenantId()],
                    ['company_id', '=', $this->requester->getCompanyId()],
                    ['country_code', '=', $countryCode]
                ])
                ->get();
    }

    /**
     * @param
     * @return
     */
    public function getTotalRow()
    {
        return DB::table('provinces')
            ->leftjoin('countries', function ($join) {
                $join
                    ->on('countries.code', '=', 'provinces.country_code');
            })
            ->where([
                ['provinces.tenant_id', $this->requester->getTenantId()],
                ['provinces.company_id', $this->requester->getCompanyId()]
            ])
            ->count();
    }

    /**
     * Get one Provinces based on province id
     * @param provinceId
     */
    public function getOne($provinceId)
    {
        return
            DB::table('provinces')
                ->select(
                    'provinces.id',
                    'provinces.code',
                    'provinces.name',
                    'countries.id as countryId',
                    'countries.name as countryName'
                )
                ->leftjoin('countries', function ($join) {
                    $join
                        ->on('countries.code', '=', 'provinces.country_code');
                })
                ->where([
                    ['provinces.tenant_id', $this->requester->getTenantId()],
                    ['provinces.company_id', $this->requester->getCompanyId()],
                    ['provinces.id', $provinceId]
                ])
                ->first();
    }

    /**
     * Insert data Provinces to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'provinces', $obj);

        return DB::table('provinces')->insertGetId($obj);
    }

    /**
     * Update data Provinces to DB
     * @param  array obj, provinceId
     */
    public function update($provinceId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'provinces', $obj);

        DB::table('provinces')
        ->where([
            ['id', $provinceId]
        ])
        ->update($obj);
    }

    /**
     * Delete data Provinces from DB
     * @param  provinceId
     */
    public function delete($provinceId)
    {
        DB::table('provinces')->where('id', $provinceId)->delete();
    }

    /**
     * @param string $code
     * @return
     */
    public function checkDuplicateProvinceCode(string $code, $countryCode)
    {
        return DB::table('provinces')->where([
            ['code', $code],
            ['country_code', $countryCode],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

    /**
     * @param string $code
     * @param $id if update data, then check duplicate code beside current province id
     * @return
     */
    public function checkDuplicateEditProvinceCode(string $code, $id)
    {
        $result = DB::table('provinces')->where([
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
     * @param int $provinceId
     * @return mixed
     */
    public function getTotalUsage(int $provinceId)
    {
        return DB::table('provinces')
            ->join('cities', function ($join) {
                $join
                    ->on('provinces.code', '=', 'cities.province_code');
            })
            ->where('provinces.id', $provinceId)
            ->where('provinces.tenant_id', $this->requester->getTenantId())
            ->count();
    }
}
