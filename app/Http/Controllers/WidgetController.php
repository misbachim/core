<?php

namespace App\Http\Controllers;

use App\Business\Dao\WidgetDao;
use App\Business\Model\AppResponse;
use App\Business\Model\Requester;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions;
use Illuminate\Support\Facades\DB, Log;

/**
 * Class for handling Widget
 *
 * @property App\Business\Dao\WidgetDao   $widgetDao *
 * @property App\Business\Model\Requester $requester *
 * @package  App\Http\Controllers
 */
class WidgetController extends Controller
{
    /**
     * WidgetController constructor
     *
     * @param App\Business\Dao\WidgetDao   $widgetDao Menu Dao
     * @param App\Business\Model\Requester $requester API requester information
     *
     * @return void
     */
    public function __construct(WidgetDao $widgetDao, Requester $requester)
    {
        parent::__construct();

        $this->widgetDao = $widgetDao;
        $this->requester = $requester;
    }

    /**
     * Get all Widget
     *
     * @param \Illuminate\Http\Request $request API requester information
     *
     * @return App\Business\Model\AppResponse $widget    Return data
     */
    public function getAll(Request $request)
    {
        /**
         * Validate request
         */
        $this->validate($request, [
            'companyId' => 'required|numeric',
            'applicationId' => 'required|numeric'
        ]);

        $userWidgets = $this->widgetDao->getUserWidgets();

        if ($userWidgets === null) {
            throw new AppException(trans('messages.dataNotFound'));
        }

        foreach ($userWidgets as $key => $container) {
            $widgets = $this->widgetDao->getAll($container->widgetId);

            $newWidgets = [];
            foreach ($widgets as $widget) {
                array_push($newWidgets, [
                    'id' => $widget->id,
                    'name' => $widget->name,
                    'description' => $widget->description,
                    'appCode' => $this->setAppCode(
                        $this->requester->getAppId()
                    ),
                    'widgetType' => $this->widgetDao->getWidgetType(
                        $widget->widgetTypeId
                    ),
                    'paramIn' => $widget->paramIn,
                    'paramOut' => $widget->paramOut
                ]);
            }

            $container->widget = (object) $newWidgets[0];
        }

        return $this->renderResponse(
            new AppResponse($userWidgets, trans('messages.allDataRetrieved'))
        );
    }

    /**
     * Get all Widget Type
     *
     * @param \Illuminate\Http\Request $request API requester information
     *
     * @return App\Business\Model\AppResponse $widget    Return data
     */
    public function getAllWidgetType(Request $request)
    {
        /**
         * Validate request
         */
        $this->validate($request, [
            'companyId' => 'required|numeric',
            'applicationId' => 'required|numeric'
        ]);

        $widgetTypes = $this->widgetDao->getAllWidgetType();

        if ($widgetTypes === null) {
            throw new AppException(trans('messages.dataNotFound'));
        }

        return $this->renderResponse(
            new AppResponse($widgetTypes, trans('messages.allDataRetrieved'))
        );
    }

    /**
     * Get one Widget by id
     *
     * @param \Illuminate\Http\Request $request Get data from body
     *
     * @return App\Business\Model\AppResponse Return data
     */
    public function getOne(Request $request)
    {
        /**
         * Validate request
         */
        $this->validate($request, [
            "id" => "required"
        ]);

        $widget = $this->widgetDao->getOne($request->id);

        if (count($widget) > 0) {
            $widget->widgetType = $this->widgetDao->getWidgetType(
                $widget->widgetTypeId
            );
        }

        $resp = new AppResponse($widget, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one Widget Type by name
     *
     * @param \Illuminate\Http\Request $request Get data from body
     *
     * @return App\Business\Model\AppResponse Return data
     */
    public function getOneWidgetType(Request $request)
    {
        /**
         * Validate request
         */
        $this->validate($request, [
            "name" => "required"
        ]);

        $widgetType = $this->widgetDao->getOneWidgetType($request->name);

        $resp = new AppResponse($widgetType, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Store widget containers per User id
     *
     * @param \Illuminate\Http\Request $request Get data from body
     *
     * @return App\Business\Model\AppResponse Return data
     */
    public function save(Request $request)
    {
        $data = [];
        $this->checkWidgetRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            // Delete old Data
            $widgets = $this->widgetDao->getUserWidgets();
            if (isset($widgets)) {
                foreach ($widgets as $widget) {
                    $this->widgetDao->deleteWidget($widget->widgetId);
                }
                $this->widgetDao->deleteUserWidget();
            }

            // Insert new Data
            foreach ($request->containers as $container) {
                $widget = $this->constructWidget($container);
                $userWidget = $this->constructUserWidget($container);

                $data['id'] = $this->widgetDao->save($widget);
                if ($data['id']) {
                    $this->widgetDao->saveUserWidget($userWidget, $data['id']);
                }
            }
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    private function setAppCode($appId)
    {
        switch ($appId) {
            case 1:
                return 'ADMIN';
                break;
            case 2: // TODO: Change later, ESS Desktop and Mobile using this Id
                return 'ESS';
                break;
            case 3: // TODO: Change later
                return 'MOBILE';
                break;
            default:
                return 'WEB';
                break;
        }
    }

    private function checkWidgetRequest(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer',
            'containers' => 'array'
        ]);
    }

    private function constructWidget($container)
    {
        if ($container['widget']) {
            $obj = [
                "id" => $container['widget']['id'],
                "name" => $container['widget']['name'],
                "description" => $container['widget']['description'],
                "app_code" => $container['widget']['appCode'],
                "widget_type_id" => $container['widget']['widgetType']['id'],
                "param_in" => json_encode($container['widget']['paramIn']),
                "param_out" => json_encode($container['widget']['paramOut'])
            ];
            return $obj;
        }
    }

    private function constructUserWidget($container)
    {
        $obj = [
            "id" => $container['id'],
            "x_position" => $container['x'],
            "y_position" => $container['y'],
            "widget_number" => $container['widgetNumber']
        ];

        return $obj;
    }
}
