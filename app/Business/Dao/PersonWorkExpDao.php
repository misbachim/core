<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PersonWorkExpDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all person work exp for one person
     * @param  personId
     */
    public function getAll($personId)
    {
        $now = Carbon::now();
        return
            DB::table('person_work_exps')
                ->select(
                    'id',
                    'date_begin as dateBegin',
                    'date_end as dateEnd',
                    'company',
                    'job_pos as jobPos',
                    'job_desc as jobDesc',
                    'location',
                    'benefit',
                    'last_salary as lastSalary',
                    'reason'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['person_id', $personId]
                ])
                ->get();
    }

    /**
     * Get one person work exp based on workExpId
     * @param personId, workExpId
     */
    public function getOne($personId, $workExpId)
    {
        return
            DB::table('person_work_exps')
                ->select(
                    'id',
                    'date_begin as dateBegin',
                    'date_end as dateEnd',
                    'company',
                    'job_pos as jobPos',
                    'job_desc as jobDesc',
                    'location',
                    'benefit',
                    'last_salary as lastSalary',
                    'reason'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['person_id', $personId],
                    ['id', $workExpId]
                ])
                ->first();
    }

    /**
     * Insert data person work exp to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_work_exps')->insertGetId($obj);
    }

    /**
     * Update data person work exp to DB
     * @param  array obj, personId, workExpId
     */
    public function update($personId, $workExpId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_work_exps')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $workExpId]
        ])
        ->update($obj);
    }

    /**
     * Delete data person work exp from DB.
     * @param personId, workExpId
     */
    public function delete($personId, $workExpId)
    {
        DB::table('person_work_exps')
        ->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['person_id', $personId],
            ['id', $workExpId]
        ])
        ->delete();
    }
}
