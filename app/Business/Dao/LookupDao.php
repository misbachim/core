<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LookupDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all lookup in ONE company
     * @param
     */
    public function getAll()
    {
        return
            DB::table('lookups')
                ->select(
                    'id',
                    'description',
                    'code',
                    'name',
                    'eff_end as effEnd',
                    'eff_begin as effBegin',
                    'lov_ltyp as lovLtyp',
                    'lovType.val_data as type',
                    'lov1.val_data as look1',
                    'lov2.val_data as look2',
                    'lov3.val_data as look3',
                    'lov4.val_data as look4',
                    'lov5.val_data as look5'
                )
                ->leftJoin('lovs as lov1', function ($join) {
                    $join->on('lov1.key_data', '=', 'lookups.lov_look_1')
                        ->where([
                            ['lov1.lov_type_code', 'LOOK'],
                            ['lov1.company_id', $this->requester->getCompanyId()],
                            ['lov1.tenant_id', $this->requester->getTenantId()]
                        ]);
                })
                ->leftJoin('lovs as lov2', function ($join) {
                    $join->on('lov2.key_data', '=', 'lookups.lov_look_2')
                        ->where([
                            ['lov2.lov_type_code', 'LOOK'],
                            ['lov2.company_id', $this->requester->getCompanyId()],
                            ['lov2.tenant_id', $this->requester->getTenantId()]
                        ]);
                })
                ->leftJoin('lovs as lov3', function ($join) {
                    $join->on('lov3.key_data', '=', 'lookups.lov_look_3')
                        ->where([
                            ['lov3.lov_type_code', 'LOOK'],
                            ['lov3.company_id', $this->requester->getCompanyId()],
                            ['lov3.tenant_id', $this->requester->getTenantId()]
                        ]);
                })
                ->leftJoin('lovs as lov4', function ($join) {
                    $join->on('lov4.key_data', '=', 'lookups.lov_look_4')
                        ->where([
                            ['lov4.lov_type_code', 'LOOK'],
                            ['lov4.company_id', $this->requester->getCompanyId()],
                            ['lov4.tenant_id', $this->requester->getTenantId()]
                        ]);
                })
                ->leftJoin('lovs as lov5', function ($join) {
                    $join->on('lov5.key_data', '=', 'lookups.lov_look_5')
                        ->where([
                            ['lov5.lov_type_code', 'LOOK'],
                            ['lov5.company_id', $this->requester->getCompanyId()],
                            ['lov5.tenant_id', $this->requester->getTenantId()]
                        ]);
                })
                ->leftJoin('lovs as lovType', function ($join) {
                    $join->on('lovType.key_data', '=', 'lookups.lov_ltyp')
                        ->where([
                            ['lovType.lov_type_code', 'LTYP'],
                            ['lovType.company_id', $this->requester->getCompanyId()],
                            ['lovType.tenant_id', $this->requester->getTenantId()]
                        ]);
                })
                ->where([
                    ['lookups.tenant_id', $this->requester->getTenantId()],
                    ['lookups.company_id', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * Get one lookup based on code
     * @param  code
     */
    public function getOne($code)
    {
        return
            DB::table('lookups')
                ->select(
                    'id',
                    'description',
                    'code',
                    'name',
                    'eff_end as effEnd',
                    'eff_begin as effBegin',
                    'lov_ltyp as lovLtyp',
                    'lovType.val_data as type',
                    'lov_look_1 as lovLook1',
                    'lov_look_2 as lovLook2',
                    'lov_look_3 as lovLook3',
                    'lov_look_4 as lovLook4',
                    'lov_look_5 as lovLook5',
                    'lov1.val_data as look1',
                    'lov2.val_data as look2',
                    'lov3.val_data as look3',
                    'lov4.val_data as look4',
                    'lov5.val_data as look5'
                )
                ->leftJoin('lovs as lov1', function ($join) {
                    $join->on('lov1.key_data', '=', 'lookups.lov_look_1')
                        ->where([
                            ['lov1.lov_type_code', 'LOOK'],
                            ['lov1.company_id', $this->requester->getCompanyId()],
                            ['lov1.tenant_id', $this->requester->getTenantId()]
                        ]);
                })
                ->leftJoin('lovs as lov2', function ($join) {
                    $join->on('lov2.key_data', '=', 'lookups.lov_look_2')
                        ->where([
                            ['lov2.lov_type_code', 'LOOK'],
                            ['lov2.company_id', $this->requester->getCompanyId()],
                            ['lov2.tenant_id', $this->requester->getTenantId()]
                        ]);
                })
                ->leftJoin('lovs as lov3', function ($join) {
                    $join->on('lov3.key_data', '=', 'lookups.lov_look_3')
                        ->where([
                            ['lov3.lov_type_code', 'LOOK'],
                            ['lov3.company_id', $this->requester->getCompanyId()],
                            ['lov3.tenant_id', $this->requester->getTenantId()]
                        ]);
                })
                ->leftJoin('lovs as lov4', function ($join) {
                    $join->on('lov4.key_data', '=', 'lookups.lov_look_4')
                        ->where([
                            ['lov4.lov_type_code', 'LOOK'],
                            ['lov4.company_id', $this->requester->getCompanyId()],
                            ['lov4.tenant_id', $this->requester->getTenantId()]
                        ]);
                })
                ->leftJoin('lovs as lov5', function ($join) {
                    $join->on('lov5.key_data', '=', 'lookups.lov_look_5')
                        ->where([
                            ['lov5.lov_type_code', 'LOOK'],
                            ['lov5.company_id', $this->requester->getCompanyId()],
                            ['lov5.tenant_id', $this->requester->getTenantId()]
                        ]);
                })
                ->leftJoin('lovs as lovType', function ($join) {
                    $join->on('lovType.key_data', '=', 'lookups.lov_ltyp')
                        ->where([
                            ['lovType.lov_type_code', 'LTYP'],
                            ['lovType.company_id', $this->requester->getCompanyId()],
                            ['lovType.tenant_id', $this->requester->getTenantId()]
                        ]);
                })
                ->where([
                    ['lookups.tenant_id', $this->requester->getTenantId()],
                    ['lookups.company_id', $this->requester->getCompanyId()],
                    ['lookups.code', $code]
                ])
                ->orderBy('id', 'desc')
                ->first();
    }

    //get Lov
    public function getAllActive($type)
    {
        return
            DB::table('lookups')
                ->select(
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()],
                    ['lov_ltyp',$type]
                ])
                ->get();
    }

    /**
     * Insert data lookup to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'lookups', $obj);

        return DB::table('lookups')-> insertGetId($obj);
    }

    /**
     * Update data lookups to DB
     */
    public function update($id, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'lookups', $obj);

        DB::table('lookups')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['id', $id]
        ])
        ->update($obj);
    }

    /**
     * Delete data lookups from DB
     * @param  code
     */
    public function delete($id)
    {
        DB::table('lookups')->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['id', $id]
        ])->delete();
    }


    /**
     * @param string $code
     * @return
     */
    public function checkDuplicateLookupCode(string $code)
    {
        return DB::table('lookups')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

}
