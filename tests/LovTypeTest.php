<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class LovTypeTest extends TestCase
{
    use Testable;

    /**
     * Test getAll endpoint.
     *
     * @return void
     */

    public function testGetAll()
    {

        $this->json('POST', '/lovType/getAll')
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.allDataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    [
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
        $this->json('POST', '/lovType/getOne',
            [
                'code'=> 'CURR'
            ]
        )
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                   'name'
                ]
            ]);
    }

    public function testSaveLovType()
    {
        DB::beginTransaction();
        $data = array(
            'code'=>'TEST',
            'name'=>'FOR TESTING ONLY'
        );
        $this->json('POST', '/lovType/save', $data)
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('lov_types', [
            'code'=>'TEST',
            'name'=>'FOR TESTING ONLY'
        ] );
        DB::rollback();
    }

    public function testSaveDuplicateLovTypeCode()
    {
        $data = array(
            'code'=>'CURR',
            'name'=>'FOR TESTING ONLY'
        );
        $this->json('POST', '/lovType/save', $data)
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateCode')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveUnitTypeBlankField()
    {
        $data = array();
        $this->json('POST', '/lovType/save', $data)
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"name",
                "key"=>"code"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testUpdateLovType()
    {
        DB::beginTransaction();
        $data = array(
            'code'=>'CURR',
            'name'=>'FOR TESTING ONLY'
        );
        $this->json('POST', '/lovType/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('lov_types', [
            'code'=>'CURR',
            'name'=>'FOR TESTING ONLY'
        ] );

        DB::rollback();
    }


    public function testUpdateLovTypeBlankField()
    {
        $data = array(
            'code'=>'CURR'
        );
        $this->json('POST', '/lovType/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"name"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testDeleteLovType()
    {
        DB::beginTransaction();
        $data = array(
            'code'      => 'CURR'
        );
        $this->json('POST', '/lovType/delete', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('lov_types', ['code' => 'CURR'] );
        DB::rollback();
    }

}
