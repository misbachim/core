<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use Illuminate\Support\Facades\DB;

/**
 * Class for model Widget
 *
 * @property App\Business\Model\Requester $requester *
 * @package  App\Business\Dao
 */
class WidgetDao
{
    /**
     * WidgetDao constructor
     *
     * @param App\Business\Model\Requester $requester API requester information
     *
     * @return void
     */
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
    }

    /**
     * Query to get All Widget data.
     *
     * @param string $widgetId Selected widget by uuid.
     *
     * @return \Illuminate\Support\Facades\DB Returns all data.
     */
    public function getAll($widgetId)
    {
        return DB::table('widget')
            ->select([
                'id',
                'name',
                'description',
                'app_code as appCode',
                'widget_type_id as widgetTypeId',
                'param_in as paramIn',
                'param_out as paramOut'
            ])
            ->where('id', '=', $widgetId)
            ->get();
    }

    /**
     * Query to get All Widget type data.
     *
     * @return \Illuminate\Support\Facades\DB Returns all data.
     */
    public function getAllWidgetType()
    {
        return DB::table('widget_type')
            ->select([
                'id',
                'name',
                'col_size as colSize',
                'row_size as rowSize'
            ])
            ->get();
    }

    /**
     * Query to get One Widget data.
     *
     * @param string $uuid Selected widget by uuid.
     *
     * @return \Illuminate\Support\Facades\DB Returns one widget.
     */
    public function getOne($uuid)
    {
        return DB::table('widget')
            ->select([
                'id',
                'name',
                'description',
                'app_code as appCode',
                'widget_type_id as widgetTypeId',
                'param_in as paramIn',
                'param_out as paramOut'
            ])
            ->where('id', '=', $uuid)
            ->first();
    }

    /**
     * Query to get One Widget type data.
     *
     * @param string $name Selected widget type by name.
     *
     * @return \Illuminate\Support\Facades\DB Returns one widget type.
     */
    public function getOneWidgetType($name)
    {
        return DB::table('widget_type')
            ->select([
                'id',
                'name',
                'col_size as colSize',
                'row_size as rowSize'
            ])
            ->where('name', '=', $name)
            ->first();
    }

    /**
     * Query to count All Row Widget data.
     *
     * @return \Illuminate\Support\Facades\DB Returns count widget.
     */
    public function getTotalRows()
    {
        return DB::table('widget')->count();
    }

    /**
     * Query to count All Row Widget data.
     *
     * @return \Illuminate\Support\Facades\DB Returns count widget.
     */
    public function getUserRoleWidget()
    {
        return DB::table('user_role_widget')
            ->select([
                'id',
                'user_role_id as userRoleId',
                'widget_id as widgetId'
            ])
            ->where('user_id', '=', $this->requester->getUserId())
            ->first();
    }

    /**
     * Query to get User Widget data.
     *
     * @return \Illuminate\Support\Facades\DB Returns User Widget.
     */
    public function getUserWidgets()
    {
        return DB::table('user_widget')
            ->select([
                'id',
                'widget_id as widgetId',
                'x_position as x',
                'y_position as y',
                'widget_number as widgetNumber'
            ])
            ->where('user_id', '=', $this->requester->getUserId())
            ->get();
    }

    /**
     * Query to get Widget Type data.
     *
     * @param string $uuid Selected Widget Type by uuid.
     *
     * @return \Illuminate\Support\Facades\DB Returns Widget Type.
     */
    public function getWidgetType($uuid)
    {
        return DB::table('widget_type')
            ->select([
                'id',
                'name',
                'col_size as colSize',
                'row_size as rowSize'
            ])
            ->where('id', '=', $uuid)
            ->first();
    }

    /**
     * Query to save User Widget data.
     *
     * @param string $obj User Widget data.
     *
     * @return \Illuminate\Support\Facades\DB Returns new Widget Id.
     */
    public function save($obj)
    {
        return DB::table('widget')->insertGetId($obj);
    }

    /**
     * Query to save User Widget data.
     *
     * @param string $obj      User Widget data.
     * @param string $widgetId Widget Id.
     *
     * @return \Illuminate\Support\Facades\DB Returns User Widget data.
     */
    public function saveUserWidget($obj, $widgetId)
    {
        $obj['user_id'] = $this->requester->getUserId();
        $obj['widget_id'] = $widgetId;

        return DB::table('user_widget')->insert($obj);
    }

    /**
     * Query to delete Widget data.
     *
     * @param string $uuid Select Widget by uuid.
     *
     * @return void
     */
    public function deleteWidget($uuid)
    {
        DB::table('widget')
            ->where('id', '=', $uuid)
            ->delete();
    }

    /**
     * Query to delete User Widget data.
     *
     * @return void
     */
    public function deleteUserWidget()
    {
        DB::table('user_widget')
            ->where('user_id', '=', $this->requester->getUserId())
            ->delete();
    }
}
