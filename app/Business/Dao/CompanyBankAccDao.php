<?php
namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;

class CompanyBankAccDao
{
    /**
     * Get all bank account in one company
     * @param  companyId
     */
    public function getAll($companyId)
    {
        return
            DB::table('company_bank_accs')
                ->select(
                    'company_id as companyId',
                    'bank_id as bankId',
                    'acc_number as accNumber',
                    'acc_name as accName'
                )
                ->where([
                    ['company_id', $companyId]
                ])
                ->get();
    }

    /**
     * Insert data bank account to DB
     * @param  array obj
     */
    public function save($obj)
    {
        DB::table('company_bank_accs')->insert($obj);
    }

    /**
     * Update data bank account to DB
     * @param  array obj, bankId
     */
    public function update($bankId, $obj)
    {
        DB::table('company_bank_accs')
        ->where([
            ['bank_id', $bankId],
        ])
        ->update($obj);
    }

    /**
     * Delete all data bank account in one company
     * @param  companyId
     */
    public function delete($companyId)
    {
        DB::table('company_bank_accs')
            ->where('company_id', $companyId)->delete();
    }
}
