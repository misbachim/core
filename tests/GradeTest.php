<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class GradeTest extends TestCase
{
    use Testable;

    /**
     * Test getAll endpoint.
     *
     * @return void
     */

    public function testGetAll()
    {

        $this->json('POST', '/grade/getAll', [
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
                        'ordinal',
                        'bottomRate',
                        'topRate'
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
        $this->json('POST', '/grade/getOne',
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
                "data" => []
            ]);
    }

    public function testSaveGrade()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
            'effBegin'  => '2017-10-13',
            'effEnd'    => '2099-12-31',
            'name'       => 'GRADE2',
            'code'       => 'GRD2',
            'ordinal'    => 1,
            'workMonth' => 12,
            'bottomRate'=> 1800000,
            'topRate'   => 220000
        );
        $this->json('POST', '/grade/save', $data)
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('grades', [
            'company_id'=>1900000000,
            'eff_begin'  => '2017-10-13',
            'eff_end'    => '2099-12-31',
            'name'       => 'GRADE2',
            'code'       => 'GRD2',
            'ordinal'    => 1,
            'work_month' => 12,
            'bottom_rate'=> 1800000,
            'mid_rate'   => 2000000,
            'top_rate'   => 2200000
        ] );
        DB::rollback();
    }

    public function testSaveGradeWrongRate()
    {
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
            'effBegin'  => '2017-10-13',
            'effEnd'    => '2099-12-31',
            'name'       => 'GRADE2',
            'code'       => 'GRD2',
            'ordinal'    => 1,
            'workMonth' => 12,
            'bottomRate'=> 2100000,
            'midRate'   => 2000000,
            'topRate'   => 1900000
        );
        $this->json('POST', '/grade/save', $data)
            ->seeJson([
                "status" => 444,
                "key"=>"workMonth",
                "key"=>"bottomRate",
                "key"=>"topRate"
            ]);
   }

    public function testSaveDuplicateGrade()
    {
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
            'effBegin'  => '2017-10-13',
            'effEnd'    => '2099-12-31',
            'name'       => 'GRADE1',
            'code'       => 'GRD1',
            'ordinal'    => 1,
            'workMonth' => 12,
            'bottomRate'=> 1800000,
            'midRate'   => 2000000,
            'topRate'   => 2300000
        );
        $this->json('POST', '/grade/save', $data)
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateCode')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testSaveGradeBlankField()
    {
        $data = array();
        $this->json('POST', '/grade/save', $data)
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"effBegin",
                "key"=>"effEnd",
                "key"=>"code",
                "key"=>"name",
                "key"=>"ordinal",
                "key"=>"workMonth",
                "key"=>"bottomRate",
                "key"=>"topRate"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testSaveGradeBeginIsBiggerThanEnd()
    {
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2009-12-31',
            'name'       => 'GRADE2',
            'code'       => 'GRD2',
            'ordinal'    => 1,
            'workMonth' => 12,
            'bottomRate'=> 1800000,
            'midRate'   => 2000000,
            'topRate'   => 2300000
        );
        $this->json('POST', '/location/save', $data)
            ->seeJson([
                "status" => 444,
                "key" => "effEnd"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testUpdateGrade()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => $this->getRequester()->getCompanyId(),
            'effBegin'  => '2017-10-13',
            'effEnd'    => '2099-12-31',
            'name'      => 'GRADE1',
            'code'      => 'GRD1',
            'id'        => 9,
            'ordinal'   => 1,
            'workMonth' => 12,
            'bottomRate'=> 1800000,
            'topRate'   => 2200000
        );
        $this->json('POST', '/grade/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('grades', [
            'company_id'=>1900000000,
            'eff_begin'  => '2017-10-13',
            'eff_end'    => '2099-12-31',
            'name'       => 'GRADE1',
            'code'       => 'GRD1',
            'id'         => 9,
            'ordinal'    => 1,
            'work_month' => 12,
            'bottom_rate'=> 1800000,
            'mid_rate'   => 2000000,
            'top_rate'   => 2200000] );

        DB::rollback();
    }

    public function testUpdateGradeBlankField()
    {
        $data = array(
            'id'        => 9,
        );
        $this->json('POST', '/grade/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"effBegin",
                "key"=>"effEnd",
                "key"=>"code",
                "key"=>"name",
                "key"=>"ordinal",
                "key"=>"workMonth",
                "key"=>"bottomRate",
                "key"=>"topRate"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }
}
