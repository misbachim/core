<?php

namespace App\Http\Controllers;

use App\Business\Dao\EmployeeIdDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Class for handling assignment process
 * @property Requester requester
 * @property EmployeeIdDao employeeIdDao
 */
class EmployeeIdController extends Controller
{
    public function __construct(
        Requester $requester,
        EmployeeIdDao $employeeIdDao
    )
    {
        parent::__construct();

        $this->requester = $requester;
        $this->employeeIdDao = $employeeIdDao;
    }

    public function getEmployeeId(Request $request)
    {
        $this->validate($request, [
            "employeeTypeCode" => "max:20",
            "lovNbft" => "required|max:20"
        ]);

        if (is_null($request->employeeTypeCode)) {
            $data = $this->employeeIdDao->getEmployeeIdNullEmployeeStatus($request->lovNbft);
        } else {
            $data = $this->employeeIdDao->getEmployeeId(
                $request->employeeTypeCode, $request->lovNbft
            );
        }

        $changeN = null;

        if(count($data) > 0){
            $format = $data->format;
            $startingSequence = $data->startingSequence;
            $lastSequence = $data->lastSequence;

            $date = Carbon::now();

            $year = $date->year;

            $changeYear = str_replace("{{YYYY}}", "$year", $format);
            $yearTwoCharacters = substr($date, 2, 2);
            $changeYearTwoCharacters = str_replace("{{YY}}", "$yearTwoCharacters", $changeYear);

            $month = sprintf("%02d", $date->month);

            $changeMonth = str_replace("{{MM}}", "$month", $changeYearTwoCharacters);
            $day = sprintf("%02d", $date->day);
            $changeDay = str_replace("{{DD}}", "$day", $changeMonth);

            if($startingSequence > $lastSequence) {
                $numberSequence = $startingSequence;
                $changeN = str_replace("{{N*}}", "$startingSequence", $changeDay);
            } else {
                $numberSequence = $lastSequence + 1;
                $changeN = str_replace("{{N*}}", "$numberSequence", $changeDay);
            }

            $data->numberFormat = $changeN;
            $data->numberSequence = $numberSequence;
        }

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

}
