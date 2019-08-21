<?php

use App\Business\Dao\CityDao;
use App\Business\Dao\DistrictDao;
use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonAddressDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

/**
 * @property PersonAddressDao personAddressDao
 * @property array personAddresses
 * @property array personAddressesT
 * @property PersonDao personDao
 * @property array person
 * @property CityDao cityDao
 * @property DistrictDao districtDao
 * @property array city
 * @property array district
 */
class PersonAddressTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->cityDao = new CityDao($this->getRequester());
        $this->districtDao = new DistrictDao($this->getRequester());
        $this->personDao = new PersonDao($this->getRequester());
        $this->personAddressDao = new PersonAddressDao($this->getRequester());

        $this->city = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'province_id' => 1,
            'code' => StringHelper::randomizeStr(5),
            'name' => StringHelper::randomizeStr(50),
            'dial_code' => '+62'
        ];
        $this->city['id'] = $this->cityDao->save($this->city);
        $this->seeInDatabase('cities', $this->city);

        $this->district = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'city_id' => $this->city['id'],
            'name' => StringHelper::randomizeStr(50)
        ];
        $this->district['id'] = $this->districtDao->save($this->district);
        $this->seeInDatabase('districts', $this->district);

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

        $personAddress = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'person_id' => $this->person['id'],
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'lov_rsty' => 'APA',
            'lov_rsow' => 'COM',
            'city_id' => $this->city['id'],
            'district_id' => $this->district['id'],
            'postal_code' => StringHelper::randomizeStr(10),
            'map_location' => StringHelper::randomizeStr(50),
            'phone' => StringHelper::randomizeStr(50),
            'fax' => StringHelper::randomizeStr(50)
        ];

        $this->personAddresses = [];
        $this->personAddressesT = [];
        foreach (range(1, 10) as $i) {
            $personAddress['address'] = StringHelper::randomizeStr(50);

            $personAddress['id'] = $this->personAddressDao->save($personAddress);
            $this->seeInDatabase('person_addresses', $personAddress);
            array_push($this->personAddresses, $personAddress);

            $personAddressT = $this->transform($personAddress);
            array_push($this->personAddressesT, $personAddressT);

            unset($personAddress['id']);
        }
    }

    public function testGetAll()
    {
        $this->personAddressesT = $this->exclude($this->personAddressesT, [
            'tenantId',
            'personId',
            'effBegin',
            'effEnd',
            'lovRsty',
            'lovRsow',
            'cityId',
            'districtId',
            'phone',
            'fax'
        ]);
        $this->personAddressesT = $this->include($this->personAddressesT, [
            'districtName' => $this->district['name'],
            'cityName' => $this->city['name']
        ]);

        $this->json('POST', '/personAddress/getAll', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'personId' => $this->person['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ])->seeJsonStructure([
            'data' => [
                [
                    'provinceName',
                    'countryName'
                ]
            ]
        ]);

        foreach ($this->personAddressesT as $personAddressT) {
            foreach ($personAddressT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetOne()
    {
        $this->personAddressesT = $this->exclude($this->personAddressesT, [
            'tenantId',
            'personId'
        ]);

        $this->json('POST', '/personAddress/getOne', [
            'id' => $this->personAddresses[0]['id'],
            'personId' => $this->personAddresses[0]['person_id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ])->seeJsonStructure([
            'data' => [
                'countryId',
                'provinceId'
            ]
        ]);

        foreach ($this->personAddressesT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $personAddress = $this->newPersonAddress();
        $personAddressT = $this->transform($personAddress);

        $this->json('POST', '/personAddress/save', $personAddressT)
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
        $personAddress['id'] = $data->id;
        $this->seeInDatabase('person_addresses', $personAddress);
        DB::table('person_addresses')->where('id', $personAddress['id'])->delete();
        $this->notSeeInDatabase('person_addresses', $personAddress);
    }

    public function testSaveInvalidEffBegin()
    {
        $personAddress = $this->newPersonAddress();
        $personAddress['eff_begin'] = '2018-12-01';
        $personAddressT = $this->transform($personAddress);

        $this->json('POST', '/personAddress/save', $personAddressT)
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

        $this->notSeeInDatabase('person_addresses', $personAddress);
    }

    public function testUpdate()
    {
        $personAddress = $this->personAddresses[0];
        $personAddress['address'] = StringHelper::randomizeStr(50);
        $personAddressT = $this->transform($personAddress);

        $this->json('POST', '/personAddress/update', $personAddressT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        $this->seeInDatabase('person_addresses', $personAddress);
        DB::table('person_addresses')->where('id', $personAddress['id'])->delete();
        $this->notSeeInDatabase('person_addresses', $personAddress);
    }

    public function tearDown()
    {
        foreach ($this->personAddresses as $personAddress) {
            DB::table('person_addresses')->where('id', $personAddress['id'])->delete();
            $this->notSeeInDatabase('person_addresses', $personAddress);
        }
        DB::table('persons')->where('id', $this->person['id'])->delete();
        $this->notSeeInDatabase('persons', $this->person);

        DB::table('districts')->where('id', $this->district['id'])->delete();
        $this->notSeeInDatabase('districts', $this->district);

        DB::table('cities')->where('id', $this->city['id'])->delete();
        $this->notSeeInDatabase('cities', $this->city);
    }

    public function newPersonAddress()
    {
        $personAddress = $this->personAddresses[0];
        $personAddress['address'] = StringHelper::randomizeStr(50);
        unset($personAddress['id']);

        return $personAddress;
    }
}
