<?php

use App\Business\Dao\WorkingConditionDao;
use App\Business\Dao\WorkingConditionTypeDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @property WorkingConditionDao workingConditionDao
 * @property WorkingConditionTypeDao workingConditionTypeDao
 */
class WorkingConditionTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        DB::beginTransaction();

        $this->workingConditionDao = new WorkingConditionDao($this->getRequester());
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

        $this->workingConditionTypeDao->save($data);
        $code = $this->workingConditionTypeDao->getAll();

        $data['working_condition_type_code'] = $code[0]->code;

        $this->workingConditionData = $data;

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
                'working_condition_type_code' => $this->workingConditionData['working_condition_type_code'],
                'code' => StringHelper::randomizeStr(16),
                'name' => StringHelper::randomizeStr(50),
                'description' => StringHelper::randomizeStr(200),
            );

            $this->workingConditionDao->save($data);
        }

        $this->json('POST', '/workingCondition/getAll', [
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
                        'workingConditionTypeCode',
                        'code',
                        'name',
                        'description'
                    ]
                ]
            ]);
    }


    public function testGetOne()
    {
        $this->workingConditionDao->save($this->workingConditionData);

        $this->json('POST', '/workingCondition/getOne', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'code' => $this->workingConditionData['code']
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
                    'workingConditionTypeCode',
                    'code',
                    'name',
                    'description'
                ]
            ]);
    }


    public function testSave()
    {
        $this->json('POST', '/workingCondition/save', $this->transform($this->workingConditionData))
            ->seeJson([
                "message" => trans("messages.dataSaved"),
                "status" => 200
            ])
            ->seeJsonStructure([
                "data" => [
                    "id"
                ]
            ]);

        $this->seeInDatabase('working_conditions', $this->workingConditionData);
    }

    public function testSaveBlankField()
    {
        $data = array(
            'company_id' => 1,
        );

        $this->json('POST', '/workingCondition/save', $this->transform($data))
            ->seeJson([
                "status" => 444,
                "message" => "The given data was invalid.",
                "key" => "effBegin",
                "key" => "effEnd",
                "key" => "workingConditionTypeCode",
                "key" => "code",
                "key" => "name",
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->notSeeInDatabase('working_conditions', $data);
    }

    public function testSaveDuplicateCode()
    {
        $this->workingConditionDao->save($this->workingConditionData);

        $this->seeInDatabase('working_conditions', $this->workingConditionData);

        $this->json('POST', '/workingCondition/save', $this->transform($this->workingConditionData))
            ->seeJson([
                'status' => 422,
                'message' => trans('messages.duplicateCode')
            ]);
    }

    public function testSaveInvalidEffBegin()
    {
        $this->workingConditionData['eff_begin'] = '2018-12-01';

        $this->json('POST', '/workingCondition/save', $this->transform($this->workingConditionData))
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

        $this->notSeeInDatabase('working_conditions', $this->workingConditionData);
    }

    public function testUpdate()
    {
        $data = $this->workingConditionData;

        $data['name'] = StringHelper::randomizeStr(50);
        $data['description'] = StringHelper::randomizeStr(200);

        $data['id'] = $this->workingConditionDao->save($this->workingConditionData);

        $this->seeInDatabase('working_conditions', $this->workingConditionData);

        $this->json('POST', '/workingCondition/update', $this->transform($data))
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('working_conditions', $this->workingConditionData);

        $this->seeInDatabase('working_conditions', $data);
    }

    public function testUpdateBlankField()
    {
        $data = array(
            'company_id' => 1,
        );

        $data['id'] = $this->workingConditionDao->save($this->workingConditionData);

        $this->json('POST', '/workingCondition/update', $this->transform($data))
            ->seeJson([
                "status" => 444,
                "key" => "effBegin",
                "key" => "effEnd",
                "key" => "workingConditionTypeCode",
                "key" => "code",
                "key" => "name",
                "key" => "description"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->notSeeInDatabase('working_conditions', $data);
    }

    public function testDelete()
    {
        $data = $this->workingConditionData;

        $data['id'] = $this->workingConditionDao->save($this->workingConditionData);
        $data['eff_end'] = Carbon::now();

        $this->json('POST', '/workingCondition/delete', $this->transform($data))
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('working_conditions', $data);
    }

    public function tearDown()
    {
        DB::rollback();
    }
}
