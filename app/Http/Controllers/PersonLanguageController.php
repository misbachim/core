<?php

namespace App\Http\Controllers;

use App\Business\Dao\PersonLanguageDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class for handling personLanguage process
 * @property Requester requester
 * @property PersonLanguageDao personLanguageDao
 */
class PersonLanguageController extends Controller
{
    public function __construct(Requester $requester, PersonLanguageDao $personLanguageDao)
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personLanguageDao = $personLanguageDao;
    }

    /**
     * Get all personLanguages for one person
     * @param Request $request
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $this->validate($request, ["personId" => "required"]);

        $personLanguages = $this->personLanguageDao->getAll($request->personId);

        $resp = new AppResponse($personLanguages, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one personLanguage based on personLanguage id
     * @param Request $request
     * @return AppResponse
     */
    public function getOne(Request $request)
    {
        $this->validate($request, [
            "personId" => "required",
            "id" => "required"
        ]);

        $personLanguage = $this->personLanguageDao->getOne(
            $request->personId,
            $request->id
        );

        $resp = new AppResponse($personLanguage, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save personLanguage to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkPersonLanguageRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $personLanguage = $this->constructPersonLanguage($request);
            $data['id'] = $this->personLanguageDao->save($personLanguage);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update personLanguage to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, ['id' => 'required']);
        $this->checkPersonLanguageRequest($request);

        DB::transaction(function () use (&$request) {
            $personLanguage = $this->constructPersonLanguage($request);
            $this->personLanguageDao->update(
                $request->personId,
                $request->id,
                $personLanguage
            );
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Delete person language by id.
     * @param Request $request
     * @return AppResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required",
            "personId" => "required"
        ]);

        DB::transaction(function () use (&$request) {
            $this->personLanguageDao->delete(
                $request->personId,
                $request->id
            );
        });

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate save/update personLanguage request.
     * @param Request $request
     */
    private function checkPersonLanguageRequest(Request $request)
    {
        $this->validate($request, [
            'personId' => 'required|integer|exists:persons,id',
            'lovLang' => 'required|max:10|exists:lovs,key_data',
            'writing' => 'required|integer|min:0|max:100',
            'speaking' => 'required|integer|min:0|max:100',
            'listening' => 'required|integer|min:0|max:100',
            'isNative' => 'required|boolean'
        ]);
    }

    /**
     * Construct a personLanguage object (array).
     * @param Request $request
     * @return array
     */
    private function constructPersonLanguage(Request $request)
    {
        $personLanguage = [
            "tenant_id" => $this->requester->getTenantId(),
            "person_id" => $request->personId,
            "lov_lang" => $request->lovLang,
            "writing" => $request->writing,
            "speaking" => $request->speaking,
            "listening" => $request->listening,
            "is_native" => $request->isNative
        ];
        return $personLanguage;
    }
}
