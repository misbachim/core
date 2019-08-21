<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class LocationTest extends TestCase
{
    use Testable;

    /**
     * Test getAll endpoint.
     *
     * @return void
     */
    public function testGetAll()
    {
        $data = array(
            'companyId' => 1900000000,
            'pageInfo'=>[
                'pageNo'=>1,
                'pageLimit'=>1
            ]
        );
        $this->json('POST', '/location/getAll', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "All data retrieved"
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        'id',
                        'effBegin',
                        'effEnd',
                        'name',
                        'country',
                        'province',
                        'city',
                        'address',
                        'phone',
                        'fax'
                    ]
                ]
            ]);
    }

    /**
     * Test getOne endpoint.
     *
     * @return void
     */
    public function testGetOne()
    {
        $data = array(
            'companyId' => 1900000000,
            'id' => 13
        );
        $this->json('POST', '/location/getOne', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "Data retrieved"
            ])
            ->seeJsonStructure([
                "data" => [
                    'id',
                    'effBegin',
                    'effEnd',
                    'code',
                    'name',
                    'description',
                    'taxOfficeCode',
                    'calendarCode',
                    'countryId',
                    'provinceId',
                    'cityId',
                    'districtId',
                    'address',
                    'postalCode',
                    'phone',
                    'fax'
                ]
            ]);
    }


    public function testSaveLocation()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'name' => 'Head Office',
            'description' => 'Head Office',
            'calendarCode' => 'CAL1',
            'taxOfficeCode' => '091',
            'cityId' => 6,
            'districtId' => 3,
            'code'=> 'KP12',
            'address' => 'wisma staco',
            'postalCode' => '12870',
            'phone' => '021-213312',
            'fax' => '021-213312'
        );
        $this->json('POST', '/location/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->seeInDatabase('locations', [
            'company_id'=>1900000000,
            'eff_begin'=> '2017-10-13',
            'eff_end'=> '2099-12-31',
            'name' => 'Head Office',
            'description' => 'Head Office',
            'calendar_code' => 'CAL1',
            'tax_office_code' => '091',
            'city_id' => 6,
            'district_id' => 3,
            'code'=> 'KP12',
            'address' => 'wisma staco',
            'postal_code' => '12870',
            'phone' => '021-213312',
            'fax' => '021-213312'] );
        DB::rollback();
    }

    public function testSaveLocationBlankField()
    {
        $data = array();
        $this->json('POST', '/location/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "message"=>"The given data was invalid.",
                "key"=>"effBegin",
                "key"=>"effEnd",
                "key"=>"code",
                "key"=>"name",
                "key"=>"cityId",
                "key"=>"provinceId",
                "key"=>"countryId",
                "key"=>"companyId"
          ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveLocationDuplicateName()
    {
        $data = array(
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'name' => 'HQ',
            'description' => 'Head Quarter',
            'calendarCode' => 'CAL1',
            'taxOfficeCode' => '091',
            'code'=> 'HQ1',
            'cityId' => 6,
            'districtId' => 3,
            'provinceId' => 5,
            'countryId' => 5,
            'address' => 'wisma staco',
            'postalCode' => '12870',
            'phone' => '021-213312',
            'fax' => '021-213312'
        );
        $this->json('POST', '/location/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateName')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testSaveLocationBeginIsBiggerThanEnd()
    {
        $data = array(
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2009-12-31',
            'name' => 'HEAD OFFICE',
            'description' => 'Head Office',
            'calendarCode' => 'CAL1',
            'taxOfficeCode' => '091',
            'code'=> 'HQ1',
            'cityId' => 6,
            'districtId' => 3,
            'provinceId' => 5,
            'countryId' => 5,
            'address' => 'wisma staco',
            'postalCode' => '12870',
            'phone' => '021-213312',
            'fax' => '021-213312'
        );
        $this->json('POST', '/location/save', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key" => "effEnd"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }

    public function testUpdateLocation()
    {
        DB::beginTransaction();
        $data = array(
            'id'=>13,
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'name' => 'HEAD OFFICE',
            'description' => 'Head Office',
            'calendarCode' => 'CAL1',
            'taxOfficeCode' => '091',
            'cityId' => 6,
            'districtId' => 3,
            'code'=> 'HO',
            'address' => 'wisma staco',
            'postalCode' => '12870',
            'phone' => '021-213312',
            'fax' => '021-213312'
        );
        $this->json('POST', '/location/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('locations', [
            'company_id'=>1900000000,
            'eff_begin'=> '2017-10-13',
            'eff_end'=> '2099-12-31',
            'name' => 'HEAD OFFICE',
            'description' => 'Head Office',
            'calendar_code' => 'CAL1',
            'tax_office_code' => '091',
            'city_id' => 6,
            'district_id' => 3,
            'code'=> 'HO',
            'address' => 'wisma staco',
            'postal_code' => '12870',
            'phone' => '021-213312',
            'fax' => '021-213312'] );

        DB::rollback();
    }

    public function testUpdateLocationBlankField()
    {
        $data = array(
            'id'=>13
        );
        $this->json('POST', '/location/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key"=>"effBegin",
                "key"=>"effEnd",
                "key"=>"name",
                "key"=>"cityId",
                "key"=>"provinceId",
                "key"=>"countryId",
                "key"=>"companyId"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testUpdateLocationDuplicateName()
    {
        $data = array(
            'id'=>13,
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2099-12-31',
            'name' => 'HQ',
            'description' => 'Head Quarter',
            'calendarCode' => 'CAL1',
            'taxOfficeCode' => '091',
            'cityId' => 6,
            'code'=> 'SG',
            'districtId' => 3,
            'provinceId' => 5,
            'countryId' => 5,
            'address' => 'wisma staco',
            'postalCode' => '12870',
            'phone' => '021-213312',
            'fax' => '021-213312'
        );
        $this->json('POST', '/location/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 422,
                "message" => trans('messages.duplicateName')

            ])
            ->seeJsonStructure([
                "data" => []
            ]);

    }

    public function testUpdateLocationBeginIsBiggerThanEnd()
    {
        $data = array(
            'id'=>13,
            'companyId' => 1900000000,
            'effBegin'=> '2017-10-13',
            'effEnd'=> '2009-12-31',
            'code'=> 'HQ',
            'name' => 'HEAD OFFICE',
            'description' => 'Head Office',
            'calendarId' => '1',
            'taxOfficeCode' => '091',
            'cityId' => 6,
            'districtId' => 3,
            'provinceId' => 5,
            'countryId' => 5,
            'address' => 'wisma staco',
            'postalCode' => '12870',
            'phone' => '021-213312',
            'fax' => '021-213312'
        );
        $this->json('POST', '/location/update', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 444,
                "key" => "effEnd"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
    }


    public function testDeleteLocation()
    {
        DB::beginTransaction();
        $data = array(
            'companyId' => 1900000000,
            'id'=>13
        );
        $this->json('POST', '/location/delete', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('locations', ['id' => 13,'company_id' => 1900000000] );
        DB::rollback();
    }


}
