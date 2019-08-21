<?php

use App\Business\Dao\EmployeeStatusDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

/**
 * @property EmployeeStatusDao employeeTypeDao
 * @property array employeeTypes
 * @property array employeeTypesT
 */
class EmployeeTypeTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->employeeTypeDao = new EmployeeStatusDao($this->getRequester());

        $employeeType = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'working_month' => 1
        ];

        $this->employeeTypes = [];
        $this->employeeTypesT = [];
        foreach (range(1, 10) as $i) {
            $employeeType['code'] = StringHelper::randomizeStr(20);
            $employeeType['name'] = StringHelper::randomizeStr(50);

            $this->employeeTypeDao->save($employeeType);
            $this->seeInDatabase('employee_types', $employeeType);
            array_push($this->employeeTypes, $employeeType);

            $employeeTypeT = $this->transform($employeeType);
            array_push($this->employeeTypesT, $employeeTypeT);
        }
    }

    public function testGetAll()
    {
        $this->employeeTypesT = $this->exclude($this->employeeTypesT, [
            'tenantId',
            'companyId'
        ]);

        $this->json('POST', '/employeeType/getAll', [
            'companyId' => $this->getRequester()->getCompanyId()
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->employeeTypesT as $employeeTypeT) {
            foreach ($employeeTypeT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetLov()
    {
        $this->json('POST', '/employeeType/lov', [
            'companyId' => $this->getRequester()->getCompanyId()
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->employeeTypesT as $employeeTypeT) {
            $this->seeJson(['code' => $employeeTypeT['code']]);
        }
    }

    public function testGetOne()
    {
        $this->employeeTypesT = $this->exclude($this->employeeTypesT, [
            'tenantId',
            'companyId'
        ]);

        $this->json('POST', '/employeeType/getOne', [
            'code' => $this->employeeTypes[0]['code'],
            'companyId' => $this->employeeTypes[0]['company_id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ]);

        foreach ($this->employeeTypesT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $employeeType = $this->newEmployeeType();
        $employeeTypeT = $this->transform($employeeType);

        $this->json('POST', '/employeeType/save', $employeeTypeT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataSaved'),
                'data' => []
            ]);

        $this->seeInDatabase('employee_types', $employeeType);
        DB::table('employee_types')->where('code', $employeeType['code'])->delete();
        $this->notSeeInDatabase('employee_types', $employeeType);
    }

    public function testSaveFloatCompanyId()
    {
        $employeeType = $this->newEmployeeType();
        $employeeType['company_id'] = 1900000000.1;
        $employeeTypeT = $this->transform($employeeType);

        $this->json('POST', '/employeeType/save', $employeeTypeT)
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

        unset($employeeType['company_id']);
        $this->notSeeInDatabase('employee_types', $employeeType);
    }

    public function testSaveDuplicateCode()
    {
        $employeeType = $this->employeeTypes[0];
        $employeeType['eff_begin'] = '2017-10-2';
        $employeeTypeT = $this->transform($employeeType);

        $this->json('POST', '/employeeType/save', $employeeTypeT)
            ->seeJson([
                'status' => 422,
                'message' => trans('messages.duplicateCode')
            ]);

        $this->notSeeInDatabase('employee_types', $employeeType);
    }

    public function testSaveCodeWithSpaces()
    {
        $employeeType = $this->employeeTypes[0];
        $employeeType['code'] = StringHelper::randomizeStr(3).' ';
        $employeeTypeT = $this->transform($employeeType);

        $this->json('POST', '/employeeType/save', $employeeTypeT)
            ->seeJson([
                'status' => 444
            ])->seeJson([
                'data' => [
                    [
                        'key' => 'code',
                        'message' => ['The code may only contain letters and numbers. No whitespace allowed.']
                    ]
                ]
            ]);

        $this->notSeeInDatabase('employee_types', $employeeType);
    }

    public function testSaveInvalidEffBegin()
    {
        $employeeType = $this->newEmployeeType();
        $employeeType['eff_begin'] = '2018-12-01';
        $employeeTypeT = $this->transform($employeeType);

        $this->json('POST', '/employeeType/save', $employeeTypeT)
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

        $this->notSeeInDatabase('employee_types', $employeeType);
    }

    public function testUpdate()
    {
        $employeeType = $this->employeeTypes[0];
        $employeeType['name'] = StringHelper::randomizeStr(50);
        $employeeTypeT = $this->transform($employeeType);

        $this->json('POST', '/employeeType/update', $employeeTypeT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        $this->seeInDatabase('employee_types', $employeeType);
        DB::table('employee_types')->where('code', $employeeType['code'])->delete();
        $this->notSeeInDatabase('employee_types', $employeeType);
    }

    public function tearDown()
    {
        foreach ($this->employeeTypes as $employeeType) {
            DB::table('employee_types')->where('code', $employeeType['code'])->delete();
            $this->notSeeInDatabase('employee_types', $employeeType);
        }
    }

    public function newEmployeeType()
    {
        $employeeType = $this->employeeTypes[0];
        $employeeType['code'] = StringHelper::randomizeStr(20);
        $employeeType['name'] = StringHelper::randomizeStr(50);

        return $employeeType;
    }
}
