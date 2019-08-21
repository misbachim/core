<?php

namespace App\Http\Controllers;

use App\Business\Dao\NumberFormatsDocumentDao;
use App\Http\Controllers\EmployeeIdController;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Class for handling assignment process
 * @property Requester requester
 * @property EmployeeIdDao employeeIdDao
 */
class NumberFormatsDocumentController extends Controller
{
    public function __construct(
        Requester $requester,
        NumberFormatsDocumentDao $numberFormatsDocumentDao,
        EmployeeIdController $employeeIdController
    )
    {
        parent::__construct();

        $this->requester = $requester;
        $this->numberFormatsDocumentDao = $numberFormatsDocumentDao;
        $this->employeeIdController = $employeeIdController;
    }

    public function getAssignmentDocument(Request $request) {
        $data = $this->employeeIdController->getEmployeeId($request);
        \Log::info(print_r($data, true));
        return $data;
    }
}