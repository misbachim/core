<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('/live', 'TestController@live');

$router->group(['prefix' => 'company'], function () use ($router) {
    $router->post('getOne', 'CompanyController@getOne');
    $router->post('save', 'CompanyController@save');
    $router->post('update', 'CompanyController@update');
    $router->post('delete', 'CompanyController@delete');
    $router->post('getSetting', 'CompanyController@getSetting');
    $router->post('getSettings', 'CompanyController@getSettings');
    $router->post('getMany', 'CompanyController@getMany');
    $router->post('getSortOrder', 'CompanyController@getSortOrder');
});

$router->group(['prefix' => 'location'], function () use ($router) {
    $router->post('getAll', 'LocationController@getAll');
    $router->post('getClockingLocations', 'LocationController@getClockingLocations');
    $router->post('getOne', 'LocationController@getOne');
    $router->post('getOneByCode', 'LocationController@getOneByCode');
    $router->post('save', 'LocationController@save');
    $router->post('update', 'LocationController@update');
    $router->post('delete', 'LocationController@delete');
    $router->post('lov', 'LocationController@getLov');
    $router->post('getDefault', 'LocationController@getDefault');
    $router->post('search', 'LocationController@search');
    $router->post('getAllActive', 'LocationController@getAllActive');
    $router->post('getAllInActive', 'LocationController@getAllInActive');
});

$router->group(['prefix' => 'locationGroup'], function () use ($router) {
    $router->post('getAll', 'LocationGroupController@getAll');
    $router->post('getOne', 'LocationGroupController@getOne');
    $router->post('save', 'LocationGroupController@save');
    $router->post('update', 'LocationGroupController@update');
    $router->post('delete', 'LocationGroupController@delete');
    $router->post('getAllActive', 'LocationGroupController@getAllActive');
    $router->post('getAllInActive', 'LocationGroupController@getAllInActive');
});

$router->group(['prefix' => 'lov'], function () use ($router) {
    $router->post('getAll', 'LovController@getAll');
    $router->post('getOne', 'LovController@getOne');
    $router->post('save', 'LovController@save');
    $router->post('update', 'LovController@update');
    $router->post('delete', 'LovController@delete');
});

$router->group(['prefix' => 'lovType'], function () use ($router) {
    $router->post('getAll', 'LovTypeController@getAll');
    $router->post('getOne', 'LovTypeController@getOne');
    $router->post('save', 'LovTypeController@save');
    $router->post('update', 'LovTypeController@update');
    $router->post('delete', 'LovTypeController@delete');
});

$router->group(['prefix' => 'country'], function () use ($router) {
    $router->post('getAll', 'CountryController@getAll');
    $router->post('getOne', 'CountryController@getOne');
    $router->post('save', 'CountryController@save');
    $router->post('update', 'CountryController@update');
    $router->post('delete', 'CountryController@delete');
    $router->post('lov', 'CountryController@getLov');
});

$router->group(['prefix' => 'province'], function () use ($router) {
    $router->post('getAll', 'ProvinceController@getAll');
    $router->post('getOne', 'ProvinceController@getOne');
    $router->post('save', 'ProvinceController@save');
    $router->post('update', 'ProvinceController@update');
    $router->post('delete', 'ProvinceController@delete');
    $router->post('lov', 'ProvinceController@getLov');
});

$router->group(['prefix' => 'city'], function () use ($router) {
    $router->post('getAll', 'CityController@getAll');
    $router->post('getOne', 'CityController@getOne');
    $router->post('save', 'CityController@save');
    $router->post('update', 'CityController@update');
    $router->post('delete', 'CityController@delete');
    $router->post('lov', 'CityController@getLov');
    $router->post('searchCity', 'CityController@searchCity');
});

$router->group(['prefix' => 'district'], function () use ($router) {
    $router->post('getAll', 'DistrictController@getAll');
    $router->post('getOne', 'DistrictController@getOne');
    $router->post('save', 'DistrictController@save');
    $router->post('update', 'DistrictController@update');
    $router->post('delete', 'DistrictController@delete');
    $router->post('lov', 'DistrictController@getLov');
});

$router->group(['prefix' => 'job'], function () use ($router) {
    $router->post('getAll', 'JobController@getAll');
    $router->post('getAllActive', 'JobController@getAllActive');
    $router->post('getAllInActive', 'JobController@getAllInActive');
    $router->post('getOne', 'JobController@getOne');
    $router->post('getOneCode', 'JobController@getOneCode');
    $router->post('getJobWorkingCondition', 'JobController@getJobWorkingCondition');
    $router->post('getJobResponsibility', 'JobController@getJobResponsibility');
    $router->post('save', 'JobController@save');
    $router->post('update', 'JobController@update');
    $router->post('delete', 'JobController@delete');
    $router->post('lov', 'JobController@getLov');
    $router->post('slov', 'JobController@getSLov');
    $router->post('search', 'JobController@search');
});

$router->group(['prefix' => 'jobFamily'], function () use ($router) {
    $router->post('getAll', 'JobFamilyController@getAll');
    $router->post('getOne', 'JobFamilyController@getOne');
    $router->post('save', 'JobFamilyController@save');
    $router->post('update', 'JobFamilyController@update');
    $router->post('delete', 'JobFamilyController@delete');
    $router->post('lov', 'JobFamilyController@getLov');
    $router->post('getAllActive', 'JobFamilyController@getAllActive');
    $router->post('getAllInActive', 'JobFamilyController@getAllInActive');
});

$router->group(['prefix' => 'jobCategory'], function () use ($router) {
    $router->post('getAll', 'JobCategoryController@getAll');
    $router->post('getOne', 'JobCategoryController@getOne');
    $router->post('save', 'JobCategoryController@save');
    $router->post('update', 'JobCategoryController@update');
    $router->post('delete', 'JobCategoryController@delete');
    $router->post('lov', 'JobCategoryController@getLov');
    $router->post('getAllActive', 'JobCategoryController@getAllActive');
    $router->post('getAllInActive', 'JobCategoryController@getAllInActive');
});

