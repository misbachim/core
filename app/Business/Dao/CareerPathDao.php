<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

class CareerPathDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
        $this->table = 'career_paths';
    }

    /*
    |-----------------------------
    | get all data ke database
    |-----------------------------
    |
    |
    */
    public function getAll() {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
        DB::table($this->table)
            ->select(
                'id',
                'name',
                'description'
            )
        ->where([
            ['company_id', $companyId],
            ['tenant_id', $tenantId],
         ])
         ->orderBy('id','DESC')
         ->get();
    }


    /*
    |-----------------------------
    | save data ke database
    |-----------------------------
    | @param $companyId <integer>
    |
    */
    public function getOne($id)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
        DB::table($this->table)
        ->select(
            'id',
            'name',
            'description'
        )
        ->where([
            ['tenant_id', $tenantId],
            ['company_id', $companyId],
            ['id', $id]
        ])
        ->first();
    }


    /*
    |-----------------------------
    | get all data ke database
    |-----------------------------
    |
    |
    */
    public function getHierarchies($id) {
        Log::info('DATA NEXT REVIEWER' . json_encode($id, JSON_PRETTY_PRINT));
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
        DB::table('career_path_hierarchies')
            ->select(
                'career_path_hierarchies.id',
                'career_path_hierarchies.level',
                'career_path_hierarchies.position_code as positionCode',
                'career_path_hierarchies.parent_position_code as parentCode',
                'positions.name as positionName'
            )
        ->join('positions', function($join) use ($companyId, $tenantId) {
            $join->on('positions.code','career_path_hierarchies.position_code')
            ->where([
                ['career_path_hierarchies.company_id', $companyId],
                ['career_path_hierarchies.tenant_id', $tenantId],
            ]);
        })
        ->where([
            ['career_path_hierarchies.company_id', $companyId],
            ['career_path_hierarchies.tenant_id', $tenantId],
            ['career_path_hierarchies.career_path_id', $id],
         ])
         ->orderBy('career_path_hierarchies.id', 'ASC')
         ->get();
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
    | cek duplicate name career path
    |-----------------------------
    |
    | 
    */
    public function checkDuplicate(string $name)
    {
        return DB::table($this->table)
        ->select('id')
        ->where([
            ['name', $name],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
    }


        /*
    |-----------------------------
    | cek duplicate name career path
    |-----------------------------
    |
    | 
    */
    public function checkDuplicateUpdate(string $name, int $id)
    {
        return DB::table($this->table)
        ->select('id')
        ->where([
            ['name', $name],
            ['id', '!=', $id],
            ['company_id', $this->requester->getCompanyId()],
            ['tenant_id', $this->requester->getTenantId()]
        ])->count();
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
        DB::table('career_path_hierarchies')->insert($obj);
    }


    /*
    |-----------------------------
    | delete data di database
    |-----------------------------
    |
    |
    */
    public function delete($id) {
        DB::table($this->table)->where('id', $id)->delete();
        $this->deleteItem($id);
    }


    /*
    |-----------------------------
    | delete data di database
    |-----------------------------
    |
    |
    */
    public function deleteItem($id) {
        DB::table('career_path_hierarchies')->where('career_path_id', $id)->delete();
    }


}
