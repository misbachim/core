<?php
use Carbon\Carbon;


class EducationInstitutionsTest extends SeededTestCase
{
    public static function setUpBeforeClass()
    {
        self::read('education_institutions');
        parent::setUpBeforeClass();
    }

    public function testGetAll() {

        $expectedEducationInstitutions = [];
        $finalExpectedEducationInstitutions = [];
        $selectedCompanyId = 0;

        foreach($this->getRecords('education_institutions') as $seededEducationInstitutions) {

            if ($selectedCompanyId === 0) {
                $selectedCompanyId = $seededEducationInstitutions['company_id'];
            }

            if ($seededEducationInstitutions['company_id'] === $selectedCompanyId) {
                array_push($expectedEducationInstitutions, [
                    'id' => intval($seededEducationInstitutions['id']),
                    'name' => $seededEducationInstitutions['name'],
                    'address' => $seededEducationInstitutions['address'],
                    'countryCode' => $seededEducationInstitutions['country_code'],
                    'lovAcreditation' => $seededEducationInstitutions['lov_acreditation'],
                    'effBegin' => $seededEducationInstitutions['eff_begin'],
                    'effEnd' => $seededEducationInstitutions['eff_end']
                ]);
            }
        }
        usort($expectedEducationInstitutions, function ($a, $b) {
            $da = strtotime($a['effEnd']);
            $db = strtotime($b['effEnd']);
            return $da < $db;
        });

        $uniqueEduInstitutions = collect($expectedEducationInstitutions)->unique('name');
        $uniqueEduInstitutions->values()->all();

        foreach ($uniqueEduInstitutions as $educationInstitution) {
            array_push($finalExpectedEducationInstitutions, [
                'id' => intval($educationInstitution['id']),
                'name' => $educationInstitution['name'],
                'address' => $educationInstitution['address'],
                'countryCode' => $educationInstitution['countryCode'],
                'lovAcreditation' => $educationInstitution['lovAcreditation'],
                'effBegin' => $educationInstitution['effBegin'],
                'effEnd' => $educationInstitution['effEnd']
            ]);
        }

        $this->expectManyObjectsSuccess('/educationInstitution/getAll', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 10
            ],

        ], $finalExpectedEducationInstitutions, trans('messages.allDataRetrieved'));
    }

    public function testGetAllActive() {

        $expectedEducationInstitutions = [];
        $finalExpectedEducationInstitutions = [];
        $selectedCompanyId = 0;
        foreach($this->getRecords('education_institutions') as $seededEducationInstitutions) {

            if ($selectedCompanyId === 0) {
                $selectedCompanyId = $seededEducationInstitutions['company_id'];
            }
            if ($seededEducationInstitutions['company_id'] === $selectedCompanyId) {
                array_push($expectedEducationInstitutions, [
                    'id' => intval($seededEducationInstitutions['id']),
                    'name' => $seededEducationInstitutions['name'],
                    'address' => $seededEducationInstitutions['address'],
                    'countryCode' => $seededEducationInstitutions['country_code'],
                    'lovAcreditation' => $seededEducationInstitutions['lov_acreditation'],
                    'effBegin' => $seededEducationInstitutions['eff_begin'],
                    'effEnd' => $seededEducationInstitutions['eff_end']
                ]);
            }
        }
        usort($expectedEducationInstitutions, function ($a, $b) {
            $da = strtotime($a['effEnd']);
            $db = strtotime($b['effEnd']);
            return $da < $db;
        });

        $uniqueEduInstitutions = collect($expectedEducationInstitutions)->unique('name');
        $uniqueEduInstitutions->values()->all();

        foreach ($uniqueEduInstitutions as $eduInstitutionsUnique) {
            if ($eduInstitutionsUnique['effEnd'] >= \Carbon\Carbon::now()) {
                array_push($finalExpectedEducationInstitutions, [
                    'id' => intval($eduInstitutionsUnique['id']),
                    'name' => $eduInstitutionsUnique['name'],
                    'address' => $eduInstitutionsUnique['address'],
                    'countryCode' => $eduInstitutionsUnique['countryCode'],
                    'lovAcreditation' => $eduInstitutionsUnique['lovAcreditation'],
                    'effBegin' => $eduInstitutionsUnique['effBegin'],
                    'effEnd' => $eduInstitutionsUnique['effEnd']
                ]);
            }
        }

        $this->expectManyObjectsSuccess('/educationInstitution/getAllActive', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 10
            ],
        ], $finalExpectedEducationInstitutions, trans('messages.allDataRetrieved'));
    }