$router->group(['prefix' => 'workingCondition'], function () use ($router) {
    $router->post('getAll', 'WorkingConditionController@getAll');
    $router->post('getOne', 'WorkingConditionController@getOne');
    $router->post('save', 'WorkingConditionController@save');
    $router->post('update', 'WorkingConditionController@update');
    $router->post('delete', 'WorkingConditionController@delete');
    $router->post('lov', 'WorkingConditionController@getLov');
    $router->post('getAllActive', 'WorkingConditionController@getAllActive');
    $router->post('getAllInActive', 'WorkingConditionController@getAllInActive');
});

$router->group(['prefix' => 'workingConditionType'], function () use ($router) {
    $router->post('getAll', 'WorkingConditionTypeController@getAll');
    $router->post('getOne', 'WorkingConditionTypeController@getOne');
    $router->post('save', 'WorkingConditionTypeController@save');
    $router->post('update', 'WorkingConditionTypeController@update');
    $router->post('delete', 'WorkingConditionTypeController@delete');
    $router->post('lov', 'WorkingConditionTypeController@getLov');
    $router->post('getAllActive', 'WorkingConditionTypeController@getAllActive');
    $router->post('getAllInActive', 'WorkingConditionTypeController@getAllInActive');
});

$router->group(['prefix' => 'responsibility'], function () use ($router) {
    $router->post('getAll', 'ResponsibilityController@getAll');
    $router->post('getAllByResponsibilityGroup', 'ResponsibilityController@getAllByResponsibilityGroup');
    $router->post('getOne', 'ResponsibilityController@getOne');
    $router->post('save', 'ResponsibilityController@save');
    $router->post('update', 'ResponsibilityController@update');
    $router->post('delete', 'ResponsibilityController@delete');
    $router->post('lov', 'ResponsibilityController@getLov');
    $router->post('getAllActive', 'ResponsibilityController@getAllActive');
    $router->post('getAllInActive', 'ResponsibilityController@getAllInActive');

});

$router->group(['prefix' => 'responsibilityGroup'], function () use ($router) {
    $router->post('getAll', 'ResponsibilityGroupController@getAll');
    $router->post('getOne', 'ResponsibilityGroupController@getOne');
    $router->post('save', 'ResponsibilityGroupController@save');
    $router->post('update', 'ResponsibilityGroupController@update');
    $router->post('delete', 'ResponsibilityGroupController@delete');
    $router->post('lov', 'ResponsibilityGroupController@getLov');
    $router->post('getAllActive', 'ResponsibilityGroupController@getAllActive');
    $router->post('getAllInActive', 'ResponsibilityGroupController@getAllInActive');
});

$router->group(['prefix' => 'grade'], function () use ($router) {
    $router->post('getAll', 'GradeController@getAll');
    $router->post('getOne', 'GradeController@getOne');
    $router->post('getOneByCode', 'GradeController@getOneByCode');
    $router->post('save', 'GradeController@save');
    $router->post('update', 'GradeController@update');
    $router->post('delete', 'GradeController@delete');
    $router->post('lov', 'GradeController@getLov');
    $router->post('getAllActive', 'GradeController@getAllActive');
    $router->post('getAllInActive', 'GradeController@getAllInActive');
});

$router->group(['prefix' => 'payRate'], function () use ($router) {
    $router->post('getAll', 'PayRateController@getAll');
    $router->post('getOne', 'PayRateController@getOne');
    $router->post('save', 'PayRateController@save');
    $router->post('update', 'PayRateController@update');
    $router->post('delete', 'PayRateController@delete');
});

$router->group(['prefix' => 'costCenter'], function () use ($router) {
    $router->post('getAll', 'CostCenterController@getAll');
    $router->post('getAllActive', 'CostCenterController@getAllActive');
    $router->post('getAllInActive', 'CostCenterController@getAllInActive');
    $router->post('getOne', 'CostCenterController@getOne');
    $router->post('save', 'CostCenterController@save');
    $router->post('update', 'CostCenterController@update');
    $router->post('delete', 'CostCenterController@delete');
    $router->post('lov', 'CostCenterController@getLov');
    $router->post('getDefault', 'CostCenterController@getDefault');
});

$router->group(['prefix' => 'unitType'], function () use ($router) {
    $router->post('getAll', 'UnitTypeController@getAll');
    $router->post('getOne', 'UnitTypeController@getOne');
    $router->post('save', 'UnitTypeController@save');
    $router->post('update', 'UnitTypeController@update');
    $router->post('delete', 'UnitTypeController@delete');
    $router->post('lov', 'UnitTypeController@getLov');
});

$router->group(['prefix' => 'unit'], function () use ($router) {
    $router->post('getAll', 'UnitController@getAll');
    $router->post('getOne', 'UnitController@getOne');
    $router->post('save', 'UnitController@save');
    $router->post('update', 'UnitController@update');
    $router->post('delete', 'UnitController@delete');
    $router->post('lov', 'UnitController@getLov');
    $router->post('slov', 'UnitController@getSLov');
    $router->post('search', 'UnitController@search');
    $router->post('searchCustom', 'UnitController@searchCustom');
    $router->post('getAllActive', 'UnitController@getAllActive');
    $router->post('getAllInActive', 'UnitController@getAllInActive');
});

$router->group(['prefix' => 'position'], function () use ($router) {
    $router->post('getAll', 'PositionController@getAll');
    $router->post('getOne', 'PositionController@getOne');
    $router->post('getPositionByUnit', 'PositionController@getPositionByUnit');
    $router->post('getPositionByEmployeeId', 'PositionController@getPositionByEmployeeId');
    $router->post('getCompetencyModelHistory', 'PositionController@getCompetencyModelHistory');
    $router->post('save', 'PositionController@save');
    $router->post('update', 'PositionController@update');
    $router->post('delete', 'PositionController@delete');
    $router->post('lov', 'PositionController@getLov');
    $router->post('slov', 'PositionController@getSLov');
    $router->post('nlov', 'PositionController@getNLov');
    $router->post('isFull', 'PositionController@isFull');
    $router->post('search', 'PositionController@search');
    $router->post('checkHeadOfUnitOnUnit', 'PositionController@checkHeadOfUnitOnUnit');
    $router->post('getAllActive', 'PositionController@getAllActive');
    $router->post('getAllInActive', 'PositionController@getAllInActive');
    $router->post('getPositionForMultipleSelect', 'PositionController@getPositionForMultipleSelect');

});

