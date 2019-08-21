<?php

class CountryTest extends SeededTestCase
{
    public static function setUpBeforeClass()
    {
        self::read('countries');
        parent::setUpBeforeClass();
    }

    public function testGetAllFirstPageWithTwoItems()
    {
        $expectedCountries = [];
        $selectedCompanyId = 0;

        // Expectation preparation
        foreach ($this->getRecords('countries') as $seededCountry) {
            // Use company ID of the first seeded country only.
            if ($selectedCompanyId === 0) {
                $selectedCompanyId = $seededCountry['company_id'];
            }

            // We expect only countries with the selected company ID.
            if ($seededCountry['company_id'] === $selectedCompanyId) {
                array_push($expectedCountries, [
                    'id' => intval($seededCountry['id']),
                    'code' => $seededCountry['code'],
                    'name' => $seededCountry['name'],
                    'dialCode' => $seededCountry['dial_code'],
                    'nationality' => $seededCountry['nationality']
                ]);

                // We expect only 2 countries.
                if (count($expectedCountries) >= 2) {
                    break;
                }
            }
        }

        // Assertion
        $this->expectManyObjectsSuccess('/country/getAll', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 2
            ]
        ], $expectedCountries, trans('messages.allDataRetrieved'));
    }

    public function testGetOne()
    {
        $seededCountry = $this->getRecords('countries')->fetchOne(0);

        // Transform seeded country to something we expect.
        $expectedCountry = $this->newObject(
            $this->transform($seededCountry),
            ['id' => intval($seededCountry['id'])],
            ['tenantId', 'companyId', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt']
        );

        $this->expectObjectSuccess('/country/getOne', [
            'companyId' => intval($seededCountry['company_id']),
            'id' => $seededCountry['id']
        ], [
            'id',
            'code',
            'name',
            'dialCode',
            'nationality'
        ], $expectedCountry, trans('messages.dataRetrieved'));
    }

    public function testSaveValidCodeSuccess()
    {
        $seededCountry = $this->getRecords('countries')->fetchOne(0);

        // Create a raw country object for in-database verification.
        $country = $this->newObject(
            $seededCountry,
            ['code' => 'BR', 'name' => 'Britania'],
            ['tenant_id', 'id', 'created_by', 'created_at', 'updated_by', 'updated_at']
        );

        $req = $this->transform($country);

        $this->expectSaveSuccess('/country/save', $req,
            $country, ['id'], trans('messages.dataSaved'), 'countries', 'id');
    }

    public function testSaveDuplicateCodeFail()
    {
        $seededCountry = $this->getRecords('countries')->fetchOne(0);

        // Create a raw country object for in-database verification.
        $country = $this->newObject(
            $seededCountry,
            ['name' => 'Britania'],
            ['tenant_id', 'id', 'created_by', 'created_at', 'updated_by', 'updated_at']
        );

        $req = $this->transform($country);

        $this->expectPostFailure('/country/save', $req,
            [], trans('messages.duplicateCode'));
    }

    public function testUpdateValidCodeSuccess()
    {
        $seededCountry = $this->getRecords('countries')->fetchOne(0);

        // Create a raw country object for in-database verification.
        $country = $this->newObject(
            $seededCountry,
            ['code' => 'BR', 'name' => 'Britania'],
            ['tenant_id', 'created_by', 'created_at', 'updated_by', 'updated_at']
        );

        $req = $this->transform($country);

        $this->expectUpdateSuccess('/country/update', $req,
            $country, [], trans('messages.dataUpdated'), 'countries');
    }

    public function testUpdateDuplicateCodeFail()
    {
        $seededCountry = $this->getRecords('countries')->fetchOne(0);
        $secondSeededCountry = $this->getRecords('countries')->fetchOne(1);

        // Create a raw country object for in-database verification.
        $country = $this->newObject(
            $seededCountry,
            ['code' => $secondSeededCountry['code'], 'name' => 'Britania'],
            ['tenant_id', 'created_by', 'created_at', 'updated_by', 'updated_at']
        );

        $req = $this->transform($country);

        $this->expectPostFailure('/country/update', $req,
            [], trans('messages.duplicateCode'));
    }

    public function testDeleteCountrySuccess()
    {
        $seededCountry = $this->getRecords('countries')->fetchOne(0);

        // Remove all provinces that use the seeded country.
        DB::table('provinces')
            ->where('country_code', $seededCountry['code'])
            ->delete();

        $this->expectPostSuccess('/country/delete', [
            'id' => $seededCountry['id']
        ], [], trans('messages.dataDeleted'));

        $this->notSeeInDatabase('countries', $seededCountry);
    }

    public function testDeleteCountryInUseFail()
    {
        $seededCountry = $this->getRecords('countries')->fetchOne(0);

        $this->expectPostFailure('/country/delete', [
            'id' => $seededCountry['id']
        ], [], trans('messages.dataInUse'));

        $this->seeInDatabase('countries', $seededCountry);
    }
}
