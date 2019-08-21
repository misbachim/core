<?php
namespace App\Business\Dao\Appraisal;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

class ReviewFormDao
{
    public function __construct(Requester $requester)
    {
        $this->connection = 'appraisal';
        $this->requester = $requester;
    }

    public function getAll($offset,$limit)
    {
        return
            DB::connection($this->connection)->table('review_forms')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'review_forms.eff_begin as effBegin',
                    'review_forms.eff_end as effEnd',
                    'rating_scale_code as ratingScaleCode',
                    'allow_user_to_archived as allowUserToArchived',
                    'status'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]
                ])
                ->orderByRaw('id DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    public function getTotalSummaryStatus()
    {
        $q = DB::connection($this->connection)->table('review_forms')
        ->select(
            DB::raw('status, count(*) as count')
        )
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()]
        ])
        ->groupBy('status')
        ->get();
        
        $listStatus = ['NEW', 'PROGRESS', 'LOCK','ARCHIVED'];
        $data = array();
        $result = array();
        foreach ($listStatus as $key => $valueListStatus) {
            $result['status'] = $valueListStatus;
            foreach ($q as $key => $valueQ) {
                if($valueQ->status == $valueListStatus){
                    $result['count'] = $valueQ->count;
                    break;
                }else{
                    $result['count'] = 0;  
                }
            }
            array_push($data, $result);
        }
        return $data;
    }

    public function getAllCountStatus($offset,$limit){
        return
            DB::connection($this->connection)->table('review_forms')
                ->select(
                    DB::raw('count(*) as count, status')
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]
                ])
                ->groupBy('status')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    public function getOne($reviewFormId)
    {
        return
            DB::connection($this->connection)->table('review_forms')
            ->select(
                    'review_forms.id',
                    'review_forms.code',
                    'review_forms.name',
                    'review_forms.description',
                    'review_forms.eff_begin as effBegin',
                    'review_forms.eff_end as effEnd',
                    'review_forms.status',
                    'review_forms.comment',
                    'review_forms.comment_mandatory as commentMandatory',
                    'review_forms.auto_calculated as autoCalculated',
                    'review_forms.allow_user_to_archived as allowUserToArchived',
                    'workflow_appraisal_code as workflowAppraisalCode',
                    'rating_scale_code as ratingScaleCode'
                )
                ->where([
                    ['review_forms.tenant_id', $this->requester->getTenantId()],
                    ['review_forms.company_id', $this->requester->getCompanyId()],
                    ['review_forms.id', $reviewFormId],
                ])
                ->first();
    }

    public function getListOfReviewTemplateByReviewFormId($reviewFormId,$offset,$limit)
    {
        return
            DB::connection($this->connection)->table('review_template_to_forms')
                ->join('review_forms', 'review_forms.code', '=', 'review_template_to_forms.review_form_code')
                ->join('review_templates', 'review_templates.code', '=', 'review_template_to_forms.review_template_code')
                ->select(
                    'review_templates.id as id',
                    'review_templates.code',
                    'review_templates.name',
                    'review_templates.description',
                    'review_templates.rating_scale_code as ratingScaleCode',
                    'review_templates.comment',
                    'review_templates.item_input_by as itemInputBy',
                    'review_templates.combination_available_for as combinationAvailableFor',
                    'review_template_to_forms.weight as weight',
                    'review_template_to_forms.id as ReviewTemplateByReviewFormId'
                )
                ->where([
                    ['review_forms.tenant_id', $this->requester->getTenantId()],
                    ['review_forms.company_id', $this->requester->getCompanyId()],
                    ['review_forms.id', $reviewFormId],
                ])
                ->orderByRaw('review_templates.id DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    public function getListOfEmployeeByReviewFormId($reviewFormId,$offset,$limit)
    {
        return
            DB::connection($this->connection)->table('review_process')
                ->join('review_requests', 'review_requests.id', '=', 'review_process.review_request_id')
                ->join('review_forms', 'review_forms.id', '=', 'review_requests.review_form_id')
                ->select(
                    'review_process.appraise_id as appraiseId',
                    'review_process.status_assessment as statusAssessment'
                )
                ->where([
                    ['review_forms.tenant_id', $this->requester->getTenantId()],
                    ['review_forms.company_id', $this->requester->getCompanyId()],
                    ['review_forms.id', $reviewFormId]
                ])
                ->groupBy('appraiseId', 'statusAssessment')
                ->orderByRaw('review_process.appraise_id ASC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();
        $obj['status'] = 'NEW';

        return DB::connection($this->connection)->table('review_forms')->insertGetId($obj);
    }

    public function saveReviewTemplateToForms($obj)
    {   
        $data = array();
        foreach ($obj as $key => $value) {
            $value['created_by'] = $this->requester->getUserId();
            $value['created_at'] = Carbon::now();
            array_push($data,$value);
        }
        
        return DB::connection($this->connection)->table('review_template_to_forms')->insert($data);
    }

    public function update($obj, $reviewFormId)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::connection($this->connection)->table('review_forms')
        ->where([
            ['id', $reviewFormId]
        ])
        ->update($obj);
    }

    public function deleteListOfReviewTemplate($reviewFormCode)
    {
        DB::connection($this->connection)->table('review_template_to_forms')
        ->where([
            ['review_form_code', $reviewFormCode]
        ])
        ->delete();
    }

    public function checkDuplicateReviewFormCode(string $code)
    {
        return DB::connection($this->connection)->table('review_forms')->where([
            ['code', $code],
            ['tenant_id',$this->requester->getTenantId()],
            ['company_id',$this->requester->getCompanyId()]
        ])->count();
    }
}
