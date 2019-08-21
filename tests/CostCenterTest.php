<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class CostCenterTest extends TestCase
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
        $this->json('POST', '/costCenter/getAll', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "All data retrieved"
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        'code',
                        'effBegin',
                        'effEnd',
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
            'code' => 'XV'
        );
        $this->json('POST', '/costCenter/getOne', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "Data retrieved"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }


    public function testSaveCostCenter()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'name' => 'Cost Center 2',
            'code' => 'CC2'
        );
        $this->json('POST', '/costCenter/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('cost_centers', ['company_id'=>1900000000,'code' => 'CC2','name'=>'Cost Center 2','eff_begin'=> '2017-10-13','eff_end'=> '2099-12-31'] );
        DB::rollback();
    }

    public function testSaveCostCenterBlank()
    {
        $data = array();
        $this->json('POST', '/costCenter/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"name",
                "key"=>"code",
                "key"=>"effBegin",
                "key"=>"effBegin",
                "key"=>"companyId"
          ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveCostCenterDuplicateCode()
    {
        $data = array(
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'name' => 'Cost Center 2',
            'code' => 'XV'
        );
        $this->json('POST', '/costCenter/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateCode')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveCostCenterDuplicateName()
    {
        $data = array(
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'name' => 'Cost Center 1',
            'code' => 'CC'
        );
        $this->json('POST', '/costCenter/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateName')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveCostCenterBeginIsBiggerThanEnd()
    {
        $data = array(
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2009-12-31',
            'name' => 'Cost Center 3',
            'code' => 'CC3'
        );
        $this->json('POST', '/costCenter/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key" => "effEnd"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testUpdateCostCenter()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => 1900000000,
            'code' => 'XV',
            'name' => 'YYY',
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31'
        );
        $this->json('POST', '/costCenter/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('cost_centers', ['company_id'=>1900000000,'code' => 'XV','name'=>'YYY','eff_begin'=> '2017-10-13','eff_end'=> '2099-12-31'] );
        DB::rollback();
    }

    public function testUpdateCityBlankField()
    {
        $data = array(
            'companyId' => 1900000000,
            'code' => 'XV'
        );
        $this->json('POST', '/costCenter/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"name",
                "key"=>"code",
                "key"=>"effBegin",
                "key"=>"effBegin"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }


    public function testUpdateCostCenterDuplicateName()
    {

        $data = array(
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'name' => 'Cost Center 1',
            'code' => 'XXI'
        );
        $this->json('POST', '/costCenter/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateName')

            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testDeleteCostCenter()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => 1900000000,
            'code' => 'XV'
        );
        $this->json('POST', '/costCenter/delete', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('cost_centers', ['company_id' => 1900000000,'code' => 'XV' ] );
        DB::rollback();
    }


}
