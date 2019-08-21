<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DutiesDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }
    /**
     * Get all duties
     * @param  $responsibilityCode
     */
    public function getAll($responsibilityCode)
    {
        return
            DB::table('duties')
                ->select(
                    'id',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['responsibility_code', $responsibilityCode]
                ])
                ->get();
    }

    /**
     * Insert data duties to DB
     * @param  array $obj
     */
    public function save($obj)
    {
        return DB::table('duties')-> insert($obj);
    }

    /**
     * Delete data duties from DB
     * @param $lookupId
     */
    public function delete($responsibilityCode)
    {
        DB::table('duties')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['responsibility_code', $responsibilityCode]
            ])
            ->delete();
    }
}
