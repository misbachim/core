<?php
class WorkflowTest extends TestCase{
    use Testable;

    public function testGetAll(){
        $data = array(
            'companyId' => 1900000000

        );
        $this->json('POST', '/workflow/getAll', $data, $this->getReqHeaders())
            ->seeJson([
                "status" => 200,
                "message" => "All data retrieved"
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        "lovWfty",
                        "isActive",
                        "valData"
                    ]
                ]
            ]);
    }
}
