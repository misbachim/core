<?php
namespace App\Business\Dao\Appraisal;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

class ReviewTemplateAvailableForDao
{
    public function __construct(Requester $requester)
    {
        $this->connection = 'appraisal';
        $this->requester = $requester;
    }

    public function saveReviewProcess($obj)
    {   
        $data = array();
        foreach ($obj as $key => $value) {
            $value['created_by'] = $this->requester->getUserId();
            $value['created_at'] = Carbon::now();
            array_push($data,$value);
        }
        
        return DB::connection($this->connection)->table('review_process')->insert($data);
    }

    public function getAllByTypeAndTemplateReviewId($type, $reviewTemplateId)
    {
        return
            DB::connection($this->connection)->table('review_template_available_for')
                ->select(
                    'id',
                    'input_type as inputType',
                    'input_id_or_code as inputIdOrCode',
                    'review_template_id as reviewTemplate'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['input_type', $type],
                    ['review_template_id', $reviewTemplateId]
                ])
                ->orderByRaw('id DESC')
                ->get();
    }

    public function getAllAligibilityByTemplateReviewId($reviewTemplateId)
    {
        return
            DB::connection($this->connection)->table('review_template_available_for')
                ->select(
                    'id',
                    'input_type as inputType',
                    'input_id_or_code as inputIdOrCode',
                    'review_template_id as reviewTemplate'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['review_template_id', $reviewTemplateId]
                ])
                ->orderByRaw('id DESC')
                ->get();
    }

    public function save($obj)
    {

        $data = array();
        foreach ($obj as $key => $value) {
            $value['created_by'] = $this->requester->getUserId();
            $value['created_at'] = Carbon::now();
            array_push($data,$value);
        }
        // Log::info(print_r($obj, true));
        return DB::connection($this->connection)->table('review_template_available_for')->insert($data);
    }

    public function deleteByReviewTemplateId($reviewTemplateId)
    {
        return DB::connection($this->connection)->table('review_template_available_for')->where('review_template_id', $reviewTemplateId)->delete();
    }
}
