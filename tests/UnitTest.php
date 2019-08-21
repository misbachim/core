<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UnitTest extends TestCase
{
    use Testable;

    /**
     * Test getAll endpoint.
     *
     * @return void
     */

    public function testGetAll()
    {

        $this->json('POST', '/unit/getAll', [
            'companyId'=>$this->getRequester()->getCompanyId()
        ])
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.allDataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        'id',
                        'effBegin',
                        'effEnd',
                        'code',
                        'name',
                        'locationName',
                        'unitTypeName'
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
        $this->json('POST', '/unit/getOne',
            [
                'companyId' => $this->getRequester()->getCompanyId(),
                'id'=> 1
            ]
        )
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    'effBegin',
                    'effEnd',
                    'code',
                    'name',
                    'locationCode',
                    'locationName',
                    'costCenterCode',
                    'costCenterName',
                    'unitTypeCode',
                    'unitTypeName'
                ]
            ]);
    }

    public function testGetLov()
    {
        $this->json('POST', '/unit/lov',
            [
                'companyId' => $this->getRequester()->getCompanyId()
            ]
        )
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.allDataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    ['code'],
                    ['name']
                ]
            ]);
    }

    public function testGetSLov()
    {
        $this->json('POST', '/unit/slov',
            [
                'companyId' => $this->getRequester()->getCompanyId(),
                'menuCode' => 'EMT01'

            ]
        )
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.allDataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    ['code'],
                    ['name']
                ]
            ]);
    }


    public function testSave()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'code' => 'U19',
            'name' => 'Unit 19',
            'locationCode' => null,
            'costCenterCode' => null,
            'unitTypeCode' => 'XV'
        );
        $this->json('POST', '/unit/save', $data)
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('units', [
            'company_id'=>1900000000,
            'eff_begin'=> '2017-10-13',
            'eff_end'=> '2099-12-31',
            'code' => 'U19',
            'name' => 'Unit 19',
            'location_code' => null,
            'cost_center_code' => null,
            'unit_type_code' => 'XV'
        ] );
        DB::rollback();
    }

    public function testSaveBlankField()
    {
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
        );
        $this->json('POST', '/unit/save', $data)
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"code",
                "key"=>"effBegin",
                "key"=>"effEnd",
                "key"=>"name",
                "key"=>"unitTypeCode"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testSaveEffBeginIsBiggerThanEnd()
    {
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2009-12-31',
            'code' => 'U19',
            'name' => 'Unit 19',
            'locationCode' => null,
            'costCenterCode' => null,
            'unitTypeCode' => 'XV'
        );
        $this->json('POST', '/unit/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key" => "effEnd"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveDuplicateCode()
    {
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'code' => 'U1',
            'name' => 'Unit 1',
            'locationCode' => null,
            'costCenterCode' => null,
            'unitTypeCode' => 'XV'
        );
        $this->json('POST', '/unit/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateCode')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testUpdate()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
            'id'=>12,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'code' => 'U19',
            'name' => 'Unit 1',
            'locationCode' => null,
            'costCenterCode' => null,
            'unitTypeCode' => 'XV'
        );
        $this->json('POST', '/unit/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('units', [
            'company_id'    =>  1900000000,
            'id'=>12,
            'eff_begin'=> '2017-10-13',
            'eff_end'=> '2099-12-31',
            'code' => 'U19',
            'name' => 'Unit 1',
            'location_code' => null,
            'cost_center_code' => null,
            'unit_type_code' => 'XV'
        ] );

        DB::rollback();
    }


    public function testUpdateBlankField()
    {
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
            'id'=>12
        );
        $this->json('POST', '/unit/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"code",
                "key"=>"effBegin",
                "key"=>"effEnd",
                "key"=>"name",
                "key"=>"unitTypeCode"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testUpdateEffBeginIsBiggerThanEnd()
    {
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
            'id'=>12,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2009-12-31',
            'code' => 'U19',
            'name' => 'Unit 1',
            'locationCode' => null,
            'costCenterCode' => null,
            'unitTypeCode' => 'XV'
        );
        $this->json('POST', '/unit/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key" => "effEnd"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }
}
