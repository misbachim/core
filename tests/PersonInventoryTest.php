<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;


class PersonInventoryTest extends TestCase
{
    use Testable;

    /**
     * Test getAll endpoint.
     *
     * @return void
     */

    public function testGetAll()
    {

        $this->json('POST', '/personInventory/getAll', [
            'companyId'=>$this->getRequester()->getCompanyId(),
            'personId'=>3
        ])
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.allDataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    [
                        'inventoryCode',
                        'dateGet',
                        'dateReturn',
                        'isLost',
                        'fileGetReceipt',
                        'fileReturnReceipt'
                    ]
                ]
            ]);
    }

    public function testGetOne()
    {
        $this->json('POST', '/personInventory/getOne', [
            'companyId'=>$this->getRequester()->getCompanyId(),
            'personId'=>3,
            'id'=>3
        ])
            ->seeJson([
                "status" => 200,
                "message" => trans('messages.dataRetrieved')
            ])
            ->seeJsonStructure([
                "data" => [
                    'inventoryCode',
                    'dateGet',
                    'dateReturn',
                    'isLost',
                    'fileGetReceipt',
                    'fileReturnReceipt'
                ]
            ]);

    }

           public function testSaveWithoutPic()
            {
                DB::beginTransaction();
                $data = array(
                    'data'=> json_encode([
                        'companyId' => $this->getRequester()->getCompanyId(),
                        'personId' => 3,
                        'inventoryCode' => 'COD',
                        'dateGet' => '2017-11-21',
                        'dateReturn' => '2019-11-21',
                        'isLost'=>false
                   ]),
                    'upload'=>0
                );
                $this->json('POST', '/personInventory/save', $data, $this->getReqHeaders())
                    ->seeJson([
                        "message"=>trans("messages.dataSaved"),
                        "status"=>200
                    ])
                    ->seeJsonStructure([
                        "data" => [
                            "id"
                        ]
                    ]);

//                $this->seeInDatabase('person_inventories', ['code'=>'FRF','name'=>'FIREFOX'] );

                DB::rollback();

            }



}
