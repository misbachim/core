<?php

namespace App\Http\Controllers;

use App\Business\Dao\UM\UserDao;
use App\Business\Dao\CustomFieldDao;
use App\Business\Dao\CustomFieldPersonBasicInfoDao;
use App\Business\Dao\CustomObjectDao;
use App\Business\Dao\CustomObjectFieldDao;
use App\Business\Dao\PersonCustomObjectDao;
use App\Business\Dao\PersonCustomObjectFieldDao;
use App\Business\Dao\PersonDao;
use App\Business\Dao\PersonFamilyDao;
use App\Business\Dao\PersonSocialMediaDao;
use App\Business\Dao\AssignmentDao;
use App\Business\Dao\WorkflowDao;
use App\Business\Dao\RequestBasicInfoDao;
use App\Business\Dao\LovDao;
use App\Business\Model\PagingAppResponse;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB, Log;
use Illuminate\Support\Collection;
use App\Jobs\SyncJob;

/**
 * Class for handling person process
 * @property Requester requester
 * @property PersonDao personDao
 * @property PersonSocialMediaDao personSocialMediaDao
 * @property CustomFieldDao customFieldDao
 * @property CustomFieldPersonBasicInfoDao customFieldPersonBasicInfoDao
 * @property CustomObjectDao customObjectDao
 * @property CustomObjectFieldDao customObjectFieldDao
 * @property PersonCustomObjectDao personCustomObjectDao
 * @property PersonCustomObjectFieldDao personCustomObjectFieldDao
 * @property PersonFamilyDao personFamilyDao
 * @property AssignmentDao assignmentDao
 * @property WorkflowDao workflowDao
 * @property RequestBasicInfoDao requestBasicInfoDao
 */
class PersonController extends Controller
{
    public function __construct(
        Requester $requester,
        PersonDao $personDao,
        UserDao $userDao,
        AssignmentDao $assignmentDao,
        CustomFieldDao $customFieldDao,
        CustomFieldPersonBasicInfoDao $customFieldPersonBasicInfoDao,
        CustomObjectDao $customObjectDao,
        CustomObjectFieldDao $customObjectFieldDao,
        PersonCustomObjectDao $personCustomObjectDao,
        PersonCustomObjectFieldDao $personCustomObjectFieldDao,
        PersonFamilyDao $personFamilyDao,
        PersonSocialMediaDao $personSocialMediaDao,
        WorkflowDao $workflowDao,
        RequestBasicInfoDao $requestBasicInfoDao,
        LovDao $lovDao
    )
    {
        parent::__construct();

        $this->requester = $requester;
        $this->personDao = $personDao;

        $this->userDao = $userDao;
        $this->assignmentDao = $assignmentDao;
        $this->customFieldDao = $customFieldDao;
        $this->customFieldPersonBasicInfoDao = $customFieldPersonBasicInfoDao;
        $this->customObjectDao = $customObjectDao;
        $this->customObjectFieldDao = $customObjectFieldDao;
        $this->personCustomObjectDao = $personCustomObjectDao;
        $this->personCustomObjectFieldDao = $personCustomObjectFieldDao;
        $this->personFamilyDao = $personFamilyDao;
        $this->personSocialMediaDao = $personSocialMediaDao;
        $this->workflowDao = $workflowDao;
        $this->requestBasicInfoDao = $requestBasicInfoDao;
        $this->lovDao = $lovDao;
    }

    /**
     * Get all persons for one tenant
     * @return AppResponse
     */
    public function getAll(Request $request)
    {
        $data = $this->personDao->getAll();
        return $this->renderResponse(new AppResponse($data, trans('messages.allDataRetrieved')));
    }

    public function getAllCandidate(Request $request)
    {
        $data = $this->personDao->getAllCandidate();
        return $this->renderResponse(new AppResponse($data, trans('messages.allDataRetrieved')));
    }

    public function getAllByBirth(Request $request)
    {
        $data = $this->personDao->getAllByBirth();
        return $this->renderResponse(new AppResponse($data, trans('messages.allDataRetrieved')));
    }

    public function getAllByDateExpiring(Request $request)
    {
        $data = $this->personDao->getAllLastPrimaryAssignment($request->companyId);
        $expiring = array();
        if (count($data) > 0) {
            for ($i = 0; $i < count($data); $i++) {
                if ($data[$i]->effEnd >= Carbon::now() && $data[$i]->effEnd < Carbon::create()->addMonths(3) && $data[$i]->lovAsta === 'ACT') {
                    $effEnd = $data[$i]->effEnd;
                    $month = Carbon::parse($effEnd . ' 17:34:15.984512', 'UTC');
                    $month->format('F');
                    $data[$i]->month = $month->format('F');
                    array_push($expiring, $data[$i]);
                }
            }
        }

        return $this->renderResponse(new AppResponse($expiring, trans('messages.allDataRetrieved')));
    }

