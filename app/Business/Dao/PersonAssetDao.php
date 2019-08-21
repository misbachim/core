<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PersonAssetDao
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
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('person_assets')
                ->select(
                    'person_assets.id',
                    'asset_code as assetCode',
                    'assets.name as assetName',
                    'assets.price as assetPrice',
                    'is_lost as isLost',
                    'person_assets.get_receipt_id as getReceiptId',
                    'get_receipt.date as receiveReceiptDate',
                    'person_assets.return_receipt_id as returnReceiptId',
                    'return_receipt.date as returnReceiptDate',
                    'person_assets.end_date as endDate'

                )
                ->join('assets', function ($join) use($companyId, $tenantId)  {
                    $join
                        ->on('assets.code', '=', 'person_assets.asset_code')
                        ->where([
                            ['assets.tenant_id', $tenantId],
                            ['assets.company_id', $companyId]
                        ]);
                })
                ->leftjoin('person_asset_receipts as get_receipt', function ($join) use($companyId, $tenantId)  {
                    $join
                        ->on('get_receipt.id', '=', 'person_assets.get_receipt_id')
                        ->where([
                            ['get_receipt.tenant_id', $tenantId],
                            ['get_receipt.company_id', $companyId]
                        ]);
                })
                ->leftjoin('person_asset_receipts as return_receipt', function ($join) use($companyId, $tenantId)  {
                    $join
                        ->on('return_receipt.id', '=', 'person_assets.return_receipt_id')
                        ->where([
                            ['return_receipt.tenant_id', $tenantId],
                            ['return_receipt.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_assets.tenant_id', $tenantId],
                    ['person_assets.company_id', $companyId],
                    ['person_assets.person_id', $personId]
                ])
                ->get();
    }

    public function getAllNearEndDate()
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('person_assets')
                ->select(
                    'assignments.employee_id as employeeId',
                    'persons.first_name as firstName',
                    'persons.last_name as lastName',
                    'assets.name as assetName',
                    'assets.price as assetPrice',
                    'person_assets.end_date as endDate'
                )
                ->join('assets', function ($join) use($companyId, $tenantId)  {
                    $join
                        ->on('assets.code', '=', 'person_assets.asset_code')
                        ->where([
                            ['assets.tenant_id', $tenantId],
                            ['assets.company_id', $companyId]
                        ]);
                })
                ->join('persons as persons', function ($join) use($companyId, $tenantId)  {
                    $join
                        ->on('persons.id', '=', 'person_assets.person_id')
                        ->where([
                            ['assets.tenant_id', $tenantId],
                            ['assets.company_id', $companyId]
                        ]);
                })
                ->leftjoin('assignments as assignments', function ($join) use($companyId, $tenantId)  {
                    $join
                        ->on('assignments.person_id', '=', 'person_assets.person_id')
                        ->where([
                            ['assignments.tenant_id', $tenantId],
                            ['assignments.company_id', $companyId],
                        ]);
                })
                ->where([
                    ['person_assets.tenant_id', $tenantId],
                    ['person_assets.company_id', $companyId],
                    ['person_assets.return_receipt_id', '=', null]
                ])
                ->whereRaw("(DATE_PART('year', person_assets.end_date::date) - DATE_PART('year', current_date::date)) * 12 +
                (DATE_PART('month', person_assets.end_date::date) - DATE_PART('month', current_date::date)) <= 3")
                ->groupBy('assignments.employee_id',
                    'persons.first_name',
                    'persons.last_name',
                    'assets.name',
                    'assets.price',
                    'person_assets.end_date')
                ->orderBy('person_assets.end_date', 'asc')
                ->get();
    }

    public function getAllNotReturned($personId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('person_assets')
                ->select(
                    'person_assets.id',
                    'person_assets.get_receipt_id as getReceiptId',
                    'asset_code as assetCode',
                    'assets.name as assetName',
                    'is_lost as isLost',
                    'return_receipt.date as returnReceiptDate'
                )
                ->join('assets', function ($join) use($companyId, $tenantId)  {
                    $join
                        ->on('assets.code', '=', 'person_assets.asset_code')
                        ->where([
                            ['assets.tenant_id', $tenantId],
                            ['assets.company_id', $companyId]
                        ]);
                })
                ->leftjoin('person_asset_receipts as return_receipt', function ($join) use($companyId, $tenantId)  {
                    $join
                        ->on('return_receipt.id', '=', 'person_assets.return_receipt_id')
                        ->where([
                            ['return_receipt.tenant_id', $tenantId],
                            ['return_receipt.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_assets.tenant_id', $tenantId],
                    ['person_assets.company_id',  $companyId],
                    ['return_receipt.date', null],
                    ['person_assets.person_id', $personId]
                ])
                ->get();
    }

    /**
    * Get personAsset based on person id and personAsset id
    * @param personId, personAssetId
    */
    public function getOne($personId, $personAssetId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('person_assets')
                ->select(
                    'person_assets.id',
                    'person_assets.get_receipt_id',
                    'person_assets.return_receipt_id',
                    'asset_code as assetCode',
                    'assets.name as assetName',
                    'is_lost as isLost',
                    'person_asset_receipts.date as receiptDate',
                    'person_asset_receipts.file_receipt as fileReceipt',
                    'person_asset_receipts.receipt_number as receiptNumber',
                    'person_asset_receipts.id as receiptId',
                    'person_asset_receipts.type as type'
                )
                ->join('assets', function ($join) use($companyId, $tenantId)  {
                    $join
                        ->on('assets.code', '=', 'person_assets.asset_code')
                        ->where([
                            ['assets.tenant_id', $tenantId],
                            ['assets.company_id', $companyId]
                        ]);
                })
                ->join('person_asset_receipts', function ($join) {
                    $join
                        ->on('person_asset_receipts.id', '=', 'person_assets.get_receipt_id')
                        ->orOn('person_asset_receipts.id', '=', 'person_assets.return_receipt_id')
                        ->where([
                            ['person_asset_receipts.tenant_id', $tenantId],
                            ['person_asset_receipts.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_assets.tenant_id', $tenantId],
                    ['person_assets.company_id', $companyId],
                    ['person_assets.person_id', $personId],
                    ['person_assets.id', $personAssetId]
                ])
                ->first();
    }


    public function getReturnReceipt($personAssetReceiptId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('person_assets')
                ->select(
                    'person_assets.return_receipt_id'
                )
                ->join('person_asset_receipts', function ($join) use($companyId, $tenantId) {
                    $join
                        ->On('person_asset_receipts.id', '=', 'person_assets.return_receipt_id')
                        ->where([
                            ['person_asset_receipts.tenant_id', $tenantId],
                            ['person_asset_receipts.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_assets.tenant_id', $tenantId],
                    ['person_assets.company_id',  $companyId],
                    ['person_assets.get_receipt_id', $personAssetReceiptId]
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

        return DB::table('person_assets')->insertGetId($obj);
    }

    /**
     * Update data PersonAsset to DB
     * @param personAssetId, obj
     */
    public function updateReceive($companyId, $personAssetId,$getReceiptId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_assets')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $companyId],
            ['id',$personAssetId],
            ['get_receipt_id', $getReceiptId]
        ])
        ->update($obj);
    }

    /**
     * Update data PersonAsset to DB
     * @param personAssetId, obj
     */
    public function updateReturn($personAssetId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_assets')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['return_receipt_id', $personAssetId]
            ])
            ->update($obj);
    }

    /**
     * Delete data PersonAsset from DB
     * @param  personAssetId
     */
    public function deleteReceive($personAssetReceiptId)
    {
        DB::table('person_assets')
            ->where('get_receipt_id', $personAssetReceiptId)
            ->delete();
    }

    /**
     * Delete data PersonAsset from DB
     * @param  personAssetId
     */
    public function deleteReturn($personAssetReceiptId)
    {
        DB::table('person_assets')
            ->where('return_receipt_id', $personAssetReceiptId)
            ->delete();
    }
}
