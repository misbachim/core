<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LovDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Lov in ONE company
     * @param  typeCode
     */
    public function getAll($typeCode)
    {
        return
            DB::table('lovs')
                ->select(
                    'key_data as keyData',
                    'val_data as valData',
                    'lovs.lov_type_code as lovTypeCode',
                    'lovs.is_disableable as isDisableable',
                    'lovs.is_active as isActive',
                    'lov_types.name as lovTypeName'
                )
                ->leftJoin('lov_types', function ($join) {
                    $join ->on('lov_types.code', '=', 'lovs.lov_type_code');
                })
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['lov_type_code', $typeCode]
                ])
                ->get();
    }

    /**
     * Get one Lov in ONE company based on key data
     * @param  keyData
     */
    public function getOne($lovTypeCode,$keyData)
    {
        return
            DB::table('lovs')
                ->select(
                    'key_data as keyData',
                    'val_data as valData',
                    'lovs.lov_type_code as lovTypeCode',
                    'lov_types.name as lovTypeName'
                )
                ->leftJoin('lov_types', function ($join) {
                    $join
                        ->on('lov_types.code', '=', 'lovs.lov_type_code');
                })
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['lov_type_code', $lovTypeCode],
                    ['key_data', $keyData]
                ])
                ->first();
    }

    /**
     * Insert data Lov to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        DB::table('lovs')->insert($obj);
    }

    public function saveAll($objs)
    {
        DB::table('lovs')->insert($objs);
    }

    /**
     * Update data Lov to DB
     * @param  array obj, keyData, lovTypeCode
     */
    public function update($keyData,$lovTypeCode,$obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('lovs')
        ->where([
            ['key_data', $keyData],
            ['lov_type_code', $lovTypeCode]
        ])
        ->update($obj);
    }

    /**
     * Delete data Lov
     * @param  array obj, keyData, lovTypeCode
     */
    public function delete($keyData,$lovTypeCode)
    {

        DB::table('lovs')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['key_data', $keyData],
                ['lov_type_code', $lovTypeCode]
            ])
            ->delete();
    }

    /**
     * Delete data Lov
     * @param  array obj, lovTypeCode
     */
    public function deleteByType($lovTypeCode)
    {
        DB::table('lovs')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['lov_type_code', $lovTypeCode],
                ['is_disableable', true]
            ])
            ->delete();
    }

    /**
     * @param string keyData, lovTypeCode
     * @return
     */
    public function checkDuplicateLovKeyData(string $keyData, string $lovTypeCode)
    {
        return DB::table('lovs')->where([
            ['key_data', $keyData],
            ['lov_type_code', $lovTypeCode],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

    /**
     * Check usage. If result > 0 it means data can not be deleted.
     * @param string $keyData, lovTypeCode
     * @return mixed
     */
    public function checkIsDeleteable(string $keyData, string $lovTypeCode)
    {
        return DB::table('lovs')->where([
            ['key_data', $keyData],
            ['lov_type_code', $lovTypeCode],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()],
            ['is_disableable',false]
        ])->count();
    }

}
