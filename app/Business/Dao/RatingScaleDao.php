<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RatingScaleDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all rating_scales
     * @param  offset, limit
     */
    public function getAll($offset, $limit)
    {
        return
            DB::table('rating_scales')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]
                ])
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all rating_scales
     * @param  offset, limit
     */
    public function getAllActive($offset, $limit)
    {
        return
            DB::table('rating_scales')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()]
                ])
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all rating_scales
     * @param  offset, limit
     */
    public function getAllInactive($offset, $limit)
    {
        return
            DB::table('rating_scales')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_end','<', Carbon::now()]
                ])
                ->orderByRaw('eff_end DESC')
                ->offset($offset)
                ->limit($limit)
                ->get();
    }

    /**
     * Get all rating_scales (lib+rating_scales) in ONE company
     * @param
     */
    public function getLov()
    {
        return
            DB::table('rating_scales')
                ->select(
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', '=', $this->requester->getTenantId()],
                    ['company_id', '=', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', Carbon::now()],
                    ['eff_end', '>=', Carbon::now()]
                ])
                ->orderByRaw('eff_end DESC')
                ->get();
    }

    /**
     * Get total data
     * @param
     */
    public function getTotalRow()
    {
        return DB::table('rating_scales')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])
            ->count();
    }

    /**
     * Get one rating_scales based on rating_scales code
     * @param  ratingScaleId
     */
    public function getOne($ratingScaleCode)
    {
        return
            DB::table('rating_scales')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $ratingScaleCode],
                ])
                ->orderBy('eff_end', 'DESC')
                ->first();
    }
    /**
     * Get all rating_scales based on rating_scales code
     * @param  $ratingScaleCode, $ratingScaleId
     */
    public function getHistory($ratingScaleCode, $ratingScaleId)
    {
        return
            DB::table('rating_scales')
                ->select(
                    'id',
                    'code',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $ratingScaleCode],
                    ['id', '!=', $ratingScaleId],
                ])
                ->orderBy('eff_end', 'DESC')
                ->get();
    }

    /**
     * Insert data rating_scales to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'rating_scales', $obj);

        return DB::table('rating_scales')->insertGetId($obj);
    }

    /**
     * Update data rating_scales to DB
     * @param  array obj, ratingScaleId
     */
    public function update($ratingScaleId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'rating_scales', $obj);

        DB::table('rating_scales')
        ->where([
            ['id', $ratingScaleId]
        ])
        ->update($obj);
    }

    /**
     * @param string $code
     * @return
     */
    public function checkDuplicateRatingScaleCode(string $code)
    {
        return DB::table('rating_scales')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }
}
