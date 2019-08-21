<?php

use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonOrganizationDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

/**
 * @property PersonOrganizationDao personOrganizationDao
 * @property array personOrganizations
 * @property array personOrganizationsT
 * @property PersonDao personDao
 * @property array person
 */
class PersonOrganizationTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->personDao = new PersonDao($this->getRequester());
        $this->personOrganizationDao = new PersonOrganizationDao($this->getRequester());

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

        $personOrganization = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'person_id' => $this->person['id'],
            'year_begin' => 2006,
            'year_end' => 2010
        ];

        $this->personOrganizations = [];
        $this->personOrganizationsT = [];
        foreach (range(1, 10) as $i) {
            $personOrganization['institution'] = StringHelper::randomizeStr(50);
            $personOrganization['description'] = StringHelper::randomizeStr(255);

            $personOrganization['id'] = $this->personOrganizationDao->save($personOrganization);
            $this->seeInDatabase('person_organizations', $personOrganization);
            array_push($this->personOrganizations, $personOrganization);

            $personOrganizationT = $this->transform($personOrganization);
            array_push($this->personOrganizationsT, $personOrganizationT);

            unset($personOrganization['id']);
        }
    }

    public function testGetAll()
    {
        $this->personOrganizationsT = $this->exclude($this->personOrganizationsT, [
            'tenantId',
            'personId'
        ]);

        $this->json('POST', '/personOrganization/getAll', [
            'personId' => $this->person['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->personOrganizationsT as $personOrganizationT) {
            foreach ($personOrganizationT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetOne()
    {
        $this->personOrganizationsT = $this->exclude($this->personOrganizationsT, [
            'tenantId',
            'personId'
        ]);

        $this->json('POST', '/personOrganization/getOne', [
            'id' => $this->personOrganizations[0]['id'],
            'personId' => $this->person['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ]);

        foreach ($this->personOrganizationsT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $personOrganization = $this->newPersonOrganization();
        $personOrganizationT = $this->transform($personOrganization);

        $this->json('POST', '/personOrganization/save', $personOrganizationT)
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
        $personOrganization['id'] = $data->id;
        $this->seeInDatabase('person_organizations', $personOrganization);
        DB::table('person_organizations')->where('id', $personOrganization['id'])->delete();
        $this->notSeeInDatabase('person_organizations', $personOrganization);
    }

    public function testSaveInvalidYearBegin()
    {
        $personOrganization = $this->newPersonOrganization();
        $personOrganization['year_begin'] = 2020;
        $personOrganization['year_end'] = 2019;
        $personOrganizationT = $this->transform($personOrganization);

        $this->json('POST', '/personOrganization/save', $personOrganizationT)
            ->seeJson([
                'status' => 444
            ])
            ->seeJson([
                'data' => [
                    [
                        'key' => 'yearBegin',
                        'message' => ['year begin must be less than or equal to yearEnd.']
                    ]
                ]
            ]);

        $this->notSeeInDatabase('person_organizations', $personOrganization);
    }

    public function testUpdate()
    {
        $personOrganization = $this->personOrganizations[0];
        $personOrganization['year_end'] = 2012;
        $personOrganizationT = $this->transform($personOrganization);

        $this->json('POST', '/personOrganization/update', $personOrganizationT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        $this->seeInDatabase('person_organizations', $personOrganization);
        DB::table('person_organizations')->where('id', $personOrganization['id'])->delete();
        $this->notSeeInDatabase('person_organizations', $personOrganization);
    }

    public function tearDown()
    {
        foreach ($this->personOrganizations as $personOrganization) {
            DB::table('person_organizations')->where('id', $personOrganization['id'])->delete();
            $this->notSeeInDatabase('person_organizations', $personOrganization);
        }
        DB::table('persons')->where('id', $this->person['id'])->delete();
        $this->notSeeInDatabase('persons', $this->person);
    }

    public function newPersonOrganization()
    {
        $personOrganization = $this->personOrganizations[0];
        $personOrganization['institution'] = StringHelper::randomizeStr(50);
        $personOrganization['description'] = StringHelper::randomizeStr(255);
        unset($personOrganization['id']);

        return $personOrganization;
    }
}