$router->group(['prefix' => 'orgStructure'], function () use ($router) {
    $router->post('getAll', 'OrgStructureController@getAll');
    $router->post('getOne', 'OrgStructureController@getOne');
    $router->post('getFlatHierarchy', 'OrgStructureController@getFlatHierarchy');
    $router->post('save', 'OrgStructureController@save');
    $router->post('update', 'OrgStructureController@update');
    $router->post('delete', 'OrgStructureController@delete');
    $router->post('getParent', 'OrgStructureController@getParent');
    $router->post('addRoot', 'OrgStructureController@addRoot');
    $router->post('addParent', 'OrgStructureController@addParent');
    $router->post('addSibling', 'OrgStructureController@addSibling');
    $router->post('addChild', 'OrgStructureController@addChild');
    $router->post('replaceNode', 'OrgStructureController@replaceNode');
    $router->post('switchNode', 'OrgStructureController@switchNode');
    $router->post('removeNode', 'OrgStructureController@removeNode');
    $router->post('getAllActive', 'OrgStructureController@getAllActive');
    $router->post('getAllInActive', 'OrgStructureController@getAllInActive');
});

$router->group(['prefix' => 'posStructure'], function () use ($router) {
    $router->post('getAll', 'PosStructureController@getAll');
    $router->post('getOne', 'PosStructureController@getOne');
    $router->post('getFlatHierarchy', 'PosStructureController@getFlatHierarchy');
    $router->post('save', 'PosStructureController@save');
    $router->post('update', 'PosStructureController@update');
    $router->post('delete', 'PosStructureController@delete');
    $router->post('getParent', 'PosStructureController@getParent');
    $router->post('addRoot', 'PosStructureController@addRoot');
    $router->post('addParent', 'PosStructureController@addParent');
    $router->post('addSibling', 'PosStructureController@addSibling');
    $router->post('addChild', 'PosStructureController@addChild');
    $router->post('replaceNode', 'PosStructureController@replaceNode');
    $router->post('switchNode', 'PosStructureController@switchNode');
    $router->post('removeNode', 'PosStructureController@removeNode');
    $router->post('search', 'PosStructureController@search');
});

$router->group(['prefix' => 'person'], function () use ($router) {
    $router->post('getAll', 'PersonController@getAll');
    $router->post('getAllCandidate', 'PersonController@getAllCandidate');
    $router->post('getAllByPosition', 'PersonController@getAllByPosition');
    $router->post('getAllByUnit', 'PersonController@getAllByUnit');
    $router->post('getAllStructureBelow', 'PersonController@getAllStructureBelow');
    $router->post('getAllByUnitWoHead', 'PersonController@getAllByUnitWoHead');
    $router->post('getAllActiveEmployees', 'PersonController@getAllActiveEmployees');
    $router->post('getOne', 'PersonController@getOne');
    $router->post('getOneEmployee', 'PersonController@getOneEmployee');
    $router->post('getMany', 'PersonController@getMany');
    $router->post('getBasicInfo', 'PersonController@getBasicInfo');
    $router->post('getHistory', 'PersonController@getHistory');
    $router->post('getHeadOfUnit', 'PersonController@getHeadOfUnit');
    $router->post('save', 'PersonController@save');
    $router->post('update', 'PersonController@update');
    $router->post('updateFromEss', 'PersonController@updateFromEss');
    $router->post('delete', 'PersonController@delete');
    $router->post('slov', 'PersonController@getSLov');
    $router->post('getLov', 'PersonController@getLov');
    $router->post('search', 'PersonController@search');
    $router->post('searchCustom', 'PersonController@searchCustom');
    $router->post('advancedSearch', 'PersonController@advancedSearch');
    $router->post('advancedSearchActiveEmployee', 'PersonController@advancedSearchActiveEmployee');
    $router->post('getHierarchy', 'PersonController@getHierarchy');
    $router->post('getDirectSubordinates', 'PersonController@getDirectSubordinates');
    $router->post('getDefaultSuperior', 'PersonController@getDefaultSuperior');
    $router->post('getAllByDateExpiring', 'PersonController@getAllByDateExpiring');
    $router->post('getAllByBirth', 'PersonController@getAllByBirth');
    $router->post('resign', 'PersonController@resign');
    $router->post('getAllByDateExpired', 'PersonController@getAllByDateExpired');
});

$router->group(['prefix' => 'personWorkExp'], function () use ($router) {
    $router->post('getAll', 'PersonWorkExpController@getAll');
    $router->post('getOne', 'PersonWorkExpController@getOne');
    $router->post('save', 'PersonWorkExpController@save');
    $router->post('update', 'PersonWorkExpController@update');
    $router->post('delete', 'PersonWorkExpController@delete');
});

$router->group(['prefix' => 'personReference'], function () use ($router) {
    $router->post('getAll', 'PersonReferenceController@getAll');
    $router->post('getOne', 'PersonReferenceController@getOne');
    $router->post('save', 'PersonReferenceController@save');
    $router->post('update', 'PersonReferenceController@update');
    $router->post('delete', 'PersonReferenceController@delete');
});

$router->group(['prefix' => 'personOrganization'], function () use ($router) {
    $router->post('getAll', 'PersonOrganizationController@getAll');
    $router->post('getOne', 'PersonOrganizationController@getOne');
    $router->post('save', 'PersonOrganizationController@save');
    $router->post('update', 'PersonOrganizationController@update');
    $router->post('delete', 'PersonOrganizationController@delete');
});

