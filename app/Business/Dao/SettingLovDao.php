<?php

namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;

class SettingLovDao
{
    public function __construct() { }

    public function getAll($typeCode)
    {
        return
            DB::table('setting_lovs')
                ->select(
                    'key_data as keyData',
                    'val_data as valData'
                )
                ->where([
                    ['setting_type_code', $typeCode]
                ])
                ->get();
    }
}
