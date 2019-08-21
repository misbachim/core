<?php

use App\Business\Dao\CountryDao;
use App\Business\Dao\PersonDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

/**
 * @property PersonDao personDao
 * @property array persons
 * @property array personsT
 * @property array person
 * @property CountryDao countryDao
 * @property array country
 */
class PersonTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->countryDao = new CountryDao($this->getRequester());
        $this->personDao = new PersonDao($this->getRequester());

        $this->country = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'code' => StringHelper::randomizeStr(2),
            'name' => StringHelper::randomizeStr(50),
            'dial_code' => StringHelper::randomizeStr(3, false, false, true),
            'nationality' => StringHelper::randomizeStr(10)
        ];
        $this->country['id'] = $this->countryDao->save($this->country);
        $this->seeInDatabase('countries', $this->country);

        $person = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'eff_begin' => '2017-10-1',
            'eff_end' => '2018-10-01',
            'birth_date' => '1990-01-01',
            'country_id' => $this->country['id'],
            'lov_ptyp' => 'APP',
            'lov_blod' => 'OO',
            'lov_gndr' => 'F',
            'lov_rlgn' => 'MOSLEM',
            'lov_mars' => 'SINGLE'
        ];

        $this->persons = [];
        $this->personsT = [];
        foreach (range(1, 10) as $i) {
            $person['id_card'] = StringHelper::randomizeStr(10);
            $person['first_name'] = StringHelper::randomizeStr(8);
            $person['last_name'] = StringHelper::randomizeStr(10);
            $person['birth_place'] = StringHelper::randomizeStr(50);
            $person['email'] = StringHelper::randomizeStr(16).'@example.com';
            $person['phone'] = StringHelper::randomizeStr(50, false, false, true);
            $person['mobile'] = StringHelper::randomizeStr(50, false, false, true);
            $person['hobbies'] = StringHelper::randomizeStr(255);
            $person['strength'] = StringHelper::randomizeStr(255);
            $person['weakness'] = StringHelper::randomizeStr(255);

            $person['id'] = $this->personDao->save($person);
            $this->seeInDatabase('persons', $person);
            array_push($this->persons, $person);

            $personT = $this->transform($person);
            array_push($this->personsT, $personT);

            unset($person['id']);
        }
    }

    public function testGetAll()
    {
        $this->personsT = $this->exclude($this->personsT, [
            'tenantId',
            'idCard',
            'effBegin',
            'effEnd',
            'birthPlace',
            'birthDate',
            'countryId',
            'phone',
            'hobbies',
            'strength',
            'weakness',
            'lovPtyp',
            'lovBlod',
            'lovGndr',
            'lovRlgn',
            'lovMars'
        ]);

        $this->json('POST', '/person/getAll', [])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ])->seeJsonStructure([
            'data' => [
                [
                    'positionName',
                    'locationName'
                ]
            ]
        ]);

        foreach ($this->personsT as $personT) {
            foreach ($personT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetOne()
    {
        $this->personsT = $this->exclude($this->personsT, [
            'tenantId',
            'idCard',
            'effBegin',
            'effEnd',
            'birthPlace',
            'birthDate',
            'countryId',
            'phone',
            'hobbies',
            'strength',
            'weakness',
            'lovBlod',
            'lovGndr',
            'lovRlgn',
            'lovMars'
        ]);

        $this->json('POST', '/person/getOne', [
            'id' => $this->persons[0]['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ])->seeJsonStructure([
            'data' => [
                'personType',
                'filePhoto',
                'supervisorId',
                'supervisorFirstName',
                'supervisorLastName',
                'employeeTypeName',
                'workingMonth',
                'positionCode',
                'positionName',
                'unitCode',
                'unitName',
                'locationCode',
                'locationName'
            ]
        ]);

        foreach ($this->personsT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testGetBasicInfo()
    {
        $this->personsT = $this->exclude($this->personsT, [
            'tenantId',
            'effBegin',
            'effEnd',
            'firstName',
            'lastName',
            'email',
            'phone',
            'mobile',
            'lovPtyp'
        ]);
        $this->personsT = $this->include($this->personsT, [
            'gender' => 'FEMALE',
            'maritalStatus' => 'SINGLE',
            'nationality' => $this->country['nationality'],
            'bloodType' => 'O',
            'religion' => 'MOSLEM'
        ]);

        $this->json('POST', '/person/getBasicInfo', [
            'id' => $this->persons[0]['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ])->seeJsonStructure([
            'data' => [
                'age',
                'socialMedias'
            ]
        ]);

        foreach ($this->personsT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $person = $this->newPerson();
        $personT = $this->transform($person);
        $personT['socialMedias'] = [
            [
                'lovSocm' => 'FB',
                'account' => StringHelper::randomizeStr('10')
            ]
        ];

        $this->post('/person/save', [
            'data' => json_encode($personT),
            'upload' => false
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataSaved')
        ])->seeJsonStructure([
            'data' => [
                'id'
            ]
        ]);

        $data = json_decode($this->response->getContent())->data;
        $person['id'] = $data->id;

        $personSocialMedia = [
            'lov_socm' => 'FB',
            'account' => $personT['socialMedias'][0]['account']
        ];

        $this->seeInDatabase('person_socmeds', $personSocialMedia);
        DB::table('person_socmeds')->where('person_id', $person['id'])->delete();
        $this->notSeeInDatabase('person_socmeds', $personSocialMedia);

        $this->seeInDatabase('persons', $person);
        DB::table('persons')->where('id', $person['id'])->delete();
        $this->notSeeInDatabase('persons', $person);
    }

    public function testSaveInvalidEffBegin()
    {
        $person = $this->newPerson();
        $person['eff_begin'] = '2018-12-01';
        $personT = $this->transform($person);
        $personT['socialMedias'] = [
            [
                'lovSocm' => 'FB',
                'account' => StringHelper::randomizeStr('10')
            ]
        ];

        $this->post('/person/save', [
            'data' => json_encode($personT),
            'upload' => false
        ])->seeJson([
            'status' => 444
        ])->seeJson([
            'data' => [
                [
                    'key' => 'effBegin',
                    'message' => ['The eff begin must be a date before or equal to eff end.']
                ]
            ]
        ]);

        $this->notSeeInDatabase('persons', $person);
    }

    public function testSaveInvalidEmail()
    {
        $person = $this->newPerson();
        $person['email'] = StringHelper::randomizeStr(20);
        $personT = $this->transform($person);
        $personT['socialMedias'] = [];

        $this->post('/person/save', [
            'data' => json_encode($personT),
            'upload' => false
        ])->seeJson([
            'status' => 444
        ])->seeJson([
            'data' => [
                [
                    'key' => 'email',
                    'message' => ['The email must be a valid email address.']
                ]
            ]
        ]);

        $this->notSeeInDatabase('persons', $person);
    }

    public function testSaveInvalidGender()
    {
        $person = $this->newPerson();
        $person['lov_gndr'] = 'X';
        $personT = $this->transform($person);
        $personT['socialMedias'] = [];

        $this->post('/person/save', [
            'data' => json_encode($personT),
            'upload' => false
        ])->seeJson([
            'status' => 444
        ])->seeJson([
            'data' => [
                [
                    'key' => 'lovGndr',
                    'message' => ['The selected lov gndr is invalid.']
                ]
            ]
        ]);

        $this->notSeeInDatabase('persons', $person);
    }

    public function testUpdate()
    {
        $person = $this->persons[0];
        $person['first_name'] = StringHelper::randomizeStr(50);
        $personT = $this->transform($person);
        $personT['socialMedias'] = [
            [
                'lovSocm' => 'FB',
                'account' => StringHelper::randomizeStr('10')
            ]
        ];

        $this->post('/person/update', [
            'data' => json_encode($personT),
            'upload' => false
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataUpdated')
        ]);

        $personSocialMedia = [
            'lov_socm' => 'FB',
            'account' => $personT['socialMedias'][0]['account']
        ];

        $this->seeInDatabase('person_socmeds', $personSocialMedia);
        DB::table('person_socmeds')->where('person_id', $person['id'])->delete();
        $this->notSeeInDatabase('person_socmeds', $personSocialMedia);

        $this->seeInDatabase('persons', $person);
        DB::table('persons')->where('id', $person['id'])->delete();
        $this->notSeeInDatabase('persons', $person);
    }

    public function testUpdateBasicInfo()
    {
        $person = $this->exclude($this->persons, [
            'eff_begin',
            'eff_end',
            'first_name',
            'last_name',
            'email',
            'mobile',
            'phone'
        ])[0];
        $person['birth_place'] = StringHelper::randomizeStr(50);
        $personT = $this->transform($person);
        $personT['socialMedias'] = [
            [
                'lovSocm' => 'FB',
                'account' => StringHelper::randomizeStr('10')
            ]
        ];

        $this->post('/person/updateBasicInfo', $personT)->seeJson([
            'status' => 200,
            'message' => trans('messages.dataUpdated')
        ]);

        $personSocialMedia = [
            'lov_socm' => 'FB',
            'account' => $personT['socialMedias'][0]['account']
        ];

        $this->seeInDatabase('person_socmeds', $personSocialMedia);
        DB::table('person_socmeds')->where('person_id', $person['id'])->delete();
        $this->notSeeInDatabase('person_socmeds', $personSocialMedia);

        $this->seeInDatabase('persons', $person);
        DB::table('persons')->where('id', $person['id'])->delete();
        $this->notSeeInDatabase('persons', $person);
    }

    public function tearDown()
    {
        foreach ($this->persons as $person) {
            DB::table('persons')->where('id', $person['id'])->delete();
            $this->notSeeInDatabase('persons', $person);
        }
        DB::table('countries')->where('id', $this->country['id'])->delete();
        $this->notSeeInDatabase('countries', $this->country);
    }

    public function newPerson()
    {
        $person = $this->persons[0];
        $person['first_name'] = StringHelper::randomizeStr(50);
        unset($person['id']);

        return $person;
    }
}
