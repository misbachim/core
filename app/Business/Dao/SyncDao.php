<?php

namespace App\Business\Dao;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SyncDao
{
    public function updateDataAccess($data)
    {
        $tableName = 'data_access_' . $data->data_access_code;
        DB::table($tableName)
            ->where([
                ['tenant_id', $data->tenant_id],
                ['company_id', $data->company_id]
            ])
            ->delete();

        $data->$tableName = array_map(function ($obj) {
            return (array)$obj;
        }, (array)$data->$tableName);
        DB::table($tableName)->insert($data->$tableName);
    }
}
