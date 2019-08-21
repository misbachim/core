<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class PosStructureDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all pos structures in One Company
     */
    public function getAll()
    {
        return
            DB::table('pos_structures')
                ->select(
                    'id',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'is_primary as isPrimary'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * Get one pos structures based on id
     * @param  posStructureId
     */
    public function getOne($posStructureId)
    {
        return
            DB::table('pos_structures')
                ->select(
                    'id',
                    'name',
                    'description',
                    'eff_begin as effBegin',
                    'eff_end as effEnd',
                    'is_primary as isPrimary'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['id', $posStructureId]
                ])
                ->first();
    }

    /**
     * Insert data pos structure to DB
     * @param  obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('pos_structures')-> insertGetId($obj);
    }

    /**
     * Update data org structure to DB
     * @param  id, obj
     */
    public function update($id, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('pos_structures')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $id]
            ])
            ->update($obj);
    }

    public function delete($id)
    {
        DB::table('pos_structures')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $id]
            ])
            ->delete();
    }

    public function updateAll($obj)
    {
        DB::table('pos_structures')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])
            ->update($obj);
    }

    public function search($query, $offset, $limit)
    {
        $now = Carbon::now();
        $searchString = strtolower("%$query%");
        return
            DB::table('pos_structures')
                ->select('id', 'name')
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['eff_begin', '<=', $now],
                    ['eff_end', '>=', $now]
                ])
                ->whereRaw('LOWER(name) like ?', [$searchString])
                ->offset($offset)
                ->limit($limit)
                ->get();
    }
}
