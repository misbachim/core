<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;


class PersonEducationTest extends TestCase
{
    use Testable;

    /**
     * Test getAll endpoint.
     *
     * @return void
     */

    public function testGetAll()
    {

        $this->json('POST', '/personEducation/getAll', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'personId' => 3
        ])
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.allDataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        'effBegin',
                        'effEnd',
                        'lovEdul',
                        'institution',
                        'subject',
                        'grade',
                        'maxGrade',
                        'yearBegin',
                        'yearEnd'
                    ]
                ]
            ]);
    }


    public function testGetOne()
    {
        $this->json('POST', '/personEducation/getOne', [
            'companyId'=>$this->getRequester()->getCompanyId(),
            'personId'=>3,
            'id'=>2
        ])
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    'effBegin',
                    'effEnd',
                    'lovEdul',
                    'institution',
                    'subject',
                    'grade',
                    'maxGrade',
                    'yearBegin',
                    'yearEnd'
                ]
            ]);

    }


    public function testSave()
    {
        DB::beginTransaction();
        $data = array(
            'personId' => 3,
            'effBegin' => '2017-11-21',
            'effEnd' => '2099-11-21',
            'lovEdul' => 'S1',
            'institution' => 'World Class University',
            'subject' => 'Mechanical Engineering',
            'grade' => 3.71,
            'maxGrade' => 4.00,
            'yearBegin' => 2010,
            'yearEnd' => 2014
        );
        $this->json('POST', '/personEducation/save', $data)
            ->seeJson([
                "message" => trans("messages.dataSaved"),
                "status" => 200
            ])
            ->seeJsonStructure([
                "data" => [
                    "id"
                ]
            ]);

        $this->seeInDatabase('person_educations', [
            'eff_begin' => '2017-11-21',
            'eff_end' => '2099-11-21',
            'lov_edul' => 'S1',
            'institution'=>'World Class University',
            'subject'=>'Mechanical Engineering',
            'grade'=>3.71,
            'max_grade' => 4.00,
            'year_begin' => 2010,
            'year_end' => 2014
        ] );

        DB::rollback();

    }


    public function testSaveBlankField()
    {
        $data = array(
            'personId' => 3,
        );
        $this->json('POST', '/personEducation/save', $data)
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"effBegin",
                "key"=>"effEnd",
                "key"=>"institution",
                "key"=>"subject",
                "key"=>"grade",
                "key"=>"maxGrade",
                "key"=>"lovEdul",
                "key"=>"yearBegin",
                "key"=>"yearEnd"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testUpdateLov()
    {
        DB::beginTransaction();
        $data = array(
            'id'=>2,
            'personId' => 3,
            'effBegin' => '2017-11-21',
            'effEnd' => '2099-11-21',
            'lovEdul' => 'S2',
            'institution' => 'World Class University',
            'subject' => 'Master of Engineering',
            'grade' => 4.00,
            'maxGrade' => 4.00,
            'yearBegin' => 2014,
            'yearEnd' => 2017
        );
        $this->json('POST', '/personEducation/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('person_educations', [
            'eff_begin' => '2017-11-21',
            'eff_end' => '2099-11-21',
            'lov_edul' => 'S2',
            'institution'=>'World Class University',
            'subject' => 'Master of Engineering',
            'grade'=> 4.00,
            'max_grade' => 4.00,
            'year_begin' => 2014,
            'year_end' => 2017
        ] );

        DB::rollback();
    }

    public function testUpdateLovBlankField()
    {
        $data = array(
            'id'=>2,
            'personId' => 3
        );
        $this->json('POST', '/personEducation/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"effBegin",
                "key"=>"effEnd",
                "key"=>"institution",
                "key"=>"subject",
                "key"=>"grade",
                "key"=>"maxGrade",
                "key"=>"lovEdul",
                "key"=>"yearBegin",
                "key"=>"yearEnd"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testDeleteLov()
    {
        DB::beginTransaction();
        $data = array(
            'id'=>2,
            'personId' => 3
        );
        $this->json('POST', '/personEducation/delete', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('person_educations', ['id' => 2, 'person_id'=>3] );
        DB::rollback();
    }
}
