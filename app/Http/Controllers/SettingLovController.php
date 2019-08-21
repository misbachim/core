<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Business\Dao\SettingLovDao;
use App\Business\Model\AppResponse;


class SettingLovController extends Controller

{
    public function __construct(SettingLovDao $settingLovDao)
    {
        parent::__construct();

        $this->settingLovDao = $settingLovDao;
    }

    public function getAll(Request $request)
    {
        $this->validate($request, [
            'typeCode' => 'required|string|exists:setting_types,code'
        ]);

        $settingLovs = $this->settingLovDao->getAll($request->typeCode);

        return $this->renderResponse(new AppResponse($settingLovs, trans('messages.allDataRetrieved')));
    }
}
