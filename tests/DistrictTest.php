<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class DistrictTest extends TestCase
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
        $this->json('POST', '/district/getAll', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "All data retrieved"
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        "id",
                        "name",
                        "cityId"
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
        $this->json('POST', '/district/getOne', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "Data retrieved"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }


    public function testSaveDistrict()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => 1900000000,
            'name' => 'Mampang',
            'cityId' => 6,
        );
        $this->json('POST', '/district/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                "data" => [
                    'id'
                ]
            ]);
        $this->seeInDatabase('districts', ['company_id'=>1900000000,'name'=>'Mampang','city_id' => 6] );
        DB::rollback();
    }

    public function testSaveDistrictBlank()
    {
        $data = array();
        $this->json('POST', '/district/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"name",
                "key"=>"cityId",
                "key"=>"companyId"
          ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveDistrictDuplicateName()
    {
        $data = array(
            'companyId' => 1900000000,
            'name' => 'Kuningan',
            'cityId' => 6,
        );
        $this->json('POST', '/district/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateName')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testUpdateDistrict()
    {
        DB::beginTransaction();
        $data = array(
            'id' => 3,
            'companyId' => 1900000000,
            'name' => 'Mampang',
            'cityId' => 5
        );
        $this->json('POST', '/district/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('districts', ['company_id'=>1900000000,'name'=>'Mampang','city_id'=>5] );
        DB::rollback();
    }

    public function testUpdateDistrictBlankField()
    {
        $data = array(
            'id' => 1,
        );
        $this->json('POST', '/district/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"id",
                "key"=>"name",
                "key"=>"cityId"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testUpdateDistrictDuplicateName()
    {
        $data = array(
            'id' => 3,
            'companyId' => 1900000000,
            'name'=>'Menteng',
            'cityId' => 6
       );
        $this->json('POST', '/district/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateName')

            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }


    public function testDeleteDistrict()
    {
        DB::beginTransaction();
        $data = array(
            'id' => 3
        );
        $this->json('POST', '/district/delete', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('districts', ['id' => 3] );
        DB::rollback();
    }

}
