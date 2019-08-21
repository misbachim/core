<?php

use Carbon\Carbon;


class CompetencyTest extends SeededTestCase
{
    public static function setUpBeforeClass()
    {
        self::read('competencies');
        parent::setUpBeforeClass();
    }

    public function testGetAll()
    {
        $expectedCompetencies = [];
        $selectedCompanyId = 0;

        foreach ($this->getRecords('competencies') as $record) {
            if($selectedCompanyId = 0) {
                $selectedCompanyId =  $record['company_id'];
            }

            if($record['company_id'] === $selectedCompanyId) {
                array_push($expectedCompetencies, [
                    'id' => intval($record['id']),
                    'code' => $record['code'],
                    'name' => $record['name'],
                    'description' => $record['description']
                ]);
            }
        }

        $this->expectManyObjectsSuccess('/competency/getAll', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 2
            ]
        ], $expectedCompetencies, trans('messages.allDataRetrieved'));    
    }

    public function testGetAllActive()
    {
        $expectedCompetencies = [];
        $finalExpectedCompetencies = [];
        $selectedCompanyId = 0;

        foreach ($this->getRecords('competencies') as $record) {
            if($record['eff_begin'] <= Carbon::now() && $record['eff_end'] >= Carbon::now()){
                if($selectedCompanyId = 0) {
                    $selectedCompanyId =  $record['company_id'];
                }
    
                if($record['company_id'] === $selectedCompanyId) {
                    array_push($expectedCompetencies, [
                        'id' => intval($record['id']),
                        'code' => $record['code'],
                        'name' => $record['name'],
                        'description' => $record['description'],
                        'effBegin' => $record['effBegin'],
                        'effEnd' => $record['effEnd']
                    ]);
                }    
            }
        }

        usort($expectedCompetencies, function ($a, $b) {
            $da = strtotime($a['id']);
            $db = strtotime($b['id']);
            return $da < $db;
        });

        info('asd', array($expectedCompetencies));

        $uniqueCompetencies = collect($expectedCompetencies)->unique('code');
        $uniqueCompetencies->values()->all();

        foreach ($uniqueCompetencies as $competencies) {
            array_push($finalExpectedCompetencies, [
                'id' => intval($competencies['id']),
                'code' => $competencies['code'],
                'name' => $competencies['name'],
                'description' => $competencies['description'],
                'effBegin' => $competencies['effBegin'],
                'effEnd' => $competencies['effEnd']
            ]);
        }

        $this->expectManyObjectsSuccess('/competency/getAll', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 2
            ]
        ], $finalExpectedCompetencies, trans('messages.allDataRetrieved'));    
    }
}
