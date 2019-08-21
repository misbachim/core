<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

class ItemProficiencyDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Item Proficiency
     */
    public function getAll($itemId)
    {
        return
            DB::table('item_proficiencies')
                ->select(
                    'item_proficiencies.rating_scale_detail_id as ratingScaleDetailId',
                    'rating_scale_details.label as label',
                    'rating_scale_details.level as level',
                    'item_proficiencies.proficiency',
                    'item_proficiencies.type_item as typeItem',
                    'item_proficiencies.item_id as itemId'
                )
                ->join('rating_scale_details', function($j) {
                        $j->on('rating_scale_details.id','item_proficiencies.rating_scale_detail_id')
                        ->where([
                            ['rating_scale_details.tenant_id', $this->requester->getTenantId()],
                            ['rating_scale_details.company_id', $this->requester->getCompanyId()]
                        ]);
                    })
                ->where([
                    ['item_proficiencies.tenant_id', $this->requester->getTenantId()],
                    ['item_proficiencies.company_id', $this->requester->getCompanyId()],
                    ['item_proficiencies.item_id', $itemId]
                ])
                ->get();
    }

    public function getAllByRatingScaleDetailId($ratingScaleDetailId)
    {
        return
            DB::table('item_proficiencies')
                ->select(
                    'item_proficiencies.rating_scale_detail_id as ratingScaleDetailId',
                    'item_proficiencies.proficiency',
                    'item_proficiencies.type_item as typeItem',
                    'item_proficiencies.item_id as itemId'
                )
                ->where([
                    ['item_proficiencies.tenant_id', $this->requester->getTenantId()],
                    ['item_proficiencies.company_id', $this->requester->getCompanyId()],
                    ['item_proficiencies.rating_scale_detail_id', $ratingScaleDetailId]
                ])
                ->get();
    }

    /**
     * Insert data Item Proficiency to DB
     * @param  array obj
     */
    public function save($obj)
    {
        LogDao::insertLogImpact($this->requester->getLogId(), 'item_proficiencies', $obj);

        DB::table('item_proficiencies')-> insert($obj);
    }

    /**
     * Update data Item Proficiency to DB
     * @param  array obj, $itemId
     */
    public function delete($itemId)
    {
        DB::table('item_proficiencies')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['item_id', $itemId]
            ])
            ->delete();
    }

}
