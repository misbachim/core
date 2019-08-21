<?php

use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonMembershipDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

/**
 * @property PersonMembershipDao personMembershipDao
 * @property array personMemberships
 * @property array personMembershipsT
 * @property PersonDao personDao
 * @property array person
 */
class PersonMembershipTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->personDao = new PersonDao($this->getRequester());
        $this->personMembershipDao = new PersonMembershipDao($this->getRequester());

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

        $personMembership = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'person_id' => $this->person['id'],
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'lov_mbty' => 'BPJSKK'
        ];

        $this->personMemberships = [];
        $this->personMembershipsT = [];
        foreach (range(1, 10) as $i) {
            $personMembership['acc_number'] = StringHelper::randomizeStr(50);

            $personMembership['id'] = $this->personMembershipDao->save($personMembership);
            $this->seeInDatabase('person_memberships', $personMembership);
            array_push($this->personMemberships, $personMembership);

            $personMembershipT = $this->transform($personMembership);
            array_push($this->personMembershipsT, $personMembershipT);

            unset($personMembership['id']);
        }
    }

    public function testGetAll()
    {
        $this->personMembershipsT = $this->exclude($this->personMembershipsT, [
            'tenantId',
            'companyId',
            'personId',
            'lovMbty'
        ]);
        $this->personMembershipsT = $this->include($this->personMembershipsT, [
            'membership' => 'BPJS KECELAKAAN KERJA'
        ]);

        $this->json('POST', '/personMembership/getAll', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'personId' => $this->person['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->personMembershipsT as $personMembershipT) {
            foreach ($personMembershipT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetOne()
    {
        $this->personMembershipsT = $this->exclude($this->personMembershipsT, [
            'tenantId',
            'companyId',
            'personId'
        ]);

        $this->json('POST', '/personMembership/getOne', [
            'id' => $this->personMemberships[0]['id'],
            'companyId' => $this->personMemberships[0]['company_id'],
            'personId' => $this->personMemberships[0]['person_id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ]);

        foreach ($this->personMembershipsT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $personMembership = $this->newPersonMembership();
        $personMembershipT = $this->transform($personMembership);

        $this->json('POST', '/personMembership/save', $personMembershipT)
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
        $personMembership['id'] = $data->id;
        $this->seeInDatabase('person_memberships', $personMembership);
        DB::table('person_memberships')->where('id', $personMembership['id'])->delete();
        $this->notSeeInDatabase('person_memberships', $personMembership);
    }

    public function testSaveFloatCompanyId()
    {
        $personMembership = $this->newPersonMembership();
        $personMembership['company_id'] = 1900000000.1;
        $personMembershipT = $this->transform($personMembership);

        $this->json('POST', '/personMembership/save', $personMembershipT)
            ->seeJson([
                'status' => 444
            ])
            ->seeJson([
                'data' => [
                    [
                        'key' => 'companyId',
                        'message' => ['The company id must be an integer.']
                    ]
                ]
            ]);

        unset($personMembership['company_id']);
        $this->notSeeInDatabase('person_memberships', $personMembership);
    }

    public function testSaveInvalidEffBegin()
    {
        $personMembership = $this->newPersonMembership();
        $personMembership['eff_begin'] = '2018-12-01';
        $personMembershipT = $this->transform($personMembership);

        $this->json('POST', '/personMembership/save', $personMembershipT)
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

        $this->notSeeInDatabase('person_memberships', $personMembership);
    }

    public function testUpdate()
    {
        $personMembership = $this->personMemberships[0];
        $personMembership['acc_number'] = StringHelper::randomizeStr(50);
        $personMembershipT = $this->transform($personMembership);

        $this->json('POST', '/personMembership/update', $personMembershipT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        $this->seeInDatabase('person_memberships', $personMembership);
        DB::table('person_memberships')->where('id', $personMembership['id'])->delete();
        $this->notSeeInDatabase('person_memberships', $personMembership);
    }

    public function tearDown()
    {
        foreach ($this->personMemberships as $personMembership) {
            DB::table('person_memberships')->where('id', $personMembership['id'])->delete();
            $this->notSeeInDatabase('person_memberships', $personMembership);
        }
        DB::table('persons')->where('id', $this->person['id'])->delete();
        $this->notSeeInDatabase('persons', $this->person);
    }

    public function newPersonMembership()
    {
        $personMembership = $this->personMemberships[0];
        $personMembership['acc_number'] = StringHelper::randomizeStr(50);
        unset($personMembership['id']);

        return $personMembership;
    }
}
