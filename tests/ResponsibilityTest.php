<?php

use App\Business\Dao\ResponsibilityDao;
use App\Business\Dao\ResponsibilityGroupDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

/**
 * @property ResponsibilityDao responsibilityDao
 * @property ResponsibilityGroupDao responsibilityGroupDao
 */
class ResponsibilityTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        DB::beginTransaction();

        $this->responsibilityDao = new ResponsibilityDao($this->getRequester());
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

        $this->responsibilityGroupDao->save($data);
        $code = $this->responsibilityGroupDao->getAll();

        $data['responsibility_group_code'] = $code[0]->code;

        $this->responsibilityData = $data;

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
                'responsibility_group_code' => $this->responsibilityData['responsibility_group_code'],
                'code' => StringHelper::randomizeStr(16),
                'name' => StringHelper::randomizeStr(50),
                'description' => StringHelper::randomizeStr(200),
            );

            $this->responsibilityDao->save($data);
        }

        $this->json('POST', '/responsibility/getAll', [
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
                        'responsibilityGroupCode',
                        'code',
                        'name',
                        'description'
                    ]
                ]
            ]);
    }

    public function testGetOne()
    {
        $this->responsibilityDao->save($this->responsibilityData);

        $this->json('POST', '/responsibility/getOne', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'code' => $this->responsibilityData['code']
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
                    'responsibilityGroupCode',
                    'code',
                    'name',
                    'description'
                ]
            ]);
    }


    public function testSave()
    {
        $this->json('POST', '/responsibility/save', $this->transform($this->responsibilityData))
            ->seeJson([
                "message" => trans("messages.dataSaved"),
                "status" => 200
            ])
            ->seeJsonStructure([
                "data" => [
                    "id"
                ]
            ]);

        $this->seeInDatabase('responsibilities', $this->responsibilityData);
    }

    public function testSaveBlankField()
    {
        $data = array(
            'company_id' => 1,
        );

        $this->json('POST', '/responsibility/save', $this->transform($data))
            ->seeJson([
                "status" => 444,
                "message" => "The given data was invalid.",
                "key" => "effBegin",
                "key" => "effEnd",
                "key" => "responsibilityGroupCode",
                "key" => "code",
                "key" => "name",
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->notSeeInDatabase('responsibilities', $data);
    }

    public function testSaveDuplicateCode()
    {
        $this->responsibilityDao->save($this->responsibilityData);

        $this->seeInDatabase('responsibilities', $this->responsibilityData);

        $this->json('POST', '/responsibility/save', $this->transform($this->responsibilityData))
            ->seeJson([
                'status' => 422,
                'message' => trans('messages.duplicateCode')
            ]);
    }

    public function testSaveInvalidEffBegin()
    {
        $this->responsibilityData['eff_begin'] = '2018-12-01';

        $this->json('POST', '/responsibility/save', $this->transform($this->responsibilityData))
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

        $this->notSeeInDatabase('responsibilities', $this->responsibilityData);
    }

    public function testUpdate()
    {
        $data = $this->responsibilityData;

        $data['name'] = StringHelper::randomizeStr(50);
        $data['description'] = StringHelper::randomizeStr(200);

        $data['id'] = $this->responsibilityDao->save($this->responsibilityData);

        $this->seeInDatabase('responsibilities', $this->responsibilityData);

        $this->json('POST', '/responsibility/update', $this->transform($data))
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataUpdated')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);
        $this->notSeeInDatabase('responsibilities', $this->responsibilityData);

        $this->seeInDatabase('responsibilities', $data);
    }

    public function testUpdateBlankField()
    {
        $data = array(
            'company_id' => 1,
        );

        $data['id'] = $this->responsibilityDao->save($this->responsibilityData);

        $this->json('POST', '/responsibility/update', $this->transform($data))
            ->seeJson([
                "status" => 444,
                "key" => "effBegin",
                "key" => "effEnd",
                "key" => "responsibilityGroupCode",
                "key" => "code",
                "key" => "name",
                "key" => "description"
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->notSeeInDatabase('responsibilities', $data);
    }

    public function testDelete()
    {
        $data = $this->responsibilityData;

        $data['id'] = $this->responsibilityDao->save($this->responsibilityData);
        $data['eff_end'] = Carbon::now();

        $this->json('POST', '/responsibility/delete', $this->transform($data))
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataDeleted')
            ])
            ->seeJsonStructure([
                "data" => []
            ]);

        $this->seeInDatabase('responsibilities', $data);
    }

    public function tearDown()
    {
        DB::rollback();
    }
}
