<?php

class SettingLovTest extends SeededTestCase
{
    public static function setUpBeforeClass()
    {
        self::read('setting_lovs', false);
        parent::setUpBeforeClass();
    }

    public function testGetAll()
    {
        $settingLovs = $this->prepareRecords('setting_lovs')
            ->where(function ($settingLov) {
                return $settingLov['setting_type_code'] === 'WFES';
            })
            ->ok();

        $objects = [];

        foreach ($settingLovs as $settingLov) {
            array_push($objects, [
                'keyData' => $settingLov['key_data'],
                'valData' => $settingLov['val_data']
            ]);
        }

        $this->expectManyObjectsSuccess('/settingLov/getAll', [
            'typeCode' => 'WFES' 
        ], $objects, trans('messages.allDataRetrieved'));
    }
}
