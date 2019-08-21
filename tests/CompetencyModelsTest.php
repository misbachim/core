<?php
use Carbon\Carbon;


class CompetencyModelsTest extends SeededTestCase
{
    public static function setUpBeforeClass()
    {
        self::read('competency_models');
        parent::setUpBeforeClass();
    }

    public function testGetAllFirstPageWithTwoItems() {

        $expectedCompetencyModels = [];
        $finalExpectedCompetencyModels = [];
        $selectedCompanyId = 0;

        foreach($this->getRecords('competency_models') as $seededCompetencyModels) {

            if ($selectedCompanyId === 0) {
                $selectedCompanyId = $seededCompetencyModels['company_id'];
            }

            if ($seededCompetencyModels['company_id'] === $selectedCompanyId) {
                array_push($expectedCompetencyModels, [
                    'id' => intval($seededCompetencyModels['id']),
                    'code' => $seededCompetencyModels['code'],
                    'name' => $seededCompetencyModels['name'],
                    'description' => $seededCompetencyModels['description'],
                    'effBegin' => $seededCompetencyModels['eff_begin'],
                    'effEnd' => $seededCompetencyModels['eff_end']
                ]);
            }
        }
        usort($expectedCompetencyModels, function ($a, $b) {
            $da = strtotime($a['effEnd']);
            $db = strtotime($b['effEnd']);
            return $da < $db;
        });

        $uniqueCompetencyModels = collect($expectedCompetencyModels)->unique('code');
        $uniqueCompetencyModels->values()->all();

        foreach ($uniqueCompetencyModels as $competencyModel) {
            array_push($finalExpectedCompetencyModels, [
                'id' => intval($competencyModel['id']),
                'code' => $competencyModel['code'],
                'name' => $competencyModel['name'],
                'description' => $competencyModel['description'],
                'effBegin' => $competencyModel['effBegin'],
                'effEnd' => $competencyModel['effEnd']
            ]);
            if (count($finalExpectedCompetencyModels) >= 2) {
                break;
            }
        }

        $this->expectManyObjectsSuccess('/competencyModel/getAll', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 2
            ],
        ], $finalExpectedCompetencyModels, trans('messages.allDataRetrieved'));

    }

    public function testGetAll() {

        $expectedCompetencyModels = [];
        $finalExpectedCompetencyModels = [];
        $selectedCompanyId = 0;

        foreach($this->getRecords('competency_models') as $seededCompetencyModels) {

            if ($selectedCompanyId === 0) {
                $selectedCompanyId = $seededCompetencyModels['company_id'];
            }

            if ($seededCompetencyModels['company_id'] === $selectedCompanyId) {
                array_push($expectedCompetencyModels, [
                    'id' => intval($seededCompetencyModels['id']),
                    'code' => $seededCompetencyModels['code'],
                    'name' => $seededCompetencyModels['name'],
                    'description' => $seededCompetencyModels['description'],
                    'effBegin' => $seededCompetencyModels['eff_begin'],
                    'effEnd' => $seededCompetencyModels['eff_end']
                ]);
            }
        }
        usort($expectedCompetencyModels, function ($a, $b) {
            $da = strtotime($a['effEnd']);
            $db = strtotime($b['effEnd']);
            return $da < $db;
        });

        $uniqueCompetencyModels = collect($expectedCompetencyModels)->unique('code');
        $uniqueCompetencyModels->values()->all();

        foreach ($uniqueCompetencyModels as $competencyModel) {
            array_push($finalExpectedCompetencyModels, [
                'id' => intval($competencyModel['id']),
                'code' => $competencyModel['code'],
                'name' => $competencyModel['name'],
                'description' => $competencyModel['description'],
                'effBegin' => $competencyModel['effBegin'],
                'effEnd' => $competencyModel['effEnd']
            ]);
        }

        $this->expectManyObjectsSuccess('/competencyModel/getAll', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 10
            ],
        ], $finalExpectedCompetencyModels, trans('messages.allDataRetrieved'));
    }

    public function testGetAllActive() {

        $expectedCompetencyModels = [];
        $finalExpectedCompetencyModels = [];
        $selectedCompanyId = 0;

        foreach($this->getRecords('competency_models') as $seededCompetencyModels) {

            if ($selectedCompanyId === 0) {
                $selectedCompanyId = $seededCompetencyModels['company_id'];
            }

            if ($seededCompetencyModels['company_id'] === $selectedCompanyId) {
                array_push($expectedCompetencyModels, [
                    'id' => intval($seededCompetencyModels['id']),
                    'code' => $seededCompetencyModels['code'],
                    'name' => $seededCompetencyModels['name'],
                    'description' => $seededCompetencyModels['description'],
                    'effBegin' => $seededCompetencyModels['eff_begin'],
                    'effEnd' => $seededCompetencyModels['eff_end']
                ]);
            }
        }
        usort($expectedCompetencyModels, function ($a, $b) {
            $da = strtotime($a['effEnd']);
            $db = strtotime($b['effEnd']);
            return $da < $db;
        });

        $uniqueCompetencyModels = collect($expectedCompetencyModels)->unique('code');
        $uniqueCompetencyModels->values()->all();

        foreach ($uniqueCompetencyModels as $competencyModelsUnique) {
            if ($competencyModelsUnique['effEnd'] >= \Carbon\Carbon::now()) {
                array_push($finalExpectedCompetencyModels, [
                    'id' => intval($competencyModelsUnique['id']),
                    'code' => $competencyModelsUnique['code'],
                    'name' => $competencyModelsUnique['name'],
                    'description' => $competencyModelsUnique['description'],
                    'effBegin' => $competencyModelsUnique['effBegin'],
                    'effEnd' => $competencyModelsUnique['effEnd']
                ]);
            }
        }

        $this->expectManyObjectsSuccess('/competencyModel/getAllActive', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 10
            ],
        ], $finalExpectedCompetencyModels, trans('messages.allDataRetrieved'));
    }

    public function testGetAllInactive() {

        $expectedCompetencyModels = [];
        $middleExpectedCompetencyModels = [];
        $finalExpectedCompetencyModels = [];

        $selectedCompanyId = 0;
        foreach($this->getRecords('competency_models') as $seededCompetencyModels) {

            if ($selectedCompanyId === 0) {
                $selectedCompanyId = $seededCompetencyModels['company_id'];
            }

            if ($seededCompetencyModels['company_id'] === $selectedCompanyId) {
                array_push($expectedCompetencyModels, [
                    'id' => intval($seededCompetencyModels['id']),
                    'code' => $seededCompetencyModels['code'],
                    'name' => $seededCompetencyModels['name'],
                    'description' => $seededCompetencyModels['description'],
                    'effBegin' => $seededCompetencyModels['eff_begin'],
                    'effEnd' => $seededCompetencyModels['eff_end']
                ]);
            }
        }
        usort($expectedCompetencyModels, function ($a, $b) {
            $da = strtotime($a['effEnd']);
            $db = strtotime($b['effEnd']);
            return $da < $db;
        });

        foreach ($expectedCompetencyModels as $compModelExpectation) {
            if ($compModelExpectation['effEnd'] < \Carbon\Carbon::now()) {
                array_push($middleExpectedCompetencyModels, [
                    'id' => intval($compModelExpectation['id']),
                    'code' => $compModelExpectation['code'],
                    'name' => $compModelExpectation['name'],
                    'description' => $compModelExpectation['description'],
                    'effBegin' => $compModelExpectation['effBegin'],
                    'effEnd' => $compModelExpectation['effEnd']
                ]);
            }
        }

        $uniqueCompetencyModel = collect($middleExpectedCompetencyModels)->unique('code');
        $uniqueCompetencyModel->values()->all();

//        foreach ($uniqueCompetencyModel as $compCompetencyUnique) {
//            if ($compCompetencyUnique['effEnd'] < \Carbon\Carbon::now()) {
//                array_push($finalExpectedCompetencyModels, [
//                    'id' => intval($compCompetencyUnique['id']),
//                    'code' => $compCompetencyUnique['code'],
//                    'name' => $compCompetencyUnique['name'],
//                    'description' => $compCompetencyUnique['description'],
//                    'effBegin' => $compCompetencyUnique['effBegin'],
//                    'effEnd' => $compCompetencyUnique['effEnd']
//                ]);
//            }
//        }
            info('testInActive',[$uniqueCompetencyModel]);
        $this->expectManyObjectsSuccess('/competencyModel/getAllInactive', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 10
            ],
        ], $finalExpectedCompetencyModels, trans('messages.allDataRetrieved'));
    }

    public function testGetOne() {
        $seededCompetencyModel = $this->getRecords('competency_models')->fetchOne(0);

        $expectedCompetencyModel = $this->newObject(
            $this->transform($seededCompetencyModel),
            ['id' => intval($seededCompetencyModel['id'])],
            ['tenantId','companyId','createdAt','createdBy','updatedAt','updatedBy']
        );

        $this->expectObjectSuccess('/competencyModel/getOne',[
            'companyId' => intval($seededCompetencyModel['company_id']),
            'id' => $seededCompetencyModel['id']
        ],[
            'id',
            'code',
            'name',
            'description',
            'effBegin',
            'effEnd'
        ], $expectedCompetencyModel, trans('messages.dataRetrieved'));
    }

//    public function testSave() {
//        $seededEducationSpecialization = $this->getRecords('education_specializations')->fetchOne(0);
//
//        $expectedEducationSpecializations = $this->newObject(
//           $seededEducationSpecialization,
//           ['code' => 'ayee','name' => 'ayee1123', 'flag' => 'desc'],
//           ['tenant_id','id','created_at','created_by','updated_at','updated_by']
//        );
//
//        $req = $this->transform($expectedEducationSpecializations);
//
//        $this->expectSaveSuccess('/educationSpecialization/save',
//            $req,
//            $expectedEducationSpecializations,
//            ['id'],
//            trans('messages.dataSaved'),
//            'education_specializations',
//            'id');
//    }

}