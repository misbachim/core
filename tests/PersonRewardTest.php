<?php

use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonRewardDao;
use App\Business\Dao\RewardDao;
use App\Business\Helper\StringHelper;
use Illuminate\Support\Facades\DB;

/**
 * @property PersonRewardDao personRewardDao
 * @property array personRewards
 * @property array personRewardsT
 * @property PersonDao personDao
 * @property array person
 * @property RewardDao rewardDao
 * @property array reward
 */
class PersonRewardTest extends TestCase
{
    use Testable;

    public function setUp()
    {
        parent::setUp();
        $this->personDao = new PersonDao($this->getRequester());
        $this->rewardDao = new RewardDao($this->getRequester());
        $this->personRewardDao = new PersonRewardDao($this->getRequester());

        $this->person = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'id_card' => StringHelper::randomizeStr(10),
            'eff_begin' => '2017-10-1',
            'eff_end' => '2018-10-01',
            'first_name' => StringHelper::randomizeStr(8),
            'birth_date' => '1990-01-01',
            'country_id' => 1,
            'lov_ptyp' => 'APP',
            'lov_gndr' => 'F',
            'lov_rlgn' => 'MOSLEM',
            'lov_mars' => 'SINGLE'
        ];
        $this->person['id'] = $this->personDao->save($this->person);
        $this->seeInDatabase('persons', $this->person);

        $this->reward = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'code' => StringHelper::randomizeStr(20),
            'name' => StringHelper::randomizeStr(50),
            'description' => StringHelper::randomizeStr(255),
            'lov_rwty' => 'P'
        ];
        $this->reward['id'] = $this->rewardDao->save($this->reward);
        $this->seeInDatabase('rewards', $this->reward);

        $personReward = [
            'tenant_id' => $this->getRequester()->getTenantId(),
            'company_id' => $this->getRequester()->getCompanyId(),
            'person_id' => $this->person['id'],
            'eff_begin' => '2017-10-01',
            'eff_end' => '2018-10-01',
            'reward_code' => $this->reward['code']
        ];

        $this->personRewards = [];
        $this->personRewardsT = [];
        foreach (range(1, 10) as $i) {
            $personReward['description'] = StringHelper::randomizeStr(255);

            $personReward['id'] = $this->personRewardDao->save($personReward);
            $this->seeInDatabase('person_rewards', $personReward);
            array_push($this->personRewards, $personReward);

            $personRewardT = $this->transform($personReward);
            array_push($this->personRewardsT, $personRewardT);

            unset($personReward['id']);
        }
    }

    public function testGetAll()
    {
        $this->personRewardsT = $this->exclude($this->personRewardsT, [
            'tenantId',
            'companyId',
            'personId',
            'rewardCode'
        ]);
        $this->personRewardsT = $this->include($this->personRewardsT, [
            'name' => $this->reward['name'],
            'type' => 'PUNISHMENT'
        ]);

        $this->json('POST', '/personReward/getAll', [
            'companyId' => $this->getRequester()->getCompanyId(),
            'personId' => $this->person['id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.allDataRetrieved')
        ]);

        foreach ($this->personRewardsT as $personRewardT) {
            foreach ($personRewardT as $field => $val) {
                $this->seeJson([$field => $val]);
            }
        }
    }

    public function testGetOne()
    {
        $this->personRewardsT = $this->exclude($this->personRewardsT, [
            'tenantId',
            'companyId',
            'personId'
        ]);
        $this->personRewardsT = $this->include($this->personRewardsT, [
            'rewardName' => $this->reward['name'],
            'lovRwty' => $this->reward['lov_rwty']
        ]);

        $this->json('POST', '/personReward/getOne', [
            'id' => $this->personRewards[0]['id'],
            'companyId' => $this->personRewards[0]['company_id'],
            'personId' => $this->personRewards[0]['person_id']
        ])->seeJson([
            'status' => 200,
            'message' => trans('messages.dataRetrieved')
        ]);

        foreach ($this->personRewardsT[0] as $field => $val) {
            $this->seeJson([$field => $val]);
        }
    }

    public function testSave()
    {
        $personReward = $this->newPersonReward();
        $personRewardT = $this->transform($personReward);

        $this->json('POST', '/personReward/save', $personRewardT)
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
        $personReward['id'] = $data->id;
        $this->seeInDatabase('person_rewards', $personReward);
        DB::table('person_rewards')->where('id', $personReward['id'])->delete();
        $this->notSeeInDatabase('person_rewards', $personReward);
    }

    public function testSaveFloatCompanyId()
    {
        $personReward = $this->newPersonReward();
        $personReward['company_id'] = 1900000000.1;
        $personRewardT = $this->transform($personReward);

        $this->json('POST', '/personReward/save', $personRewardT)
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

        unset($personReward['company_id']);
        $this->notSeeInDatabase('person_rewards', $personReward);
    }

    public function testSaveInvalidEffBegin()
    {
        $personReward = $this->newPersonReward();
        $personReward['eff_begin'] = '2018-12-01';
        $personRewardT = $this->transform($personReward);

        $this->json('POST', '/personReward/save', $personRewardT)
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

        $this->notSeeInDatabase('person_rewards', $personReward);
    }

    public function testUpdate()
    {
        $personReward = $this->personRewards[0];
        $personReward['description'] = StringHelper::randomizeStr(255);
        $personRewardT = $this->transform($personReward);

        $this->json('POST', '/personReward/update', $personRewardT)
            ->seeJson([
                'status' => 200,
                'message' => trans('messages.dataUpdated')
            ]);

        $this->seeInDatabase('person_rewards', $personReward);
        DB::table('person_rewards')->where('id', $personReward['id'])->delete();
        $this->notSeeInDatabase('person_rewards', $personReward);
    }

    public function tearDown()
    {
        foreach ($this->personRewards as $personReward) {
            DB::table('person_rewards')->where('id', $personReward['id'])->delete();
            $this->notSeeInDatabase('person_rewards', $personReward);
        }
        DB::table('rewards')->where('id', $this->reward['id'])->delete();
        $this->notSeeInDatabase('rewards', $this->reward);

        DB::table('persons')->where('id', $this->person['id'])->delete();
        $this->notSeeInDatabase('persons', $this->person);
    }

    public function newPersonReward()
    {
        $personReward = $this->personRewards[0];
        $personReward['description'] = StringHelper::randomizeStr(255);
        unset($personReward['id']);

        return $personReward;
    }
}
