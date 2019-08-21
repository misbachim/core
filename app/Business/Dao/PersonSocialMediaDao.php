<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 */
class PersonSocialMediaDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all person socialMedia for one person
     * @param $personId
     */
    public function getAll($personId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();

        return
            DB::table('person_socmeds')
                ->select(
                    'person_socmeds.id',
                    'person_socmeds.lov_socm as lovSocm',
                    'social_medias.val_data as socialMedia',
                    'person_socmeds.account'
                )
                ->leftJoin('lovs as social_medias', function ($join) use($companyId, $tenantId)  {
                    $join->on('social_medias.key_data', '=', 'person_socmeds.lov_socm')
                        ->where([
                            ['social_medias.lov_type_code', 'SOCM'],
                            ['social_medias.tenant_id', $tenantId],
                            ['social_medias.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['person_socmeds.tenant_id', $tenantId],
                    ['person_socmeds.person_id', $personId]
                ])
                ->get();
    }

    /**
     * Get one person socialMedia based on personSocialMediaId
     * @param $personId
     * @param $personSocialMediaId
     * @return
     */
    public function getOne($personId, $personSocialMediaId)
    {
        return
            DB::table('person_socmeds')
                ->select(
                    'id',
                    'lov_socm as lovSocm',
                    'account'
                )
                ->where([
                    ['tenant_id', $this->requester->getTenantId()],
                    ['person_id', $personId],
                    ['id', $personSocialMediaId]
                ])
                ->first();
    }

    /**
     * Insert data person socialMedia to DB
     * @param  array $obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('person_socmeds')->insertGetId($obj);
    }

    /**
     * Update data person socialMedia to DB
     * @param $personId
     * @param $personSocialMediaId
     * @param $obj
     */
    public function update($personId, $personSocialMediaId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('person_socmeds')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['person_id', $personId],
                ['id', $personSocialMediaId]
            ])
            ->update($obj);
    }

    /**
     * Delete data person socialMedia from DB.
     * @param $personId
     * @param $personSocialMediaId
     */
    public function delete($personId, $personSocialMediaId)
    {
        DB::table('person_socmeds')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['person_id', $personId],
                ['id', $personSocialMediaId]
            ])
            ->delete();
    }

    public function deleteByPersonId($personId)
    {
        DB::table('person_socmeds')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['person_id', $personId]
            ])
            ->delete();
    }
}