$router->group(['prefix' => 'personLanguage'], function () use ($router) {
    $router->post('getAll', 'PersonLanguageController@getAll');
    $router->post('getOne', 'PersonLanguageController@getOne');
    $router->post('save', 'PersonLanguageController@save');
    $router->post('update', 'PersonLanguageController@update');
    $router->post('delete', 'PersonLanguageController@delete');
});

$router->group(['prefix' => 'personMembership'], function () use ($router) {
    $router->post('getAll', 'PersonMembershipController@getAll');
    $router->post('getOne', 'PersonMembershipController@getOne');
    $router->post('save', 'PersonMembershipController@save');
    $router->post('update', 'PersonMembershipController@update');
    $router->post('delete', 'PersonMembershipController@delete');
});

$router->group(['prefix' => 'personEducation'], function () use ($router) {
    $router->post('getAll', 'PersonEducationController@getAll');
    $router->post('getOne', 'PersonEducationController@getOne');
    $router->post('save', 'PersonEducationController@save');
    $router->post('update', 'PersonEducationController@update');
    $router->post('delete', 'PersonEducationController@delete');
});

$router->group(['prefix' => 'personFamily'], function () use ($router) {
    $router->post('getAll', 'PersonFamilyController@getAll');
    $router->post('getOne', 'PersonFamilyController@getOne');
    $router->post('save', 'PersonFamilyController@save');
    $router->post('update', 'PersonFamilyController@update');
    $router->post('saveEss', 'PersonFamilyController@saveEss');
    $router->post('updateEss', 'PersonFamilyController@updateEss');
    $router->post('updateEmergencyContact', 'PersonFamilyController@updateEmergencyContact');
    $router->post('delete', 'PersonFamilyController@delete');
    $router->post('search', 'PersonFamilyController@search');
    $router->post('lov', 'PersonFamilyController@getLov');
    $router->post('lovBenefitFamily', 'PersonFamilyController@getLovCustomBenefit');
});

$router->group(['prefix' => 'personAddress'], function () use ($router) {
    $router->post('getAll', 'PersonAddressController@getAll');
    $router->post('getOne', 'PersonAddressController@getOne');
    $router->post('save', 'PersonAddressController@save');
    $router->post('update', 'PersonAddressController@update');
    $router->post('saveEss', 'PersonAddressController@saveEss');
    $router->post('updateEss', 'PersonAddressController@updateEss');
    $router->post('delete', 'PersonAddressController@delete');
});

$router->group(['prefix' => 'personDocument'], function () use ($router) {
    $router->post('getAll', 'PersonDocumentController@getAll');
    $router->post('getAllByFlag', 'PersonDocumentController@getAllByFlag');
    $router->post('getOne', 'PersonDocumentController@getOne');
    $router->post('save', 'PersonDocumentController@save');
    $router->post('update', 'PersonDocumentController@update');
    $router->post('saveEss', 'PersonDocumentController@saveEss');
    $router->post('updateEss', 'PersonDocumentController@updateEss');
    $router->post('delete', 'PersonDocumentController@delete');
    $router->post('deleteFile', 'PersonDocumentController@deleteFile');
});

$router->group(['prefix' => 'personExtTraining'], function () use ($router) {
    $router->post('getAll', 'PersonExtTrainingController@getAll');
    $router->post('getOne', 'PersonExtTrainingController@getOne');
    $router->post('save', 'PersonExtTrainingController@save');
    $router->post('update', 'PersonExtTrainingController@update');
    $router->post('saveEss', 'PersonExtTrainingController@saveEss');
    $router->post('updateEss', 'PersonExtTrainingController@updateEss');
    $router->post('delete', 'PersonExtTrainingController@delete');
});

$router->group(['prefix' => 'asset'], function () use ($router) {
    $router->post('getAll', 'AssetController@getAll');
    $router->post('getAllActive', 'AssetController@getAllActive');
    $router->post('getAllInactive', 'AssetController@getAllInactive');
    $router->post('getOne', 'AssetController@getOne');
    $router->post('save', 'AssetController@save');
    $router->post('update', 'AssetController@update');
    $router->post('delete', 'AssetController@delete');
    $router->post('lov', 'AssetController@getLov');
});

$router->group(['prefix' => 'personAsset'], function () use ($router) {
    $router->post('getAll', 'PersonAssetController@getAll');
    $router->post('getAllReceipt', 'PersonAssetController@getAllReceipt');
    $router->post('getAllNotReturned', 'PersonAssetController@getAllNotReturned');
    $router->post('getAllNearEndDate', 'PersonAssetController@getAllNearEndDate');
    $router->post('getOne', 'PersonAssetController@getOne');
    $router->post('save', 'PersonAssetController@save');
    $router->post('update', 'PersonAssetController@update');
    $router->post('delete', 'PersonAssetController@delete');
});

$router->group(['prefix' => 'reward'], function () use ($router) {
    $router->post('getAll', 'RewardController@getAll');
    $router->post('getOne', 'RewardController@getOne');
    $router->post('save', 'RewardController@save');
    $router->post('update', 'RewardController@update');
    $router->post('delete', 'RewardController@delete');
    $router->post('lov', 'RewardController@getLov');
    $router->post('getAllActive', 'RewardController@getAllActive');
    $router->post('getAllInActive', 'RewardController@getAllInActive');
});

$router->group(['prefix' => 'personReward'], function () use ($router) {
    $router->post('getAll', 'PersonRewardController@getAll');
    $router->post('getOne', 'PersonRewardController@getOne');
    $router->post('save', 'PersonRewardController@save');
    $router->post('update', 'PersonRewardController@update');
    $router->post('delete', 'PersonRewardController@delete');
});

