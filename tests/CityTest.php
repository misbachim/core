<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CityTest extends TestCase
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
        $this->json('POST', '/city/getAll', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "All data retrieved"
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        "id",
                        "code",
                        "name",
                        "dialCode",
                        "provinceId",
                        "provinceName"
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
            'id' => 6
        );
        $this->json('POST', '/city/getOne', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "Data retrieved"
            ])
            ->seeJsonStructure([
                "data" => [
                    "id",
                    "code",
                    "name",
                    "dialCode",
                    "provinceId",
                    "provinceName"
                ]
            ]);
    }


    public function testSaveCity()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => 1900000000,
            'code' => 'BDG',
            'name' => 'Bandung',
            'dialCode'=>'+6222',
            'provinceId' => 5
        );
        $this->json('POST', '/city/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                "data" => [
                    'id'
                ]
            ]);
        $this->seeInDatabase('cities', ['company_id'=>1900000000,'code' => 'BDG','name'=>'Bandung','dial_code'=>'+6222','province_id' => 5] );
        DB::rollback();
    }

    public function testSaveCityBlank()
    {
        $data = array();
        $this->json('POST', '/city/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"name",
                "key"=>"code",
                "key"=>"dialCode",
                "key"=>"provinceId",
                "key"=>"companyId"
          ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveCityDuplicateCode()
    {
        $data = array(
            'companyId' => 1900000000,
            'code' => 'JKT',
            'name' => 'Jakarta',
            'dialCode'=>'+6221',
            'provinceId' => 5,
        );
        $this->json('POST', '/city/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateCode')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testUpdateCity()
    {
        DB::beginTransaction();
        $data = array(
            'id' => 1,
            'companyId' => 1900000000,
            'code' => 'BD',
            'name' => 'Bandung',
            'dialCode'=>'+6222',
            'provinceId' => 5
        );
        $this->json('POST', '/city/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('cities', ['company_id'=>1900000000,'code' => 'BD','name'=>'Bandung','province_id' => 5,'dial_code'=>'+6222'] );
        DB::rollback();
    }

    public function testUpdateCityBlankField()
    {
        $data = array(
            'id' => 1,
        );
        $this->json('POST', '/city/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"id",
                "key"=>"name",
                "key"=>"code",
                "key"=>"provinceId",
                "key"=>"dialCode"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testUpdateCityDuplicateCode()
    {
        $data = array(
            'id' => 1,
            'companyId' => 1900000000,
            'code'=>'JKT',
            'name'=>'Jakarta',
            'dialCode'=>'+6221',
            'provinceId'=>5,
        );
        $this->json('POST', '/city/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateCode')

            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }


    public function testDeleteCity()
    {
        DB::beginTransaction();
        $data = array(
            'id' => 1
        );
        $this->json('POST', '/city/delete', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('cities', ['id' => 1] );
        DB::rollback();
    }

    public function testDeleteCityInUse()
    {
        $data = array(
            'id' => 6
        );
        $this->json('POST', '/city/delete', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.dataInUse')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }
}
