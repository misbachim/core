<?php
namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;
use App\Business\Model\Requester;
use Carbon\Carbon;

class LovTypeDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Lov Type
     */
    public function getAll()
    {
        return
            DB::table('lov_types')
                ->select('code', 'name')
                ->get();
    }

    /**
     * Get one Lov Type based on code
     * @param  code
     */
    public function getOne($code)
    {
        return
            DB::table('lov_types')
                ->select('name')
                ->where([
                    ['code', $code]
                ])
                ->first();
    }

    /**
     * Insert data Lov Type to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        DB::table('lov_types')->insert($obj);
    }

    public function upsert($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        DB::table('lov_types')->updateOrInsert(['code' => $obj['code']], $obj);
    }

    /**
     * Update data Lov Type to DB
     * @param  array obj, code
     */
    public function update($code, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('lov_types')
        ->where([
            ['code', $code]
        ])
        ->update($obj);
    }

    /**
     * Delete data Lov Type from DB
     * @param code
     */
    public function delete($code)
    {
        DB::table('lov_types')->where('code', $code)->delete();
    }

    /**
     * @param string code
     * @return
     */
    public function checkDuplicateLovTypeCode(string $code)
    {
        return DB::table('lov_types')->where([
            ['code', $code]
        ])->count();
    }
}