$router->group(['prefix' => 'assignment'], function () use ($router) {
    $router->post('getAll', 'AssignmentController@getAll');
    $router->post('getAllTransactions', 'AssignmentController@getAllTransactions');
    $router->post('getLov', 'AssignmentController@getLov');
    $router->post('getOne', 'AssignmentController@getOne');
    $router->post('getOneTransaction', 'AssignmentController@getOneTransaction');
    $router->post('getFirstAssignment', 'AssignmentController@getFirstAssignment');
    $router->post('getLastPrimary', 'AssignmentController@getLastPrimary');
    $router->post('getHistoryEmployeeBenefit', 'AssignmentController@getHistoryEmployeeBenefit');
    $router->post('save', 'AssignmentController@save');
    $router->post('update', 'AssignmentController@update');
    $router->post('fix', 'AssignmentController@fix');
    $router->post('terminate', 'AssignmentController@terminate');
    $router->post('cancelTermination', 'AssignmentController@cancelTermination');
    $router->post('approve', 'AssignmentController@approve');
    $router->post('checkEmployeeId', 'AssignmentController@checkEmployeeId');
    $router->post('checkPositionVacant', 'AssignmentController@checkPositionVacant');
    $router->post('getPercentFitByPositionCode', 'AssignmentController@getPercentFitByPositionCode');
});

$router->group(['prefix' => 'assignmentReason'], function () use ($router) {
    $router->post('getAll', 'AssignmentReasonController@getAll');
    $router->post('getOne', 'AssignmentReasonController@getOne');
    $router->post('save', 'AssignmentReasonController@save');
    $router->post('update', 'AssignmentReasonController@update');
    $router->post('delete', 'AssignmentReasonController@delete');
    $router->post('lov', 'AssignmentReasonController@getLov');
});

$router->group(['prefix' => 'employeeStatus'], function () use ($router) {
    $router->post('getAll', 'EmployeeStatusController@getAll');
    $router->post('getOne', 'EmployeeStatusController@getOne');
    $router->post('save', 'EmployeeStatusController@save');
    $router->post('update', 'EmployeeStatusController@update');
    $router->post('delete', 'EmployeeStatusController@delete');
    $router->post('lov', 'EmployeeStatusController@getLov');
});

$router->group(['prefix' => 'autonumber'], function () use ($router) {
    $router->post('getAll', 'AutonumberController@getAll');
    $router->post('getOne', 'AutonumberController@getOne');
    $router->post('save', 'AutonumberController@save');
    $router->post('update', 'AutonumberController@update');
    $router->post('updateLastSequence', 'AutonumberController@updateLastSequence');
    $router->post('delete', 'AutonumberController@delete');
    $router->post('lov', 'AutonumberController@getLov');
});

$router->group(['prefix' => 'numberFormat'], function () use ($router) {
    $router->post('getAll', 'NumberFormatController@getAll');
    $router->post('getOne', 'NumberFormatController@getOne');
    $router->post('save', 'NumberFormatController@save');
    $router->post('update', 'NumberFormatController@update');
    $router->post('delete', 'NumberFormatController@delete');
});

$router->group(['prefix' => 'employeeId'], function () use ($router) {
    $router->post('getEmployeeId', 'EmployeeIdController@getEmployeeId');
});

$router->group(['prefix' => 'customField'], function () use ($router) {
    $router->post('getAll', 'CustomFieldController@getAll');
    $router->post('getAllByModule', 'CustomFieldController@getAllByModule');
    $router->post('getOne', 'CustomFieldController@getOne');
    $router->post('save', 'CustomFieldController@save');
    $router->post('update', 'CustomFieldController@update');
});

$router->group(['prefix' => 'migrationTool'], function () use ($router) {
    $router->post('getAllMtModule', 'MigrationToolController@getAllMtModule');
    $router->post('getAllListCleanse', 'MigrationToolController@getAllListCleanse');
    $router->post('generateTemplate', 'MigrationToolController@generateTemplate');
    $router->post('lovModuleAttributes', 'MigrationToolController@lovModuleAttributes');
    $router->post('getModuleAttributes', 'MigrationToolController@getModuleAttributes');
    $router->post('getErrorValue', 'MigrationToolController@getErrorValue');
    $router->post('importMigrationData', 'MigrationToolController@importMigrationData');
    $router->post('deleteAllTempRecord', 'MigrationToolController@deleteAllTempRecord');
    $router->post('deleteAllTempRecordWithUserId', 'MigrationToolController@deleteAllTempRecordWithUserId');
    $router->post('updateCleansing', 'MigrationToolController@updateCleansing');
    $router->post('updateAttachment', 'MigrationToolController@updateAttachment');
    $router->post('moveTemporaryToTable', 'MigrationToolController@moveTemporaryToTable');
    $router->post('searchBatches', 'MigrationToolController@searchBatches');
});

$router->group(['prefix' => 'customObject'], function () use ($router) {
    $router->post('getAll', 'CustomObjectController@getAll');
    $router->post('getAllByLovCusobj', 'CustomObjectController@getAllByLovCusobj');
    $router->post('getOne', 'CustomObjectController@getOne');
    $router->post('save', 'CustomObjectController@save');
    $router->post('update', 'CustomObjectController@update');
    $router->post('delete', 'CustomObjectController@delete');
    $router->post('getPersonIdForView', 'CustomObjectController@getPersonIdForView');
});

$router->group(['prefix' => 'customObjectField'], function () use ($router) {
    $router->post('getAllField', 'CustomObjectFieldController@getAllField');
});

$router->group(['prefix' => 'personCustomObject'], function () use ($router) {
    $router->post('getAll', 'PersonCustomObjectController@getAll');
    $router->post('getAllItems', 'PersonCustomObjectController@getAllItems');
    $router->post('getOne', 'PersonCustomObjectController@getOne');
    $router->post('getAllFields', 'PersonCustomObjectController@getAllFields');
    $router->post('save', 'PersonCustomObjectController@save');
    $router->post('updateFields', 'PersonCustomObjectController@updateFields');
    $router->post('delete', 'PersonCustomObjectController@delete');
});