    public
    function getAllByDateExpired(Request $request)
    {
        $data = $this->personDao->getAllLastPrimaryAssignment($request->companyId);
        $expired = array();
        if (count($data) > 0) {
            for ($i = 0; $i < count($data); $i++) {
                if ($data[$i]->effEnd < Carbon::now() && $data[$i]->lovAsta === 'ACT') {
                    $effEnd = $data[$i]->effEnd;
                    $month = Carbon::parse($effEnd . ' 17:34:15.984512', 'UTC');
                    $month->format('F');
                    $data[$i]->month = $month->format('F');
                    array_push($expired, $data[$i]);
                }
            }
        }

        return $this->renderResponse(new AppResponse($expired, trans('messages.allDataRetrieved')));
    }

    public
    function getAllByPosition(Request $request)
    {
        $this->validate($request, [
            'positionCode' => 'required'
        ]);

        $persons = $this->personDao->getAllByPosition($request->positionCode);

        return $this->renderResponse(new AppResponse($persons, trans('messages.allDataRetrieved')));
    }

    public
    function getAllByUnit(Request $request)
    {
        $persons = $this->personDao->getAllByUnit($request->unitCode);

        return $this->renderResponse(new AppResponse($persons, trans('messages.allDataRetrieved')));
    }

    public
    function getAllByUnitWoHead(Request $request)
    {
        $persons = array();
        $hou = $this->personDao->getHeadOfUnit($request->unitCode);
        info('$hou', [$hou]);
        if ($hou) { //get all person except head of unit
            $persons = $this->personDao->getAllByUnitExHou($request->unitCode, $hou->id);
        } else { // if head position is currently vacant
            $persons = $this->personDao->getAllByUnit($request->unitCode);
        }

        return $this->renderResponse(new AppResponse($persons, trans('messages.allDataRetrieved')));
    }

    public
    function getAllStructureBelow(Request $request)
    {
        $this->validate($request, [
            "orgStructureId" => "required",
            "unitCode" => "required"
        ]);

        $persons = array();
        $hou = $this->personDao->getHeadOfUnit($request->unitCode);
        if ($hou) { //get all person except head of unit
            $persons = $this->personDao->getAllByUnitExHou($request->unitCode, $hou->id);
        } else { // if head position is currently vacant
            $persons = $this->personDao->getAllByUnit($request->unitCode);
        }

        $data = $this->getUnitBelow(
            $request->unitCode,
            $request->orgStructureId
        );

        $temp = null;

        while ($data) {
            //            info('m', $data);
            $len = count($data['children']);
            if ($data['children'] != []) {
                if ($data['children'][0]) {
                    if ($len > 1) {
                        $temp = $data['children'][1];
                    }
                    $data = $data['children'][0];
                }
                $children = $this->personDao->getAllByUnit($data['code']);
                $persons = array_merge($persons, $children);
            } else {
                if ($temp != null) {
                    $data = $temp;
                    $children = $this->personDao->getAllByUnit($data['code']);
                    $persons = array_merge($persons, $children);
                    $temp = null;
                } else {
                    break;
                }
            }
        }

        $resp = new AppResponse($persons, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get employee that doesn't have user
     * @param Request $request
     * @return AppResponse
     */
    public
    function getAllActiveEmployees()
    {
        $persons = $this->personDao->getAllActiveEmployees();
        return $this->renderResponse(new AppResponse($persons, trans('messages.dataRetrieved')));
    }

    public
    function getSLov(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'menuCode' => 'required'
        ]);

        // if Super Admin and has no role
        if ($this->requester->getIsUserSA() && !$this->requester->getRoleIds()) {
            $lov = $this->personDao->getLov();
        } else {
            $lov = $this->personDao->getSLov($request->menuCode);
        }

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public
    function getLov(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required'
        ]);

        //get person lov without checking any permission and role
        $lov = $this->personDao->getLov();

