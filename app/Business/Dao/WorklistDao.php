<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

class WorklistDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Get all Worklist
     * @param
     */
    public function getAllWorklist($approverId)
    {
        return
            DB::table('worklists')
                ->select(
                    'worklists.id',
                    'worklists.lov_wfty as lovWfty',
                    'worklists.request_id as requestId',
                    'worklists.ordinal',
                    'worklists.requester_id as requesterId',
                    'worklists.approver_id as approverId',
                    'worklists.answer',
                    'worklists.is_active as isActive',
                    'wfty.val_data as valData',
                    'worklists.sub_type as subType',
                    'worklists.description as description',
                    'worklists.created_at as requestDate'
                )
                ->distinct()
                ->join('lovs as wfty', function ($join) {
                    $join
                        ->on('wfty.key_data', '=', 'worklists.lov_wfty')
                        ->on('wfty.tenant_id', '=', 'worklists.tenant_id')
                        ->on('wfty.company_id', '=', 'worklists.company_id')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY']
                        ]);
                })
                ->where([
                    ['worklists.approver_id', $approverId],
                    ['worklists.tenant_id', $this->requester->getTenantId()],
                    ['worklists.company_id', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * Get all Worklist Subordinate
     * @param
     */
    public function getAllWorklistSubordinateBackup($approverId, $typeRequest, $offset, $limit, $status = null, $requesterId = null)
    {
        $query = DB::table('worklists')
                ->select(
                    'worklists.id',
                    'worklists.lov_wfty as lovWfty',
                    'worklists.request_id as requestId',
                    'worklists.ordinal',
                    'worklists.requester_id as requesterId',
                    'worklists.approver_id as approverId',
                    'worklists.answer',
                    'worklists.is_active as isActive',
                    'wfty.val_data as valData',
                    'worklists.sub_type as subType',
                    'worklists.description as description',
                    'worklists.created_at as requestDate'
                )
                ->distinct()
                ->join('lovs as wfty', function ($join) {
                    $join
                        ->on('wfty.key_data', '=', 'worklists.lov_wfty')
                        ->on('wfty.tenant_id', '=', 'worklists.tenant_id')
                        ->on('wfty.company_id', '=', 'worklists.company_id')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY']
                        ]);
                })
                // ->join('leave_requests', function ($join) {
                //     $join
                //         ->on('leave_requests.id', '=', 'worklists.request_id')
                //         ->on('leave_requests.tenant_id', '=', 'worklists.tenant_id')
                //         ->on('leave_requests.company_id', '=', 'worklists.company_id')
                //         ->where([
                //             ['worklists.lov_wfty', 'LEAV']
                //         ]);
                // })
                // ->join('leave_request_details', function ($join) {
                //     $join
                //         ->on('leave_requests.id', '=', 'leave_request_details.leave_request_id')
                //         ->on('leave_requests.tenant_id', '=', 'leave_request_details.tenant_id')
                //         ->on('leave_requests.company_id', '=', 'leave_request_details.company_id')
                //         ->where([
                //             ['leave_request_details.date', '>=', '2019-05-10'],
                //             ['leave_request_details.date', '<=', '2019-05-11']
                //         ]);
                // })
                ->join('request_raw_timesheets', function ($join) {
                    $join
                        ->on('request_raw_timesheets.id', '=', 'worklists.request_id')
                        ->on('request_raw_timesheets.tenant_id', '=', 'worklists.tenant_id')
                        ->on('request_raw_timesheets.company_id', '=', 'worklists.company_id')
                        ->where([
                            ['worklists.lov_wfty', 'ATTD'],
                            // ['request_raw_timesheets.date', '>=', '2019-01-10'],
                            // ['request_raw_timesheets.date', '<=', '2019-08-11']
                        ]);
                })
                ->where([
                    ['worklists.approver_id', $approverId],
                    ['worklists.tenant_id', $this->requester->getTenantId()],
                    ['worklists.company_id', $this->requester->getCompanyId()]
                ])
                ->orderBy('worklists.created_at', 'DESC');

                if($status) {
                    $query->where('worklists.answer', $status == 'P' ? null : $status);
                }

                if($status == 'P') {
                    $query->where('worklists.is_active', true);
                }

                if($requesterId) {
                    $query->where('worklists.requester_id', $requesterId);
                }

                if($limit > 0) {
                    $query->offset($offset);
                    $query->limit($limit);
                }

                if(count($typeRequest) > 0) {
                    $query->whereIn('worklists.lov_wfty', $typeRequest);
                } else {
                    $query->whereIn('worklists.lov_wfty', ['']);
                }

        return $query->get();
    }

    /**
     * Get all Worklist Subordinate
     * @param
     */
    public function getAllWorklistSubordinate($approverId, $typeRequest, $offset, $limit, $status = null, $requesterId = null, $startDate = null, $endDate = null)
    {        
        //get table
        $table = null;
        if(count($typeRequest)){
            if($typeRequest == 'ATTD'){
                $table = 'v_subordinate_req_attd';
            }else if($typeRequest == 'LEAV'){
                $table = 'v_subordinate_req_leav';
            }else if($typeRequest == 'PERM'){
                $table = 'v_subordinate_req_perm';
            }else if($typeRequest == 'OVER'){
                $table = 'v_subordinate_req_over';
            }else if($typeRequest == 'BENF'){
                $table = 'v_subordinate_req_benf';
            }else if($typeRequest == 'MPP'){
                $table = 'v_subordinate_req_mpp';
            }else if($typeRequest == 'RFAR'){
                $table = 'v_subordinate_req_review';
            }else if($typeRequest == 'TRV'){
                $table = 'v_subordinate_req_trv';
            }else if($typeRequest == 'TRVX'){
                $table = 'v_subordinate_req_trvx';
            }else if($typeRequest == 'LOAN'){
                $table = 'v_subordinate_req_loan';
            }
        }
        
        $query = DB::table($table)
            ->select(
                'id',
                'lov_wfty as lovWfty',
                'request_id as requestId',
                'ordinal',
                'requester_id as requesterId',
                'approver_id as approverId',
                'answer',
                'is_active as isActive',
                'val_data as valData',
                'sub_type as subType',
                'description as description',
                'request_date as requestDate',
                'start_date as startDate',
                'end_date as endDate'
            )
            ->where([
                ['approver_id', $approverId],
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])
            ->orderBy('request_date', 'DESC');

            if($status) {
                $query->where(function ($query) use ($status) {
                    $query->where('answer', $status == 'P' ? null : $status);
                    $query->orWhere('answer', $status == 'P' ? '' : $status);
                });
            }

            if($status == 'P') {
                $query->where('is_active', true);
            }

            if($requesterId) {
                $query->where('requester_id', $requesterId);
            }

            if($startDate) {
                $query->where('start_date', '>=',$startDate);
            }

            if($endDate) {
                $query->where('end_date', '<=', $endDate);
            }

            if($limit > 0) {
                $query->offset($offset);
                $query->limit($limit);
            }

            // Log::info(print_r($approverId,true));

        if($table != null){
            return $query->get();
        }else{
            return null;
        }
    }

    public function countGetWorklistSubordinate($approverId, $typeRequest, $status = null, $requesterId = null, $startDate = null, $endDate = null)
    {
        //get table
        $table = null;
        if(count($typeRequest)){
            if($typeRequest == 'ATTD'){
                $table = 'v_subordinate_req_attd';
            }else if($typeRequest == 'LEAV'){
                $table = 'v_subordinate_req_leav';
            }else if($typeRequest == 'PERM'){
                $table = 'v_subordinate_req_perm';
            }else if($typeRequest == 'OVER'){
                $table = 'v_subordinate_req_over';
            }else if($typeRequest == 'BENF'){
                $table = 'v_subordinate_req_benf';
            }else if($typeRequest == 'MPP'){
                $table = 'v_subordinate_req_mpp';
            }else if($typeRequest == 'RFAR'){
                $table = 'v_subordinate_req_review';
            }else if($typeRequest == 'TRV'){
                $table = 'v_subordinate_req_trv';
            }else if($typeRequest == 'TRVX'){
                $table = 'v_subordinate_req_trvx';
            }else if($typeRequest == 'LOAN'){
                $table = 'v_subordinate_req_loan';
            }
        }
        
        $query = DB::table($table)
            ->select(
                'id',
                'lov_wfty as lovWfty',
                'request_id as requestId',
                'ordinal',
                'requester_id as requesterId',
                'approver_id as approverId',
                'answer',
                'is_active as isActive',
                'val_data as valData',
                'sub_type as subType',
                'description as description',
                'request_date as requestDate',
                'start_date as startDate',
                'end_date as endDate'
            )
            ->where([
                ['approver_id', $approverId],
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()]
            ])
            ->orderBy('request_date', 'DESC');

            if($status) {
                $query->where('answer', $status == 'P' ? null : $status);
            }

            if($status == 'P') {
                $query->where('is_active', true);
            }

            if($requesterId) {
                $query->where('requester_id', $requesterId);
            }

            if($startDate) {
                $query->where('start_date', '>=',$startDate);
            }

            if($endDate) {
                $query->where('end_date', '<=', $endDate);
            }

        if($table != null){
            return $query->count();
        }else{
            return null;
        }
    }

    public function countGetWorklistSubordinateBackup($approverId, $typeRequest, $status = null, $requesterId = null)
    {
        $query = DB::table('worklists')
            ->select(
                'worklists.id',
                'worklists.lov_wfty as lovWfty',
                'worklists.request_id as requestId',
                'worklists.ordinal',
                'worklists.requester_id as requesterId',
                'worklists.approver_id as approverId',
                'worklists.answer',
                'worklists.is_active as isActive',
                'wfty.val_data as valData',
                'worklists.sub_type as subType',
                'worklists.description as description',
                'worklists.created_at as requestDate'
            )
            ->distinct()
            ->join('lovs as wfty', function ($join) {
                $join
                    ->on('wfty.key_data', '=', 'worklists.lov_wfty')
                    ->on('wfty.tenant_id', '=', 'worklists.tenant_id')
                    ->on('wfty.company_id', '=', 'worklists.company_id')
                    ->where([
                        ['wfty.lov_type_code', 'WFTY']
                    ]);
            })
            ->where([
                ['worklists.approver_id', $approverId],
                ['worklists.tenant_id', $this->requester->getTenantId()],
                ['worklists.company_id', $this->requester->getCompanyId()]
            ]);

            if($status) {
                $query->where('worklists.answer', $status == 'P' ? null : $status);
            }

            if($status == 'P') {
                $query->where('worklists.is_active', true);
            }

            if($requesterId) {
                $query->where('worklists.requester_id', $requesterId);
            }

            if(count($typeRequest) > 0) {
                $query->whereIn('worklists.lov_wfty', $typeRequest);
            } else {
                $query->whereIn('worklists.lov_wfty', ['']);
            }

        return $query->count();
    }

    /**
     * Get all Worklist Answered
     * @param
     */
    public function getAllWorklistAnswered($approverId)
    {
        return
            DB::table('worklists')
                ->select(
                    'worklists.id',
                    'worklists.lov_wfty as lovWfty',
                    'worklists.request_id as requestId',
                    'worklists.ordinal',
                    'worklists.requester_id as requesterId',
                    'worklists.approver_id as approverId',
                    'worklists.answer',
                    'worklists.is_active as isActive',
                    'wfty.val_data as valData',
                    'worklists.sub_type as subType',
                    'worklists.description as description'
                )
                ->join('lovs as wfty', function ($join) {
                    $join
                        ->on('wfty.key_data', '=', 'worklists.lov_wfty')
                        ->on('wfty.tenant_id', '=', 'worklists.tenant_id')
                        ->on('wfty.company_id', '=', 'worklists.company_id')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY']
                        ]);
                })
                ->whereNotNull('worklists.answer')
                ->where([
                    ['worklists.approver_id', $approverId],
                    ['worklists.is_active', false],
                    ['worklists.tenant_id', $this->requester->getTenantId()],
                    ['worklists.company_id', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * Get all Worklist Answered
     * @param
     */
    public function getAllWorklistResponse($approverId)
    {
        return
            DB::table('worklists')
                ->select(
                    'worklists.id',
                    'worklists.lov_wfty as lovWfty',
                    'worklists.request_id as requestId',
                    'worklists.ordinal',
                    'worklists.requester_id as requesterId',
                    'worklists.approver_id as approverId',
                    'worklists.answer',
                    'worklists.is_active as isActive',
                    'wfty.val_data as valData',
                    'worklists.sub_type as subType',
                    'worklists.description as description'
                )
                ->join('lovs as wfty', function ($join) {
                    $join
                        ->on('wfty.key_data', '=', 'worklists.lov_wfty')
                        ->on('wfty.tenant_id', '=', 'worklists.tenant_id')
                        ->on('wfty.company_id', '=', 'worklists.company_id')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY']
                        ]);
                })
                ->whereNull('worklists.answer')
                ->where([
                    ['worklists.approver_id', $approverId],
                    ['worklists.is_active', true],
                    ['worklists.tenant_id', $this->requester->getTenantId()],
                    ['worklists.company_id', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * Get all Worklist Answered
     * @param
     */
    public function getAllWorklistEscalation($approverId)
    {
        return
            DB::table('worklists')
                ->select(
                    'worklists.id',
                    'worklists.lov_wfty as lovWfty',
                    'worklists.request_id as requestId',
                    'worklists.ordinal',
                    'worklists.requester_id as requesterId',
                    'worklists.approver_id as approverId',
                    'worklists.answer',
                    'worklists.is_active as isActive',
                    'wfty.val_data as valData',
                    'worklists.sub_type as subType',
                    'worklists.description as description'
                )
                ->join('lovs as wfty', function ($join) {
                    $join
                        ->on('wfty.key_data', '=', 'worklists.lov_wfty')
                        ->on('wfty.tenant_id', '=', 'worklists.tenant_id')
                        ->on('wfty.company_id', '=', 'worklists.company_id')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY']
                        ]);
                })
                ->whereNull('worklists.answer')
                ->where([
                    ['worklists.approver_id', $approverId],
                    ['worklists.is_active', false],
                    ['worklists.tenant_id', $this->requester->getTenantId()],
                    ['worklists.company_id', $this->requester->getCompanyId()]
                ])
                ->get();
    }

    /**
     * Get all Worklist based on Request Id
     * @param
     */
    public function getAllByRequestId($requestId)
    {
        return
            DB::table('worklists')
                ->select(
                    'worklists.id',
                    'worklists.lov_wfty as lovWfty',
                    'worklists.request_id as requestId',
                    'worklists.ordinal',
                    'worklists.requester_id as requesterId',
                    'worklists.approver_id as approverId',
                    'worklists.answer',
                    'worklists.is_active as isActive',
                    'worklists.notes',
                    'worklists.updated_at as updatedAt',
                    'wfty.val_data as valData',
                    'worklists.sub_type as subType'
                )
                ->join('lovs as wfty', function ($join) {
                    $join
                        ->on('wfty.key_data', '=', 'worklists.lov_wfty')
                        ->on('wfty.tenant_id', '=', 'worklists.tenant_id')
                        ->on('wfty.company_id', '=', 'worklists.company_id')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY']
                        ]);
                })
                ->where([
                    ['worklists.request_id', $requestId],
                    ['worklists.tenant_id', $this->requester->getTenantId()],
                    ['worklists.company_id', $this->requester->getCompanyId()]
                ])
                ->orderBy('worklists.ordinal', 'ASC')
                ->get();
    }

    /**
     * Get all Worklist based on Request Id and Lov Wfty
     * @param
     */
    public function getAllByRequestIdAndLovWfty($requestId, $lovWfty)
    {
        return
            DB::table('worklists')
                ->select(
                    'worklists.id',
                    'worklists.lov_wfty as lovWfty',
                    'worklists.request_id as requestId',
                    'worklists.ordinal',
                    'worklists.requester_id as requesterId',
                    'worklists.approver_id as approverId',
                    'worklists.answer',
                    'worklists.is_active as isActive',
                    'worklists.notes',
                    'worklists.updated_at as updatedAt',
                    'wfty.val_data as valData',
                    'worklists.sub_type as subType',
                    'worklists.description as description'
                )
                ->join('lovs as wfty', function ($join) {
                    $join
                        ->on('wfty.key_data', '=', 'worklists.lov_wfty')
                        ->on('wfty.tenant_id', '=', 'worklists.tenant_id')
                        ->on('wfty.company_id', '=', 'worklists.company_id')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY']
                        ]);
                })
                ->where([
                    ['worklists.request_id', $requestId],
                    ['worklists.lov_wfty', $lovWfty],
                    ['worklists.tenant_id', $this->requester->getTenantId()],
                    ['worklists.company_id', $this->requester->getCompanyId()]
                ])
                ->orderBy('worklists.ordinal', 'ASC')
                ->get();
    }

    /**
     * Get all Worklist based on Request Id, Lov Wfty and Description
     * @param
     */
    public function getAllByRequestIdLovWftyAndDesc($requestId, $lovWfty, $desc)
    {
        return
            DB::table('worklists')
                ->select(
                    'worklists.id',
                    'worklists.lov_wfty as lovWfty',
                    'worklists.request_id as requestId',
                    'worklists.ordinal',
                    'worklists.requester_id as requesterId',
                    'worklists.approver_id as approverId',
                    'worklists.answer',
                    'worklists.is_active as isActive',
                    'worklists.notes',
                    'worklists.updated_at as updatedAt',
                    'wfty.val_data as valData',
                    'worklists.sub_type as subType',
                    'worklists.description as description'
                )
                ->join('lovs as wfty', function ($join) {
                    $join
                        ->on('wfty.key_data', '=', 'worklists.lov_wfty')
                        ->on('wfty.tenant_id', '=', 'worklists.tenant_id')
                        ->on('wfty.company_id', '=', 'worklists.company_id')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY']
                        ]);
                })
                ->where([
                    ['worklists.request_id', $requestId],
                    ['worklists.lov_wfty', $lovWfty],
                    ['worklists.description', $desc],
                    ['worklists.tenant_id', $this->requester->getTenantId()],
                    ['worklists.company_id', $this->requester->getCompanyId()]
                ])
                ->orderBy('worklists.ordinal', 'ASC')
                ->get();
    }

    /**
     * Get one Worklist based on worklist id
     * @param
     */
    public function getOne($worklistId)
    {
        return
            DB::table('worklists')
                ->select(
                    'worklists.id',
                    'worklists.lov_wfty as lovWfty',
                    'worklists.request_id as requestId',
                    'worklists.ordinal',
                    'worklists.requester_id as requesterId',
                    'worklists.approver_id as approverId',
                    'worklists.answer',
                    'worklists.is_active as isActive',
                    'wfty.val_data as valData',
                    'worklists.sub_type as subType',
                    'worklists.description as description',
                    'worklists.created_at as requestDate'
                )
                ->join('lovs as wfty', function ($join) {
                    $join
                        ->on('wfty.key_data', '=', 'worklists.lov_wfty')
                        ->on('wfty.tenant_id', '=', 'worklists.tenant_id')
                        ->on('wfty.company_id', '=', 'worklists.company_id')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY']
                        ]);
                })
                ->where([
                    ['worklists.id', $worklistId],
                    ['worklists.tenant_id', $this->requester->getTenantId()],
                    ['worklists.company_id', $this->requester->getCompanyId()]
                ])
                ->orderBy('worklists.request_id')
                ->first();
    }

    public function getNextWorklist($ordinal, $requestId)
    {
        return
            DB::table('worklists')
                ->select(
                    'worklists.id',
                    'worklists.lov_wfty as lovWfty',
                    'worklists.request_id as requestId',
                    'worklists.ordinal',
                    'worklists.requester_id as requesterId',
                    'worklists.approver_id as approverId',
                    'worklists.answer',
                    'worklists.is_active as isActive',
                    'wfty.val_data as valData',
                    'worklists.sub_type as subType',
                    'worklists.description as description'
                )
                ->join('lovs as wfty', function ($join) {
                    $join
                        ->on('wfty.key_data', '=', 'worklists.lov_wfty')
                        ->on('wfty.tenant_id', '=', 'worklists.tenant_id')
                        ->on('wfty.company_id', '=', 'worklists.company_id')
                        ->where([
                            ['wfty.lov_type_code', 'WFTY']
                        ]);
                })
                ->where([
                    ['worklists.ordinal', '>', $ordinal],
                    ['worklists.request_id', $requestId],
                    ['worklists.tenant_id', $this->requester->getTenantId()],
                    ['worklists.company_id', $this->requester->getCompanyId()]
                ])
                ->orderBy('worklists.ordinal', 'ASC')
                ->limit(1)
                ->first();
    }

    /**
     * Save data worklists
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        return DB::table('worklists')->insertGetId($obj);
    }

    /**
     * Save data worklists
     * @param  array obj
     */
    public function multipleSave($obj)
    {
        $data = array();
        foreach ($obj as $key => $value) {
            info(print_r($value,true));
            $value['created_by'] = $this->requester->getUserId();
            $value['created_at'] = Carbon::now();
            array_push($data,$value);
        }

        return DB::table('worklists')->insert($data);
    }

    /**
     * Update data worklist to DB
     * @param  array obj, lovWfty
     */
    public function update($workflowId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('worklists')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['id', $workflowId]
            ])
            ->update($obj);
    }

    /**
     * Update data worklist to DB
     * @param  array obj, lovWfty
     */
    public function updateStatus($id, $lovWfty, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        DB::table('worklists')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['company_id', $this->requester->getCompanyId()],
                ['request_id', $id],
                ['lov_wfty', $lovWfty]
            ])
            ->update($obj);
    }


}