$router->group(['prefix' => 'workflow'], function () use ($router) {
    $router->post('getAll', 'WorkflowController@getAll');
    $router->post('getAllUnit', 'WorkflowController@getAllUnit');
    $router->post('getAllEmployee', 'WorkflowController@getAllEmployee');
    $router->post('getAllLocation', 'WorkflowController@getAllLocation');
    $router->post('getAllProject', 'WorkflowController@getAllProject');
    $router->post('getOne', 'WorkflowController@getOne');
    $router->post('update', 'WorkflowController@update');
    $router->post('delete', 'WorkflowController@delete');
});

$router->group(['prefix' => 'worklist'], function () use ($router) {
    $router->post('getWorklist', 'WorklistController@getWorklist');
    $router->post('getWorklistSubordinate', 'WorklistController@getWorklistSubordinate');
    $router->post('countGetWorklistSubordinate', 'WorklistController@countGetWorklistSubordinate');
    $router->post('getWorklistByRequestId', 'WorklistController@getWorklistByRequestId');
    $router->post('getAllByRequestIdAndLovWfty', 'WorklistController@getAllByRequestIdAndLovWfty');
    $router->post('getAllByRequestIdLovWftyAndDesc', 'WorklistController@getAllByRequestIdLovWftyAndDesc');
    $router->post('getOneWorklist', 'WorklistController@getOneWorklist');
    $router->post('generateWorklist', 'WorklistController@generateWorklist');
    $router->post('answerRequest', 'WorklistController@answerRequest');
    $router->post('forwardRequest', 'WorklistController@forwardRequest');
    $router->post('saveWorklist', 'WorklistController@save');
    $router->post('updateStatusWorklist', 'WorklistController@updateStatus');
    $router->post('multipleSaveWorklist', 'WorklistController@multipleSave');
});

$router->group(['prefix' => 'reportParameter'], function () use ($router) {
    $router->post('firstName', 'ReportParameterController@getAllPersonFirstName');
    $router->post('lastName', 'ReportParameterController@getAllPersonLastName');
    $router->post('id', 'ReportParameterController@getAllPersonId');
    $router->post('job', 'ReportParameterController@getAllJobCodeName');
    $router->post('unit', 'ReportParameterController@getAllUnitCodeName');
    $router->post('position', 'ReportParameterController@getAllPositionCodeName');
    $router->post('location', 'ReportParameterController@getAllLocationCodeName');
    $router->post('project', 'ReportParameterController@getAllProjectName');
});

$router->group(['prefix' => 'settingLov'], function () use ($router) {
    $router->post('getAll', 'SettingLovController@getAll');
});

$router->group(['prefix' => 'educationInstitution'], function () use ($router) {
    $router->post('getAll', 'EducationInstitutionController@getAll');
    $router->post('getAllActive', 'EducationInstitutionController@getAllActive');
    $router->post('getAllInactive', 'EducationInstitutionController@getAllInactive');
    $router->post('getOne', 'EducationInstitutionController@getOne');
    $router->post('getHistory', 'EducationInstitutionController@getHistory');
    $router->post('save', 'EducationInstitutionController@save');
    $router->post('update', 'EducationInstitutionController@update');
    $router->post('lov', 'EducationInstitutionController@getLov');
});

$router->group(['prefix' => 'educationSpecialization'], function () use ($router) {
    $router->post('getAll', 'EducationSpecializationController@getAll');
    $router->post('getAllActive', 'EducationSpecializationController@getAllActive');
    $router->post('getAllInactive', 'EducationSpecializationController@getAllInactive');
    $router->post('getOne', 'EducationSpecializationController@getOne');
    $router->post('getHistory', 'EducationSpecializationController@getHistory');
    $router->post('save', 'EducationSpecializationController@save');
    $router->post('update', 'EducationSpecializationController@update');
    $router->post('lov', 'EducationSpecializationController@getLov');
});

$router->group(['prefix' => 'ratingScale'], function () use ($router) {
    $router->post('getAll', 'RatingScaleController@getAll');
    $router->post('getAllActive', 'RatingScaleController@getAllActive');
    $router->post('getAllInactive', 'RatingScaleController@getAllInactive');
    $router->post('getOne', 'RatingScaleController@getOne');
    $router->post('save', 'RatingScaleController@save');
    $router->post('getHistory', 'RatingScaleController@getHistory');
    $router->post('lov', 'RatingScaleController@getLov');
    $router->post('detailLov', 'RatingScaleController@getDetailLov');
    $router->post('checkCondition', 'RatingScaleController@checkCondition');
});

$router->group(['prefix' => 'ratingScaleDetail'], function () use ($router) {
    $router->post('getAllByRatingScaleId', 'RatingScaleDetailController@getAllByRatingScaleId');
});

$router->group(['prefix' => 'credential'], function () use ($router) {
    $router->post('getAll', 'CredentialController@getAll');
    $router->post('getAllActive', 'CredentialController@getAllActive');
    $router->post('getAllInactive', 'CredentialController@getAllInactive');
    $router->post('getAllByCode', 'CredentialController@getAllByCode');
    $router->post('getOne', 'CredentialController@getOne');
    $router->post('save', 'CredentialController@save');
    $router->post('update', 'CredentialController@update');
    $router->post('lov', 'CredentialController@getLov');
});

$router->group(['prefix' => 'provider'], function () use ($router) {
    $router->post('getAll', 'ProviderController@getAll');
    $router->post('getAllActive', 'ProviderController@getAllActive');
    $router->post('getAllInactive', 'ProviderController@getAllInactive');
    $router->post('getOne', 'ProviderController@getOne');
    $router->post('save', 'ProviderController@save');
    $router->post('update', 'ProviderController@update');
    $router->post('lov', 'ProviderController@getLov');
});

$router->group(['prefix' => 'competency'], function () use ($router) {
    $router->post('getAll', 'CompetencyController@getAll');
    $router->post('getAllActive', 'CompetencyController@getAllActive');
    $router->post('getAllInactive', 'CompetencyController@getAllInactive');
    $router->post('getAllByCode', 'CompetencyController@getAllByCode');
    $router->post('getOne', 'CompetencyController@getOne');
    $router->post('getOneById', 'CompetencyController@getOneById');
    $router->post('save', 'CompetencyController@save');
    $router->post('lov', 'CompetencyController@getLov');
});

