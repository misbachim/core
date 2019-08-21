<?php

use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonReferenceDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

/**
 * @property PersonReferenceDao personReferenceDao
 * @property array personReferences
 * @property array personReferencesT
 * @property PersonDao personDao
 * @property array person
 */
class PersonReferenceTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->personDao = new PersonDao($this->getRequester());
        $this->personReferenceDao = new PersonReferenceDao($this->getRequester());

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

        $personReference = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'person_id' => $this->person['id']
        ];

        $this->personReferences = [];
        $this->personReferencesT = [];
        foreach (range(1, 10) as $i) {
            $personReference['name'] = StringHelper::randomizeStr(50);
            $personReference['relationship'] = StringHelper::randomizeStr(50);
            $personReference['description'] = StringHelper::randomizeStr(255);
            $personReference['phone'] = StringHelper::randomizeStr(50, false, false, true);

            $personReference['id'] = $this->personReferenceDao->save($personReference);
            $this->seeInDatabase('person_references', $personReference);
            array_push($this->personReferences, $personReference);

            $personReferenceT = $this->transform($personReference);
            array_push($this->personReferencesT, $personReferenceT);

            unset($personReference['id']);
        }
    }

    public function testGetAll()
    {
        $this->personReferencesT = $this->exclude($this->personReferencesT, [
            'tenantId',
            'personId'
        ]);

        $this->json('POST', '/personReference/getAll', [
            'personId' => $this->person['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->personReferencesT as $personReferenceT) {
            foreach ($personReferenceT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetOne()
    {
        $this->personReferencesT = $this->exclude($this->personReferencesT, [
            'tenantId',
            'personId'
        ]);

        $this->json('POST', '/personReference/getOne', [
            'id' => $this->personReferences[0]['id'],
            'personId' => $this->person['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ]);

        foreach ($this->personReferencesT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $personReference = $this->newPersonReference();
        $personReferenceT = $this->transform($personReference);

        $this->json('POST', '/personReference/save', $personReferenceT)
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
        $personReference['id'] = $data->id;
        $this->seeInDatabase('person_references', $personReference);
        DB::table('person_references')->where('id', $personReference['id'])->delete();
        $this->notSeeInDatabase('person_references', $personReference);
    }

    public function testUpdate()
    {
        $personReference = $this->personReferences[0];
        $personReference['relationship'] = StringHelper::randomizeStr(50);
        $personReferenceT = $this->transform($personReference);

        $this->json('POST', '/personReference/update', $personReferenceT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        $this->seeInDatabase('person_references', $personReference);
        DB::table('person_references')->where('id', $personReference['id'])->delete();
        $this->notSeeInDatabase('person_references', $personReference);
    }

    public function tearDown()
    {
        foreach ($this->personReferences as $personReference) {
            DB::table('person_references')->where('id', $personReference['id'])->delete();
            $this->notSeeInDatabase('person_references', $personReference);
        }
        DB::table('persons')->where('id', $this->person['id'])->delete();
        $this->notSeeInDatabase('persons', $this->person);
    }

    public function newPersonReference()
    {
        $personReference = $this->personReferences[0];
        $personReference['name'] = StringHelper::randomizeStr(50);
        $personReference['description'] = StringHelper::randomizeStr(255);
        unset($personReference['id']);

        return $personReference;
    }
}
