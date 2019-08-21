<?php

namespace App\Http\Controllers;

use App\Business\Dao\ReportTemplatesDao;
use App\Business\Model\AppResponse;
use App\Business\Model\Requester;
// use App\Exceptions\AppException; not used yet
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class for handling rating_scale process
 */
class ReportTemplatesController extends Controller
{
    public function __construct(
        Requester $requester,
        ReportTemplatesDao $reportTemplatesDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->reportTemplatesDao = $reportTemplatesDao;
    }

    /**
     * Get all report template by category
     * @param request
     */
    public function getAllByCategory(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "category" => "required|string"
        ]);

        $getReportTemplates = $this->reportTemplatesDao->getAllByCategory(
            $request->category,
            $this->requester->getCompanyId(),
            $this->requester->getTenantId()
        );

        // if there is no custom template
        // then use default one
        if (count($getReportTemplates) == 0) {
            $getReportTemplates = $this->reportTemplatesDao->getAllByCategory(
                $request->category,
                '0',
                '0'
            );
        }

        $resp = new AppResponse($getReportTemplates, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one report template by id
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "companyId" => "required|integer",
            "id" => "required|integer"
        ]);

        $getReportTemplate = $this->reportTemplatesDao->getOne(
            $request->id
        );

        $resp = new AppResponse($getReportTemplate, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }
}
