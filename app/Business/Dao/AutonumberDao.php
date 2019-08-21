<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutonumberDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all autonumber
     * @param  offset, limit
     */
    public function getAll()
    {
        return
            DB::table('autonumbers')
                ->select(
                    'id',
                    'name',
                    'starting_sequence as startingSequence',
                    'last_sequence as lastSequence'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * Get all countries (lib+countries) in ONE company
     * @param
     */
    public function getLov()
    {
        return
            DB::table('autonumbers')
                ->select(
                    'id',
                    'name'
                )
                ->where([
                    ['tenant_id', '=', $this->requester->getTenantId()],
                    ['company_id', '=', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * @param
     * @return
     */
    public function getTotalRow()
    {
        return DB::table('autonumbers')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])
            ->count();
    }

    /**
     * Get one autonumber based on country id
     * @param  $autonumberId
     */
    public function getOne($autonumberId)
    {
        return
            DB::table('autonumbers')
                ->select(
                    'id',
                    'name',
                    'starting_sequence as startingSequence',
                    'last_sequence as lastSequence'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['id', $autonumberId],
                ])
                ->first();
    }

    /**
     * Insert data autonumber to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'autonumbers', $obj);

        return DB::table('autonumbers')->insertGetId($obj);
    }

    /**
     * Update data autonumber to DB
     * @param  array obj, $autonumberId
     */
    public function update($autonumberId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'autonumbers', $obj);

        DB::table('autonumbers')
            ->where([
                ['id', $autonumberId]
            ])
            ->update($obj);
    }

    /**
     * Delete data country from DB
     * @param  $autonumberId
     */
    public function delete($autonumberId)
    {
        DB::table('autonumbers')->where('id', $autonumberId)->delete();
    }

    /**
     * @param string $name
     * @return
     */
    public function checkDuplicateAutonumberName(string $name)
    {
        return DB::table('autonumbers')->where([
            ['name', $name],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }


    /**
     * @param string $code
     * @param $id if update data, then check duplicate code beside current user id
     * @return
     */
    public function checkDuplicateEditAutonumberName(string $name,$id)
    {
        $result = DB::table('autonumbers')->where([
            ['name', $name],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ]);

        if (!is_null($id)) {
            $result->where('id', '!=', $id);
        }

        return $result->count();
    }


    public function checkMaxStartingNumber(string $start, $id)
    {
        $result = DB::table('autonumbers')->where([
            ['last_sequence','>' , $start],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ]);

        if (!is_null($id)) {
            $result->where('id', '=', $id);
        }

        return $result->count();
    }

    /**
     * Check usage. If result > 0 it means there is a dependency.
     * @param int $autonumberId
     * @return mixed
     */
//    public function getTotalUsage(int $autonumberId)
//    {
//        return DB::table('autonumbers')
//            ->join('provinces', function ($join) {
//                $join
//                    ->on('countries.id', '=', 'provinces.country_id');
//            })
//            ->where('countries.id', $autonumberId)
//            ->where('countries.tenant_id', $this->requester->getTenantId())
//            ->count();
//    }

}
