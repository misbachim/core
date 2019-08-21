<?php
namespace App\Business\Dao\Appraisal;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

class ReviewProcessTemplateDao
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
        
        return DB::connection($this->connection)->table('review_process_templates')->insert($data);
    }

    public function getAllByReviewFormReviewTemplateId($listOfReviewTemplate){
        return
            DB::connection($this->connection)->table('review_process_templates')
            ->join('review_template_to_forms', 'review_template_to_forms.id', '=', 'review_process_templates.review_form_review_template_id')
            ->join('review_templates', 'review_templates.code', '=', 'review_template_to_forms.review_template_code')
            ->select(
                    'review_process_templates.id',
                    'review_process_templates.review_process_id',
                    'review_process_templates.review_form_review_template_id',
                    'review_template_to_forms.review_form_code',
                    'review_templates.id as review_template_id'
                )
                ->whereIn('review_process_templates.review_process_id', $listOfReviewTemplate)
                ->where([
                    ['review_process_templates.tenant_id', $this->requester->getTenantId()],
                    ['review_process_templates.company_id', $this->requester->getCompanyId()]
                ])
                ->orderByRaw('review_process_templates.id DESC')
                ->get();
    }
}
