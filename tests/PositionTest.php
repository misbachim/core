<?php

use App\Business\Dao\JobDao;
use App\Business\Dao\UnitDao;
use App\Business\Dao\UnitTypeDao;
use Illuminate\Support\Facades\DB;
use App\Business\Helper\StringHelper;
use App\Business\Dao\PositionDao;
use App\Business\Dao\GradeDao;

/**
 * @property PositionDao positionDao
 * @property JobDao jobDao
 * @property UnitDao unitDao
 * @property UnitTypeDao unitTypeDao
 * @property GradeDao gradeDao
 * @property array grades
 * @property array gradesT
 * @property array job
 * @property array unitType
 * @property array unit
 * @property array positions
 * @property array positionsT
 */
class PositionTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->positionDao = new PositionDao($this->getRequester());
        $this->jobDao = new JobDao($this->getRequester());
        $this->unitDao = new UnitDao($this->getRequester());
        $this->unitTypeDao = new UnitTypeDao($this->getRequester());
        $this->gradeDao = new GradeDao($this->getRequester());

        $grade = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'ordinal' => 2,
            'work_month' => 12,
            'bottom_rate' => 10000,
            'mid_rate' => 20000,
            'top_rate' => 30000
        ];
        $this->grades = [];
        $this->gradesT = [];
        foreach (range(1, 5) as $i) {
            $grade['code'] = StringHelper::randomizeStr(5);
            $grade['name'] = StringHelper::randomizeStr(50);

            $grade['id'] = $this->gradeDao->save($grade);
            $this->seeInDatabase('grades', $grade);
            array_push($this->grades, $grade);

            $gradeT = $this->transform($grade);
            array_push($this->gradesT, $gradeT);
        }

        $this->job = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'code' => StringHelper::randomizeStr(5),
            'name' => StringHelper::randomizeStr(50),
            'description' => StringHelper::randomizeStr(160),
            'ordinal' => 2
        ];
        $this->job['id'] = $this->jobDao->save($this->job);
        $this->seeInDatabase('jobs', $this->job);

        $this->unitType = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'code' => StringHelper::randomizeStr(5),
            'name' => StringHelper::randomizeStr(50),
            'unit_level' => 1
        ];
        $this->unitTypeDao->save($this->unitType);
        $this->seeInDatabase('unit_types', $this->unitType);

        $this->unit = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'code' => StringHelper::randomizeStr(5),
            'name' => StringHelper::randomizeStr(50),
            'unit_type_code' => $this->unitType['code']
        ];
        $this->unit['id'] = $this->unitDao->save($this->unit);
        $this->seeInDatabase('units', $this->unit);

        $position = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'unit_code' => $this->unit['code'],
            'job_code' => $this->job['code'],
            'is_head' => true,
            'is_single' => true
        ];
        $this->positions = [];
        $this->positionsT = [];
        foreach (range(1, 15) as $i) {
            $position['code'] = StringHelper::randomizeStr(5);
            $position['name'] = StringHelper::randomizeStr(50);
            $position['description'] = StringHelper::randomizeStr(160);

            $position['id'] = $this->positionDao->save($position);
            $this->seeInDatabase('positions', $position);
            array_push($this->positions, $position);

            $positionT = $this->transform($position);
            array_push($this->positionsT, $positionT);

            unset($position['id']);
        }
    }

    public function testGetAll()
    {
        $this->positionsT = $this->exclude($this->positionsT, [
            'tenantId',
            'companyId',
            'isHead',
            'isSingle'
        ]);

        $this->json('POST', '/position/getAll', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'pageInfo' => [
                'pageLimit' => 15,
                'pageNo' => 1
            ]
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved'),
            'pageInfo' => [
                'totalRows' => 15,
                'pageLimit' => 15,
                'pageNo' => 1,
                'totalPages' => 1
            ]
        ])->seeJsonStructure([
            'data' => [
                [
                    'unitName',
                    'jobName'
                ]
            ]
        ]);

        foreach (array_slice($this->positionsT, 0, 15) as $positionT) {
            foreach ($positionT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetLov()
    {
        $this->json('POST', '/position/lov', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'unitCode' => $this->unit['code'],
            'jobCode' => $this->job['code']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->positionsT as $positionT) {
            $this->seeJson(['code' => $positionT['code']]);
        }
    }

    public function testGetAllInvalidPageInfo()
    {
        $this->positionsT = $this->exclude($this->positionsT, [
            'tenantId',
            'companyId'
        ]);

        $this->json('POST', '/position/getAll', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'pageInfo' => [
                'pageLimit' => -5,
                'pageNo' => -1
            ]
        ])->seeJson([
            'status' => 444
        ]);
    }

    public function testGetOne()
    {
        $position = $this->newPosition();
        $position['grades'] = [
            [
                'code' => $this->grades[0]['code']
            ],
            [
                'code' => $this->grades[1]['code']
            ]
        ];
        $position['cost_center_code'] = '';

        $this->json('POST', '/position/save', $this->transform($position))
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataSaved')
            ])->seeJsonStructure([
                'data' => [
                    'id'
                ]
            ]);

        $data = json_decode($this->response->getContent())->data;

        $this->json('POST', '/position/getOne', [
                'id' => $data->id,
                'companyId' => $position['company_id']
            ])->seeJson([
                'status' => 200,
                'message' => trans('messages.dataRetrieved')
            ])->seeJsonStructure([
                'data' => [
                    'costCenterCode',
                    'grades'
                ]
            ]);

        unset($position['tenant_id']);
        unset($position['company_id']);
        unset($position['grades']);
        $positionT = $this->transform($position);

        $this->seeJson(['bottomRate' => $this->grades[0]['bottom_rate']]);
        $this->seeJson(['midRate' => $this->grades[0]['mid_rate']]);
        $this->seeJson(['topRate' => $this->grades[0]['top_rate']]);

        foreach ($positionT as $field => $val) {
            $this->seeJson([$field => $val]);
        }

        $this->cleanUpSave($position);
    }

    public function testSave()
    {
        $position = $this->newPosition();
        $position['grades'] = [
            [
                'code' => $this->grades[0]['code']
            ],
            [
                'code' => $this->grades[1]['code']
            ]
        ];
        $position['cost_center_code'] = '';
        $positionT = $this->transform($position);

        $this->json('POST', '/position/save', $positionT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataSaved')
            ])->seeJsonStructure([
                'data' => [
                    'id'
                ]
            ]);

        foreach ($position['grades'] as $grade) {
            $this->seeInDatabase('position_grades', [
                'position_code' => $position['code'],
                'grade_code' => $grade['code']
            ]);
        }
        unset($position['grades']);
        $this->cleanUpSave($position);
    }

    public function testSaveFloatCompanyId()
    {
        $position = $this->newPosition();
        $position['company_id'] = 1900000000.1;
        $position['cost_center_code'] = '';
        $position['grades'] = [];
        $positionT = $this->transform($position);

        $this->json('POST', '/position/save', $positionT)
            ->seeJson([
                'status' => 444,
            ])
            ->seeJson([
                'data' => [
                    [
                        'key' => 'companyId',
                        'message' => ['The company id must be an integer.']
                    ]
                ]
            ]);

        unset($position['company_id']);
        unset($position['grades']);
        $this->notSeeInDatabase('positions', $position);
    }

    public function testSaveDuplicateCode()
    {
        $position = $this->positions[0];
        $position['eff_begin'] = '2017-10-2';
        $position['cost_center_code'] = '';
        $position['grades'] = [];
        $positionT = $this->transform($position);

        $this->json('POST', '/position/save', $positionT)
            ->seeJson([
                'status' => 422,
                'message' => trans('messages.duplicateCode')
            ]);

        unset($position['grades']);
        $this->notSeeInDatabase('positions', $position);
    }

    public function testSaveInvalidEffBegin()
    {
        $position = $this->newPosition();
        $position['eff_begin'] = '2018-12-01';
        $position['cost_center_code'] = '';
        $position['grades'] = [];
        $positionT = $this->transform($position);

        $this->json('POST', '/position/save', $positionT)
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

        unset($position['grades']);
        $this->notSeeInDatabase('positions', $position);
    }

    public function testSaveInvalidBottomRate()
    {
        $position = $this->newPosition();
        $position['grades'] = [
            [
                'code' => $this->grades[0]['code'],
                'bottomRate' => -1,
                'topRate' => 0
            ],
            [
                'code' => $this->grades[1]['code']
            ]
        ];
        $position['cost_center_code'] = '';
        $positionT = $this->transform($position);

        $this->json('POST', '/position/save', $positionT)
            ->seeJson([
                'status' => 444
            ])
            ->seeJson([
                'data' => [
                    [
                        'key' => 'grades.0.bottomRate',
                        'message' => ['The grades.0.bottom rate must be at least 0.']
                    ]
                ]
            ]);

        foreach ($position['grades'] as $grade) {
            $this->notSeeInDatabase('position_grades', [
                'position_code' => $position['code'],
                'grade_code' => $grade['code']
            ]);
        }
        unset($position['grades']);
        $this->notSeeInDatabase('positions', $position);
    }

    public function testSaveInvalidTopRate()
    {
        $position = $this->newPosition();
        $position['grades'] = [
            [
                'code' => $this->grades[0]['code'],
                'bottomRate' => 10000,
                'topRate' => 4000
            ]
        ];
        $position['cost_center_code'] = '';
        $positionT = $this->transform($position);

        $this->json('POST', '/position/save', $positionT)
            ->seeJson([
                'status' => 444
            ])
            ->seeJson([
                'key' => 'grades.0.topRate',
                'message' => ['grades.0.top rate must be greater than or equal to grades.0.bottomRate.']
            ]);

        foreach ($position['grades'] as $grade) {
            $this->notSeeInDatabase('position_grades', [
                'position_code' => $position['code'],
                'grade_code' => $grade['code']
            ]);
        }
        unset($position['grades']);
        $this->notSeeInDatabase('positions', $position);
    }

    public function testUpdate()
    {
        $position = $this->positions[0];
        $oldCode = $position['code'];
        $position['code'] = StringHelper::randomizeStr(5);
        $position['name'] = StringHelper::randomizeStr(50);
        $position['description'] = StringHelper::randomizeStr(160);
        $position['grades'] = [
            [
                'code' => $this->grades[0]['code'],
                'bottomRate' => 200,
                'topRate' => 10000
            ]
        ];
        $position['cost_center_code'] = '';
        $positionT = $this->transform($position);

        $this->json('POST', '/position/update', $positionT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        foreach ($position['grades'] as $grade) {
            $this->seeInDatabase('position_grades', [
                'position_code' => $position['code'],
                'grade_code' => $grade['code'],
                'bottom_rate' => $grade['bottomRate'],
                'mid_rate' => intdiv($grade['bottomRate']+$grade['topRate'], 2),
                'top_rate' => $grade['topRate']
            ]);
        }
        unset($position['grades']);
        $this->notSeeInDatabase('positions', $position);
        $position['code'] = $oldCode;
        $this->seeInDatabase('positions', $position);
        DB::table('positions')->where('id', $position['id'])->delete();
        $this->notSeeInDatabase('positions', $position);
        DB::table('position_grades')->delete();
    }

    public function tearDown()
    {
        foreach ($this->positions as $position) {
            DB::table('positions')->where('id', $position['id'])->delete();
            $this->notSeeInDatabase('positions', $position);
        }

        DB::table('units')->where('id', $this->unit['id'])->delete();
        $this->notSeeInDatabase('units', $this->unit);

        DB::table('unit_types')->where('code', $this->unitType['code'])->delete();
        $this->notSeeInDatabase('unit_types', $this->unitType);

        DB::table('jobs')->where('id', $this->job['id'])->delete();
        $this->notSeeInDatabase('jobs', $this->job);

        foreach ($this->grades as $grade) {
            DB::table('grades')->where('id', $grade['id'])->delete();
            $this->notSeeInDatabase('grades', $grade);
        }
    }

    private function newPosition()
    {
        $position = $this->positions[0];
        $position['code'] = StringHelper::randomizeStr(5);
        $position['name'] = StringHelper::randomizeStr(50);
        $position['description'] = StringHelper::randomizeStr(160);
        unset($position['id']);

        return $position;
    }

    private function cleanUpSave($position)
    {
        $data = json_decode($this->response->getContent())->data;
        $position['id'] = $data->id;
        $this->seeInDatabase('positions', $position);
        DB::table('positions')->where('id', $position['id'])->delete();
        $this->notSeeInDatabase('positions', $position);
        DB::table('job_grades')->delete();
    }
}
