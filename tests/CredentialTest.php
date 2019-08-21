<?php

class CredentialTest extends SeededTestCase
{
    public static function setUpBeforeClass()
    {
        self::read('credentials');
        parent::setUpBeforeClass();
    }

    public function testGetAllPageActiveCredential()
    {
        $expectedCredentials = [];
        $expectedCredential = [];
        $selectedCompanyId = 0;
        foreach ($this->getRecords('credentials') as $seededCredential) {
            if ($selectedCompanyId === 0) {
                $selectedCompanyId = $seededCredential['company_id'];
            }

            if ($seededCredential['company_id'] === $selectedCompanyId) {
                array_push($expectedCredentials, [
                    'code' => $seededCredential['code'],
                    'name' => $seededCredential['name'],
                    'description' => $seededCredential['description'],
                    'qualificationSourceName' => $seededCredential['qualification_source_name'],
                    'renewalCycle' => $seededCredential['renewal_cycle'],
                    'notificationDays' => $seededCredential['notification_days'],
                    'effBegin' => $seededCredential['eff_begin'],
                    'effEnd' => $seededCredential['eff_end']
                ]);
            }
        }

        usort($expectedCredentials, function ($a, $b) {
            $da = strtotime($a['effEnd']);
            $db = strtotime($b['effEnd']);
            return $da < $db;
        });

        info('sorting', array($expectedCredentials));

        $uniqueCredential = collect($expectedCredentials)->unique('code');
        $uniqueCredential->values()->all();

        info('duplicate', array($uniqueCredential));


        foreach ($uniqueCredential as $credentialUnique) {
            if ($credentialUnique['effEnd'] >= \Carbon\Carbon::now()) {
                array_push($expectedCredential, [
                    'code' => $credentialUnique['code'],
                    'name' => $credentialUnique['name'],
                    'description' => $credentialUnique['description'],
                    'qualificationSourceName' => $credentialUnique['qualificationSourceName'],
                    'renewalCycle' => $credentialUnique['renewalCycle'],
                    'notificationDays' => $credentialUnique['notificationDays'],
                    'effBegin' => $credentialUnique['effBegin'],
                    'effEnd' => $credentialUnique['effEnd']
                ]);
            }
        }
        info('expected', array($expectedCredential));

        // Assertion
        $this->expectManyObjectsSuccess('/credential/getAllActive', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 10000
            ]
        ], $expectedCredential, trans('messages.allDataRetrieved'));
    }
}
