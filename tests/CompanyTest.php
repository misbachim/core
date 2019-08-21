<?php

class CompanyTest extends SeededTestCase
{
    public static function setUpBeforeClass()
    {
        self::read('companies');
        parent::setUpBeforeClass();
    }

    public function testGetOne()
    {
        // Load dependencies.
        self::read('company_settings');
        self::read('setting_types', null);

        $company = $this->transform( $this->getRecords('companies')->fetchOne(0) );

        // Retrieve company settings.
        $settings = $this->prepareRecords('company_settings')
            ->where(function ($setting) use (&$company) {
                return $setting['company_id'] == $company['id'];
            })
            ->ok();


        // Transform raw company to as expected.
        $company = $this->newObject(
            $company,
            ['id' => intval($company['id']), 'fileLogo' => null],
            ['tenantId', 'isDeleted', 'createdBy', 'createdAt', 'updatedBy', 'updatedAt']
        );

        // Set company settings as part of company object.
        $company['settings'] = [];
        foreach ($settings as $setting) {
            $settingType = $this->prepareRecords('setting_types')
                ->where(function ($settingType) use (&$setting) {
                    return $settingType['code'] === $setting['setting_type_code'];
                })
                ->ok()
                ->fetchOne(0);

            array_push($company['settings'], [
                'typeCode' => $setting['setting_type_code'],
                'typeName' => $settingType['name'],
                'lovKeyData' => $setting['setting_lov_key_data']
            ]);
        }

        // Unset setting types should be included, too.
        $settingTypes = $this->prepareRecords('setting_types')->ok();

        foreach ($settingTypes as $settingType) {
            $filteredSettings = array_filter($company['settings'],
                function ($setting) use (&$settingType) {
                    return $setting['typeCode'] === $settingType['code'];
                });

            if (count($filteredSettings) === 0) {
                array_push($company['settings'], [
                    'typeCode' => $settingType['code'],
                    'typeName' => $settingType['name'],
                    'lovKeyData' => null
                ]);
            }
        }

        $this->expectObjectSuccess('/company/getOne', [
            'id' => $company['id']
        ], [
            "id",
            "name",
            "description",
            "companyTaxNumber",
            "locationCode",
            "effBegin",
            "effEnd",
            "fileLogo",
            "settings"
        ], $company, trans('messages.dataRetrieved'));
    }

    public function testGetMany()
    {
        $companyIds = [];
        $objects = [];
        foreach ($this->getRecords('companies') as $record) {
            array_push($companyIds, $record['id']);
            array_push($objects, [
                'id' => intval($record['id']),
                'name' => $record['name']
            ]);
        }

        $this->expectManyObjectsSuccess('/company/getMany', [
            'companyIds' => $companyIds
        ], $objects, trans('messages.allDataRetrieved'));
    }

    public function testGetSettings()
    {
        // Load dependencies.
        self::read('company_settings');

        $companyIds = [];
        $objects = [];

        foreach ($this->getRecords('companies') as $record) {
            array_push($companyIds, $record['id']);

            $companySettings = $this->prepareRecords('company_settings')
                ->where(function ($companySetting) use (&$record) {
                    return $companySetting['company_id'] == $record['id'];
                })
                ->ok();

            $setting = [];
            foreach ($companySettings as $companySetting) {
                $setting[$companySetting['setting_type_code']] =
                  $companySetting['setting_lov_key_data'];
            }

            array_push($objects, [
                'companyId' => intval($record['id']),
                'companyName' => $record['name'],
                'setting' => $setting
            ]);
        }

        $this->expectManyObjectsSuccess('/company/getSettings', [
            'companyIds' => $companyIds
        ], $objects, trans('messages.allDataRetrieved'));
    }

    public function testSaveWithoutPic()
    {
        $record = $this->getRecords('companies')->fetchOne(0);
        $company = $this->newObject($record, [
            'name' => 'Evil Corp.'
        ], [
            'tenant_id',
            'id',
            'is_deleted',
            'created_by',
            'created_at',
            'updated_by',
            'updated_at',
            'file_logo'
        ]);

        $req = $company;
        $req['settings'] = [
            [
                'typeCode' => 'NSEP',
                'lovKeyData' => '.'
            ]
        ];

        $companyId = $this->expectSaveSuccess('/company/save', [
            'data' => json_encode($this->transform($req)),
            'upload' => 0
        ], $company, ['id'], trans('messages.dataSaved'), 'companies', 'id');

        $this->seeInDatabase('company_settings', [
            'setting_type_code' => 'NSEP',
            'setting_lov_key_data' => '.'
        ]);

        $this->checkSaveCompanyHasSeedData($record['tenant_id'], $companyId);
    }

    public function testUpdateWithoutPic()
    {
        $record = $this->getRecords('companies')->fetchOne(0);
        $company = $this->newObject($record, [
            'name' => 'Evil Corp.'
        ], [
            'tenant_id',
            'is_deleted',
            'created_by',
            'created_at',
            'updated_by',
            'updated_at',
            'file_logo'
        ]);

        $req = $company;
        $req['settings'] = [
            [
                'typeCode' => 'NSEP',
                'lovKeyData' => '.'
            ]
        ];
        $req['isNew'] = false;

        $this->expectUpdateSuccess('/company/update', [
            'data' => json_encode($this->transform($req)),
            'upload' => 0
        ], $company, [], trans('messages.dataUpdated'), 'companies');
    }

    private function checkSaveCompanyHasSeedData($tenantId, $companyId)
    {
        // Expect seed data to be inserted.
        self::read('*', 'company');

        $seededTables = ['lovs', 'countries', 'provinces', 'cities'];

        foreach ($seededTables as $seededTable) {
            $records = $this->prepareRecords($seededTable)->ok();
            foreach ($records as $record) {
                $record = $this->newObject($record, [
                    'tenant_id' => intval($tenantId),
                    'company_id' => intval($companyId),
                ], ['created_at', 'updated_by', 'updated_at']);
                $this->seeInDatabase($seededTable, $record);
            }
        }
    }
}
