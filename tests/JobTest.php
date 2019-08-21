<?php

use App\Business\Dao\JobFamilyDao;
use Illuminate\Support\Facades\DB;
use App\Business\Helper\StringHelper;
use App\Business\Dao\JobDao;
use App\Business\Dao\GradeDao;

/**
 * @property JobDao jobDao
 * @property array jobs
 * @property array jobsT
 * @property array jobFamily
 * @property JobFamilyDao jobFamilyDao
 * @property GradeDao gradeDao
 * @property array grades
 * @property array gradesT
 */
class JobTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->jobDao = new JobDao($this->getRequester());
        $this->jobFamilyDao = new JobFamilyDao($this->getRequester());
        $this->gradeDao = new GradeDao($this->getRequester());

        $job = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'ordinal' => 2
        ];
        $this->jobFamily = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'code' => StringHelper::randomizeStr(16),
            'name' => StringHelper::randomizeStr(50)
        ];
        $this->jobFamily['id'] = $this->jobFamilyDao->save($this->jobFamily);
        $this->seeInDatabase('job_families', $this->jobFamily);

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

        $this->jobs = [];
        $this->jobsT = [];
        foreach (range(1, 15) as $i) {
            $job['code'] = StringHelper::randomizeStr(5);
            $job['name'] = StringHelper::randomizeStr(50);
            $job['description'] = StringHelper::randomizeStr(160);
            $job['job_family_code'] = $this->jobFamily['code'];

            $job['id'] = $this->jobDao->save($job);
            $this->seeInDatabase('jobs', $job);
            array_push($this->jobs, $job);

            $jobT = $this->transform($job);
            array_push($this->jobsT, $jobT);

            unset($job['id']);
        }
    }

    public function testGetAll()
    {
        $this->jobsT = $this->exclude($this->jobsT, [
            'tenantId',
            'companyId',
            'jobFamilyCode',
            'ordinal'
        ]);

        $this->json('POST', '/job/getAll', [
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
                    'jobFamilyName'
                ]
            ]
        ]);

        foreach (array_slice($this->jobsT, 0, 15) as $jobT) {
            foreach ($jobT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetLov()
    {
        $this->json('POST', '/job/lov', [
            'companyId' => $this->getRequester()->getCompanyId()
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->jobsT as $jobT) {
            $this->seeJson(['code' => $jobT['code']]);
        }
    }

    public function testGetAllInvalidPageInfo()
    {
        $this->jobsT = $this->exclude($this->jobsT, [
            'tenantId',
            'companyId',
            'jobFamilyCode',
            'ordinal'
        ]);

        $this->json('POST', '/job/getAll', [
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
        $job = $this->newJob();
        $job['grades'] = [
            [
                'code' => $this->grades[0]['code']
            ],
            [
                'code' => $this->grades[1]['code']
            ]
        ];

        $this->json('POST', '/job/save', $this->transform($job))
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataSaved')
            ])->seeJsonStructure([
                'data' => [
                    'id'
                ]
            ]);

        $data = json_decode($this->response->getContent())->data;

        $this->json('POST', '/job/getOne', [
                'id' => $data->id,
                'companyId' => $job['company_id']
            ])->seeJson([
                'status' => 200,
                'message' => trans('messages.dataRetrieved')
            ])->seeJsonStructure([
                'data' => [
                    'grades'
                ]
            ]);

        unset($job['tenant_id']);
        unset($job['company_id']);
        unset($job['grades']);
        $jobT = $this->transform($job);

        $this->seeJson(['bottomRate' => $this->grades[0]['bottom_rate']]);
        $this->seeJson(['midRate' => $this->grades[0]['mid_rate']]);
        $this->seeJson(['topRate' => $this->grades[0]['top_rate']]);

        foreach ($jobT as $field => $val) {
            $this->seeJson([$field => $val]);
        }

        $this->cleanUpSave($job);
    }

    public function testSave()
    {
        $job = $this->newJob();
        $job['grades'] = [
            [
                'code' => $this->grades[0]['code']
            ],
            [
                'code' => $this->grades[1]['code']
            ]
        ];
        $jobT = $this->transform($job);

        $this->json('POST', '/job/save', $jobT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataSaved')
            ])->seeJsonStructure([
                'data' => [
                    'id'
                ]
            ]);

        foreach ($job['grades'] as $grade) {
            $this->seeInDatabase('job_grades', [
                'job_code' => $job['code'],
                'grade_code' => $grade['code']
            ]);
        }
        unset($job['grades']);
        $this->cleanUpSave($job);
    }

    public function testSaveFloatCompanyId()
    {
        $job = $this->newJob();
        $job['company_id'] = 1900000000.1;
        $job['grades'] = [];
        $jobT = $this->transform($job);

        $this->json('POST', '/job/save', $jobT)
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

        unset($job['company_id']);
        unset($job['grades']);
        $this->notSeeInDatabase('jobs', $job);
    }

    public function testSaveDuplicateCode()
    {
        $job = $this->jobs[0];
        $job['eff_begin'] = '2017-10-2';
        $job['grades'] = [];
        $jobT = $this->transform($job);

        $this->json('POST', '/job/save', $jobT)
            ->seeJson([
                'status' => 422,
                'message' => trans('messages.duplicateCode')
            ]);

        unset($job['grades']);
        $this->notSeeInDatabase('jobs', $job);
    }

    public function testSaveInvalidJobFamilyCode()
    {
        $job = $this->newJob();
        $job['job_family_code'] = $this->jobFamily['code'].'X';
        $job['grades'] = [];
        $jobT = $this->transform($job);

        $this->json('POST', '/job/save', $jobT)
            ->seeJson([
                'status' => 444
            ])
            ->seeJson([
                'data' => [
                    [
                        'key' => 'jobFamilyCode',
                        'message' => ['The selected job family code is invalid.']
                    ]
                ]
            ]);

        unset($job['grades']);
        $this->notSeeInDatabase('jobs', $job);
    }

    public function testSaveInvalidEffBegin()
    {
        $job = $this->newJob();
        $job['eff_begin'] = '2018-12-01';
        $job['grades'] = [];
        $jobT = $this->transform($job);

        $this->json('POST', '/job/save', $jobT)
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

        unset($job['grades']);
        $this->notSeeInDatabase('jobs', $job);
    }

    public function testSaveInvalidBottomRate()
    {
        $job = $this->newJob();
        $job['grades'] = [
            [
                'code' => $this->grades[0]['code'],
                'bottomRate' => -1,
                'topRate' => 0
            ],
            [
                'code' => $this->grades[1]['code']
            ]
        ];
        $jobT = $this->transform($job);

        $this->json('POST', '/job/save', $jobT)
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

        foreach ($job['grades'] as $grade) {
            $this->notSeeInDatabase('job_grades', [
                'job_code' => $job['code'],
                'grade_code' => $grade['code']
            ]);
        }
        unset($job['grades']);
        $this->notSeeInDatabase('jobs', $job);
    }

    public function testSaveInvalidTopRate()
    {
        $job = $this->newJob();
        $job['grades'] = [
            [
                'code' => $this->grades[0]['code'],
                'bottomRate' => 10000,
                'topRate' => 4000
            ]
        ];
        $jobT = $this->transform($job);

        $this->json('POST', '/job/save', $jobT)
            ->seeJson([
                'status' => 444
            ])
            ->seeJson([
                'key' => 'grades.0.topRate',
                'message' => ['grades.0.top rate must be greater than or equal to grades.0.bottomRate.']
            ]);

        foreach ($job['grades'] as $grade) {
            $this->notSeeInDatabase('job_grades', [
                'job_code' => $job['code'],
                'grade_code' => $grade['code']
            ]);
        }
        unset($job['grades']);
        $this->notSeeInDatabase('jobs', $job);
    }

    public function testUpdate()
    {
        $job = $this->jobs[0];
        $oldCode = $job['code'];
        $job['code'] = StringHelper::randomizeStr(5);
        $job['name'] = StringHelper::randomizeStr(50);
        $job['description'] = StringHelper::randomizeStr(160);
        $job['grades'] = [
            [
                'code' => $this->grades[0]['code'],
                'bottomRate' => 200,
                'topRate' => 10000
            ]
        ];
        $jobT = $this->transform($job);

        $this->json('POST', '/job/update', $jobT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        foreach ($job['grades'] as $grade) {
            $this->seeInDatabase('job_grades', [
                'job_code' => $job['code'],
                'grade_code' => $grade['code'],
                'bottom_rate' => $grade['bottomRate'],
                'mid_rate' => intdiv($grade['bottomRate']+$grade['topRate'], 2),
                'top_rate' => $grade['topRate']
            ]);
        }
        unset($job['grades']);
        $this->notSeeInDatabase('jobs', $job);
        $job['code'] = $oldCode;
        $this->seeInDatabase('jobs', $job);
        DB::table('jobs')->where('id', $job['id'])->delete();
        $this->notSeeInDatabase('jobs', $job);
        DB::table('job_grades')->delete();
    }

    public function tearDown()
    {
        foreach ($this->jobs as $job) {
            DB::table('jobs')->where('id', $job['id'])->delete();
            $this->notSeeInDatabase('jobs', $job);
        }
        foreach ($this->grades as $grade) {
            DB::table('grades')->where('id', $grade['id'])->delete();
            $this->notSeeInDatabase('grades', $grade);
        }

        DB::table('job_families')->where('id', $this->jobFamily['id'])->delete();
    }

    private function newJob()
    {
        $job = $this->jobs[0];
        $job['code'] = StringHelper::randomizeStr(5);
        $job['name'] = StringHelper::randomizeStr(50);
        $job['description'] = StringHelper::randomizeStr(160);
        unset($job['id']);

        return $job;
    }

    private function cleanUpSave($job)
    {
        $data = json_decode($this->response->getContent())->data;
        $job['id'] = $data->id;
        $this->seeInDatabase('jobs', $job);
        DB::table('jobs')->where('id', $job['id'])->delete();
        $this->notSeeInDatabase('jobs', $job);
        DB::table('job_grades')->delete();
    }
}
