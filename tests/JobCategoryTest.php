<?php

use App\Business\Dao\JobCategoryDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;


/**
 * @property JobCategoryDao jobCategoryDao
 */
class JobCategoryTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        DB::beginTransaction();

        $this->jobCategoryDao = new JobCategoryDao($this->getRequester());

        $data = array(
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'code' => StringHelper::randomizeStr(16),
            'name' => StringHelper::randomizeStr(50),
            'description' => StringHelper::randomizeStr(200),
        );

        $this->jobCategoryData = $data;

    }

    /**
     * Test getAll endpoint.
     *
     * @return void
     */

    public function testGetAll()
    {
        for ($i = 0; $i < 5; $i++) {
            $data = array(
                'tenant_id' => $this->getRequester()->getTenantId(),
                'company_id' => $this->getRequester()->getCompanyId(),
                'eff_begin' => '2017-10-01',
                'eff_end' => '2018-10-01',
                'code' => StringHelper::randomizeStr(16),
                'name' => StringHelper::randomizeStr(50),
                'description' => StringHelper::randomizeStr(200),
            );

            $this->jobCategoryDao->save($data);
        }

        $this->json('POST', '/jobCategory/getAll', [
            'companyId' => $this->getRequester()->getCompanyId()
        ])
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.allDataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        'effBegin',
                        'effEnd',
                        'code',
                        'name',
                        'description'
                    ]
                ]
            ]);
    }


    public function testGetOne()
    {
        $this->jobCategoryDao->save($this->jobCategoryData);

        $this->json('POST', '/jobCategory/getOne', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'code' => $this->jobCategoryData['code']
        ])
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    'id',
                    'effBegin',
                    'effEnd',
                    'code',
                    'name',
                    'description'
                ]
            ]);
    }


    public function testSave()
    {
        $this->json('POST', '/jobCategory/save', $this->transform($this->jobCategoryData))
            ->seeJson([
                "message" => trans("messages.dataSaved"),
                "status" => 200
            ])
            ->seeJsonStructure([
                "data" => [
                    "id"
                ]
            ]);

        $this->seeInDatabase('job_categories', $this->jobCategoryData);
    }

    public function testSaveBlankField()
    {
        $data = array(
            'company_id' => 1,
        );

        $this->json('POST', '/jobCategory/save', $this->transform($data))
            ->seeJson([
                "status" => 444,
                "message" => "The given data was invalid.",
                "key" => "effBegin",
                "key" => "effEnd",
                "key" => "code",
                "key" => "name",
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->notSeeInDatabase('job_categories', $data);
    }

    public function testSaveDuplicateCode()
    {
        $this->jobCategoryDao->save($this->jobCategoryData);

        $this->seeInDatabase('job_categories', $this->jobCategoryData);

        $this->json('POST', '/jobCategory/save', $this->transform($this->jobCategoryData))
            ->seeJson([
                'status' => 422,
                'message' => trans('messages.duplicateCode')
            ]);
    }

    public function testSaveInvalidEffBegin()
    {
        $this->jobCategoryData['eff_begin'] = '2018-12-01';

        $this->json('POST', '/jobCategory/save', $this->transform($this->jobCategoryData))
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

        $this->notSeeInDatabase('job_categories', $this->jobCategoryData);
    }

    public function testUpdate()
    {
        $data = $this->jobCategoryData;

        $data['name'] = StringHelper::randomizeStr(50);
        $data['description'] = StringHelper::randomizeStr(200);

        $data['id'] = $this->jobCategoryDao->save($this->jobCategoryData);

        $this->seeInDatabase('job_categories', $this->jobCategoryData);

        $this->json('POST', '/jobCategory/update', $this->transform($data))
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('job_categories', $this->jobCategoryData);

        $this->seeInDatabase('job_categories', $data);
    }

    public function testUpdateBlankField()
    {
        $data = array(
            'company_id' => 1,
        );

        $data['id'] = $this->jobCategoryDao->save($this->jobCategoryData);

        $this->json('POST', '/jobCategory/update', $this->transform($data))
            ->seeJson([
                "status" => 444,
                "key" => "effBegin",
                "key" => "effEnd",
                "key" => "code",
                "key" => "name",
                "key" => "description"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->notSeeInDatabase('job_categories', $data);
    }

    public function testDelete()
    {
        $data = $this->jobCategoryData;

        $data['id'] = $this->jobCategoryDao->save($this->jobCategoryData);
        $data['eff_end'] = Carbon::now();

        $this->json('POST', '/jobCategory/delete', $this->transform($data))
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('job_categories', $data);
    }

    public function tearDown()
    {
        DB::rollback();
    }
}
