<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomFieldDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Custom Field in One Company
     */
    public function getAll()
    {
        return
            DB::table('cf')
            ->select(
                'cf.id',
                'cf.lov_cusmod as lovCusmod',
                'cusmod.val_data as cusmod',
                'cf.name',
                'cf.lov_cdtype as lovCdtype',
                'cdType.val_data as cdType',
                'cf.field_name as fieldName',
                'cf.lov_type_code as lovTypeCode',
                'cf.is_active as isActive'
            )
            ->join('lovs as cusmod', function ($join) {
                $join
                    ->on('cusmod.key_data', '=', 'cf.lov_cusmod')
                    ->on('cusmod.tenant_id', '=', 'cf.tenant_id')
                    ->on('cusmod.company_id', '=', 'cf.company_id');
            })
            ->join('lovs as cdType', function ($join) {
                $join
                    ->on('cdType.key_data', '=', 'cf.lov_cdtype')
                    ->on('cdType.tenant_id', '=', 'cf.tenant_id')
                    ->on('cdType.company_id', '=', 'cf.company_id');
            })
            ->where([
                ['cf.tenant_id', $this->requester->getTenantId()],
                ['cf.company_id', $this->requester->getCompanyId()]
            ])
            ->get();
    }

    /**
     * Get one Custom Field in One Company based on CF id
     * @param cfId
     */
    public function getOne($cfId)
    {
        return
            DB::table('cf')
            ->select(
                'cf.id',
                'cf.lov_cusmod as lovCusmod',
                'cusmod.val_data as cusmod',
                'cf.name',
                'cf.field_name as fieldName',
                'cf.lov_cdtype as lovCdtype',
                'cdType.val_data as cdType',
                'cf.lov_type_code as lovTypeCode',
                'cf.is_active as isActive',
                'lov_types.name as lovTypeName'
            )
            ->join('lovs as cusmod', function ($join) {
                $join
                    ->on('cusmod.key_data', '=', 'cf.lov_cusmod')
                    ->on('cusmod.tenant_id', '=', 'cf.tenant_id')
                    ->on('cusmod.company_id', '=', 'cf.company_id');
            })
            ->join('lovs as cdType', function ($join) {
                $join
                    ->on('cdType.key_data', '=', 'cf.lov_cdtype')
                    ->on('cdType.tenant_id', '=', 'cf.tenant_id')
                    ->on('cdType.company_id', '=', 'cf.company_id');
            })
            ->leftjoin('lov_types', function ($join) {
                $join
                    ->on('lov_types.code', '=', 'cf.lov_type_code')
                    ->where([
                        ['cf.tenant_id', $this->requester->getTenantId()],
                        ['cf.company_id', $this->requester->getCompanyId()]
                    ]);;
            })
            ->where([
                ['cf.tenant_id', $this->requester->getTenantId()],
                ['cf.company_id', $this->requester->getCompanyId()],
                ['cf.id', $cfId]
            ])
            ->first();
    }


    /**
     * Get one Custom Field in One Company based on CF id
     * @param cfId
     */
    public function getOneByFieldName($fieldName)
    {
        return
            DB::table('cf')
            ->select(
                'cf.id',
                'cf.lov_cusmod as lovCusmod',
                'cusmod.val_data as cusmod',
                'cf.name',
                'cf.field_name as fieldName',
                'cf.lov_cdtype as lovCdtype',
                'cdType.val_data as cdType',
                'cf.lov_type_code as lovTypeCode',
                'cf.is_active as isActive',
                'lov_types.name as lovTypeName'
            )
            ->join('lovs as cusmod', function ($join) {
                $join
                    ->on('cusmod.key_data', '=', 'cf.lov_cusmod')
                    ->on('cusmod.tenant_id', '=', 'cf.tenant_id')
                    ->on('cusmod.company_id', '=', 'cf.company_id');
            })
            ->join('lovs as cdType', function ($join) {
                $join
                    ->on('cdType.key_data', '=', 'cf.lov_cdtype')
                    ->on('cdType.tenant_id', '=', 'cf.tenant_id')
                    ->on('cdType.company_id', '=', 'cf.company_id');
            })
            ->leftjoin('lov_types', function ($join) {
                $join
                    ->on('lov_types.code', '=', 'cf.lov_type_code')
                    ->where([
                        ['cf.tenant_id', $this->requester->getTenantId()],
                        ['cf.company_id', $this->requester->getCompanyId()]
                    ]);;
            })
            ->where([
                ['cf.tenant_id', $this->requester->getTenantId()],
                ['cf.company_id', $this->requester->getCompanyId()],
                ['cf.field_name', $fieldName]
            ])
            ->first();
    }

    /**
     * Get all Custom Fields for one module.
     * @param $lovCusmod
     * @return
     */
    public function getAllForModule($lovCusmod)
    {
        return
            DB::table('cf')
            ->select(
                'cf.id',
                'cf.lov_cusmod as lovCusmod',
                'cusmod.val_data as cusmod',
                'cf.name',
                'cf.field_name as fieldName',
                'cf.lov_cdtype as lovCdtype',
                'cdType.val_data as cdType',
                'cf.lov_type_code as lovTypeCode',
                'cf.is_active as isActive',
                'lov_types.name as lovTypeName'
            )
            ->join('lovs as cusmod', function ($join) {
                $join
                    ->on('cusmod.key_data', '=', 'cf.lov_cusmod')
                    ->on('cusmod.tenant_id', '=', 'cf.tenant_id')
                    ->on('cusmod.company_id', '=', 'cf.company_id');
            })
            ->join('lovs as cdType', function ($join) {
                $join
                    ->on('cdType.key_data', '=', 'cf.lov_cdtype')
                    ->on('cdType.tenant_id', '=', 'cf.tenant_id')
                    ->on('cdType.company_id', '=', 'cf.company_id');
            })
            ->leftjoin('lov_types', function ($join) {
                $join
                    ->on('lov_types.code', '=', 'cf.lov_type_code')
                    ->where([
                        ['cf.tenant_id', $this->requester->getTenantId()],
                        ['cf.company_id', $this->requester->getCompanyId()]
                    ]);;
            })
            ->where([
                ['cf.tenant_id', $this->requester->getTenantId()],
                ['cf.company_id', $this->requester->getCompanyId()],
                ['cf.lov_cusmod', $lovCusmod],
                ['cf.is_active', true]
            ])
            ->orderBy('cf.field_name', 'asc')
            ->get();
    }

    /**
     * Get last field name
     * @param cusmod
     */
    public function getLastFieldName($cusmod)
    {
        return
            DB::table('cf')
            ->select(
                'cf.field_name as fieldName'
            )
            ->where([
                ['cf.tenant_id', $this->requester->getTenantId()],
                ['cf.company_id', $this->requester->getCompanyId()],
                ['cf.lov_cusmod', $cusmod]
            ])
            ->orderBy('field_name', 'desc')
            ->first();
    }

    public function getNameById($id)
    {
        return
            DB::table('cf')
            ->select(
                'name'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['field_name', $id]
            ])
            ->get();
    }

    /**
     * Insert data custom field to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'cf', $obj);

        return DB::table('cf')->insertGetId($obj);
    }

    /**
     * Update data custom field to DB
     * @param cfId , array obj
     */
    public function update($cfId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'cf', $obj);

        DB::table('cf')
            ->where([
                ['id', $cfId]
            ])
            ->update($obj);
    }

    /**
     * Delete data custom field from DB
     * @param cfId
     */
    public function delete($cfId)
    {
        DB::table('cf')->where('id', $cfId)->delete();
    }
}
