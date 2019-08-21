<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

class LookupDetailDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }
    /**
     * Get all lookup detail
     * @param  $lookupId
     */
    public function getAll($lookupId)
    {
        return
            DB::table('lookup_details')
                ->select(
                    'look_1_code as look1code',
                    'look_2_code as look2code',
                    'look_3_code as look3code',
                    'look_4_code as look4code',
                    'look_5_code as look5code',
                    'amount'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['lookup_id', $lookupId]
                ])
                ->get();
    }

    /**
     * Insert data lookup detail to DB
     * @param  array $obj
     */
    public function save($obj)
    {
        LogDao::insertLogImpact($this->requester->getLogId(), 'lookup_details', $obj);

        DB::table('lookup_details')-> insert($obj);
    }

    /**
     * Delete data lookup detail from DB
     * @param $lookupId
     */
    public function delete($lookupId)
    {
        DB::table('lookup_details')
            ->where('lookup_id', $lookupId)
            ->delete();
    }
}