$router->group(['prefix' => 'competencyModel'], function () use ($router) {
    $router->post('getAll', 'CompetencyModelController@getAll');
    $router->post('getAllActive', 'CompetencyModelController@getAllActive');
    $router->post('getAllInactive', 'CompetencyModelController@getAllInactive');
    $router->post('getAllCompetency', 'CompetencyModelController@getAllCompetency');
    $router->post('getHistory', 'CompetencyModelController@getHistory');
    $router->post('getOne', 'CompetencyModelController@getOne');
    $router->post('update', 'CompetencyModelController@update');
    $router->post('save', 'CompetencyModelController@save');
    $router->post('lov', 'CompetencyModelController@getLov');
    $router->post('getAllCompetencyByModelCode', 'CompetencyModelController@getAllCompetencyByModelCode');
});

$router->group(['prefix' => 'competencyGroup'], function () use ($router) {
    $router->post('getAll', 'CompetencyGroupController@getAll');
    $router->post('getAllActive', 'CompetencyGroupController@getAllActive');
    $router->post('getAllInactive', 'CompetencyGroupController@getAllInactive');
    $router->post('getHistory', 'CompetencyGroupController@getHistory');
    $router->post('getOne', 'CompetencyGroupController@getOne');
    $router->post('update', 'CompetencyGroupController@update');
    $router->post('save', 'CompetencyGroupController@save');
    $router->post('lov', 'CompetencyGroupController@getLov');
});

$router->group(['prefix' => 'personCredential'], function () use ($router) {
    $router->post('getAll', 'PersonCredentialController@getAll');
    $router->post('getOne', 'PersonCredentialController@getOne');
    $router->post('save', 'PersonCredentialController@save');
    $router->post('update', 'PersonCredentialController@update');
    $router->post('getAllInactive', 'PersonCredentialController@getAllInactive');
    $router->post('delete', 'PersonCredentialController@delete');
});

$router->group(['prefix' => 'personCompetencyModel'], function () use ($router) {
    $router->post('getAll', 'PersonCompetencyModelController@getAll');
    $router->post('getHistory', 'PersonCompetencyModelController@getHistory');
    $router->post('getTemporary', 'PersonCompetencyModelController@getTemporaryPersonCompetency');
    $router->post('checkCondition', 'PersonCompetencyModelController@checkConditionDataCompetency');
    $router->post('save', 'PersonCompetencyModelController@save');
});

$router->group(['prefix' => 'lookup'], function () use ($router) {
    $router->post('getAll', 'LookupController@getAll');
    $router->post('getOne', 'LookupController@getOne');
    $router->post('getLov', 'LookupController@getLov');
    $router->post('save', 'LookupController@save');
    $router->post('update', 'LookupController@update');
});

$router->group(['prefix' => 'requestFamilies'], function () use ($router) {
    $router->post('getAll', 'RequestFamiliesController@getAll');
    $router->post('getOne', 'RequestFamiliesController@getOne');
    $router->post('save', 'RequestFamiliesController@save');
    $router->post('update', 'RequestFamiliesController@update');
    $router->post('delete', 'RequestFamiliesController@delete');
    $router->post('checkIfRequestIsPending', 'RequestFamiliesController@checkIfRequestIsPending');
});

$router->group(['prefix' => 'requestAddresses'], function () use ($router) {
    $router->post('getAll', 'RequestAddressesController@getAll');
    $router->post('getOne', 'RequestAddressesController@getOne');
    $router->post('save', 'RequestAddressesController@save');
    $router->post('update', 'RequestAddressesController@update');
    $router->post('delete', 'RequestAddressesController@delete');
    $router->post('checkIfRequestIsPending', 'RequestAddressesController@checkIfRequestIsPending');
});

$router->group(['prefix' => 'requestDocuments'], function () use ($router) {
    $router->post('getAll', 'RequestDocumentsController@getAll');
    $router->post('getOne', 'RequestDocumentsController@getOne');
    $router->post('save', 'RequestDocumentsController@save');
    $router->post('update', 'RequestDocumentsController@update');
    $router->post('delete', 'RequestDocumentsController@delete');
    $router->post('checkIfRequestIsPending', 'RequestDocumentsController@checkIfRequestIsPending');
});

$router->group(['prefix' => 'dashboard'], function () use ($router) {
    $router->post('getAll', 'DashboardController@getAll');
    $router->post('getActiveStructureHierarchies', 'DashboardController@getActiveStructureHierarchies');
    $router->post('getOneForPieChart', 'DashboardController@getOneForPieChart');
});

$router->group(['prefix' => 'reportTemplates'], function () use ($router) {
    $router->post('getAllByCategory', 'ReportTemplatesController@getAllByCategory');
    $router->post('getOne', 'ReportTemplatesController@getOne');
});

$router->group(['prefix' => 'project'], function () use ($router) {
    $router->post('getAll', 'ProjectController@getAll');
    $router->post('getOne', 'ProjectController@getOne');
    $router->post('save', 'ProjectController@save');
    $router->post('update', 'ProjectController@update');
    $router->post('delete', 'ProjectController@delete');
    $router->post('getLov', 'ProjectController@getLov');
});

$router->group(['prefix' => 'employeeProject'], function () use ($router) {
    $router->post('getAll', 'EmployeeProjectController@getAll');
    $router->post('getLov', 'EmployeeProjectController@getLov');
    $router->post('search', 'EmployeeProjectController@search');
    $router->post('getAllByAssignment', 'EmployeeProjectController@getAllByAssignment');
    $router->post('getOne', 'EmployeeProjectController@getOne');
    $router->post('save', 'EmployeeProjectController@save');
    $router->post('update', 'EmployeeProjectController@update');
    $router->post('delete', 'EmployeeProjectController@delete');
    $router->post('searchEmployeeProject', 'EmployeeProjectController@searchEmployeeProject');
});

