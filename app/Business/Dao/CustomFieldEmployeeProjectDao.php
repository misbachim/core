<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomFieldEmployeeProjectDao {
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }


    /**
     * Get ont data of cf_employee_project by employee project id from DB
     * @param  array obj
     */
    public function getOneByEmployeeProjectId($id, $lovNbft){
        return
            DB::table('cf_employee_project')
                ->select(
                    'employee_project_id as employeeProjectId',
                    'c1',
                    'c2',
                    'c3',
                    'c4',
                    'c5',
                    'c6',
                    'c7',
                    'c8',
                    'c9',
                    'c10',
                    'lov_nbft as lovNbft'
                )
                ->where([
                    ['employee_project_id', $id],
                    ['tenant_id', $this->requester->getTenantId()],
                    ['company_id', $this->requester->getCompanyId()],
                    ['lov_nbft', $lovNbft]
                ])
                ->orderBy('id', 'desc')
                ->first();
    }

    /**
     * Insert data cf_employee_project to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('cf_employee_project')->insertGetId($obj);
    }

    /**
     * Update data cf_employee_project to DB
     * @param  array obj, credentialId
     */
    public function update($id, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('cf_employee_project')
            ->where([
                ['id', $id]
            ])
            ->update($obj);
    }
}