<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

/**
 * @property Requester requester
 */
class CustomObjectFieldDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    public function getAll($customObjectId)
    {
        return
            DB::table('co_fields')
            ->select(
                'id',
                'name',
                'lov_cdtype as lovCdtype',
                'lov_type_code as lovTypeCode',
                'is_disabled as isDisabled'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['co_id', $customObjectId]
            ])
            ->get();
    }

    public function getAllField()
    {
        return
            DB::table('co_fields')
            ->select(
                'id',
                'name',
                'lov_cdtype as lovCdtype',
                'lov_type_code as lovTypeCode',
                'is_disabled as isDisabled'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])
            ->get();
    }

    public function getNameById($id)
    {
        return
            DB::table('co_fields')
            ->select(
                'name'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $id]
            ])
            ->get();
    }

    public function saveAll($objects)
    {
        DB::table('co_fields')->insert($objects);
    }

    public function deleteAll($customObjectId)
    {
        DB::table('co_fields')->where('co_id', $customObjectId)->delete();
    }
}
