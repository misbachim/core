<?php

use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonFamilyDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

/**
 * @property PersonFamilyDao personFamilyDao
 * @property array personFamilies
 * @property array personFamiliesT
 * @property PersonDao personDao
 * @property array person
 */
class PersonFamilyTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->personDao = new PersonDao($this->getRequester());
        $this->personFamilyDao = new PersonFamilyDao($this->getRequester());

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

        $personFamily = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'person_id' => $this->person['id'],
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'lov_famr' => 'BRO',
            'lov_gndr' => 'F',
            'lov_edul' => 'DIP',
            'birth_date' => '1990-12-01'
        ];

        $this->personFamilies = [];
        $this->personFamiliesT = [];
        foreach (range(1, 10) as $i) {
            $personFamily['name'] = StringHelper::randomizeStr(50);
            $personFamily['occupation'] = StringHelper::randomizeStr(50);
            $personFamily['description'] = StringHelper::randomizeStr(255);

            $personFamily['id'] = $this->personFamilyDao->save($personFamily);
            $this->seeInDatabase('person_families', $personFamily);
            array_push($this->personFamilies, $personFamily);

            $personFamilyT = $this->transform($personFamily);
            array_push($this->personFamiliesT, $personFamilyT);

            unset($personFamily['id']);
        }
    }

    public function testGetAll()
    {
        $this->personFamiliesT = $this->exclude($this->personFamiliesT, [
            'tenantId',
            'personId',
            'lovFamr',
            'lovGndr',
            'lovEdul',
            'description',
            'effBegin',
            'effEnd',
            'birthDate'
        ]);
        $this->personFamiliesT = $this->include($this->personFamiliesT, [
            'relationship' => 'BROTHER',
            'education' => 'DIPLOMA 12'
        ]);

        $this->json('POST', '/personFamily/getAll', [
            'personId' => $this->person['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ])->seeJsonStructure([
            'data' => [
                [
                    'age'
                ]
            ]
        ]);

        foreach ($this->personFamiliesT as $personFamilyT) {
            foreach ($personFamilyT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetOne()
    {
        $this->personFamiliesT = $this->exclude($this->personFamiliesT, [
            'tenantId',
            'personId'
        ]);

        $this->json('POST', '/personFamily/getOne', [
            'id' => $this->personFamilies[0]['id'],
            'personId' => $this->personFamilies[0]['person_id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ]);

        foreach ($this->personFamiliesT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $personFamily = $this->newPersonFamily();
        $personFamilyT = $this->transform($personFamily);

        $this->json('POST', '/personFamily/save', $personFamilyT)
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
        $personFamily['id'] = $data->id;
        $this->seeInDatabase('person_families', $personFamily);
        DB::table('person_families')->where('id', $personFamily['id'])->delete();
        $this->notSeeInDatabase('person_families', $personFamily);
    }

    public function testSaveInvalidEffBegin()
    {
        $personFamily = $this->newPersonFamily();
        $personFamily['eff_begin'] = '2018-12-01';
        $personFamilyT = $this->transform($personFamily);

        $this->json('POST', '/personFamily/save', $personFamilyT)
            ->seeJson([
                'status' => 444
            ])
            ->seeJson([
                'data' => [
                    [
                        'key' => 'effBegin',
                        'message' => ['The eff begin must be a date before or equal to eff end.']
                    ]
                ]
            ]);

        $this->notSeeInDatabase('person_families', $personFamily);
    }

    public function testUpdate()
    {
        $personFamily = $this->personFamilies[0];
        $personFamily['name'] = StringHelper::randomizeStr(50);
        $personFamilyT = $this->transform($personFamily);

        $this->json('POST', '/personFamily/update', $personFamilyT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        $this->seeInDatabase('person_families', $personFamily);
        DB::table('person_families')->where('id', $personFamily['id'])->delete();
        $this->notSeeInDatabase('person_families', $personFamily);
    }

    public function tearDown()
    {
        foreach ($this->personFamilies as $personFamily) {
            DB::table('person_families')->where('id', $personFamily['id'])->delete();
            $this->notSeeInDatabase('person_families', $personFamily);
        }
        DB::table('persons')->where('id', $this->person['id'])->delete();
        $this->notSeeInDatabase('persons', $this->person);
    }

    public function newPersonFamily()
    {
        $personFamily = $this->personFamilies[0];
        $personFamily['name'] = StringHelper::randomizeStr(50);
        unset($personFamily['id']);

        return $personFamily;
    }
}
