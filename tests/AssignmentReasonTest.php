<?php

use App\Business\Dao\AssignmentReasonDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

/**
 * @property AssignmentReasonDao assignmentReasonDao
 * @property array assignmentReasons
 * @property array assignmentReasonsT
 */
class AssignmentReasonTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->assignmentReasonDao = new AssignmentReasonDao($this->getRequester());

        $assignmentReason = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'lov_acty' => 'HIRE'
        ];

        $this->assignmentReasons = [];
        $this->assignmentReasonsT = [];
        foreach (range(1, 10) as $i) {
            $assignmentReason['code'] = StringHelper::randomizeStr(20);
            $assignmentReason['description'] = StringHelper::randomizeStr(255);

            $assignmentReason['id'] = $this->assignmentReasonDao->save($assignmentReason);
            $this->seeInDatabase('assignment_reasons', $assignmentReason);
            array_push($this->assignmentReasons, $assignmentReason);

            $assignmentReasonT = $this->transform($assignmentReason);
            array_push($this->assignmentReasonsT, $assignmentReasonT);

            unset($assignmentReason['id']);
        }
    }

    public function testGetAll()
    {
        $this->assignmentReasonsT = $this->exclude($this->assignmentReasonsT, [
            'tenantId',
            'companyId',
            'lovActy'
        ]);
        $this->assignmentReasonsT = $this->include($this->assignmentReasonsT, [
            'actionType' => 'HIRE'
        ]);

        $this->json('POST', '/assignmentReason/getAll', [
            'companyId' => $this->getRequester()->getCompanyId()
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->assignmentReasonsT as $assignmentReasonT) {
            foreach ($assignmentReasonT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetLov()
    {
        $this->json('POST', '/assignmentReason/lov', [
            'companyId' => $this->getRequester()->getCompanyId()
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->assignmentReasonsT as $assignmentReasonT) {
            $this->seeJson(['code' => $assignmentReasonT['code']]);
        }
    }

    public function testGetOne()
    {
        $this->assignmentReasonsT = $this->exclude($this->assignmentReasonsT, [
            'tenantId',
            'companyId'
        ]);

        $this->json('POST', '/assignmentReason/getOne', [
            'id' => $this->assignmentReasons[0]['id'],
            'companyId' => $this->assignmentReasons[0]['company_id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ]);

        foreach ($this->assignmentReasonsT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $assignmentReason = $this->newAssignmentReason();
        $assignmentReasonT = $this->transform($assignmentReason);

        $this->json('POST', '/assignmentReason/save', $assignmentReasonT)
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
        $assignmentReason['id'] = $data->id;
        $this->seeInDatabase('assignment_reasons', $assignmentReason);
        DB::table('assignment_reasons')->where('id', $assignmentReason['id'])->delete();
        $this->notSeeInDatabase('assignment_reasons', $assignmentReason);
    }

    public function testSaveFloatCompanyId()
    {
        $assignmentReason = $this->newAssignmentReason();
        $assignmentReason['company_id'] = 1900000000.1;
        $assignmentReasonT = $this->transform($assignmentReason);

        $this->json('POST', '/assignmentReason/save', $assignmentReasonT)
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

        unset($assignmentReason['company_id']);
        $this->notSeeInDatabase('assignment_reasons', $assignmentReason);
    }

    public function testSaveDuplicateCode()
    {
        $assignmentReason = $this->assignmentReasons[0];
        $assignmentReason['eff_begin'] = '2017-10-2';
        $assignmentReasonT = $this->transform($assignmentReason);

        $this->json('POST', '/assignmentReason/save', $assignmentReasonT)
            ->seeJson([
                'status' => 422,
                'message' => trans('messages.duplicateCode')
            ]);

        $this->notSeeInDatabase('assignment_reasons', $assignmentReason);
    }

    public function testSaveInvalidEffBegin()
    {
        $assignmentReason = $this->newAssignmentReason();
        $assignmentReason['eff_begin'] = '2018-12-01';
        $assignmentReasonT = $this->transform($assignmentReason);

        $this->json('POST', '/assignmentReason/save', $assignmentReasonT)
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

        $this->notSeeInDatabase('assignment_reasons', $assignmentReason);
    }

    public function testUpdate()
    {
        $assignmentReason = $this->assignmentReasons[0];
        $oldCode = $assignmentReason['code'];
        $assignmentReason['code'] = StringHelper::randomizeStr(20);
        $assignmentReason['description'] = StringHelper::randomizeStr(255);
        $assignmentReasonT = $this->transform($assignmentReason);

        $this->json('POST', '/assignmentReason/update', $assignmentReasonT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        $this->notSeeInDatabase('assignment_reasons', $assignmentReason);
        $assignmentReason['code'] = $oldCode;
        $this->seeInDatabase('assignment_reasons', $assignmentReason);
        DB::table('assignment_reasons')->where('id', $assignmentReason['id'])->delete();
        $this->notSeeInDatabase('assignment_reasons', $assignmentReason);
    }

    public function tearDown()
    {
        foreach ($this->assignmentReasons as $assignmentReason) {
            DB::table('assignment_reasons')->where('id', $assignmentReason['id'])->delete();
            $this->notSeeInDatabase('assignment_reasons', $assignmentReason);
        }
    }

    public function newAssignmentReason()
    {
        $assignmentReason = $this->assignmentReasons[0];
        $assignmentReason['code'] = StringHelper::randomizeStr(20);
        $assignmentReason['description'] = StringHelper::randomizeStr(255);
        unset($assignmentReason['id']);

        return $assignmentReason;
    }
}