$router->group(['prefix' => 'customFieldEmployeeProject'], function () use ($router) {
    $router->post('getOne', 'CustomFieldEmployeeProjectController@getOneByEmployeeProjectId');
    $router->post('save', 'CustomFieldEmployeeProjectController@saveAndUpdateCfEmployeeProject');
});

$router->group(['prefix' => 'numberFormatsDocument'], function () use ($router) {
    $router->post('getAssignmentDocument', 'NumberFormatsDocumentController@getAssignmentDocument');
});

/**
 * Route for Widget
 *
 * @uses $router->group
 */
$router->group(['prefix' => 'widget'], function () use ($router) {
    $router->post('getAll', 'WidgetController@getAll');
    $router->post('getAllWidgetType', 'WidgetController@getAllWidgetType');
    $router->post('getOne', 'WidgetController@getOne');
    $router->post('getOneWidgetType', 'WidgetController@getOneWidgetType');
    $router->post('save', 'WidgetController@save');
});

$router->group(['prefix' => 'jobCompetency'], function () use ($router) {
    $router->post('save', 'JobCompetencyController@save');
    $router->post('saveSkill', 'JobCompetencyController@saveSkill');
    $router->post('getAllJobCompetency', 'JobCompetencyController@getAllJobCompetency');
});


$router->group(['prefix' => 'positionCompetency'], function () use ($router) {
    $router->post('save', 'PositionCompetencyController@save');
    $router->post('saveSkill', 'PositionCompetencyController@saveSkill');
    $router->post('getAllByPositionCode', 'PositionCompetencyController@getAllPositionCompetency');
});

$router->group(['prefix' => 'requestPersonAddresses'], function () use ($router) {
    $router->post('getAll', 'RequestPersonAddressesController@getAll');
    $router->post('getOne', 'RequestPersonAddressesController@getOne');
    $router->post('save', 'RequestPersonAddressesController@save');
});

$router->group(['prefix' => 'requestPersonFamilies'], function () use ($router) {
    $router->post('getAll', 'RequestPersonFamiliesController@getAll');
    $router->post('getOne', 'RequestPersonFamiliesController@getOne');
    $router->post('save', 'RequestPersonFamiliesController@save');
});

$router->group(['prefix' => 'requestPersonDocuments'], function () use ($router) {
    $router->post('getAll', 'RequestPersonDocumentsController@getAll');
    $router->post('getOne', 'RequestPersonDocumentsController@getOne');
    $router->post('save', 'RequestPersonDocumentsController@save');
});

$router->group(['prefix' => 'requestPersonSocmeds'], function () use ($router) {
    $router->post('getAll', 'RequestPersonSocmedsController@getAll');
    $router->post('getOne', 'RequestPersonSocmedsController@getOne');
    $router->post('save', 'RequestPersonSocmedsController@save');
});

$router->group(['prefix' => 'requestPersons'], function () use ($router) {
    $router->post('getAll', 'RequestPersonsController@getAll');
    $router->post('getOne', 'RequestPersonsController@getOne');
    $router->post('save', 'RequestPersonsController@save');
});

$router->group(['prefix' => 'profileRequests'], function () use ($router) {
    $router->post('getAll', 'ProfileRequestController@getAll');
    $router->post('getOne', 'ProfileRequestController@getOne');
    $router->post('save', 'ProfileRequestController@save');
    $router->post('update', 'ProfileRequestController@update');
    $router->post('checkIfRequestIsPending', 'ProfileRequestController@checkIfRequestIsPending');
});

$router->group(['prefix' => 'readinessLevel'], function () use ($router) {
    $router->post('getAll', 'ReadinessLevelController@getAll');
    $router->post('save', 'ReadinessLevelController@save');
    $router->post('update', 'ReadinessLevelController@update');
    $router->post('lov', 'ReadinessLevelController@lov');
});

$router->group(['prefix' => 'successionPool'], function () use ($router) {
    $router->post('getAll', 'SuccessionPoolController@getAll');
    $router->post('getOne', 'SuccessionPoolController@getOne');
    $router->post('getSuccessorCandidate', 'SuccessionPoolController@getSuccessorCandidate');
    $router->post('save', 'SuccessionPoolController@save');
    $router->post('saveSuccessor', 'SuccessionPoolController@saveSuccessor');
    $router->post('update', 'SuccessionPoolController@update');
    $router->post('deleteSuccessor', 'SuccessionPoolController@deleteSuccessor');

});

$router->group(['prefix' => 'performanceLevel'], function () use ($router) {
    $router->post('getOne', 'PerformanceLevelController@getOne');
    $router->post('save', 'PerformanceLevelController@save');
});

$router->group(['prefix' => 'potentialLevel'], function () use ($router) {
    $router->post('getOne', 'PotentialLevelController@getOne');
    $router->post('save', 'PotentialLevelController@save');
});


$router->group(['prefix' => 'potentialPerformanceMatrix'], function () use ($router) {
    $router->post('getAll', 'PotentialPerformanceMatrixController@getAll');
    $router->post('save', 'PotentialPerformanceMatrixController@save');
});

$router->group(['prefix' => 'talentPool'], function () use ($router) {
    $router->post('getAll', 'TalentPoolController@getAll');
    $router->post('getOne', 'TalentPoolController@getOne');
    $router->post('save', 'TalentPoolController@save');
    $router->post('update', 'TalentPoolController@update');
    $router->post('saveNominee', 'TalentPoolNomineeController@save');
    $router->post('deleteNominee', 'TalentPoo #regionlNomineeController@delete');
    $router->post('updateNominee', 'TalentPoolNomineeController@update');
});


$router->group(['prefix' => 'careerPath'], function () use ($router) {
    $router->post('save', 'CareerPathController@save');
    $router->post('getAll', 'CareerPathController@getAll');
    $router->post('getOne', 'CareerPathController@getOne');
    $router->post('delete', 'CareerPathController@delete');
    $router->post('update', 'CareerPathController@update');
});
