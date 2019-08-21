<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssetDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Asset for one company
     * @param
     */
    public function getAll($offset,$limit)
    {
        return
            DB::table('assets')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name',
                    'price',
                    'description',
                    'type'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]
                ])
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get All Active Asset in One company
     */
    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('assets')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name',
                    'price',
                    'description',
                    'type'
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
     * Get All InActive Job Asset in One company
     */
    public function getAllInActive()
    {
        return
        DB::table('assets')
            ->select(
                'id',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'code',
                'name',
                'price',
                'description',
                'type'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['eff_end', '<', Carbon::now()]
            ])
            ->get();
    }


    /**
     * Get all Asset in ONE company
     * @param
     */
    public function getLov()
    {
        return
            DB::table('assets')
                ->select(
                    'assets.code',
                    'assets.name'
                )
//                ->leftjoin('person_assets', 'person_assets.asset_code', '=', 'assets.code')
                ->where([
                    ['assets.tenant_id', '=', $this->requester->getTenantId()],
                    ['assets.company_id', '=', $this->requester->getCompanyId()],
                    ['eff_begin','<=', Carbon::now()],
                    ['eff_end','>=', Carbon::now()]
                ])
//                ->whereRaw('assets.code NOT IN (select asset_code from person_assets where return_receipt_id IS NULL)')

        ->get();
    }

    /**
     * Get one Asset based on assetCode
     * @param  assetId
     */
    public function getOne($assetid)
    {
        return
            DB::table('assets')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name',
                    'price',
                    'description',
                    'type'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['id', $assetid]
                ])
                ->first();
    }

    /**
     * Insert data asset to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'assets', $obj);

        return DB::table('assets')->insert($obj);
    }

    /**
     * Update data asset to DB
     * @param  array obj, assetId
     */
    public function update($assetCode, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'assets', $obj);

        DB::table('assets')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['code', $assetCode]
        ])
        ->update($obj);
    }

    /**
     * Delete data asset from DB.
     * @param assetId
     */
    public function delete($assetCode)
    {
        DB::table('assets')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['code', $assetCode]
        ])
        ->delete();
    }

    /**
     * @param string $code
     * @return
     */
    public function checkDuplicateAssetCode(string $code)
    {
        return DB::table('assets')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

    public function getTotalRows()
    {
        return
            DB::table('assets')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])->count();
    }

}
