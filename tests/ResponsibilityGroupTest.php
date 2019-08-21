<?php

use App\Business\Dao\ResponsibilityGroupDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @property ResponsibilityGroupDao responsibilityGroupDao
 */
class ResponsibilityGroupTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        DB::beginTransaction();

        $this->responsibilityGroupDao = new ResponsibilityGroupDao($this->getRequester());

        $data = array(
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'code' => StringHelper::randomizeStr(16),
            'name' => StringHelper::randomizeStr(50),
            'description' => StringHelper::randomizeStr(200),
        );

        $this->responsibilityGroupData = $data;

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

            $this->responsibilityGroupDao->save($data);
        }

        $this->json('POST', '/responsibilityGroup/getAll', [
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
        $this->responsibilityGroupDao->save($this->responsibilityGroupData);

        $this->json('POST', '/responsibilityGroup/getOne', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'code' => $this->responsibilityGroupData['code']
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
        $this->json('POST', '/responsibilityGroup/save', $this->transform($this->responsibilityGroupData))
            ->seeJson([
                "message" => trans("messages.dataSaved"),
                "status" => 200
            ])
            ->seeJsonStructure([
                "data" => [
                    "id"
                ]
            ]);

        $this->seeInDatabase('responsibility_groups', $this->responsibilityGroupData);
    }

    public function testSaveBlankField()
    {
        $data = array(
            'company_id' => 1,
        );

        $this->json('POST', '/responsibilityGroup/save', $this->transform($data))
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

        $this->notSeeInDatabase('responsibility_groups', $data);
    }

    public function testSaveDuplicateCode()
    {
        $this->responsibilityGroupDao->save($this->responsibilityGroupData);

        $this->seeInDatabase('responsibility_groups', $this->responsibilityGroupData);

        $this->json('POST', '/responsibilityGroup/save', $this->transform($this->responsibilityGroupData))
            ->seeJson([
                'status' => 422,
                'message' => trans('messages.duplicateCode')
            ]);
    }

    public function testSaveInvalidEffBegin()
    {
        $this->responsibilityGroupData['eff_begin'] = '2018-12-01';

        $this->json('POST', '/responsibilityGroup/save', $this->transform($this->responsibilityGroupData))
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

        $this->notSeeInDatabase('responsibility_groups', $this->responsibilityGroupData);
    }

    public function testUpdate()
    {
        $data = $this->responsibilityGroupData;

        $data['name'] = StringHelper::randomizeStr(50);
        $data['description'] = StringHelper::randomizeStr(200);

        $data['id'] = $this->responsibilityGroupDao->save($this->responsibilityGroupData);

        $this->seeInDatabase('responsibility_groups', $this->responsibilityGroupData);

        $this->json('POST', '/responsibilityGroup/update', $this->transform($data))
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('responsibility_groups', $this->responsibilityGroupData);

        $this->seeInDatabase('responsibility_groups', $data);
    }

    public function testUpdateBlankField()
    {
        $data = array(
            'company_id' => 1,
        );

        $data['id'] = $this->responsibilityGroupDao->save($this->responsibilityGroupData);

        $this->json('POST', '/responsibilityGroup/update', $this->transform($data))
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

        $this->notSeeInDatabase('responsibility_groups', $data);
    }

    public function testDelete()
    {
        $data = $this->responsibilityGroupData;

        $data['id'] = $this->responsibilityGroupDao->save($this->responsibilityGroupData);
        $data['eff_end'] = Carbon::now();

        $this->json('POST', '/responsibilityGroup/delete', $this->transform($data))
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('responsibility_groups', $data);
    }

    public function tearDown()
    {
        DB::rollback();
    }
}
