<?php
namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;

class ReportTemplatesDao
{

    /**
     * Get all report_templates
     * @param  offset, limit
     */
    public function getAllByCategory($category,  $company, $tenant)
    {
        return
            DB::table('report_templates')
            ->select(
                'id',
                'filename',
                'name'
            )
            ->where([
                ['category', $category],
                ['tenant_id', $tenant],
                ['company_id', $company]
            ])
            ->get();
    }
    /**
     * Get one rating_scales based on rating_scales code
     * @param  ratingScaleId
     */
    public function getOne($id)
    {
        return
            DB::table('report_templates')
            ->select(
                'id',
                'filename',
                'name'
            )
            ->where([
                ['id', $id],
            ])
            ->first();
    }
}
