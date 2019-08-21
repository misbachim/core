<?php
namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

class PotentialPerformanceMatrixDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
        $this->table = 'performance_potential_matrixs';
    }

    /*
    |-----------------------------
    | get all data ke database
    |-----------------------------
    |
    |
    */
    public function getAll($companyId) {
        return DB::table($this->table)
            ->select(
                'id',
                'description'
            )
        ->where([
            ['company_id', $companyId],
         ])
         ->get();
    }



    /*
    |-----------------------------
    | save data ke database
    |-----------------------------
    |
    | @param obj dari controller
    */
    public function save($obj) {
        DB::table($this->table)->insert($obj);
    }


    /*
    |-----------------------------
    | update eff end data
    |-----------------------------
    |
    | @param
    */
    public function endActiveData($id) {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::today();
        $obj['eff_end'] = Carbon::today();
        DB::table($this->table)->where([['id', $id],])->update($obj);
    }


    /*
    |-----------------------------
    | delete data di database
    |-----------------------------
    |
    |
    */
    public function delete() {
        DB::table($this->table)
            ->where([
                ['company_id', $this->requester->getCompanyId()],
             ])
            ->delete();
    }
}
