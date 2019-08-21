<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

class RatingScaleDetailDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all rating_scale_details by rating scale code
     * @param  ratingScaleId
     */
    public function getAllByRatingScale($ratingScaleId)
    {
        return
            DB::table('rating_scale_details')
                ->select(
                    'id',
                    'rating_scale_id as ratingScaleId',
                    'level',
                    'label'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['rating_scale_id', $ratingScaleId]
                ])
                ->orderByRaw('level ASC')
                ->get();
    }

    /**
     * Get one rating_scale_details based on rating_scale_details id
     * @param  ratingScaleDetailId
     */
    public function getOne($ratingScaleDetailId)
    {
        return
            DB::table('rating_scale_details')
                ->select(
                    'id',
                    'rating_scale_id as ratingScaleId',
                    'level',
                    'label')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['id', $ratingScaleDetailId],
                ])
                ->first();
    }

    /**
     * Insert data rating_scale_details to DB
     * @param  array $obj
     */
    public function save($obj)
    {
        LogDao::insertLogImpact($this->requester->getLogId(), 'rating_scale_details', $obj);

        DB::table('rating_scale_details')-> insert($obj);
    }

    /**
     * Delete data rating_scale_details to DB
     * @param  $ratingScaleId
     */
    public function delete($ratingScaleId)
    {
        DB::table('rating_scale_details')
            ->where('rating_scale_id', $ratingScaleId)
            ->delete();
    }

    /**
     * Get Number of Levels by Id
     * @param
     */
    public function getNumberOfLevels($ratingScaleId)
    {
        return DB::table('rating_scale_details')
            ->where([
                ['rating_scale_id', $ratingScaleId],
            ])
            ->count();
    }

    /**
     * @description get rating scale detail by rating scale code
     */
    public function getRatingLevelByCode($code)
    {
        Log::info($code);
        return DB::table('rating_scales')
                ->select(
                    'rating_scale_details.level',
                    'rating_scale_details.label',
                    'rating_scale_details.id'
                )
                ->join('rating_scale_details', function($j) {
                    $j->on('rating_scales.id', 'rating_scale_details.rating_scale_id')
                    ->where([
                        ['rating_scale_details.tenant_id', $this->requester->getTenantId()],
                        ['rating_scale_details.company_id', $this->requester->getCompanyId()]
                    ]);
                })
                ->where([
                    ['rating_scales.tenant_id', $this->requester->getTenantId()],
                    ['rating_scales.company_id', $this->requester->getCompanyId()],
                    ['rating_scales.code', $code],
                    ['rating_scales.eff_end', '>', Carbon::now()]
                ])
                ->orderBy('level')
                ->get();
    }
}
