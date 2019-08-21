<?php

namespace App\Http\Controllers;

use App\Business\Dao\CompanyBankAccDao;
use App\Business\Dao\CompanyDao;
use App\Business\Dao\Payroll\PayrollPeriodCurrencyDao;
use App\Business\Model\Requester;
use App\Business\Model\AppResponse;
use App\Exceptions\AppException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\SyncJob;
use App\Jobs\NotifyJob;
use League\Csv\Reader;
use League\Csv\Statement;
use Carbon\Carbon;

/**
 * Class for handling company process
 */
class CompanyController extends Controller
{
    public function __construct(
        Requester $requester,
        CompanyDao $companyDao,
        CompanyBankAccDao $companyBankAccDao,
        PayrollPeriodCurrencyDao $payrollPeriodCurrencyDao
    ) {
        parent::__construct();

        $this->requester = $requester;
        $this->companyDao = $companyDao;
        $this->companyBankAccDao = $companyBankAccDao;
        $this->payrollPeriodCurrencyDao = $payrollPeriodCurrencyDao;
    }

    /**
     * Get One company in one tenant
     * @param request
     */
    public function getOne(Request $request)
    {
        $this->validate($request, ['id' => 'required|integer|exists:companies,id']);
        $data = array();
        $company = $this->companyDao->getOne($request->id);
        $companySetting = $this->companyDao->getAllCompanySettings($request->id);
        foreach ($companySetting as $setting) {
            $isDisable = false;

            if ($setting->typeCode == 'CURR') {
                $checkCurr = $this->payrollPeriodCurrencyDao->checkIfCompanyCurrencyIsUsed($setting->lovKeyData);
                if ($checkCurr) {
                    $isDisable = true;
                }
            }

            array_push($data, [
                'typeCode' => $setting->typeCode,
                'typeName' => $setting->typeName,
                'lovKeyData' => $setting->lovKeyData,
                'fixValue' => $setting->fixValue,
                'vtype' => $setting->vtype,
                'isDisable' => $isDisable
            ]);
        }

        $company->settings = $data;

        $resp = new AppResponse($company, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get One company in one tenant
     * @param request
     */
    public function getSortOrder()
    {
        // $this->validate($request, ['id' => 'required|integer|exists:companies,id']);
        $data = array();
        $company = $this->companyDao->getSortOrder();

        $data = $company->sortOrder + 1;
        $resp = new AppResponse($data, trans('messages.dataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save company to DB
     * @param Request $request
     * @return AppResponse
     */
    public function save(Request $request)
    {
        $data = array();
        $this->checkCompanyRequest($request);

        DB::transaction(function () use (&$request, &$data) {
            $company = $this->constructCompany($request);
            $company['id'] = $this->generateCompanyId();
            if ($company['id'] === -1) {
                throw new AppException(trans('messages.saveFail'));
            }
            if ($request->upload == true) {
                $fileUris = $this->getFileUris($request, $data, $company['id']);
                if (!empty($fileUris)) {
                    $company['file_logo'] = $fileUris['PP'];
                }
            }

            $getCompany = $this->companyDao->getOneBySortOrder($request->sortOrder);
            if ($getCompany) {
                $lastSortOrder = $this->companyDao->getSortOrder();
                $sortOrder =
                    [
                        "sort_order" => $lastSortOrder->sortOrder + 1
                    ];
                $this->companyDao->update(
                    $getCompany->id,
                    $sortOrder
                );
            }



            $this->companyDao->save($company);
            $data['id'] = $company['id'];

            // Save company settings.
            //            $settings = [];
            //            foreach ($request->settings as $setting) {
            //                $this->companyDao->saveCompanySetting([
            //                    'tenant_id' => $this->requester->getTenantId(),
            //                    'company_id' => $company['id'],
            //                    'setting_type_code' => $setting['typeCode'],
            //                    'setting_lov_key_data' => $setting['lovKeyData']
            //                ]);
            //            }

            $this->seedCompanyData($company['id']);
        });

        $resp = new AppResponse($data, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Save company setting to DB
     * @param Request $request
     * @return AppResponse
     */
    public function saveCompanySetting(Request $request)
    {
        $data = array();
        $this->validate($request, [
            'companyId' => 'required|integer',
            'settingTypeCode' => 'required|string',
            'settingLovKeyData' => 'required|string'
        ]);

        DB::transaction(function () use (&$request, &$data) {
            $companySetting = $this->constructCompanySetting($request);
            $this->companyDao->saveCompanySetting($companySetting);
        });

        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }

    /**
     * Update company to DB
     * @param Request $request
     * @return AppResponse
     */
    public function update(Request $request)
    {
        $data = array();
        $this->checkCompanyRequest($request);
        $this->validate($request, ['id' => 'required|integer']);

        $company = [];
        $shouldSync = false;

        DB::transaction(function () use (&$request, &$data, &$company, &$shouldSync) {
            $company = $this->constructCompany($request);

            if ($request->upload) {
                $getCompany = $this->companyDao->getOne($request->id);
                if ($getCompany->fileLogo) {
                    $this->deleteFile($request, $getCompany->fileLogo);
                    // if (!$this->deleteFile($request, $getCompany->fileLogo)) {
                    //     throw new AppException(trans('messages.updateFail'));
                    // }
                }
                $fileUris = $this->getFileUris($request, $data, $request->id);
                if (!empty($fileUris)) {
                    $company['file_logo'] = $fileUris['PP'];
                }
            }

            $oldCompany = $this->companyDao->getOne(
                $request->id
            );
            $shouldSync = ($oldCompany->name !== $company['name']);
            $companies = $this->companyDao->getAll($this->requester->getTenantId());
            if (count($companies) > 0) {
                $currentCompany = $this->companyDao->getOne($request->id);
                for ($i = 0; $i < count($companies); $i++) {
                    if ($companies[$i]->sortOrder === $request->sortOrder) {
                        $sortOrder =
                            [
                                "sort_order" => $currentCompany->sortOrder
                            ];
                        $this->companyDao->update(
                            $companies[$i]->id,
                            $sortOrder
                        );
                    }
                }
            }
            $this->companyDao->update(
                $request->id,
                $company
            );

            if (!$request->isNew) {
                // Save new company settings.
                $this->companyDao->deleteCompanySettings($request->id);

                foreach ($request->settings as $setting) {
                    $this->companyDao->saveCompanySetting([
                        'tenant_id' => $this->requester->getTenantId(),
                        'company_id' => $request->id,
                        'setting_type_code' => $setting['typeCode'],
                        'setting_lov_key_data' => $setting['lovKeyData'],
                        'fix_value' => $setting['fixValue']
                    ]);
                }
            }
        });

        if ($shouldSync) {
            dispatch(new SyncJob('core', 'um', [
                'entity' => 'company',
                'company_id' => $request->id,
                'company_name' => $company['name']
            ]));
        }

        $resp = new AppResponse($data, trans('messages.dataUpdated'));
        return $this->renderResponse($resp);
    }

    /**
     * Save company setting to DB
     * @param Request $request
     * @return AppResponse
     */
    public function updateCompanySetting(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'required|integer',
            'settingTypeCode' => 'required|string',
            'settingLovKeyData' => 'required|string'
        ]);

        DB::transaction(function () use (&$request, &$data) {
            $companySetting = [
                "setting_lov_key_data" => $request->settingLovKeyData
            ];
            $this->companyDao->updateCompanySetting($request->companyId, $request->settingTypeCode, $companySetting);
        });

        $resp = new AppResponse(null, trans('messages.dataSaved'));
        return $this->renderResponse($resp);
    }


    /**
     * Get multiple companies' settings.
     * @param Request $request
     * @return AppResponse
     */
    public function getSettings(Request $request)
    {
        $this->validate($request, [
            'companyIds' => 'array'
        ]);

        $data = array();
        $this->companyDao->findSetting(
            $this->requester->getTenantId(),
            $request->companyIds
        )->each(function ($item, $key) use (&$data, &$isDisable) {
            if (!array_key_exists($item->companyId, $data)) {
                $data[$item->companyId] = [
                    'companyId' => $item->companyId,
                    'companyName' => $item->companyName,
                    'setting' => [$item->lovTypeCode => $item->lovKeyData]
                ];
            } else {
                $data[$item->companyId]['setting'][$item->lovTypeCode] = $item->lovKeyData;
            }
            return true;
        });
        $data = array_values($data);

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Get companies' settings.
     * @param Request $request
     * @return AppResponse
     */
    public function getSetting(Request $request)
    {
        $this->validate($request, [
            'companyId' => 'integer|required'
        ]);

        $data = $this->companyDao->getSetting(
            $this->requester->getTenantId(),
            $request->companyId
        );

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getMany(Request $request)
    {
        $this->validate($request, [
            'companyIds' => 'array'
        ]);

        $data = $this->companyDao->getCompanyByManyId($this->requester->getTenantId(), $request->companyIds);

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    public function getCompanyBankAcc(Request $request)
    {
        $this->validate($request, [
            'companyIds' => 'array'
        ]);

        $data = $this->companyBankAccDao->getAll($this->requester->getTenantId(), $request->companyIds);

        $resp = new AppResponse($data, trans('messages.allDataRetrieved'));
        return $this->renderResponse($resp);
    }

    /**
     * Validate company save request.
     * @param Request $request
     * @return Request
     */
    private function checkCompanyRequest(Request $request)
    {
        $this->validate($request, [
            'data' => 'required',
            'upload' => 'required|boolean'
        ]);

        if ($request->upload == true) {
            $this->validate($request, [
                'docTypes' => 'required|array|min:1',
                'fileContents' => 'required|array|min:1',
                'ref' => 'required|string|max:255'
            ]);
        }

        $reqData = (array) json_decode($request->data);
        if ($reqData['settings']) {
            $reqData['settings'] = array_map(function ($setting) {
                return (array) $setting;
            }, $reqData['settings']);
        }

        if (null === $reqData) {
            throw new AppException(trans('messages.jsonInvalid'));
        }
        $request->merge($reqData);

        $this->validate($request, [
            'effBegin' => 'required|date',
            'effEnd' => 'required|date|after_or_equal:effBegin',
            'name' => 'required|max:50',
            'description' => 'present',
            'companyTaxNumber' => 'required|max:50',
            'locationCode' => 'present'
        ]);

        return $request;
    }

    /**
     * Validate company update request.
     * @param Request $request
     * @return Request
     */
    //    private function checkCompanyRequest(Request $request)
    //    {
    //        $this->validate($request, [
    //            'data' => 'required',
    //            'upload' => 'required|boolean'
    //        ]);
    //
    //        if ($request->upload == true) {
    //            $this->validate($request, [
    //                'docTypes' => 'required|array|min:1',
    //                'fileContents' => 'required|array|min:1',
    //                'ref' => 'required|string|max:255'
    //            ]);
    //        }
    //
    //        $reqData = (array) json_decode($request->data);
    //        $reqData['settings'] = array_map(function ($setting) {
    //            return (array) $setting;
    //        }, $reqData['settings']);
    //
    //        if (null === $reqData) {
    //            throw new AppException(trans('messages.jsonInvalid'));
    //        }
    //        $request->merge($reqData);
    //
    //        $this->validate($request, [
    //            'effBegin' => 'required|date',
    //            'effEnd' => 'required|date|after_or_equal:effBegin',
    //            'name' => 'required|max:50',
    //            'description' => 'present',
    //            'companyTaxNumber' => 'required|max:50',
    //            'locationCode' => 'present',
    //            'settings' => 'required|array|min:1',
    //            'settings.*.typeCode' => 'required|string|exists:setting_types,code',
    //            'settings.*.lovKeyData' => 'present|string|nullable|exists:setting_lovs,key_data'
    //        ]);
    //
    //        return $request;
    //    }

    /**
     * Generate random number for company id
     * @return int
     */
    private function generateCompanyId()
    {
        $x = 1;

        do {
            $randomNumber = rand(1000000000, 2000000000);
            $company = $this->companyDao->getCompanyById($randomNumber);
            if (null === $company) {
                return $randomNumber;
            }
            $x++;
        } while ($x <= 100);
        return -1;
    }

    /**
     * Construct a company object for storage (array).
     * @param Request $request
     * @return array
     */
    private function constructCompany(Request $request)
    {
        $company = [
            "tenant_id" => $this->requester->getTenantId(),
            "eff_begin" => $request->effBegin,
            "eff_end" => $request->effEnd,
            "name" => $request->name,
            "description" => $request->description,
            "company_tax_number" => $request->companyTaxNumber,
            "tax_withholder_number" => $request->taxWithholderNumber,
            "tax_withholder_name" => $request->taxWithholderName,
            "location_code" => $request->locationCode,
            "sort_order" => $request->sortOrder
        ];
        return $company;
    }

    /**
     * Construct a company object for storage (array).
     * @param Request $request
     * @return array
     */
    private function constructCompanySetting(Request $request)
    {
        $company = [
            "tenant_id" => $this->requester->getTenantId(),
            "company_id" => $request->companyId,
            "setting_type_code" => $request->settingTypeCode,
            "setting_lov_key_data" => $request->settingLovKeyData
        ];
        return $company;
    }

    /**
     * Get all uploaded file URIs from File service.
     * @param Request $request
     * @param array $data
     * @return array
     */
    private function getFileUris(Request $request, array &$data, $companyId = null)
    {
        $guzzle = new \GuzzleHttp\Client();
        try {
            $response = $guzzle->request(
                'POST',
                env('CDN_SERVICE_SAVE_API'),
                [
                    'multipart' => $this->constructPayload($request, $companyId),
                    'headers' => [
                        'Authorization' => $request->headers->get('authorization'),
                        'Origin' => $request->headers->get('origin')
                    ]
                ]
            );
            $body = json_decode($response->getBody()->getContents());
            if ($body->status === 200) {
                $data['file'] = (array) $body;
                return (array) $body->data->fileUris;
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $data['file'] = [];
            if ($e->hasResponse()) {
                $body = $e->getResponse()->getBody();
                $data['file'] = (array) json_decode($body->getContents());
            } else {
                $data['file']['status'] = 500;
                $data['file']['message'] = $e->getMessage();
                $data['file']['data'] = null;
            }
        }

        return [];
    }

    /**
     * Construct a multipart payload for uploading file to File service.
     * @param Request $request
     * @return array
     */
    private function constructPayload(Request $request, $companyId = null)
    {
        $payload = array([
            'name' => 'data',
            'contents' => $request->data
        ], [
            'name' => 'ref',
            'contents' => $request->ref
        ], [
            'name' => 'companyId',
            'contents' => $companyId
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

    private function deleteFile($request, $fileUri)
    {
        $guzzle = new \GuzzleHttp\Client();
        try {
            $response = $guzzle->request('POST', env('CDN_SERVICE_DELETE_API'), [
                'form_params' => ['fileUri' => $fileUri, 'companyId' => $request->id],
                'headers' => [
                    'Authorization' => $request->headers->get('authorization'),
                    'Origin' => $request->headers->get('origin')
                ]
            ]);
            $body = json_decode($response->getBody()->getContents());
            return $body->status === 200;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            info($e->getMessage());
            return false;
        }
        // should never reach here
    }

    private function seedCompanyData(int $companyId)
    {
        // Read all file paths having 'company' as a prefix.
        $filePaths = glob(base_path() . '/database/seeds/csvs/company-*.csv', GLOB_ERR);
        if (!$filePaths) {
            throw new AppException(trans('messages.seedCompanyDataFailed'));
        }

        foreach ($filePaths as $filePath) {
            // Retrieve the table name from file path.
            $fileName = basename($filePath, '.csv');
            $fileNameParts = explode('-', $fileName);

            // Either prefix-connection-table_name or prefix-table_name
            if (count($fileNameParts) > 2) {
                $fileNameLastParts = array_slice($fileNameParts, 2);
                $connection = $fileNameParts[1];
            } else {
                $fileNameLastParts = array_slice($fileNameParts, 1);
                $connection = null;
            }

            $tableName = implode('', $fileNameLastParts);

            if (count($tableName) === 0) {
                throw new AppException(trans('messages.seedCompanyDataFailed'));
            }

            // Read csv file.
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);

            // Process records to be seeded for this company.
            $records = (new Statement())->process($csv);
            $rows = [];
            foreach ($records as $record) {
                $record['tenant_id'] = $this->requester->getTenantId();
                $record['company_id'] = $companyId;
                $record['created_at'] = Carbon::now();

                // If a column has an empty string value, then consider it as NULL.
                foreach ($record as $column => $val) {
                    if ($record[$column] == '') {
                        $record[$column] = null;
                    }
                }
                array_push($rows, $record);
            }

            // Seed data!
            if ($connection) {
                // TODO: Is there a better way to avoid testing failures?
                if (env('APP_ENV') === 'testing') {
                    continue;
                }
                info('tablename', (array) $tableName);
                info('rows', (array) $rows);
                DB::connection($connection)->table($tableName)->insert($rows);
            } else {

                DB::table($tableName)->insert($rows);
            }
        }
    }
}
