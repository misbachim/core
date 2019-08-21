<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class UnitTypeTest extends TestCase
{
    use Testable;

    /**
     * Test getAll endpoint.
     *
     * @return void
     */

    public function testGetAll()
    {

        $this->json('POST', '/unitType/getAll', [
            'companyId'=>$this->getRequester()->getCompanyId()
        ])
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.allDataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        'code',
                        'name',
                        'unitLevel'
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
        $this->json('POST', '/unitType/getOne',
            [
                'companyId' => $this->getRequester()->getCompanyId(),
                'code'=> 'XV'
            ]
        )
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    'code',
                    'name',
                    'unitLevel'
                ]
            ]);
    }

    public function testSaveUnitType()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
            'name'       => 'Unit Type 2',
            'code'       => 'UT2',
            'unitLevel' => '2'
        );
        $this->json('POST', '/unitType/save', $data)
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('unit_types', [
            'company_id'=>1900000000,
            'name'       => 'Unit Type 2',
            'code'       => 'UT2',
            'unit_level' => '2'
        ] );
        DB::rollback();
    }

    public function testSaveUnitTypeBlankField()
    {
        $data = array();
        $this->json('POST', '/unitType/save', $data)
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"code",
                "key"=>"name",
                "key"=>"unitLevel"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testUpdateGrade()
    {
        DB::beginTransaction();
        $data = array(
            'companyId'     => $this->getRequester()->getCompanyId(),
            'name'          => 'UNIT 01',
            'code'          => 'XV',
            'unitLevel'     => '1'
        );
        $this->json('POST', '/unitType/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('unit_types', [
            'company_id'    =>  1900000000,
            'name'          => 'UNIT 01',
            'code'          => 'XV',
            'unit_level'     => '1'
        ] );

        DB::rollback();
    }


    public function testUpdateUnitTypeBlankField()
    {
        $data = array(
            'code'          => 'XV'
        );
        $this->json('POST', '/unitType/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"code",
                "key"=>"name",
                "key"=>"unitLevel"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }
}