//    public function testGetAllInactive() {
//
//        $expectedEducationInstitutions = [];
//        $middleExpectedEducationInstitutions = [];
//        $finalExpectedEducationInstitutions = [];
//
//        $selectedCompanyId = 0;
//        foreach($this->getRecords('education_specializations') as $seededEducationSpecializations) {
//
//            if ($selectedCompanyId === 0) {
//                $selectedCompanyId = $seededEducationSpecializations['company_id'];
//            }
//            if ($seededEducationSpecializations['company_id'] === $selectedCompanyId) {
//                array_push($expectedEducationSpecializations, [
//                    'id' => intval($seededEducationSpecializations['id']),
//                    'code' => $seededEducationSpecializations['code'],
//                    'name' => $seededEducationSpecializations['name'],
//                    'lovCategoryEducation' => $seededEducationSpecializations['lov_category_education'],
//                    'description' => $seededEducationSpecializations['description'],
//                    'effBegin' => $seededEducationSpecializations['eff_begin'],
//                    'effEnd' => $seededEducationSpecializations['eff_end']
//                ]);
//            }
//        }
//        usort($expectedEducationSpecializations, function ($a, $b) {
//            $da = strtotime($a['effEnd']);
//            $db = strtotime($b['effEnd']);
//            return $da < $db;
//        });
//
//        foreach ($expectedEducationSpecializations as $eduSpecializationExpectation) {
//            if ($eduSpecializationExpectation['effEnd'] < \Carbon\Carbon::now()) {
//                array_push($middleExpectedEducationSpecializations, [
//                    'id' => intval($eduSpecializationExpectation['id']),
//                    'code' => $eduSpecializationExpectation['code'],
//                    'name' => $eduSpecializationExpectation['name'],
//                    'lovCategoryEducation' => $eduSpecializationExpectation['lovCategoryEducation'],
//                    'description' => $eduSpecializationExpectation['description'],
//                    'effBegin' => $eduSpecializationExpectation['effBegin'],
//                    'effEnd' => $eduSpecializationExpectation['effEnd']
//                ]);
//            }
//        }
//
//        $uniqueEduSpecialization = collect($middleExpectedEducationSpecializations)->unique('code');
//        $uniqueEduSpecialization->values()->all();
//
//        foreach ($uniqueEduSpecialization as $eduSpecializationUnique) {
//            if ($eduSpecializationUnique['effEnd'] < \Carbon\Carbon::now()) {
//                array_push($finalExpectedEducationSpecializations, [
//                    'id' => intval($eduSpecializationUnique['id']),
//                    'code' => $eduSpecializationUnique['code'],
//                    'name' => $eduSpecializationUnique['name'],
//                    'lovCategoryEducation' => $eduSpecializationUnique['lovCategoryEducation'],
//                    'description' => $eduSpecializationUnique['description'],
//                    'effBegin' => $eduSpecializationUnique['effBegin'],
//                    'effEnd' => $eduSpecializationUnique['effEnd']
//                ]);
//            }
//        }
//
//        $this->expectManyObjectsSuccess('/educationSpecialization/getAllInactive', [
//            'companyId' => $selectedCompanyId,
//            'pageInfo' => [
//                'pageNo' => 1,
//                'pageLimit' => 10
//            ],
//        ], $finalExpectedEducationSpecializations, trans('messages.allDataRetrieved'));
//    }

//    public function testGetOne() {
//        $seededEducationSpecialization = $this->getRecords('education_specializations')->fetchOne(0);
//
//        $expectedEducationSpecializations = $this->newObject(
//            $this->transform($seededEducationSpecialization),
//            ['id' => intval($seededEducationSpecialization['id'])],
//            ['tenantId','companyId','createdAt','createdBy','updatedAt','updatedBy']
//        );
//        info('expectGetOne', array($expectedEducationSpecializations));
//        $this->expectObjectSuccess('/educationSpecialization/getOne',[
//            'companyId' => intval($seededEducationSpecialization['company_id']),
//            'id' => $seededEducationSpecialization['id']
//        ],[
//            'id',
//            'code',
//            'name',
//            'description',
//            'lovCategoryEducation',
//            'effBegin',
//            'effEnd'
//        ], $expectedEducationSpecializations, trans('messages.dataRetrieved'));
//    }

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