<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

class PotentialLevelDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
        $this->table = 'potential_levels';
    }

    /*
    |-----------------------------
    | get all data ke database
    |-----------------------------
    |
    |
    */
    public function getAllItem($companyId, $id) {
        return
        DB::table('potential_level_details')
            ->select(
                'id',
                'level',
                'description'
            )
        ->where([
            ['company_id', $companyId],
            ['potential_level_id', $id],
         ])
         ->get();
    }


    /*
    |-----------------------------
    | save data ke database
    |-----------------------------
    | @param $companyId <integer>
    |
    */
    public function getOne($companyId)
    {
        $tenantId = $this->requester->getTenantId();
        return
        DB::table($this->table)
        ->select(
            'id',
            'level as potentialLevel'
        )
        ->where([
            ['tenant_id', $tenantId],
            ['company_id', $companyId]
        ])
        ->first();
    }



    /*
    |----------------------------------
    | save data ke database dan get id
    |-----------------------------------
    |
    | @param obj dari controller
    */
    public function saveGetId($obj) {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();
        return DB::table($this->table)->insertGetId($obj);
    }


    /*
    |-----------------------------
    | update data ke database
    |-----------------------------
    |
    |
    */
    public function update($obj, $id) {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();
        DB::table($this->table)->where([['id', $id],])->update($obj);
    }


    /*
    |-----------------------------
    | save data ke database
    |-----------------------------
    | @param array object dari controller
    |
    */
    public function saveItems($obj) {
        DB::table('potential_level_details')->insert($obj);
    }

    /*
    |-----------------------------
    | delete data di database
    |-----------------------------
    |
    |
    */
    public function deleteItems($id) {
        DB::table('potential_level_details')
            ->where([
                ['potential_level_id', $id],
             ])
            ->delete();
    }


}
