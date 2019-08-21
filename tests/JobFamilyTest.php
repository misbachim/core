<?php

use App\Business\Dao\JobFamilyDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @property JobFamilyDao jobFamilyDao
 * @property array jobFamilies
 * @property array jobFamiliesT
 */
class JobFamilyTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->jobFamilyDao = new JobFamilyDao($this->getRequester());
        $jobFamily = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01'
        ];

        $this->jobFamilies = [];
        $this->jobFamiliesT = [];
        foreach (range(1, 10) as $i) {
            $jobFamily['code'] = StringHelper::randomizeStr(16);
            $jobFamily['name'] = StringHelper::randomizeStr(50);
            $jobFamily['description'] = StringHelper::randomizeStr(200);

            $jobFamily['id'] = $this->jobFamilyDao->save($jobFamily);
            $this->seeInDatabase('job_families', $jobFamily);
            array_push($this->jobFamilies, $jobFamily);

            $jobFamilyT = $this->transform($jobFamily);
            array_push($this->jobFamiliesT, $jobFamilyT);

            unset($jobFamily['id']);
        }
    }

    public function testGetAll()
    {
        $this->jobFamiliesT = $this->exclude($this->jobFamiliesT, [
            'tenantId',
            'companyId'
        ]);

        $this->json('POST', '/jobFamily/getAll', [
            'companyId' => $this->getRequester()->getCompanyId()
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->jobFamiliesT as $jobFamilyT) {
            foreach ($jobFamilyT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetLov()
    {
        $this->json('POST', '/jobFamily/lov', [
            'companyId' => $this->getRequester()->getCompanyId()
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->jobFamiliesT as $jobFamilyT) {
            $this->seeJson(['code' => $jobFamilyT['code']]);
        }
    }

    public function testGetOne()
    {
        $this->jobFamiliesT = $this->exclude($this->jobFamiliesT, [
            'tenantId',
            'companyId'
        ]);

        $this->json('POST', '/jobFamily/getOne', [
            'code' => $this->jobFamilies[0]['code'],
            'companyId' => $this->jobFamilies[0]['company_id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ]);

        foreach ($this->jobFamiliesT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $jobFamily = $this->newJobFamily();
        $jobFamilyT = $this->transform($jobFamily);

        $this->json('POST', '/jobFamily/save', $jobFamilyT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataSaved')
            ])
            ->seeJsonStructure([
                'data' => [
                    'id'
                ]
            ]);

        $data = json_decode($this->response->getContent())->data;
        $jobFamily['id'] = $data->id;
        $this->seeInDatabase('job_families', $jobFamily);
        DB::table('job_families')->where('id', $jobFamily['id'])->delete();
        $this->notSeeInDatabase('job_families', $jobFamily);
    }

    public function testSaveFloatCompanyId()
    {
        $jobFamily = $this->newJobFamily();
        $jobFamily['company_id'] = 1900000000.1;
        $jobFamilyT = $this->transform($jobFamily);

        $this->json('POST', '/jobFamily/save', $jobFamilyT)
            ->seeJson([
                'status' => 444
            ])
            ->seeJson([
                'data' => [
                    [
                        'key' => 'companyId',
                        'message' => ['The company id must be an integer.']
                    ]
                ]
            ]);

        unset($jobFamily['company_id']);
        $this->notSeeInDatabase('job_families', $jobFamily);
    }

    public function testSaveDuplicateCode()
    {
        $jobFamily = $this->jobFamilies[0];
        $jobFamily['eff_begin'] = '2017-10-2';
        $jobFamily['grades'] = [];
        $jobFamilyT = $this->transform($jobFamily);

        $this->json('POST', '/jobFamily/save', $jobFamilyT)
            ->seeJson([
                'status' => 422,
                'message' => trans('messages.duplicateCode')
            ]);

        unset($jobFamily['grades']);
        $this->notSeeInDatabase('job_families', $jobFamily);
    }

    public function testSaveInvalidEffBegin()
    {
        $jobFamily = $this->newJobFamily();
        $jobFamily['eff_begin'] = '2018-12-01';
        $jobFamilyT = $this->transform($jobFamily);

        $this->json('POST', '/jobFamily/save', $jobFamilyT)
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

        $this->notSeeInDatabase('job_families', $jobFamily);
    }

    public function testUpdate()
    {
        $jobFamily = $this->jobFamilies[0];
        $oldCode = $jobFamily['code'];
        $jobFamily['code'] = StringHelper::randomizeStr(5);
        $jobFamily['name'] = StringHelper::randomizeStr(50);
        $jobFamilyT = $this->transform($jobFamily);

        $this->json('POST', '/jobFamily/update', $jobFamilyT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        $this->notSeeInDatabase('job_families', $jobFamily);
        $jobFamily['code'] = $oldCode;
        $this->seeInDatabase('job_families', $jobFamily);
        DB::table('job_families')->where('id', $jobFamily['id'])->delete();
        $this->notSeeInDatabase('job_families', $jobFamily);
    }

    public function tearDown()
    {
        foreach ($this->jobFamilies as $jobFamily) {
            DB::table('job_families')->where('id', $jobFamily['id'])->delete();
            $this->notSeeInDatabase('job_families', $jobFamily);
        }
    }

    public function newJobFamily()
    {
        $jobFamily = $this->jobFamilies[0];
        $jobFamily['code'] = StringHelper::randomizeStr(5);
        $jobFamily['name'] = StringHelper::randomizeStr(50);
        unset($jobFamily['id']);

        return $jobFamily;
    }
}
