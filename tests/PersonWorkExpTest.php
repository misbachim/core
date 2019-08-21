<?php

use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonWorkExpDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;


class PersonWorkExpTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->personDao = new PersonDao($this->getRequester());
        $this->personWorkExpDao = new PersonWorkExpDao($this->getRequester());

        $this->person = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'id_card' => StringHelper::randomizeStr(10),
            'eff_begin' => '2017-10-1',
            'eff_end' => '2018-10-01',
            'first_name' => StringHelper::randomizeStr(8),
            'birth_date' => '1990-01-01',
            'country_id' => 1,
            'lov_ptyp' => 'APP',
            'lov_gndr' => 'F',
            'lov_rlgn' => 'MOSLEM',
            'lov_mars' => 'SINGLE'
        ];
        $this->person['id'] = $this->personDao->save($this->person);
        $this->seeInDatabase('persons', $this->person);

        $personWorkExp = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'person_id' => $this->person['id'],
            'date_begin'=> '2017-10-01',
            'date_end' => '2018-10-01',
            'company'=> 'DRIFE',
            'job_pos'=> 'SPV',
            'job_desc'=>'Supervisor',
            'location'=>'Jakarta',
            'benefit'=>'BPJS',
            'reason'=>'Family Break'
        ];

        $this->personWorkExps = [];
        $this->personWorkExpsT = [];
        foreach (range(1, 10) as $i) {
            $personWorkExp['last_salary'] = (int) StringHelper::randomizeStr(6, false, false, true);

            $personWorkExp['id'] = $this->personWorkExpDao->save($personWorkExp);
            $this->seeInDatabase('person_work_exps', $personWorkExp);
            array_push($this->personWorkExps, $personWorkExp);

            $personWorkExpT = $this->transform($personWorkExp);
            array_push($this->personWorkExpsT, $personWorkExpT);

            unset($personWorkExp['id']);
        }
    }

    public function testGetAll()
    {
        $this->personWorkExpsT = $this->exclude($this->personWorkExpsT, [
            'tenantId',
            'personId'
        ]);

        $this->json('POST', '/personWorkExp/getAll', [
            'personId' => $this->person['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->personWorkExpsT as $personWorkExpsT) {
            foreach ($personWorkExpsT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetOne()
    {
        $this->personWorkExpsT = $this->exclude($this->personWorkExpsT, [
            'tenantId',
            'personId'
        ]);

        $this->json('POST', '/personWorkExp/getOne', [
            'id' => $this->personWorkExps[0]['id'],
            'personId' => $this->personWorkExps[0]['person_id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ]);

        foreach ($this->personWorkExpsT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $personWorkExp = $this->newPersonWorkExp();
        $personWorkExpT = $this->transform($personWorkExp);

        $this->json('POST', '/personWorkExp/save', $personWorkExpT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                'data' => [
                    'id'
                ]
            ]);

        $data = json_decode($this->response->getContent())->data;
        $personWorkExp['id'] = $data->id;
        $this->seeInDatabase('person_work_exps', $personWorkExp);
        DB::table('person_work_exps')->where('id', $personWorkExp['id'])->delete();
        $this->notSeeInDatabase('person_work_exps', $personWorkExp);
    }

    public function testSaveInvalidEffBegin()
    {
        $personWorkExp = $this->newPersonWorkExp();
        $personWorkExp['date_begin'] = '2018-12-01';
        $personWorkExpT = $this->transform($personWorkExp);

        $this->json('POST', '/personWorkExp/save', $personWorkExpT)
            ->seeJson([
                "status"=>444,
                "key"=>"dateEnd"
            ]);

        $this->notSeeInDatabase('person_work_exps', $personWorkExp);
    }

    public function testUpdate()
    {
        $personWorkExp = $this->personWorkExps[0];
        $personWorkExp['last_salary'] = (int) StringHelper::randomizeStr(6, false, false, true);
        $personWorkExpT = $this->transform($personWorkExp);

        $this->json('POST', '/personWorkExp/update', $personWorkExpT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        $this->seeInDatabase('person_work_exps', $personWorkExp);
        DB::table('person_work_exps')->where('id', $personWorkExp['id'])->delete();
        $this->notSeeInDatabase('person_work_exps', $personWorkExp);
    }

    public function tearDown()
    {
        foreach ($this->personWorkExps as $personWorkExp) {
            DB::table('person_work_exps')->where('id', $personWorkExp['id'])->delete();
            $this->notSeeInDatabase('person_work_exps', $personWorkExp);
        }
        DB::table('persons')->where('id', $this->person['id'])->delete();
        $this->notSeeInDatabase('persons', $this->person);
    }

    public function newPersonWorkExp()
    {
        $personWorkExp = $this->personWorkExps[0];
        $personWorkExp['last_salary'] = (int) StringHelper::randomizeStr(6, false, false, true);
        unset($personWorkExp['id']);

        return $personWorkExp;
    }
}
