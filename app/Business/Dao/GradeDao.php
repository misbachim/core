<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GradeDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all grade in ONE company
     * @param companyId
     */
    public function getAll()
    {
        // $now = Carbon::now();
        return
            DB::table('grades')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name',
                    'ordinal',
                    'bottom_rate as bottomRate',
                    'mid_rate as midRate',
                    'top_rate as topRate'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * Get all Active Grade in ONE company
     */
    public function getAllActive($offset = null, $limit = null)
    {
        return
            DB::table('grades')
            ->select(
                'id',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'code',
                'name',
                'ordinal',
                'bottom_rate as bottomRate',
                'mid_rate as midRate',
                'top_rate as topRate'
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
     * Get all InActive Grade in ONE company
     */
    public function getAllInActive()
    {
        return
            DB::table('grades')
            ->select(
                'id',
                'eff_begin as effBegin',
                'eff_end as effEnd',
                'code',
                'name',
                'ordinal',
                'bottom_rate as bottomRate',
                'mid_rate as midRate',
                'top_rate as topRate'
            )
            ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_end', '<', Carbon::now()]
                ])
            ->get();
    }

    


    /**
     * Get all grade in ONE company
     * @param
     */
    public function getLov()
    {
        $now = Carbon::now();
        return
            DB::table('grades')
                ->select(
                    'code',
                    'name'
                )
                ->where([
                    ['tenant_id', '=', $this->requester->getTenantId()],
                    ['company_id', '=', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', $now],
                    ['eff_end', '>=', $now]
                ])
                ->get();
    }

    /**
     * Get one grade based on grade id
     * @param gradeId
     */
    public function getOne($gradeId)
    {
        return
            DB::table('grades')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name',
                    'ordinal',
                    'work_month as workMonth',
                    'bottom_rate as bottomRate',
                    'mid_rate as midRate',
                    'top_rate as topRate'
                )
                ->selectRaw('(bottom_rate+top_rate)/2 as "midRate"')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['id', $gradeId]
                ])
                ->first();
    }

    /**
     * Get one grade based on grade code
     * @param gradeId
     */
    public function getOneByCode($gradeCode)
    {
        return
            DB::table('grades')
                ->select(
                    'id',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'code',
                    'name',
                    'ordinal',
                    'work_month as workMonth',
                    'bottom_rate as bottomRate',
                    'mid_rate as midRate',
                    'top_rate as topRate'
                )
                ->selectRaw('(bottom_rate+top_rate)/2 as "midRate"')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['code', $gradeCode]
                ])
                ->first();
    }

    /**
     * Insert data grade to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'grades', $obj);

        return DB::table('grades')->insertGetId($obj);
    }

    /**
     * Update data grade to DB
     * @param  array obj, gradeId
     */
    public function update($gradeId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'grades', $obj);

        DB::table('grades')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['company_id', $this->requester->getCompanyId()],
            ['id', $gradeId]
        ])
        ->update($obj);
    }

    /**
     * @param string code
     * @return
     */
    public function checkDuplicateGradeCode(string $code)
    {
        return DB::table('grades')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }

    /**
     * @param string $code
     * @param code if update data, then check duplicate code beside current grade code
     * @return
     */
    public function checkDuplicateEditGradeCode(string $code)
    {
        $result = DB::table('grades')->where([
            ['code', $code],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ]);

        if (!is_null($code)) {
            $result->where('code', '!=', $code);
        }

        return $result->count();
    }

    public function getTotalRows()
    {
        return
            DB::table('grades')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])->count();
    }
}
