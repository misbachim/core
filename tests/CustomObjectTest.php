<?php

use App\Business\Dao\CustomObjectDao;
use App\Business\Dao\CustomObjectFieldDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

/**
 * @property CustomObjectDao customObjectDao
 * @property CustomObjectFieldDao customObjectFieldDao
 * @property array customObjects
 * @property array customObjectsT
 * @property array customObjectFields
 * @property array customObjectFieldsT
 */
class CustomObjectTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();

        $this->customObjectDao = new CustomObjectDao($this->getRequester());
        $this->customObjectFieldDao = new CustomObjectFieldDao($this->getRequester());

        $customObject = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'is_disabled' => false
        ];

        $this->customObjects = [];
        $this->customObjectsT = [];
        for ($i = 0; $i < 5; $i++) {
            $customObject['lov_cusmod'] = StringHelper::randomizeStr(20);
            $customObject['name'] = StringHelper::randomizeStr(50);
            $customObject['description'] = StringHelper::randomizeStr(255);

            $customObject['id'] = $this->customObjectDao->save($customObject);
            $this->seeInDatabase('co', $customObject);
            array_push($this->customObjects, $customObject);

            $customObjectT = $this->transform($customObject);
            array_push($this->customObjectsT, $customObjectT);

            unset($customObject['id']);
        }

        $customObjectField = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'co_id' => $this->customObjects[0]['id'],
            'lov_cdtype' => 'TXT',
            'is_disabled' => false
        ];

        $this->customObjectFields = [];
        $this->customObjectFieldsT = [];
        for ($i = 0; $i < 5; $i++) {
            $customObjectField['name'] = StringHelper::randomizeStr(50);
            $customObjectField['lov_type_code'] = StringHelper::randomizeStr(10);

            $customObjectField['id'] = DB::table('co_fields')->insertGetId($customObjectField);
            $this->seeInDatabase('co_fields', $customObjectField);
            array_push($this->customObjectFields, $customObjectField);

            $customObjectFieldT = $this->transform($customObjectField);
            array_push($this->customObjectFieldsT, $customObjectFieldT);

            unset($customObjectField['id']);
        }
    }

    public function testGetAll()
    {
        $this->customObjectsT = $this->exclude($this->customObjectsT, [
            'tenantId',
            'companyId',
            'lovCusmod'
        ]);

        $this->json('POST', '/customObject/getAll', [
            'companyId' => $this->getRequester()->getCompanyId()
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->customObjectsT as $customObjectT) {
            foreach ($customObjectT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function tearDown()
    {
        foreach ($this->customObjectFields as $customObjectField) {
            DB::table('co_fields')->where('id', $customObjectField['id'])->delete();
            $this->notSeeInDatabase('co_fields', $customObjectField);
        }
        foreach ($this->customObjects as $customObject) {
            DB::table('co')->where('id', $customObject['id'])->delete();
            $this->notSeeInDatabase('co', $customObject);
        }
    }
}
