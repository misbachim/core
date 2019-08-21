<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class LovTest extends TestCase
{
    use Testable;

    /**
     * Test getAll endpoint.
     *
     * @return void
     */

    public function testGetAll()
    {

        $this->json('POST', '/lov/getAll', [
            'companyId'=>$this->getRequester()->getCompanyId(),
            'lovTypeCode'=>'CURR'
        ])
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.allDataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        'keyData',
                        'valData',
                        'lovTypeCode',
                        'lovTypeName'
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
        $this->json('POST', '/lov/getOne',
            [
                'companyId' => $this->getRequester()->getCompanyId(),
                'keyData'=> 'IDR'
            ]
        )
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    'valData',
                    'lovTypeCode',
                    'lovTypeName'
                ]
            ]);
    }

    public function testSaveLov()
    {
        DB::beginTransaction();
        $data = array(
            'companyId'        => $this->getRequester()->getCompanyId(),
            'keyData'          => 'JPY',
            'valData'          => 'JAPAN YEN',
            'lovTypeCode'      => 'CURR'
        );
        $this->json('POST', '/lov/save', $data)
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('lovs', [
            'company_id'        =>1900000000,
            'key_data'          => 'JPY',
            'val_data'          => 'JAPAN YEN',
            'lov_type_code'     => 'CURR',
        ] );
        DB::rollback();
    }

    public function testSaveDuplicateLov()
    {
        $data = array(
            'companyId'        => $this->getRequester()->getCompanyId(),
            'keyData'          => 'IDR',
            'valData'          => 'INDONESIAN RUPIAH',
            'lovTypeCode'      => 'CURR',
        );
        $this->json('POST', '/lov/save', $data)
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
        $this->json('POST', '/lov/save', $data)
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"keyData",
                "key"=>"valData",
                "key"=>"lovTypeCode"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testUpdateLov()
    {
        DB::beginTransaction();
        $data = array(
            'companyId'     => $this->getRequester()->getCompanyId(),
            'keyData'       => 'IDR',
            'valData'          => 'RUPIAH INDO',
            'lovTypeCode'      => 'CURR',
        );
        $this->json('POST', '/lov/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('lovs', [
            'company_id'    =>  1900000000,
            'key_data'      => 'IDR',
            'val_data'      => 'RUPIAH INDO',
            'lov_type_code' => 'CURR',
        ] );

        DB::rollback();
    }


    public function testUpdateLovBlankField()
    {
        $data = array(
            'companyId'     => $this->getRequester()->getCompanyId(),
            'keyData'          => 'IDR',
            'lovTypeCode'      => 'CURR'
        );
        $this->json('POST', '/lov/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"lovTypeCode",
                "key"=>"keyData",
                "key"=>"valData"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testDeleteLov()
    {
        DB::beginTransaction();
        $data = array(
            'keyData'          => 'zz',
            'lovTypeCode'      => 'EDUL'
        );
        $this->json('POST', '/lov/delete', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('lovs', ['key_data' => 'zz', 'lov_type_code'=>'EDUL', 'is_deleteable'=>true] );
        DB::rollback();
    }

}
