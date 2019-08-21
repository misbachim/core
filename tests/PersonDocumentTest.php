<?php

use App\Business\Dao\CityDao;
use App\Business\Dao\DistrictDao;
use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonDocumentDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

/**
 * @property PersonDocumentDao personDocumentDao
 * @property array personDocuments
 * @property array personDocumentsT
 * @property PersonDao personDao
 * @property array person
 * @property CityDao cityDao
 * @property DistrictDao districtDao
 * @property array city
 * @property array district
 */
class PersonDocumentTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->personDao = new PersonDao($this->getRequester());
        $this->personDocumentDao = new PersonDocumentDao($this->getRequester());

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

        $personDocument = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'person_id' => $this->person['id'],
            'lov_dcty' => 'CE',
            'expired' => '2018-10-01'
        ];

        $this->personDocuments = [];
        $this->personDocumentsT = [];
        foreach (range(1, 10) as $i) {
            $personDocument['name'] = StringHelper::randomizeStr(50);
            $personDocument['file_document'] = StringHelper::randomizeStr(300);

            $personDocument['id'] = $this->personDocumentDao->save($personDocument);
            $this->seeInDatabase('person_documents', $personDocument);
            array_push($this->personDocuments, $personDocument);

            $personDocumentT = $this->transform($personDocument);
            array_push($this->personDocumentsT, $personDocumentT);

            unset($personDocument['id']);
        }
    }

    public function testGetAll()
    {
        $this->personDocumentsT = $this->exclude($this->personDocumentsT, [
            'tenantId',
            'personId',
            'lovDcty'
        ]);

        $this->json('POST', '/personDocument/getAll', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'personId' => $this->person['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->personDocumentsT as $personDocumentT) {
            foreach ($personDocumentT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetOne()
    {
        $this->personDocumentsT = $this->exclude($this->personDocumentsT, [
            'tenantId',
            'personId'
        ]);

        $this->json('POST', '/personDocument/getOne', [
            'id' => $this->personDocuments[0]['id'],
            'personId' => $this->personDocuments[0]['person_id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ]);

        foreach ($this->personDocumentsT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $personDocument = $this->newPersonDocument();
        unset($personDocument['file_document']);
        $personDocumentT = $this->transform($personDocument);

        $payload = [
            'data' => json_encode($personDocumentT),
            'upload' => false
        ];

        $this->json('POST', '/personDocument/save', $payload)
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
        $personDocument['id'] = $data->id;
        $this->seeInDatabase('person_documents', $personDocument);
        DB::table('person_documents')->where('id', $personDocument['id'])->delete();
        $this->notSeeInDatabase('person_documents', $personDocument);
    }

    public function testUpdate()
    {
        $personDocument = $this->personDocuments[0];
        $personDocument['name'] = StringHelper::randomizeStr(50);
        $personDocumentT = $this->transform($personDocument);

        $payload = [
            'data' => json_encode($personDocumentT),
            'upload' => false
        ];

        $this->json('POST', '/personDocument/update', $payload)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated'),
                'data' => []
            ]);

        $this->seeInDatabase('person_documents', $personDocument);
        DB::table('person_documents')->where('id', $personDocument['id'])->delete();
        $this->notSeeInDatabase('person_documents', $personDocument);
    }

    public function tearDown()
    {
        foreach ($this->personDocuments as $personDocument) {
            DB::table('person_documents')->where('id', $personDocument['id'])->delete();
            $this->notSeeInDatabase('person_documents', $personDocument);
        }
        DB::table('persons')->where('id', $this->person['id'])->delete();
        $this->notSeeInDatabase('persons', $this->person);
    }

    public function newPersonDocument()
    {
        $personDocument = $this->personDocuments[0];
        $personDocument['name'] = StringHelper::randomizeStr(50);
        unset($personDocument['id']);

        return $personDocument;
    }
}
