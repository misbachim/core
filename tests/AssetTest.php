<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class AssetTest extends TestCase
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
            'companyId' => 1900000000
        );

        $this->json('POST', '/asset/getAll', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "All data retrieved"
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        'effBegin',
                        'effEnd',
                        'code',
                        'name',
                        'description',
                        'type'
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
            'code' => 'PC'
        );
        $this->json('POST', '/asset/getOne', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "Data retrieved"
            ])
            ->seeJsonStructure([
                "data" => [
                    'effBegin',
                    'effEnd',
                    'code',
                    'name',
                    'description',
                    'type'
                ]
            ]);
    }

    public function testGetLov()
    {
        $data = array(
            'companyId' => 1900000000
        );
        $this->json('POST', '/asset/lov', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "All data retrieved"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSave()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => 1900000000,
            'effBegin' => '2017-11-28',
            'effEnd' => '2099-11-28',
            'code' => 'MS',
            'name' => 'Mouse Logitech',
            'description' => 'Mouse Logitech',
            'type' => 'Mouse'
        );
        $this->json('POST', '/asset/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('assets', ['company_id'=>1900000000,'code' => 'MS','name'=>'Mouse Logitech','type' => 'Mouse'] );
        DB::rollback();
    }

    public function testSaveBeginIsBiggerThanEnd()
    {
        $data = array(
            'companyId' => 1900000000,
            'effBegin' => '2017-11-28',
            'effEnd' => '2009-11-28',
            'code' => 'MS',
            'name' => 'Mouse Logitech',
            'description' => 'Mouse Logitech',
            'type' => 'Mouse'
        );
        $this->json('POST', '/asset/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status"=>444,
                "key"=>"effEnd"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
     }

    public function testSaveBlank()
    {
        $data = array(
            'companyId' => 1900000000
        );
        $this->json('POST', '/asset/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"effBegin",
                "key"=>"effEnd",
                "key"=>"code",
                "key"=>"name",
                "key"=>"type"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveDuplicateCode()
    {
        $data = array(
            'companyId' => 1900000000,
            'effBegin' => '2017-11-28',
            'effEnd' => '2099-11-28',
            'code' => 'PC',
            'name' => 'HP PC',
            'description' => 'PC HP',
            'type' => 'PC'
        );
        $this->json('POST', '/asset/save', $data, $this->getReqHeaders())
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
            'companyId' => 1900000000,
            'effBegin' => '2017-11-28',
            'effEnd' => '2099-11-28',
            'code' => 'PC',
            'name' => 'Lenovo PC',
            'description' => 'PC Lenovo',
            'type' => 'PC'
        );
        $this->json('POST', '/asset/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('assets', ['company_id'=>1900000000,'code' => 'PC','name'=>'Lenovo PC','type' => 'PC'] );
        DB::rollback();
    }

    public function testUpdateBlankField()
    {
        $data = array(
            'code' => 'PC',
        );
        $this->json('POST', '/asset/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"effBegin",
                "key"=>"effEnd",
                "key"=>"code",
                "key"=>"name",
                "key"=>"type"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testDelete()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => 1900000000,
            'code' => 'PC'
        );
        $this->json('POST', '/asset/delete', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('assets', ['code' => 'PC'] );
        DB::rollback();
    }

}
