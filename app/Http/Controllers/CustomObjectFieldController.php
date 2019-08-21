<?php

namespace App\Http\Controllers;

use App\Business\Dao\CustomObjectFieldDao;
use App\Business\Model\AppResponse;
use App\Business\Model\Requester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * @property Requester requester
 * @property CustomObjectFieldDao customObjectFieldDao
 */
class CustomObjectFieldController extends Controller
{
    public function __construct(
        Requester $requester,
        CustomObjectFieldDao $customObjectFieldDao
    ) {
        $this->requester = $requester;
        $this->customObjectFieldDao = $customObjectFieldDao;
    }

    public function getAllField(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required'
        ]);

        $customObjectFields = $this->customObjectFieldDao->getAllField();

        return $this->renderResponse(new AppResponse($customObjectFields, trans('messages.allDataRetrieved')));
    }
}
