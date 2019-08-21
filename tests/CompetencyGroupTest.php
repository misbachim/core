<?php
use Carbon\Carbon;


class CompetencyGroupTest extends SeededTestCase
{
    public static function setUpBeforeClass()
    {
        self::read('competency_groups');
        parent::setUpBeforeClass();
    }

    public function testGetAll()
    {
        $expectedCompetencyGroups = [];
        $selectedCompanyId = 0;

        foreach ($this->getRecords('competency_groups') as $record) {
            if($selectedCompanyId = 0) {
                $selectedCompanyId =  $record['company_id'];
            }

            if($record['company_id'] === $selectedCompanyId) {
                array_push($expectedCompetencyGroups, [
                    'id' => intval($record['id']),
                    'code' => $record['code'],
                    'name' => $record['name'],
                    'description' => $record['description']
                ]);
            }
        }

        $this->expectManyObjectsSuccess('/competencyGroup/getAll', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 2
            ]
        ], $expectedCompetencyGroups, trans('messages.allDataRetrieved'));    
    }

    public function testGetOne()
    {
        $seededCompetencyGroups = $this->getRecords('competency_groups')->fetchOne(0);

        info('asd', array($seededCompetencyGroups));

        $expectedCompetencyGroups = $this->newObject(
            $this->transform($seededCompetencyGroups),
            ['id' => intval($seededCompetencyGroups['id'])],
            ['tenantId','companyId','effBegin','effEnd','createdAt','createdBy','updatedAt','updatedBy']
        );

        $this->expectObjectSuccess('/competencyGroup/getOne', [
                'id' => $seededCompetencyGroups['id']
            ], [
                'id',
                'code',
                'name',
                'description'
            ],$expectedCompetencyGroups, trans('messages.dataRetrieved'));
    }
}