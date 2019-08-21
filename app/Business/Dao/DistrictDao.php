<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DistrictDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all District
     * @param offset, limit
     */
    public function getAll($offset,$limit,$cityId)
    {
        return
            DB::table('districts')
                ->select(
                    'districts.id',
                    'districts.name',
                    'city_id as cityId',
                    'cities.name as cityName'
                )
                ->leftjoin('cities', function ($join) {
                    $join
                        ->on('cities.id', '=', 'districts.city_id');
                })
                ->where([
                    ['districts.city_id', $cityId],
                    ['districts.tenant_id', $this->requester->getTenantId()],
                    ['districts.company_id', $this->requester->getCompanyId()]
                ])
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all districts (lib+districts) in ONE company
     * @param cityId
     */
    public function getLov($cityId)
    {
        return
            DB::table('districts')
                ->select(
                    'id',
                    'name'
                )
                ->where([
                    ['tenant_id', '=', $this->requester->getTenantId()],
                    ['company_id', '=', $this->requester->getCompanyId()],
                    ['city_id', '=', $cityId]
                ])
                ->get();
    }

    /**
     * @param
     * @return
     */
    public function getTotalRow()
    {
        return DB::table('districts')
            ->leftjoin('cities', function ($join) {
                $join
                    ->on('cities.id', '=', 'districts.city_id');
            })
            ->where([
                ['districts.tenant_id', $this->requester->getTenantId()],
                ['districts.company_id', $this->requester->getCompanyId()]
            ])
            ->count();
    }

    /**
    * Get one District based on district id
    * @param districtId
    */
    public function getOne($districtId)
    {
        return
            DB::table('districts')
                ->select(
                    'districts.id',
                    'districts.name',
                    'city_id as cityId',
                    'cities.name as cityName'
                )
                ->leftjoin('cities', function ($join) {
                    $join
                        ->on('cities.id', '=', 'districts.city_id');
                })
                ->where([
                    ['districts.tenant_id', $this->requester->getTenantId()],
                    ['districts.company_id', $this->requester->getCompanyId()],
                    ['districts.id', $districtId]
                ])
                ->first();
    }

    /**
     * Insert data District to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('districts')->insertGetId($obj);
    }

    /**
     * Update data District to DB
     * @param  array obj, districtId
     */
    public function update($districtId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('districts')
        ->where([
            ['id', $districtId]
        ])
        ->update($obj);
    }

    /**
     * Delete data District from DB
     * @param  districtId
     */
    public function delete($districtId)
    {
        DB::table('districts')->where('id', $districtId)->delete();
    }

    /**
     * @param string $code
     * @param $id if update data, then check duplicate code beside current district id
     * @return
     */
    public function checkDuplicateEditDistrictName(string $name,int $cityId, $id)
    {
        $result = DB::table('districts')->where([
            ['name', $name],
            ['city_id', $cityId],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ]);

        if (!is_null($id)) {
            $result->where('id', '!=', $id);
        }

        return $result->count();
    }

    /**
     * @param string $code
     * @return
     */
    public function checkDuplicateDistrictName(string $name, int $cityId)
    {
        return DB::table('districts')->where([
            ['name', $name],
            ['city_id', $cityId],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }
}
