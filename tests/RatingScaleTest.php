<?php

class RatingScaleTest extends SeededTestCase
{
    public static function setUpBeforeClass()
    {
        self::read('rating_scales');
        parent::setUpBeforeClass();
    }

    public function testGetAll() {

        $expectedRatingScales = [];
        $finalExpectedRatingScales = [];
        $selectedCompanyId = 0;

        foreach($this->getRecords('rating_scales') as $seededRatingScale) {

            if ($selectedCompanyId === 0) {
                $selectedCompanyId = $seededRatingScale['company_id'];
            }

            if ($seededRatingScale['company_id'] === $selectedCompanyId) {
                array_push($expectedRatingScales, [
                    'id' => intval($seededRatingScale['id']),
                    'code' => $seededRatingScale['code'],
                    'name' => $seededRatingScale['name'],
                    'description' => $seededRatingScale['description'],
                    'effBegin' => $seededRatingScale['eff_begin'],
                    'effEnd' => $seededRatingScale['eff_end']
                ]);
            }
        }
        usort($expectedRatingScales, function ($a, $b) {
            $da = strtotime($a['effEnd']);
            $db = strtotime($b['effEnd']);
            return $da < $db;
        });

        info('dataTest', [$expectedRatingScales]);

        $uniqueRatingScales = collect($expectedRatingScales)->unique('code');
        $uniqueRatingScales->values()->all();

        foreach ($uniqueRatingScales as $ratingScale) {
            array_push($finalExpectedRatingScales, [
                'id' => intval($ratingScale['id']),
                'code' => $ratingScale['code'],
                'name' => $ratingScale['name'],
                'description' => $ratingScale['description'],
                'effBegin' => $ratingScale['effBegin'],
                'effEnd' => $ratingScale['effEnd']
            ]);
        }

        $this->expectManyObjectsSuccess('/ratingScale/getAll', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 10
            ],

        ], $finalExpectedRatingScales, trans('messages.allDataRetrieved'));
    }

    public function testGetAllActive() {

        $expectedRatingScales = [];
        $finalExpectedRatingScales = [];
        $selectedCompanyId = 0;
        foreach($this->getRecords('rating_scales') as $seededRatingScale) {

            if ($selectedCompanyId === 0) {
                $selectedCompanyId = $seededRatingScale['company_id'];
            }
            if ($seededRatingScale['company_id'] === $selectedCompanyId) {
                array_push($expectedRatingScales, [
                    'id' => intval($seededRatingScale['id']),
                    'code' => $seededRatingScale['code'],
                    'name' => $seededRatingScale['name'],
                    'description' => $seededRatingScale['description'],
                    'effBegin' => $seededRatingScale['eff_begin'],
                    'effEnd' => $seededRatingScale['eff_end']
                ]);
            }
        }
        usort($expectedRatingScales, function ($a, $b) {
            $da = strtotime($a['effEnd']);
            $db = strtotime($b['effEnd']);
            return $da < $db;
        });

        $uniqueRatingScales = collect($expectedRatingScales)->unique('code');
        $uniqueRatingScales->values()->all();

        foreach ($uniqueRatingScales as $ratingScaleUnique) {
            if ($ratingScaleUnique['effEnd'] >= \Carbon\Carbon::now()) {
                array_push($finalExpectedRatingScales, [
                    'id' => intval($ratingScaleUnique['id']),
                    'code' => $ratingScaleUnique['code'],
                    'name' => $ratingScaleUnique['name'],
                    'description' => $ratingScaleUnique['description'],
                    'effBegin' => $ratingScaleUnique['effBegin'],
                    'effEnd' => $ratingScaleUnique['effEnd']
                ]);
            }
        }

        $this->expectManyObjectsSuccess('/ratingScale/getAllActive', [
            'companyId' => $selectedCompanyId,
            'pageInfo' => [
                'pageNo' => 1,
                'pageLimit' => 10
            ],
        ], $finalExpectedRatingScales, trans('messages.allDataRetrieved'));
    }

    // public function testGetAllInactive() {

    //     $expectedRatingScales = [];
    //     $middleExpectedEducationSpecializations = [];
    //     $finalExpectedRatingScales = [];

    //     $selectedCompanyId = 0;
    //     foreach($this->getRecords('rating_scales') as $seededRatingScale) {

    //         if ($selectedCompanyId === 0) {
    //             $selectedCompanyId = $seededRatingScale['company_id'];
    //         }
    //         if ($seededRatingScale['company_id'] === $selectedCompanyId) {
    //             array_push($expectedRatingScales, [
    //                 'id' => intval($seededRatingScale['id']),
    //                 'code' => $seededRatingScale['code'],
    //                 'name' => $seededRatingScale['name'],
                    
    //                 'description' => $seededRatingScale['description'],
    //                 'effBegin' => $seededRatingScale['eff_begin'],
    //                 'effEnd' => $seededRatingScale['eff_end']
    //             ]);
    //         }
    //     }
    //     usort($expectedRatingScales, function ($a, $b) {
    //         $da = strtotime($a['effEnd']);
    //         $db = strtotime($b['effEnd']);
    //         return $da < $db;
    //     });

    //     foreach ($expectedRatingScales as $ratingScaleExpectation) {
    //         if ($ratingScaleExpectation['effEnd'] < \Carbon\Carbon::now()) {
    //             array_push($middleExpectedEducationSpecializations, [
    //                 'id' => intval($ratingScaleExpectation['id']),
    //                 'code' => $ratingScaleExpectation['code'],
    //                 'name' => $ratingScaleExpectation['name'],
    //                 'description' => $ratingScaleExpectation['description'],
    //                 'effBegin' => $ratingScaleExpectation['effBegin'],
    //                 'effEnd' => $ratingScaleExpectation['effEnd']
    //             ]);
    //         }
    //     }

    //     $uniqueRatingScales = collect($middleExpectedEducationSpecializations)->unique('code');
    //     $uniqueRatingScales->values()->all();

    //     foreach ($uniqueRatingScales as $ratingScaleUnique) {
    //         if ($ratingScaleUnique['effEnd'] < \Carbon\Carbon::now()) {
    //             array_push($finalExpectedRatingScales, [
    //                 'id' => intval($ratingScaleUnique['id']),
    //                 'code' => $ratingScaleUnique['code'],
    //                 'name' => $ratingScaleUnique['name'],
    //                 'description' => $ratingScaleUnique['description'],
    //                 'effBegin' => $ratingScaleUnique['effBegin'],
    //                 'effEnd' => $ratingScaleUnique['effEnd']
    //             ]);
    //         }
    //     }

    //     $this->expectManyObjectsSuccess('/ratingScale/getAllInactive', [
    //         'companyId' => $selectedCompanyId,
    //         'pageInfo' => [
    //             'pageNo' => 1,
    //             'pageLimit' => 10
    //         ],
    //     ], $finalExpectedRatingScales, trans('messages.allDataRetrieved'));
    // }

    public function testGetOne() {
        $seededEducationSpecialization = $this->getRecords('rating_scales')->fetchOne(0);

        $expectedRatingScales = $this->newObject(
            $this->transform($seededEducationSpecialization),
            ['id' => intval($seededEducationSpecialization['id'])],
            ['tenantId','companyId','createdAt','createdBy','updatedAt','updatedBy','effEnd']
        );
        info('expectGetOne', array($expectedRatingScales));

        $this->expectObjectSuccess('/ratingScale/getOne',[
            'code' => $seededEducationSpecialization['code']
        ],[
            'id',
            'code',
            'name',
            'description',
            'effBegin',
        ], $expectedRatingScales, trans('messages.dataRetrieved'));
    }
}
