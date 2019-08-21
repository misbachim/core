<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

class ItemBehaviourDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Item Behaviour
     */
    public function getAll($itemId)
    {
        return
            DB::table('item_behaviours')
                ->select(
                    'behaviour',
                    'item_id as itemId'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['item_id', $itemId]
                ])
                ->get();
    }

    /**
     * Insert data Item Behaviour to DB
     * @param  array obj
     */
    public function save($obj)
    {
        LogDao::insertLogImpact($this->requester->getLogId(), 'item_behaviours', $obj);

        DB::table('item_behaviours')-> insert($obj);
    }

    /**
     * Update data Item Behaviour to DB
     * @param  array obj, $itemId
     */
    public function delete($itemId)
    {
        DB::table('item_behaviours')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['item_id', $itemId]
            ])
            ->delete();
    }

}
