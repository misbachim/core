<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LocationGroupDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all location group in ONE company
     * @param  $offset, limit
     */
    public function getAll($offset,$limit)
    {
        return
            DB::table('location_groups')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name'

                )
                //->leftJoin('locations', 'location_groups.id', '=', 'location.groups_id')

                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]

                ])
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all Active location group
     * @param  $offset, limit
     */
    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('location_groups')
            ->select(
                'id',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'code',
                'name'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['eff_begin', '<=', Carbon::now()],
                ['eff_end', '>=', Carbon::now()]
            ])
            ->orderByRaw('eff_end DESC')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Get all InActive location group
     * @param  $offset, limit
     */
    public function getAllInActive()
    {
        return
            DB::table('location_groups')
            ->select(
                'id',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'code',
                'name'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['eff_end', '<', Carbon::now()]
            ])
            ->get();
    }

    /**
     * @return
     */
    public function getTotalRow()
    {
        return DB::table('location_groups')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])
            ->count();
    }

    /**
     * Get one location group based on location group id
     * @param  locationGroupId
     */
    public function getOne($locationGroupId)
    {
        return
            DB::table('location_groups')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['id', $locationGroupId]
                ])
                ->first();
    }

    /**
     * Insert data location group to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'location_groups', $obj);

        return DB::table('location_groups')-> insertGetId($obj);
    }

    /**
     * Update data Location Group to DB
     */
    public function update($id, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'location_groups', $obj);

        DB::table('location_groups')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['id', $id]
        ])
        ->update($obj);
    }

    /**
     * Delete data location group from DB
     * @param  code
     */
    public function delete($id)
    {
        DB::table('location_groups')->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['id', $id]
        ])->delete();
    }


    /**
     * @param string $code
     * @return
     */
    public function checkDuplicateLocationGroupCode(string $code)
    {
        return DB::table('location_groups')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

    /**
     * @param string $code
     * @param $id if update data, then check duplicate code beside current LG id
     * @return
     */
    public function checkDuplicateEditLocationGroupCode(string $code, $id)
    {
        $result = DB::table('location_groups')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ]);

        if (!is_null($id)) {
            $result->where('id', '!=', $id);
        }

        return $result->count();
    }
}
