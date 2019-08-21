<?php

use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonLanguageDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

/**
 * @property PersonLanguageDao personLanguageDao
 * @property array personLanguages
 * @property array personLanguagesT
 * @property PersonDao personDao
 * @property array person
 */
class PersonLanguageTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->personDao = new PersonDao($this->getRequester());
        $this->personLanguageDao = new PersonLanguageDao($this->getRequester());

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

        $personLanguage = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'person_id' => $this->person['id'],
            'lov_lang' => 'EN',
            'is_native' => true
        ];

        $this->personLanguages = [];
        $this->personLanguagesT = [];
        foreach (range(1, 10) as $i) {
            $personLanguage['writing'] = (int) StringHelper::randomizeStr(2, false, false, true);
            $personLanguage['speaking'] = (int) StringHelper::randomizeStr(2, false, false, true);
            $personLanguage['listening'] = (int) StringHelper::randomizeStr(2, false, false, true);

            $personLanguage['id'] = $this->personLanguageDao->save($personLanguage);
            $this->seeInDatabase('person_languages', $personLanguage);
            array_push($this->personLanguages, $personLanguage);

            $personLanguageT = $this->transform($personLanguage);
            array_push($this->personLanguagesT, $personLanguageT);

            unset($personLanguage['id']);
        }
    }

    public function testGetAll()
    {
        $this->personLanguagesT = $this->exclude($this->personLanguagesT, [
            'tenantId',
            'personId',
            'isNative',
            'lovLang'
        ]);
        $this->personLanguagesT = $this->include($this->personLanguagesT, [
            'language' => 'ENGLISH'
        ]);

        $this->json('POST', '/personLanguage/getAll', [
            'personId' => $this->person['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->personLanguagesT as $personLanguageT) {
            foreach ($personLanguageT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetOne()
    {
        $this->personLanguagesT = $this->exclude($this->personLanguagesT, [
            'tenantId',
            'personId'
        ]);

        $this->json('POST', '/personLanguage/getOne', [
            'id' => $this->personLanguages[0]['id'],
            'personId' => $this->person['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ]);

        foreach ($this->personLanguagesT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $personLanguage = $this->newPersonLanguage();
        $personLanguageT = $this->transform($personLanguage);

        $this->json('POST', '/personLanguage/save', $personLanguageT)
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
        $personLanguage['id'] = $data->id;
        $this->seeInDatabase('person_languages', $personLanguage);
        DB::table('person_languages')->where('id', $personLanguage['id'])->delete();
        $this->notSeeInDatabase('person_languages', $personLanguage);
    }

    public function testUpdate()
    {
        $personLanguage = $this->personLanguages[0];
        $personLanguage['writing'] = 60;
        $personLanguage['speaking'] = 90;
        $personLanguage['listening'] = 80;
        $personLanguageT = $this->transform($personLanguage);

        $this->json('POST', '/personLanguage/update', $personLanguageT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        $this->seeInDatabase('person_languages', $personLanguage);
        DB::table('person_languages')->where('id', $personLanguage['id'])->delete();
        $this->notSeeInDatabase('person_languages', $personLanguage);
    }

    public function tearDown()
    {
        foreach ($this->personLanguages as $personLanguage) {
            DB::table('person_languages')->where('id', $personLanguage['id'])->delete();
            $this->notSeeInDatabase('person_languages', $personLanguage);
        }
        DB::table('persons')->where('id', $this->person['id'])->delete();
        $this->notSeeInDatabase('persons', $this->person);
    }

    public function newPersonLanguage()
    {
        $personLanguage = $this->personLanguages[0];
        $personLanguage['writing'] = (int) StringHelper::randomizeStr(2, false, false, true);
        $personLanguage['speaking'] = (int) StringHelper::randomizeStr(2, false, false, true);
        $personLanguage['listening'] = (int) StringHelper::randomizeStr(2, false, false, true);
        unset($personLanguage['id']);

        return $personLanguage;
    }
}
