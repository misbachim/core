<?php

use App\Business\Dao\WorkingConditionTypeDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @property WorkingConditionTypeDao workingConditionTypeDao
 */
class WorkingConditionTypeTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        DB::beginTransaction();

        $this->workingConditionTypeDao = new WorkingConditionTypeDao($this->getRequester());

        $data = array(
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'code' => StringHelper::randomizeStr(16),
            'name' => StringHelper::randomizeStr(50),
            'description' => StringHelper::randomizeStr(200),
        );

        $this->workingConditionTypeData = $data;

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

            $this->workingConditionTypeDao->save($data);
        }

        $this->json('POST', '/workingConditionType/getAll', [
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
        $this->workingConditionTypeDao->save($this->workingConditionTypeData);

        $this->json('POST', '/workingConditionType/getOne', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'code' => $this->workingConditionTypeData['code']
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
        $this->json('POST', '/workingConditionType/save', $this->transform($this->workingConditionTypeData))
            ->seeJson([
                "message" => trans("messages.dataSaved"),
                "status" => 200
            ])
            ->seeJsonStructure([
                "data" => [
                    "id"
                ]
            ]);

        $this->seeInDatabase('working_condition_types', $this->workingConditionTypeData);
    }

    public function testSaveBlankField()
    {
        $data = array(
            'company_id' => 1,
        );

        $this->json('POST', '/workingConditionType/save', $this->transform($data))
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

        $this->notSeeInDatabase('working_condition_types', $data);
    }

    public function testSaveDuplicateCode()
    {
        $this->workingConditionTypeDao->save($this->workingConditionTypeData);

        $this->seeInDatabase('working_condition_types', $this->workingConditionTypeData);

        $this->json('POST', '/workingConditionType/save', $this->transform($this->workingConditionTypeData))
            ->seeJson([
                'status' => 422,
                'message' => trans('messages.duplicateCode')
            ]);
    }

    public function testSaveInvalidEffBegin()
    {
        $this->workingConditionTypeData['eff_begin'] = '2018-12-01';

        $this->json('POST', '/workingConditionType/save', $this->transform($this->workingConditionTypeData))
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

        $this->notSeeInDatabase('working_condition_types', $this->workingConditionTypeData);
    }

    public function testUpdate()
    {
        $data = $this->workingConditionTypeData;

        $data['name'] = StringHelper::randomizeStr(50);
        $data['description'] = StringHelper::randomizeStr(200);

        $data['id'] = $this->workingConditionTypeDao->save($this->workingConditionTypeData);

        $this->seeInDatabase('working_condition_types', $this->workingConditionTypeData);

        $this->json('POST', '/workingConditionType/update', $this->transform($data))
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('working_condition_types', $this->workingConditionTypeData);

        $this->seeInDatabase('working_condition_types', $data);
    }

    public function testUpdateBlankField()
    {
        $data = array(
            'company_id' => 1,
        );

        $data['id'] = $this->workingConditionTypeDao->save($this->workingConditionTypeData);

        $this->json('POST', '/workingConditionType/update', $this->transform($data))
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

        $this->notSeeInDatabase('working_condition_types', $data);
    }

    public function testDelete()
    {
        $data = $this->workingConditionTypeData;

        $data['id'] = $this->workingConditionTypeDao->save($this->workingConditionTypeData);
        $data['eff_end'] = Carbon::now();

        $this->json('POST', '/workingConditionType/delete', $this->transform($data))
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('working_condition_types', $data);
    }

    public function tearDown()
    {
        DB::rollback();
    }
}
