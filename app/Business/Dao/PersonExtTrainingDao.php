<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PersonExtTrainingDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all PersonExtTrainings
     * @param  tenantId, personId
     */
    public function getAll($tenantId, $personId)
    {
        return
            DB::table('person_ext_training')
                ->select(
                    'id',
                    'institution',
                    'year_begin as yearBegin',
                    'year_end as yearEnd',
                    'description',
                    'file_certificate as fileCertificate'
                )
                ->where([
                    ['tenant_id', $tenantId],
                    ['person_id', $personId]
                ])
                ->get();
    }

    /**
    * Get personExtTraining based on person id and personExtTraining id
    * @param  tenantId, personId, personExtTrainingId
    */
    public function getOne($tenantId, $personId, $personExtTrainingId)
    {
        return
            DB::table('person_ext_training')
                ->select(
                    'id',
                    'institution',
                    'year_begin as yearBegin',
                    'year_end as yearEnd',
                    'description',
                    'file_certificate as fileCertificate'
                )
                ->where([
                    ['tenant_id', $tenantId],
                    ['person_id', $personId],
                    ['id', $personExtTrainingId]
                ])
                ->first();
    }

    /**
     * Insert data PersonExtTraining to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_ext_training')->insertGetId($obj);
    }

    /**
     * Update data PersonExtTraining to DB
     * @param  tenantId, personId, personExtTrainingId, obj
     */
    public function update($tenantId, $personId, $personExtTrainingId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_ext_training')
        ->where([
            ['tenant_id', $tenantId],
            ['person_id', $personId],
            ['id', $personExtTrainingId]
        ])
        ->update($obj);
    }

    /**
     * Delete data PersonExtTraining from DB
     * @param  personExtTrainingId
     */
    public function delete($personExtTrainingId)
    {
        DB::table('person_ext_training')->where('id', $personExtTrainingId)->delete();
    }
}
