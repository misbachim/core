<?php
namespace App\Business\Dao\Appraisal;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Log;
use Carbon\Carbon;

class ReviewProcessDao
{
    public function __construct(Requester $requester)
    {
        $this->connection = 'appraisal';
        $this->requester = $requester;
    }

    public function saveReviewProcess($obj)
    {   
        // Log::info(print_r($obj, true));
        // $data = array();
        // foreach ($obj as $key => $value) {
        //     $value['created_by'] = $this->requester->getUserId();
        //     $value['created_at'] = Carbon::now();
        //     array_push($data,$value);
        // }
        
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::connection($this->connection)->table('review_process')->insertGetId($obj);
    }

    public function getAllByReviewRequest($reviewRequestId){
        return
            DB::connection($this->connection)
                ->table('review_process')
                ->select(
                    'id',
                    'review_request_id',
                    'appraise_id',
                    'appraiser_id'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['review_request_id', $reviewRequestId],
                ])
                ->orderByRaw('id DESC')
                ->get();
    }
}
