<?php
namespace App\Business\Dao\Appraisal;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

class ReviewTemplateDao
{
    public function __construct(Requester $requester)
    {
        $this->connection = 'appraisal';
        $this->requester = $requester;
    }

    public function getAll($offset,$limit)
    {
        return
            DB::connection($this->connection)->table('review_templates')
                ->select(
                    'id',
                    'code',
                    'name',
                    'individual_goal as individualGoal',
                    'individual_competency as individualCompetency',
                    'rating_scale_code as ratingScaleCode',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
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

    public function getAllByInstructionCode($instructionCode)
    {
        return
            DB::connection($this->connection)->table('review_templates')
                ->select(
                    'id',
                    'code',
                    'name',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['instruction_code', $instructionCode]
                ])
                ->orderByRaw('id DESC')
                ->get();
    }

    public function getOne($reviewTemplateId)
    {
        return
            DB::connection($this->connection)->table('review_templates')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'rating_scale_code as ratingScaleCode',
                    'comment',
                    'comment_mandatory as commentMandatory',
                    'comment_item as commentItem',
                    'comment_item_mandatory as commentItemMandatory',
                    'available_for_all as availableForAll',
                    'instruction_code as instructionCode',
                    'individual_goal as individualGoal',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['id', $reviewTemplateId],
                ])
                ->first();
    }

    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();
        // Log::info(print_r($obj, true));
        return DB::connection($this->connection)->table('review_templates')->insertGetId($obj);
    }

    public function update($obj,$reviewTemplateId)
    {
        Log::info(print_r($obj, true));
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::connection($this->connection)->table('review_templates')
        ->where([
            ['id', $reviewTemplateId]
        ])
        ->update($obj);
    }

    public function checkDuplicateReviewTemplateCode(string $code)
    {
        return DB::connection($this->connection)->table('review_templates')->where([
            ['code', $code],
            ['tenant_id',$this->requester->getTenantId()],
            ['company_id',$this->requester->getCompanyId()]
        ])->count();
    }
}
