<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class LocationGroupTest extends TestCase
{
    use Testable;

    /**
     * Test getAll endpoint.
     *
     * @return void
     */
    public function testGetAll()
    {
        $data = array(
            'companyId' => 1900000000,
            'pageInfo'=>[
                'pageNo'=>1,
                'pageLimit'=>1
            ]
        );
        $this->json('POST', '/locationGroup/getAll', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "All data retrieved"
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        'id',
                        'effBegin',
                        'effEnd',
                        'code',
                        'name'
                    ]
                ]
            ]);
    }

    /**
     * Test getOne endpoint.
     *
     * @return void
     */
    public function testGetOne()
    {
        $data = array(
            'companyId' => 1900000000,
            'id' => 28
        );
        $this->json('POST', '/locationGroup/getOne', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "Data retrieved"
            ])
            ->seeJsonStructure([
                "data" => [
                    'id',
                    'effBegin',
                    'effEnd',
                    'code',
                    'name'
                ]
            ]);
    }


    public function testSaveLocationGroup()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'name' => 'Singapore Groups',
            'code' => 'SG',
            'locationDetail' => [
                [
                    "companyId"        => 17751,
                    "code"             => 'RJ7'
                ]
            ]
        );
        $this->json('POST', '/locationGroup/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                "data" => [
                    'id'
                ]
            ]);

        $this->seeInDatabase('location_groups', [
            'company_id' => 1900000000,
            'eff_begin'=> '2017-10-13',
            'eff_end'=> '2099-12-31',
            'name' => 'Singapore Groups',
            'code' => 'SG'
        ] );
        $this->seeInDatabase('location_group_details', [
            'company_id' => 1900000000,
            'location_code'  => 'RJ7'
        ] );

        DB::rollback();
    }

    public function testSaveLocationGroupBlankField()
    {
        $data = array();
        $this->json('POST', '/locationGroup/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"effBegin",
                "key"=>"effEnd",
                "key"=>"name",
                "key"=>"code",
                "key"=>"companyId",
                "key"=>"locationDetail"
          ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveLocationGroupDuplicateCode()
    {
        $data = array(
            'companyId' => 17751,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'name' => 'Location Group 1',
            'code' => 'LG1',
            'locationDetail' => [
                [
                    "companyId"        => 17751,
                    "code"             => 'RJ7'
                ]
            ]
        );
        $this->json('POST', '/locationGroup/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateCode')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveLocationGroupBeginIsBiggerThanEnd()
    {
        $data = array(
            'companyId' => 17751,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2009-12-31',
            'name' => 'Singapore',
            'code' => 'SG',
            'locationDetail' => [
                [
                    "companyId"        => 17751,
                    "code"               => 'RJ7'
                ]
            ]
        );
        $this->json('POST', '/locationGroup/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key" => "effEnd"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testUpdateLocationGroup()
    {
        DB::beginTransaction();
        $data = array(
            'id' => 145,
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'name' => 'Singapore Groups',
            'code' => 'SG',
            'locationDetail' => [
                [
                    "companyId"        => 1900000000,
                    "code"             => 'RJ7'
                ]
            ]
        );
        $this->json('POST', '/locationGroup/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('location_groups', [
            'id' => 145,
            'company_id' => 1900000000,
            'eff_begin'=> '2017-10-13',
            'eff_end'=> '2099-12-31',
            'name' => 'Singapore Groups',
            'code' => 'SG',
        ]);

        $this->seeInDatabase('location_group_details', [
            'company_id' => 1900000000,
            "location_code"  => 'RJ7',
            'location_group_id'=>145
        ] );

        DB::rollback();
    }

    public function testUpdateLocationGroupBlankField()
    {
        $data = array(
            'id' => 28,
        );
        $this->json('POST', '/locationGroup/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"effBegin",
                "key"=>"effEnd",
                "key"=>"name",
                "key"=>"code",
                "key"=>"companyId",
                "key"=>"locationDetail"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testUpdateLocationDuplicateCode()
    {
        DB::beginTransaction();

        $data = array(
            'id' => 29,
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'name' => 'Singapore Groups',
            'code' => 'ID',
            'locationDetail' => [
                [
                    "companyId"        => 1900000000,
                    "code"               => 'RJ7'
                ]
            ]
        );
        $this->json('POST', '/locationGroup/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateCode')

            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        DB::rollback();

    }

    public function testUpdateLocationBeginIsBiggerThanEnd()
    {
        $data = array(
            'id' => 29,
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2009-12-31',
            'name' => 'Singapore Groups',
            'code' => 'SG',
            'locationDetail' => [
                [
                    "companyId"        => 1900000000,
                    "code"               => 'RJ7'
                ]
            ]
        );
        $this->json('POST', '/locationGroup/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key" => "effEnd"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }


    public function testDeleteLocationGroup()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => 1900000000,
            'id' => 29
        );
        $this->json('POST', '/locationGroup/delete', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('locations', ['id' => 29] );
        DB::rollback();
    }


}
