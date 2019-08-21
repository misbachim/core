<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PersonAssetReceiptDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all PersonInventories
     * @param  personId
     */
    public function getAll($personId)
    {
        return
            DB::table('person_asset_receipts')
                ->select(
                    'person_asset_receipts.id as id',
                    'person_asset_receipts.date as date',
                    'person_asset_receipts.file_receipt as fileReceipt',
                    'person_asset_receipts.receipt_number as receiptNumber',
                    'person_asset_receipts.type as type',
                    'person_assets.is_payroll_calculation as isCalculation'

                )
                ->join('person_assets', function ($join) {
                    $join
                        ->on('person_assets.get_receipt_id', '=','person_asset_receipts.id')
                        ->orOn('person_assets.return_receipt_id', '=', 'person_asset_receipts.id' )
                        ->where([
                            ['person_assets.tenant_id', $this->requester->getTenantId()],
                            ['person_assets.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['person_asset_receipts.tenant_id', $this->requester->getTenantId()],
                    ['person_asset_receipts.company_id', $this->requester->getCompanyId()],
                    ['person_asset_receipts.person_id', $personId]
                ])
                ->get();
    }


    /**
     * Get all PersonInventories
     * @param  id
     */
    public function getAssetNameByReceipt($id)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('person_asset_receipts')
                ->select(
                    'assets.name as assetName',
                    'assets.code as assetCode',
                    'return.is_lost as isLost',
                    'get.get_receipt_id as getReceiptId'
                )
                ->leftjoin('person_assets as get', function ($join) use($companyId, $tenantId)  {
                    $join->on('get.get_receipt_id', '=', 'person_asset_receipts.id')
                        ->where([
                            ['get.tenant_id', $tenantId],
                            ['get.company_id', $companyId]
                        ]);
                })
                ->leftjoin('person_assets as return', function ($join) use($companyId, $tenantId)  {
                    $join->on('return.return_receipt_id', '=', 'person_asset_receipts.id')
                        ->where([
                            ['return.tenant_id', $tenantId],
                            ['return.company_id', $companyId]
                        ]);
                })
                ->leftjoin('assets', function ($join) use($companyId, $tenantId)  {
                    $join
                        ->on('assets.code', '=', 'get.asset_code')
                        ->where([
                            ['assets.tenant_id', $tenantId],
                            ['assets.company_id', $companyId]
                        ])
                        ->orOn('assets.code', '=', 'return.asset_code')
                        ->where([
                            ['assets.tenant_id', $tenantId],
                            ['assets.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_asset_receipts.tenant_id', $tenantId],
                    ['person_asset_receipts.company_id', $companyId],
                    ['person_asset_receipts.id', $id    ]
                ])
                ->get();
    }

    /**
    * Get personAsset based on person id and personAsset id
    * @param personId, personAssetReceiptId
    */
    public function getOne($personId, $personAssetReceiptId)
    {
        return
            DB::table('person_asset_receipts')
                ->select(
                    'person_asset_receipts.id as id',
                    'person_asset_receipts.type as type',
                    'person_asset_receipts.date as date',
                    'person_asset_receipts.receipt_number as receiptNumber',
                    'person_asset_receipts.file_receipt as fileReceipt',
                    'person_assets.is_payroll_calculation as isCalculation'
                )
                ->join('person_assets', function ($join) {
                    $join
                        ->on('person_assets.get_receipt_id', '=','person_asset_receipts.id')
                        ->orOn('person_assets.return_receipt_id', '=', 'person_asset_receipts.id' )
                        ->where([
                            ['person_assets.tenant_id', $this->requester->getTenantId()],
                            ['person_assets.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['person_asset_receipts.tenant_id', $this->requester->getTenantId()],
                    ['person_asset_receipts.company_id', $this->requester->getCompanyId()],
                    ['person_asset_receipts.person_id', $personId],
                    ['person_asset_receipts.id', $personAssetReceiptId]
                ])
                ->first();
    }

    /**
     * Insert data PersonAsset to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_asset_receipts')->insertGetId($obj);
    }

    /**
     * Update data PersonAsset to DB
     * @param  personId, personAssetReceiptId, obj
     */
    public function update($personId, $personAssetReceiptId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_asset_receipts')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['person_id', $personId],
            ['id', $personAssetReceiptId]
        ])
        ->update($obj);
    }

    /**
     * Delete data PersonAsset from DB
     * @param  personAssetReceiptId
     */
    public function delete($personAssetReceiptId)
    {
        DB::table('person_asset_receipts')->where('id', $personAssetReceiptId)->delete();
    }
}
