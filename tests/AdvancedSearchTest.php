<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use App\Business\Dao\PersonDao;
use App\Business\Dao\UnitDao;
use App\Business\Dao\JobDao;
use App\Business\Dao\PositionDao;
use App\Business\Dao\AssignmentDao;

class AdvancedSearchTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->persons = [
            [
                'tenant_id' => $this->getRequester()->getTenantId(),
                'id_card' => '463856382492',
                'eff_begin' => '2017-04-01',
                'eff_end' => '2018-05-23',
                'first_name' => 'Max',
                'last_name' => 'Magic',
                'birth_place' => 'Japan',
                'birth_date' => '1990-01-01',
                'die_date' => '9999-09-09',
                'email' => 'max@gmail.com',
                'mobile' => '+6263826832732',
                'social_media' => '@maxmagic',
                'country_id' => 1,
                'lov_ptyp' => 'EMP',
                'lov_blod' => 'A',
                'lov_gndr' => 'M',
                'lov_rlgn' => 'MOSLEM',
                'lov_mars' => 'MARRIED'
            ]
        ];
        $this->personDao = new PersonDao($this->getRequester());

        $this->personsT = [];
        foreach ($this->persons as &$person) {
            $person['id'] = $this->personDao->save($person);
            $this->seeInDatabase('persons', $person);

            $personT = $this->transform($person);
            array_push($this->personsT, $personT);
        }

        $this->dataAccess = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'user_id' => $this->getRequester()->getUserId(),
            'menu_code' => 'EMT01',
            'data_access_value' => '%',
            'privilege' => 'A'
        ];

        $this->unitType = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'code' => StringHelper::randomizeStr(5),
            'name' => StringHelper::randomizeStr(50),
            'unit_level' => 1
        ];
        $this->unitTypeDao = new UnitDao($this->getRequester());
        $this->unitTypeDao->save($this->unitType);
        $this->seeInDatabase('unit_types', $this->unitType);

        $this->unit = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'code' => 'U45',
            'name' => 'Sample Unit 45',
            'unit_type_code' => $this->unitType['code']
        ];
        $this->unitDao = new UnitDao($this->getRequester());
        $this->unit['id'] = $this->unitDao->save($this->unit);
        $this->seeInDatabase('units', $this->unit);
        DB::table('data_access_uni')->insert($this->dataAccess);
        $this->seeInDatabase('data_access_uni', $this->dataAccess);

        $this->job = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'job_family_code' => 1,
            'code' => 'J34',
            'description' => 'blah blah blah',
            'ordinal' => 1
        ];
        $this->jobDao = new JobDao($this->getRequester());
        $this->job['id'] = $this->jobDao->save($this->job);
        $this->seeInDatabase('jobs', $this->job);
        DB::table('data_access_job')->insert($this->dataAccess);
        $this->seeInDatabase('data_access_job', $this->dataAccess);

        $this->position = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'code' => 'P45',
            'description' => 'blah blah blah',
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'pay_rate_id' => 1,
            'cost_center_code' => 'CC',
            'is_head' => true,
            'is_single' => false,
            'job_id' => $this->job['id'],
            'unit_id' => $this->unit['id']
        ];
        $this->positionDao = new PositionDao($this->getRequester());
        $this->position['id'] = $this->positionDao->save($this->position);
        $this->seeInDatabase('positions', $this->position);
        DB::table('data_access_pos')->insert($this->dataAccess);
        $this->seeInDatabase('data_access_pos', $this->dataAccess);

        $this->assignment = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'id' => 123456,
            'em_person_id' => $this->persons[0]['id'],
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'is_primary' => true,
            'employee_id' => '463856382492',
            'employee_status_code' => 'MANAGER',
            'unit_id' => $this->unit['id'],
            'job_id' => $this->job['id'],
            'position_id' => $this->position['id'],
            'cost_center_code' => 'CC',
            'grade_code' => 'A',
            'location_id' => 1,
            'lov_asta' => 'R',
            'supervisor_id' => 2
        ];
        $this->assignmentDao = new AssignmentDao($this->getRequester());
        $this->assignment['id'] = $this->assignmentDao->save($this->assignment);
        $this->seeInDatabase('assignments', $this->assignment);

        $this->searchData = [
            'selectedFields' => ['Id', 'Name', 'Age', 'Birth Place'],
            'criteria' => [
                [
                    'field' => 'Birth Place',
                    'conj' => 'c',
                    'val' => 'Japan'
                ],
                [
                    'field' => 'Begin Date',
                    'conj' => 'b',
                    'val' => ['2017-03-28', '2017-05-06']
                ],
                [
                    'field' => 'Marital Status',
                    'conj' => '=',
                    'val' => 'MARRIED'
                ],
                [
                    'field' => 'Id',
                    'conj' => 'ni',
                    'val' => [2, 3]
                ],
                [
                    'field' => 'Email',
                    'conj' => 'nc',
                    'val' => 'yahoo'
                ]
            ]
        ];
    }

    public function testSearchPerson()
    {
        $this->json('POST', '/person/search', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'menuCode' => 'EMT01',
            'searchData' => $this->searchData
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved'),
            'data' => [
                [
                    'age' => '27 years 10 mons 6 days',
                    'birthPlace' => 'Japan',
                    'name' => 'Max Magic',
                    'id' => $this->persons[0]['id']
                ]
            ]
        ]);
    }

    public function testSearchNotPerson()
    {
        $this->searchData['criteria'] = [
            [
                'field' => 'Begin Date',
                'conj' => 'nb',
                'val' => ['2017-03-28', '2017-05-06']
            ],
            [
                'field' => 'Blood Type',
                'conj' => '!=',
                'val' => 'A'
            ]
        ];
        $this->json('POST', '/person/search', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'menuCode' => 'EMT01',
            'searchData' => $this->searchData
        ])
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.allDataRetrieved')
            ])->seeJson([
                [
                    'age' => '27 years 10 mons 6 days',
                    'birthPlace' => 'Japan',
                    'name' => 'Max Magic',
                    'id' => $this->persons[0]['id']
                ]
            ], $negate = true);
    }

    public function tearDown()
    {
        DB::table('assignments')->where('id', $this->assignment['id'])->delete();
        $this->notSeeInDatabase('assignments', $this->assignment);
        DB::table('positions')->where('id', $this->position['id'])->delete();
        $this->notSeeInDatabase('positions', $this->position);
        DB::table('jobs')->where('id', $this->job['id'])->delete();
        $this->notSeeInDatabase('jobs', $this->job);
        DB::table('units')->where('id', $this->unit['id'])->delete();
        $this->notSeeInDatabase('units', $this->unit);

        foreach ($this->persons as $person) {
            DB::table('persons')->where('id', $person['id'])->delete();
            $this->notSeeInDatabase('persons', $person);
        }
    }
}
