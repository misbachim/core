<?php
namespace App\Business\Dao;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Business\Model\Requester;
use App\Business\Dao\Notification\UserNotificationDao;

class LogDao
{
	private static function insertArrLogImpact($logId,$table,$arr,$ignored_columns,$data) {
		$temp = $data;
		foreach($arr as $key => $value) {

			if(is_array($value)) {
				$temp = LogDao::insertArrLogImpact($logId,$table,$value,$ignored_columns,$temp);
			}
			else {

				//Get data only if value is not empty and key is not in the ignored columns.
				if( $value && (!in_array($key, $ignored_columns)) ) {

					$log_impact = [
						'log_id'=>$logId
					  , 'table'=>$table
					  , 'column'=>$key
					  , 'value'=>$value
					];

					array_push($temp,$log_impact);
				}

			}
		}

		return $temp;
	}



	public function __construct(Requester $requester)
	{
		$this->requester = $requester;
	}



    /**
     * Get logs filtered by parameters.
     *
     * @param array $allUsersId
     * @param int $userId
     * @param date $startDate
     * @param date $endDate
     * @return insert
     */
	public function getAll($allUserIds, $userId, $startDate, $endDate)
	{
		$temp = DB::connection('log')->table('logs')
		    ->join('apis', 'logs.api_id', '=', 'apis.id')
		    ->leftJoin('log_impacts', 'log_impacts.log_id', '=', 'logs.id')
		    ->select(
		        'logs.id as id'
		      , 'logs.user_id as userId'
		      , 'logs.created_at as time'
		      , 'apis.description as description'
		      , 'log_impacts.column as columnImpact'
		      , 'log_impacts.value as valueImpact'
		    )
		    ->where('logs.tenant_id',$this->requester->getTenantId())
		    ->where('logs.company_id',$this->requester->getCompanyId())
		    ->whereIn('logs.user_id',$allUserIds)
		;

		if($userId) {
			$temp = $temp->where('logs.user_id',$userId);
		}
		if($startDate) {
			$temp = $temp->where('logs.created_at','>=',$startDate);
		}
		if($endDate) {
			$temp = $temp->where('logs.created_at','<=',$endDate);
		}

		return $temp->orderBy('logs.created_at','logs.user_id')->get();
	}



    /**
     * Insert impacted values into logs.
     *
     * @param bigInt $logId
     * @param string $table
     * @param array $arr
     * @return insert
     */
	public static function insertLog(Requester $requester, int $userId, string $service, $routePrefix, string $routeAction)
	{
		$api = DB::connection('log')->table('apis')->select('id')->where([
		    ['service', $service]
		  , ['route_prefix', $routePrefix]
		  , ['route_action', $routeAction]
		])->first();

		if($api) {
			$temp = [
			    'created_at'=>Carbon::now()
			  , 'user_id'=>$userId
			  , 'api_id'=>$api->id
			  , 'tenant_id'=>$requester->getTenantId()
			  , 'company_id'=>$requester->getCompanyId()
			];

			return DB::connection('log')->table('logs')->insertGetId($temp);
		}
		else {
			return null;
		}
	}



    /**
     * Insert into logs impacted values.
     *
     * @param bigInt $logId
     * @param string $table
     * @param array $arr
     * @return insert
     */
	public static function insertLogImpact($logId, string $table, array $arr) {
		//Get list of columns which must be ignored.
		$ignored_columns =
			array_column(DB::connection('log')->table('ignored_columns')->select('column')->get()->toArray()
			           , 'column'
			);

		//Get data to insert.
		$data = LogDao::insertArrLogImpact($logId,$table,$arr,$ignored_columns,[]);

		//Insert data.
		return DB::connection('log')->table('log_impacts')->insert($data);
	}



	public function syncApis()
	{
		$umApis  = DB::table('apis')->get();
		$logApis = collect(DB::connection('log')->table('apis')->get());

		$data = [];
		$n=1;
		foreach($umApis as $umApi) {
			$logApi = $logApis->where('id',$umApi->id);

			if(count($logApi) == 0) {
				$temp = [
				    'id'=> $n++
				  , 'service'=>$umApi->service
				  , 'route_prefix'=>$umApi->route_prefix
				  , 'route_action'=>$umApi->route_action
				  , 'description'=> ''
				  , 'created_by'=>$this->requester->getUserId()
				  , 'created_at'=>Carbon::now()
				];
				array_push($data, $temp);
			}
		}
		if(count($data) == 0) {
			return null;
		}
		else {
			return DB::connection('log')->table('apis')->insert($data);
		}
	}



}
?>