        $resp = new AppResponse($lov, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public
    function getHeadOfUnit(Request $request)
    {
        $this->validate($request, [
            "unitCode" => "required"
        ]);
        $person = $this->personDao->getHeadOfUnit($request->unitCode);
        if ($person !== null) {
            $person->firstAssignment = $this->assignmentDao->getFirstAssignment($person->id);
        }

        $resp = new AppResponse($person, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one person based on person id
     * @param Request $request
     * @return AppResponse
     */
    public
    function getOne(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);
        $person = $this->personDao->getOne($request->id);
        if ($person !== null) {
            $person->firstAssignment = $this->assignmentDao->getFirstAssignment($request->id);
        }

        $resp = new AppResponse($person, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get one person based on employee id
     * @param Request $request
     * @return AppResponse
     */
    public
    function getOneEmployee(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);

        if ($request->has('menuCode') && !$this->requester->getIsUserSA()) {
            $person = $this->personDao->getOneEmployeeSecure($request->id, $request->menuCode);
            if ($person !== null) {
                $person->firstAssignment = $this->assignmentDao->getFirstAssignment($person->id);
            }
        } else {
            $person = $this->personDao->getOneEmployee($request->id);
            if ($person !== null) {
                $person->firstAssignment = $this->assignmentDao->getFirstAssignment($person->id);
            }
        }

        $resp = new AppResponse($person, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public
    function getOneEmployeeForWorklist(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);
        $person = $this->personDao->getOneEmployee($request->id);
        if ($person !== null) {
            $person->firstAssignment = $this->assignmentDao->getFirstAssignment($person->id);
        }

        //$resp = new AppResponse($person, trans('messages.dataRetrieved'));
        //return $this->renderResponse($resp);
        return $person;
    }

    public
    function getMany(Request $request)
    {
        $this->validate($request, [
            "personIds" => "required",
            "byEmployeeId" => "present"
        ]);

        if ($request->byEmployeeId) {
            $persons = $this->personDao->getManyByEmployeeId($request->personIds);
        } else {
            $persons = $this->personDao->getMany($request->personIds);
        }

        if (count($persons) == 0) {
            throw new AppException(trans('messages.dataDoesNotExist'));
        }

        return $this->renderResponse(new AppResponse($persons, trans('messages.dataRetrieved')));
    }

    public
    function getBasicInfo(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);

        $person = $this->personDao->getBasicInfo($request->id, $request->companyId);
        if ($person !== null) {
            $person->socialMedias = $this->personSocialMediaDao->getAll($request->id);
            $person->emergencyContact = $this->personFamilyDao->getAllEmergency($request->id);
            $person->customField = $this->customFieldDao->getAllForModule('PBI');
        }

        $resp = new AppResponse($person, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public
    function getHistory(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);

        $person = $this->personDao->getHistory($request->id);

        $person = $person->splice(1);

        $resp = new AppResponse($person, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    public
    function search(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'menuCode' => 'required',
            'searchQuery' => 'present'
        ]);

        // search for instructor
        if ($request->has('instructorMode')) {
            if ($request->instructorMode) {
                $instructor = $this->personDao->searchInstructor($request->searchQuery);
                return $this->renderResponse(new AppResponse($instructor, trans('messages.allDataRetrieved')));
            }
        }

        // if Super Admin and has no role
        if ($this->requester->getIsUserSA() && !$this->requester->getRoleIds()) {
            $employees = $this->personDao->searchSA($request->searchQuery);
        } else {
            $employees = $this->personDao->search($request->menuCode, $request->searchQuery);
        }

        return $this->renderResponse(new AppResponse($employees, trans('messages.allDataRetrieved')));
    }

    public
    function searchCustom(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required',
            'menuCode' => 'required',
            'searchQuery' => 'present'
        ]);

        // if Super Admin and has no role
        if ($this->requester->getIsUserSA() && !$this->requester->getRoleIds()) {
            $employees = $this->personDao->searchCustomSA($request->searchQuery);
        } else if ($this->requester->getAppId() == config('constant.ess_app_id')) {
            $employees = $this->personDao->searchCustomSA($request->searchQuery);
        } else {
            $employees = $this->personDao->searchCustom($request->menuCode, $request->searchQuery);
        }

        return $this->renderResponse(new AppResponse($employees, trans('messages.allDataRetrieved')));
    }

    public
    function resign(Request $request)
    {
        $this->validate($request, ["companyId" => "required"]);

        $person = $this->personDao->resign($request->companyId);
        $resp = new AppResponse($person, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public
    function advancedSearch(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|exists:companies,id',
            'menuCode' => 'required',
            'searchData' => 'required',
            'pageInfo' => 'required'
        ]);
        $request->merge((array)$request->pageInfo);
        $this->validate($request, [
            'pageLimit' => 'present|nullable|integer|min:0',
            'pageNo' => 'present|nullable|integer|min:1',
            'order' => 'nullable|present|string',
            'orderDirection' => 'nullable|present|in:asc,desc',
        ]);
        $request->merge((array)$request->searchData);
        $this->validate($request, [
            'selectedFields' => 'required|array|min:1',
            'criteria' => 'present|array',
            'criteria.*.field' => 'nullable|min:1|max:4',
            'criteria.*.conj' => 'nullable|min:1|max:3',
            'criteria.*.val' => ''
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        //$limit = 2147483647; // Limit set to Max Value of Integers
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);
        $order = PagingAppResponse::getOrder($request->pageInfo);
        $orderDirection = PagingAppResponse::getOrderDirection($request->pageInfo);

        info('$offset', array($offset));
        info('$limit', array($limit));

        $request->has('activeStatus') ?
            $activeStatus = $request->activeStatus
            : $activeStatus = '';

        $request->has('localSearch') ?
            $localSearch = $request->localSearch
            : $localSearch = '';


        // advanced Search for overtime request ESS
        if ($request->has('unitCode') && $request->has('personId')) {
            info('masuk mana0');
            $data = $this->personDao->advancedSearchUnit(
                $request->searchData,
                $offset,
                $limit,
                $order,
                $orderDirection,
                $activeStatus,
                $localSearch,
                $request->unitCode,
                $request->personId
            );

            // if Super Admin and has no role
        } else if ($this->requester->getIsUserSA()) {
            info('masuk mana1');

            $data = $this->personDao->advancedSearchSA(
                $request->searchData,
                $offset,
                $limit,
                $order,
                $orderDirection,
                $activeStatus,
                $localSearch
            );
        } else {
            info('masuk mana2');
            $data = $this->personDao->advancedSearch(
                $request->menuCode,
                $request->searchData,
                $offset,
                $limit,
                $order,
                $orderDirection,
                $activeStatus,
                $localSearch
            );
        }

        $results = $data[0];
        $totalRows = $data[1];

        info(print_r($results, true));

        // processing for inject selected field
        foreach ($results as $key => $res) {
            foreach ($request->searchData['selectedFields'] as $fieldId) {
                // custom object
                if (is_int($fieldId)) {
                    $field = $this->personCustomObjectFieldDao->getValue($res->id, $fieldId);
                    // check if it is from lov
                    if (count($field)) {
                        $name = $field->name;
                        if ($field->lovTypeCode) {
                            $lov = $this->lovDao->getOne($field->lovTypeCode, $field->value);
                            if (count($lov)) {
                                info($name);
                                $res->$name = $lov->valData;
                            } else {
                                $res->$name = $field->value;
                            }
                        } else {
                            info($name);
                            $res->$name = $field->value;
                        }
                    }
                } // custom field
                else if ($fieldId[0] == 'c') {
                    $field = $this->customFieldDao->getOneByFieldName($fieldId);
                    // check if it is from lov
                    if (count($field)) {
                        if ($field->lovTypeCode) {
                            $name = $field->name;

                            $lovKeyData = $res->$name;
                            $lov = $this->lovDao->getOne($field->lovTypeCode, $lovKeyData);

                            if (count($lov)) {
                                $value = $lov->valData;
                                $res->$name = $value;
                            }
                        }
                    }
                }
            }
        }

        return $this->renderResponse(
            new PagingAppResponse($results, trans('messages.allDataRetrieved'), $limit, $totalRows, $pageNo)
        );
    }

    public
    function advancedSearchActiveEmployee(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|exists:companies,id',
            'menuCode' => 'required',
            'searchData' => 'required',
            'pageInfo' => 'required'
        ]);
        $request->merge((array)$request->pageInfo);
        $this->validate($request, [
            'pageLimit' => 'present|nullable|integer|min:0',
            'pageNo' => 'present|nullable|integer|min:1',
            'order' => 'nullable|present|string',
            'orderDirection' => 'nullable|present|in:asc,desc',
        ]);
        $request->merge((array)$request->searchData);
        $this->validate($request, [
            'selectedFields' => 'required|array|min:1',
            'criteria' => 'present|array',
            'criteria.*.field' => 'nullable|min:1|max:4',
            'criteria.*.conj' => 'nullable|min:1|max:3',
            'criteria.*.val' => ''
        ]);

        $offset = PagingAppResponse::getOffset($request->pageInfo);
        $limit = PagingAppResponse::getPageLimit($request->pageInfo);
        //$limit = 2147483647; // Limit set to Max Value of Integers
        $pageNo = PagingAppResponse::getPageNo($request->pageInfo);
        $order = PagingAppResponse::getOrder($request->pageInfo);
        $orderDirection = PagingAppResponse::getOrderDirection($request->pageInfo);

        $activeStatus = 'yes';
        $localSearch = '';

        // if Super Admin and has no role
        if ($this->requester->getIsUserSA() && !$this->requester->getRoleIds()) {
            $data = $this->personDao->advancedSearchSA(
                $request->searchData,
                $offset,
                $limit,
                $order,
                $orderDirection,
                $activeStatus,
                $localSearch
            );
        } else {
            $data = $this->personDao->advancedSearch(
                $request->menuCode,
                $request->searchData,
                $offset,
                $limit,
                $order,
                $orderDirection,
                $activeStatus,
                $localSearch
            );
        }

        // info('$data',array($data[0]));
        $result = $data[0];
        $totalRows = $data[1];

        return $this->renderResponse(
            new PagingAppResponse($result, trans('messages.allDataRetrieved'), $limit, $totalRows, $pageNo)
        );
    }

    /**
     * Save person to DB
     * @param Request $request
     * @return AppResponse
     */
    public
    function save(Request $request)
    {
        $data = array();

        $this->validate($request, [
            'effBegin' => 'required|date|before_or_equal:effEnd',
            'effEnd' => 'required|date',
            'firstName' => 'required|max:50',
            'lastName' => 'present|max:50',
            'email' => 'present|email|max:50',
            'phone' => 'present|max:50',
            'mobile' => 'present|max:50',
            'lovPtyp' => 'required|max:10|exists:lovs,key_data'
        ]);
        $this->checkPersonBasicInfoRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $person = $this->constructPerson($request);
            if ($request->has('vacancyId')) {
                $person['vacancy_id'] = $request->vacancyId;
            }
            if ($request->has('candidateReadyToHireId')) {
                $person['candidate_ready_to_hire_id'] = $request->candidateReadyToHireId;
            }

            $data['id'] = $this->personDao->save($person);
            $request->id = $data['id'];
            $this->savePersonSocialMedias($request);
            $this->saveCustomFieldPersonBasicInfo($request, $request->effBegin, $request->effEnd);

            // WTF IT THIS CODE??
            // Why u changed the active (login) user's person to this person?
            // $this->userDao->update($this->requester->getUserId(), ['person_id' => $data['id']]);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    public
    function updateBasicInfo(Request $request)
    {
        $this->checkPersonBasicInfoRequest($request);
        $this->validate($request, ['id' => 'required']);

        DB::transaction(function () use (&$request, &$person, &$shouldSync) {
            $personBasicInfo = $this->constructPersonBasicInfo($request);

            $this->personDao->update(
                $request->id,
                $personBasicInfo
            );
            $this->savePersonSocialMedias($request);
        });

        $resp = new AppResponse(null, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Update person to DB
     * @param Request $request
     * @return AppResponse
     */
    public
    function update(Request $request)
    {
        $data = array();
        if ($request->hire == false) {
            $this->checkPersonRequest($request);
        }
        $this->validate($request, ['id' => 'required']);

        $person = [];
        $shouldSync = false;
        info('$request->hire', [$request->hire]);
        info('$request->upload', [$request->upload]);
        info('$request->id', [$request->id]);

        DB::transaction(function () use (&$request, &$data, &$person, &$shouldSync) {
            $oldPerson = $this->personDao->getOne($request->id);
            if ($request->upload) {
                if ($oldPerson->filePhoto) {
                    $this->deleteFile($request, $oldPerson->filePhoto);
                    //                    if (!$this->deleteFile($request, $oldPerson->filePhoto)) {
                    //                        throw new AppException(trans('messages.updateFail'));
                    //                    }
                }
                $fileUris = $this->getFileUris($request, $data);
                if (!empty($fileUris)) {
                    $person['file_photo'] = $fileUris['PP'];
                }

                $this->personDao->updatePerson(
                    $request->id,
                    $person
                );
            } else if ($request->hire) {
                $person['lov_ptyp'] = 'EMP';

                //                if($request->vacancyId) { $person['vacancy_id'] = $request->vacancyId; }
                //                if($request->candidateReadyToHireId) { $person['candidate_ready_to_hire_id'] = $request->candidateReadyToHireId; }

                $this->personDao->updatePerson(
                    $request->id,
                    $person
                );
            } else {
                $person = $this->constructPerson($request);
                $shouldSync = ($oldPerson->firstName !== $person['first_name'] ||
                    $oldPerson->lastName !== $person['last_name']);
                if ($request->isHistory) {
                    $person['eff_end'] = Carbon::now();
                    $date = [
                        'eff_end' => Carbon::now()
                    ];
                    $this->personDao->update(
                        $request->id,
                        $request->effBegin,
                        $date
                    );
                    $this->customFieldPersonBasicInfoDao->update($request->id, $request->effBegin, $date);

                    $person['id'] = $oldPerson->id;
                    $person['eff_begin'] = Carbon::now();
                    $person['eff_end'] = config('constant.defaultEndDate');
                    $this->personDao->save($person);

                    $this->saveCustomFieldPersonBasicInfo($request, $person['eff_begin'], $person['eff_end']);
                } else {
                    $this->personDao->update(
                        $request->id,
                        $oldPerson->effBegin,
                        $person
                    );
                    $this->updateCustomFieldPersonBasicInfo($request);
                }
                $this->savePersonSocialMedias($request);
            }
        });

        if ($shouldSync) {
            dispatch(new SyncJob('core', 'um', [
                'entity' => 'person',
                'person_id' => $request->id,
                'person_first_name' => $person['first_name'],
                'person_last_name' => $person['last_name']
            ]));
        }

        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Update person from ESS to DB
     * @param Request $request
     * @property WorkflowDao workflowDao
     * @return AppResponse
     */
    public
    function updateFromEss(Request $request)
    {
        $this->validate($request, [
            'id' => 'required'
        ]);

        //        $workflow = $this->workflowDao->getOne("PROF");

        $req = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $this->requester->getCompanyId(),
            "employee_id" => $request->employeeId,
            "email" => $request->email,
            "mobile" => $request->mobile,
            "hobbies" => $request->hobbies,
            "strength" => $request->strength,
            "weakness" => $request->weakness,
            "country_code" => $request->countryCode,
            "lov_rlgn" => $request->lovRlgn,
            "lov_mars" => $request->lovMars
        ];

        $data = array();

        //        if(!$workflow->isActive) {

        $req['status'] = 'A';

        $person = [];
        $shouldSync = false;

        DB::transaction(function () use (&$request, &$req, &$data, &$person, &$shouldSync) {

            $oldPerson = $this->personDao->getOne($request->id);

            $person = [
                'eff_begin' => Carbon::now()->toDateTimeString(),
                'eff_end' => '9999-12-31',
                "email" => $request->email,
                "mobile" => $request->mobile . " ",
                "birth_place" => $request->birthPlace,
                "birth_date" => $request->birthDate,
                "hobbies" => $request->hobbies,
                "strength" => $request->strength,
                "weakness" => $request->weakness,
                "country_code" => $request->countryCode,
                "lov_blod" => $request->lovBlod,
                "lov_gndr" => $request->lovGndr,
                "lov_rlgn" => $request->lovRlgn,
                "lov_mars" => $request->lovMars
            ];

            $this->personDao->update(
                $request->id,
                $oldPerson->effBegin,
                $person
            );

            //            $this->requestBasicInfoDao->save($req);
        });

        $resp = new AppResponse($data, trans('messages.dataUpdated'));

        return $this->renderResponse($resp);
        //        }
        //        else {
        //			$req['status'] = 'P';
        //
        //            DB::transaction(function () use (&$req, &$data) {
        //
        //                $data['id'] = $this->requestBasicInfoDao->save($req);
        //
        //            });
        //        }

        $resp = new AppResponse($data, trans('messages.dataUpdated'));

        return $this->renderResponse($resp);
    }

    /**
     * Update is_deleted = 1 person to DB
     * @param Request $request
     * @return AppResponse
     */
    public
    function delete(Request $request)
    {
        $this->validate($request, [
            "id" => "required"
        ]);

        $this->personDao->delete(
            $request->id,
            $request->effBegin,
            $request->effEnd
        );

        $this->customFieldPersonBasicInfoDao->delete(
            $request->id,
            $request->effBegin,
            $request->effEnd
        );

        $resp = new AppResponse(null, trans('messages.dataDeleted'));
        return $this->renderResponse($resp);
    }

    public
    function getHierarchy(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:persons,id'
        ]);

        $person = $this->personDao->getOne($request->id);
        $supervisor = $this->personDao->getDirectSupervisor($request->id);
        $subordinates = $this->personDao->getDirectSubordinates($request->id);
        $children = array_map(function ($subordinate) {
            return [
                'id' => $subordinate->id,
                'fullName' => $subordinate->firstName . ' ' . $subordinate->lastName,
                'filePhoto' => $subordinate->filePhoto,
                'position' => $subordinate->position,
                'unit' => $subordinate->unit
            ];
        }, $subordinates->toArray());

        $hierarchy = [
            'id' => $person->id,
            'fullName' => $person->firstName . ' ' . $person->lastName,
            'filePhoto' => $person->filePhoto,
            'position' => $person->positionName,
            'unit' => $person->unitName
        ];
        if (count($children) > 0) {
            $hierarchy['children'] = $children;
        }
        if ($supervisor) {
            $hierarchy = [
                'id' => $supervisor->id,
                'fullName' => $supervisor->firstName . ' ' . $supervisor->lastName,
                'filePhoto' => $supervisor->filePhoto,
                'position' => $supervisor->position,
                'unit' => $supervisor->unit,
                'children' => [$hierarchy]
            ];
        }

        return $this->renderResponse(new AppResponse($hierarchy, trans('messages.allDataRetrieved')));
    }

    public
    function getDirectSubordinates(Request $request)
    {
        $this->validate($request, [
            'id' => 'required'
        ]);

        $subordinates = $this->personDao->getDirectSubordinates($request->id);

        return $this->renderResponse(new AppResponse($subordinates, trans('messages.allDataRetrieved')));
    }

    public
    function getDefaultSuperior(Request $request)
    {
        $this->validate($request, [
            'positionCode' => 'required'
        ]);

        $superior = $this->personDao->getDefaultSuperior($request->positionCode);

        return $this->renderResponse(new AppResponse($superior, trans('messages.dataRetrieved')));
    }

    /**
     * Validate save/update person request.
     * @param Request $request
     */
    private
    function checkPersonRequest(Request $request)
    {
        $this->validate($request, [
            'upload' => 'required|boolean'
        ]);

        if ($request->upload == true) {
            $this->validate($request, [
                'docTypes' => 'required|array|min:1',
                'fileContents' => 'required|array|min:1',
                'ref' => 'required|string|max:255',
                'companyId' => 'required|integer|exists:companies,id'
            ]);
        } else {
            $this->validate($request, [
                'effBegin' => 'required|date|before_or_equal:effEnd',
                'effEnd' => 'required|date',
                'firstName' => 'required|max:50',
                'lastName' => 'present|max:50',
                'email' => 'nullable|email|max:50',
                'phone' => 'present|max:50',
                'mobile' => 'present|max:50',
                'lovPtyp' => 'required|max:10|exists:lovs,key_data'
            ]);
            $this->checkPersonBasicInfoRequest($request);
        }
    }

    private
    function checkPersonBasicInfoRequest($request)
    {
        $this->validate($request, [
            'idCard' => 'present|max:20',
            'birthPlace' => 'present|max:50',
            'birthDate' => 'required|date',
            'socialMedias' => 'present|array',
            'hobbies' => 'present|max:255',
            'strength' => 'present|max:255',
            'weakness' => 'present|max:255',
            'countryCode' => 'required|exists:countries,code',
            'lovBlod' => 'nullable|max:10|exists:lovs,key_data',
            'lovGndr' => 'required|max:10|exists:lovs,key_data',
            'lovRlgn' => 'required|max:10|exists:lovs,key_data',
            'lovMars' => 'required|max:10|exists:lovs,key_data'
        ]);
    }

    /**
     * Construct a person object (array).
     * @param Request $request
     * @return array
     */
    private
    function constructPerson(Request $request)
    {
        $person = [
            "tenant_id" => $this->requester->getTenantId(),
            'eff_begin' => $request->effBegin,
            'eff_end' => $request->effEnd,
            "first_name" => $request->firstName,
            "last_name" => $request->lastName,
            "email" => $request->email,
            "phone" => $request->phone,
            "mobile" => $request->mobile,
            "id_card" => $request->idCard,
            "birth_place" => $request->birthPlace,
            "birth_date" => $request->birthDate,
            "hobbies" => $request->hobbies,
            "strength" => $request->strength,
            "weakness" => $request->weakness,
            "country_code" => $request->countryCode,
            "lov_ptyp" => $request->lovPtyp,
            "lov_blod" => $request->lovBlod,
            "lov_gndr" => $request->lovGndr,
            "lov_rlgn" => $request->lovRlgn,
            "lov_mars" => $request->lovMars
        ];
        return $person;
    }

    private
    function constructPersonBasicInfo($request)
    {
        $personBasicInfo = [
            "id_card" => $request->idCard,
            "birth_place" => $request->birthPlace,
            "birth_date" => $request->birthDate,
            "hobbies" => $request->hobbies,
            "strength" => $request->strength,
            "weakness" => $request->weakness,
            "country_code" => $request->countryCode,
            "lov_blod" => $request->lovBlod,
            "lov_gndr" => $request->lovGndr,
            "lov_rlgn" => $request->lovRlgn,
            "lov_mars" => $request->lovMars
        ];
        return $personBasicInfo;
    }

    /**
     * Get all uploaded file URIs from File service.
     * @param Request $request
     * @param array $data
     * @return array
     */
    private
    function getFileUris(Request $request, array &$data)
    {
        $guzzle = new \GuzzleHttp\Client();
        try {
            $response = $guzzle->request(
                'POST',
                env('CDN_SERVICE_SAVE_API'),
                [
                    'multipart' => $this->constructPayload($request),
                    'headers' => [
                        'Authorization' => $request->headers->get('authorization'),
                        'Origin' => $request->headers->get('origin')
                    ]
                ]
            );
            $body = json_decode($response->getBody()->getContents());
            if ($body->status === 200) {
                $data['file'] = (array)$body;
                return (array)$body->data->fileUris;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $data['file'] = [];
            if ($e->hasResponse()) {
                $body = $e->getResponse()->getBody();
                $data['file'] = (array)json_decode($body->getContents());
            } else {
                $data['file']['status'] = 500;
                $data['file']['message'] = $e->getMessage();
                $data['file']['data'] = null;
            }
        }

        return [];
    }

    private
    function deleteFile($request, $fileUri)
    {
        $guzzle = new \GuzzleHttp\Client();
        try {
            $response = $guzzle->request('POST', env('CDN_SERVICE_DELETE_API'), [
                'form_params' => ['fileUri' => $fileUri, 'companyId' => $request->companyId],
                'headers' => [
                    'Authorization' => $request->headers->get('authorization'),
                    'Origin' => $request->headers->get('origin')
                ]
            ]);
            $body = json_decode($response->getBody()->getContents());
            return $body->status === 200;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return false;
        }
        // should never reach here
    }

    /**
     * Construct a multipart payload for uploading file to File service.
     * @param Request $request
     * @return array
     */
    private
    function constructPayload(Request $request)
    {
        $payload = array([
            'name' => 'data',
            'contents' => $request->data
        ], [
            'name' => 'ref',
            'contents' => $request->ref
        ], [
            'name' => 'companyId',
            'contents' => $request->companyId
        ]);
        foreach ($request->docTypes as $i => $docType) {
            array_push($payload, [
                'name' => "docTypes[$i]",
                'contents' => $docType
            ]);
        }
        foreach ($request->fileContents as $i => $file) {
            array_push($payload, [
                'name' => "fileContents[$i]",
                'contents' => file_get_contents($file),
                'filename' => $file->getClientOriginalName()
            ]);
        }

        return $payload;
    }

    private
    function savePersonSocialMedias(Request $request)
    {
        if ($request->has('socialMedias')) {
            $this->personSocialMediaDao->deleteByPersonId($request->id);
        }
        for ($i = 0; $i < count($request->socialMedias); $i++) {
            $socialMediaReq = new \Illuminate\Http\Request();
            $socialMedia = (array)$request->socialMedias[$i];
            $socialMediaReq->replace([
                'lovSocm' => $socialMedia['lovSocm'],
                'account' => $socialMedia['account']
            ]);
            $this->validate($socialMediaReq, [
                "lovSocm" => 'required|max:10|exists:lovs,key_data',
                "account" => 'required|max:255'
            ]);
            $data = [
                'tenant_id' => $this->requester->getTenantId(),
                'person_id' => $request->id,
                'lov_socm' => $socialMedia['lovSocm'],
                'account' => $socialMedia['account']
            ];
            $this->personSocialMediaDao->save($data);
        }
    }

    private
    function saveCustomFieldPersonBasicInfo(Request $request, $effBegin, $effEnd)
    {
        if ($request->isNew) {
            $data = [
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'eff_begin' => $request->effBegin,
                'eff_end' => $request->effEnd,
                'person_id' => $request->id,
                'id' => $request->id
            ];
        } else {
            $data = [
                'id' => $request->id,
                'tenant_id' => $this->requester->getTenantId(),
                'company_id' => $this->requester->getCompanyId(),
                'eff_begin' => $effBegin,
                'eff_end' => $effEnd,
                'person_id' => $request->id,
                'c1' => $request->c1,
                'c2' => $request->c2,
                'c3' => $request->c3,
                'c4' => $request->c4,
                'c5' => $request->c5,
                'c6' => $request->c6,
                'c7' => $request->c7,
                'c8' => $request->c8,
                'c9' => $request->c9,
                'c10' => $request->c10
            ];
        }

        $this->customFieldPersonBasicInfoDao->save($data);
    }

    private
    function updateCustomFieldPersonBasicInfo(Request $request)
    {
        $data = [
            'tenant_id' => $this->requester->getTenantId(),
            'company_id' => $this->requester->getCompanyId(),
            'eff_begin' => $request->effBegin,
            'eff_end' => $request->effEnd,
            'person_id' => $request->id,
            'id' => $request->id,
            'c1' => $request->c1,
            'c2' => $request->c2,
            'c3' => $request->c3,
            'c4' => $request->c4,
            'c5' => $request->c5,
            'c6' => $request->c6,
            'c7' => $request->c7,
            'c8' => $request->c8,
            'c9' => $request->c9,
            'c10' => $request->c10
        ];
        $this->customFieldPersonBasicInfoDao->update($request->id, $request->effBegin, $data);
    }

    private
    function savePersonCustomObject($personId, $effBegin, $effEnd)
    {
        DB::transaction(function () use ($personId, $effBegin, $effEnd) {
            $customObjects = $this->customObjectDao->getAllByLovCusobj('PSN');
            foreach ($customObjects as $customObject) {
                $this->personCustomObjectDao->save([
                    'tenant_id' => $this->requester->getTenantId(),
                    'company_id' => $this->requester->getCompanyId(),
                    'person_id' => $personId,
                    'co_id' => $customObject->id,
                    'eff_begin' => $effBegin,
                    'eff_end' => $effEnd
                ]);
            }
        });
    }

    /**
     * Get structure hierarchy for organization
     * @param companyId , orgStructureId
     */
    private
    function getUnitBelow($unitCode, $orgStructureId)
    {
        $data = $this->personDao->getRecursive(
            $unitCode,
            $orgStructureId
        );

        // Prepare mapping of (code => node).
        $nodes = [];

        $collection = collect($data);

        $data = $collection->unique('code')->toArray();

        foreach ($data as $datum) {
            $nodes[$datum->code] = [
                'code' => $datum->code,
                'parentCode' => $datum->parentCode,
            ];
        }

        foreach (array_reverse($data) as $datum) {
            // Return root node.
            if (!$datum->parentCode) {
                // Reverse children array of node due to the algorithm used.
                if (array_key_exists('children', $nodes[$datum->code])) {
                    $nodes[$datum->code]['children'] = array_reverse($nodes[$datum->code]['children']);
                }
                if (!array_key_exists('children', $nodes[$datum->code])) {
                    $nodes[$datum->code]['children'] = [];
                }
                return $nodes[$datum->code];
            }
            // Initialize children array of parent node.
            if (!array_key_exists('children', $nodes[$datum->parentCode])) {
                $nodes[$datum->parentCode]['children'] = [];
            }
            // Reverse children array of child node due to the algorithm used.
            if (array_key_exists('children', $nodes[$datum->code])) {
                $nodes[$datum->code]['children'] = array_reverse($nodes[$datum->code]['children']);
            }

            if (!array_key_exists('children', $nodes[$datum->code])) {
                $nodes[$datum->code]['children'] = [];
            }
            // Move node to parent's children.
            array_push($nodes[$datum->parentCode]['children'], $nodes[$datum->code]);
            unset($nodes[$datum->code]);
        }
        return count($nodes) === 0 ? null : $nodes;
    }
}
