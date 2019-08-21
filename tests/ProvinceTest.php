<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class ProvinceTest extends TestCase
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
        $this->json('POST', '/province/getAll', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.allDataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        'countryId',
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
            'id' => 5
        );
        $this->json('POST', '/province/getOne', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveProvince()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => 1900000000,
            'code' => 'BL',
            'name' => 'Bali',
            'countryId' => 5,
        );
        $this->json('POST', '/province/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                "data" => [
                    'id'
                ]
            ]);
        $this->seeInDatabase('provinces', ['company_id'=>1900000000,'code' => 'BL','name'=>'Bali','country_id' => 5] );
        DB::rollback();
    }

    public function testSaveProvinceBlank()
    {
        $data = array();
        $this->json('POST', '/province/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"name",
                "key"=>"code",
                "key"=>"countryId"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveProvinceDuplicateCode()
    {
        $data = array(
            'companyId' => 1900000000,
            'code' => 'JT',
            'name' => 'Jawa Tengah',
            'countryId' => 5,
        );
        $this->json('POST', '/province/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateCode')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testUpdateProvince()
    {
        DB::beginTransaction();
        $data = array(
            'id' => 5,
            'companyId' => 1900000000,
            'code' => 'BL',
            'name' => 'Bali',
            'countryId' => 5,
        );
        $this->json('POST', '/province/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('provinces', ['company_id'=>1900000000,'code' => 'BL','name'=>'Bali','country_id' => 5] );
        DB::rollback();
    }

    public function testUpdateProvinceBlankField()
    {
        $data = array(
            'id'=>5
        );
        $this->json('POST', '/province/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"id",
                "key"=>"name",
                "key"=>"code",
                "key"=>"countryId"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testUpdateProvinceDuplicateCode()
    {
        $data = array(
            'id' => 5,
            'companyId' => 1900000000,
            'code' => 'JT',
            'name' => 'Jawa Tengah',
            'countryId' => 5,
        );
        $this->json('POST', '/province/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateCode')

            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }


    public function testDeleteProvince()
    {
        DB::beginTransaction();
        $data = array(
            'id' => 4
        );
        $this->json('POST', '/province/delete', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('provinces', ['id' => 4] );
        DB::rollback();
    }

    public function testDeleteProvinceInUse()
    {
        $data = array(
            'id' => 5
        );
        $this->json('POST', '/province/delete', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.dataInUse')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }


}
