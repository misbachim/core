<?php
namespace App\Business\Dao\Appraisal;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

class ReviewItemDao
{
    public function __construct(Requester $requester)
    {
        $this->connection = 'appraisal';
        $this->requester = $requester;
    }

    public function save($obj)
    {
        $data = array();
        foreach ($obj as $key => $value) {
            $value['created_by'] = $this->requester->getUserId();
            $value['created_at'] = Carbon::now();
            array_push($data,$value);
        }
        // return DB::table('review_items')->insert($data);
        return DB::connection($this->connection)->table('review_items')->insertGetId($obj);
    }

    public function update($reviewItemId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::connection($this->connection)->table('review_items')
        ->where([
            ['id', $reviewItemId]
        ])
        ->update($obj);
    }

    public function getReviewItemByTemplateId($reviewTemplateId){
        return
            DB::connection($this->connection)->table('review_items')
            ->select(
                'id',
                'item_description as itemDescription',
                'weight',
                'use_rating_review as useRatingReview',
                'use_view_in_self_assessment as useViewInSelfAssessment',
                'use_view_in_other_assessment as useViewInOtherAssessment',
                'use_hidden_from_employee as useHiddenFromEmployee',
                'review_template_id as reviewTemplateId'
            )
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['review_template_id', $reviewTemplateId],
            ])
            ->orderByRaw('id DESC')
            ->get();
    }

    public function deleteByReviewTemplateId($reviewTemplateId)
    {
        return DB::connection($this->connection)->table('review_items')->where('review_template_id', $reviewTemplateId)->delete();
    }
}
