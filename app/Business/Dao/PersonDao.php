<?php

namespace App\Business\Dao;

use App\Business\Model\Requester;
use App\Business\Helper\SearchQueryBuilder;
use function GuzzleHttp\Psr7\str;
use Illuminate\Support\Facades\DB, Log;
use Carbon\Carbon;

class PersonDao
{
    public function __construct(Requester $requester)
    {
        $this->requester = $requester;
        $this->fieldMap = config('constant.fieldMap');
    }

    /**
     * Get all Persons
     */
    public function getAll()
    {
        $params = [
            'tenantId' => $this->requester->getTenantId(),
            'companyId' => $this->requester->getCompanyId(),
            'sysdate' => Carbon::now()
        ];
        $query = "SELECT DISTINCT persons.id,
                persons.first_name as \"firstName\",
                persons.last_name as \"lastName\",
                persons.email,
                assignments.employee_id as \"employeeId\",
                positions.name as \"positionName\",
                units.name as \"unitName\",
                jobs.name as \"jobName\",
                locations.name as \"locationName\"
                from persons
                JOIN assignments
                    on assignments.person_id=persons.id
                    AND assignments.is_primary=true
                    AND assignments.tenant_id = :tenantId
                    AND assignments.company_id = :companyId
                    AND :sysdate between assignments.eff_begin and assignments.eff_end
                    AND assignments.lov_asta = 'ACT'
                JOIN positions
                    on positions.code=assignments.position_code
                    AND positions.tenant_id = :tenantId
                    AND positions.company_id = :companyId
                JOIN units
                    on units.code=assignments.unit_code
                    AND units.tenant_id = :tenantId
                    AND units.company_id = :companyId
                JOIN jobs
                    on jobs.code=assignments.job_code
                    AND jobs.tenant_id = :tenantId
                    AND jobs.company_id = :companyId
                JOIN locations
                    on locations.code=assignments.location_code
                    AND locations.tenant_id = :tenantId
                    AND locations.company_id = :companyId
                WHERE persons.tenant_id = :tenantId
                    AND :sysdate between persons.eff_begin and persons.eff_end
                ";
        return DB::select($query, $params);
    }

    /**
     * Get all Candidate
     */
    public function getAllCandidate()
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $now = Carbon::now();

        return DB::table('persons')
            ->select(
                'persons.first_name as firstName',
                'persons.last_name as lastName',
                'persons.id',
                'persons.email',
                'persons.mobile',
                'persons.birth_date as birthDate',
                'persons.birth_place as birthPlace',
                'persons.country_code as countryCode',
                'persons.lov_gndr as lovGndr',
                'persons.lov_mars as lovMars',
                'persons.lov_blod as lovBlod',
                'persons.lov_rlgn as lovRlgn',
                'countries.name as nationality',
                'genders.val_data as gender',
                'marital_status.val_data as maritalStatus',
                'blood_types.val_data as bloodType',
                'religions.val_data as religion',
                'persons.vacancy_id as vacancyId',
                'persons.candidate_ready_to_hire_id as candidateReadyToHireId'

            )
            ->leftJoin('countries', function ($join) use ($companyId, $tenantId) {
                $join->on('countries.code', '=', 'persons.country_code')
                    ->where([
                        ['countries.tenant_id', $tenantId],
                        ['countries.company_id', $companyId]
                    ]);
            })
            ->leftJoin('lovs as genders', function ($join) use ($companyId, $tenantId) {
                $join->on('genders.key_data', '=', 'persons.lov_gndr')
                    ->where([
                        ['genders.lov_type_code', 'GNDR'],
                        ['genders.tenant_id', $tenantId],
                        ['genders.company_id', $companyId]
                    ]);
            })
            ->leftJoin('lovs as marital_status', function ($join) use ($companyId, $tenantId) {
                $join->on('marital_status.key_data', '=', 'persons.lov_mars')
                    ->where([
                        ['marital_status.lov_type_code', 'MARS'],
                        ['marital_status.tenant_id', $tenantId],
                        ['marital_status.company_id', $companyId]
                    ]);
            })
            ->leftJoin('lovs as blood_types', function ($join) use ($companyId, $tenantId) {
                $join->on('blood_types.key_data', '=', 'persons.lov_blod')
                    ->where([
                        ['blood_types.lov_type_code', 'BLOD'],
                        ['blood_types.tenant_id', $tenantId],
                        ['blood_types.company_id', $companyId]
                    ]);
            })
            ->leftJoin('lovs as religions', function ($join) use ($companyId, $tenantId) {
                $join->on('religions.key_data', '=', 'persons.lov_rlgn')
                    ->where([
                        ['religions.lov_type_code', 'RLGN'],
                        ['religions.tenant_id', $tenantId],
                        ['religions.company_id', $companyId]
                    ]);
            })
            ->where('persons.tenant_id', $tenantId)
            ->where('persons.lov_ptyp', 'CAN')
            ->where('persons.eff_begin', '<=', $now)
            ->where('persons.eff_end', '>=', $now)
            ->get();
    }

    public function getAllByBirth()
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $now = Carbon::now();

        return DB::table('persons')
            ->selectRaw(
                'DISTINCT ON (assignments.employee_id,persons.birth_date ) ' .
                'persons.first_name as "firstName", ' .
                'persons.last_name as "lastName", ' .
                'assignments.employee_id as "employeeId", ' .
                'units.name as "unitName", ' .
                'persons.eff_begin as "effBegin", ' .
                'persons.birth_date as "birthDate"'
            )
            ->leftJoin('assignments', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('assignments.person_id', '=', 'persons.id')
                    ->where([
                        ['assignments.is_primary', true],
                        ['assignments.eff_begin', '<=', $now],
                        ['assignments.eff_end', '>=', $now],
                        ['assignments.tenant_id', $tenantId],
                        ['assignments.company_id', $companyId],
                        ['assignments.lov_asta', '=', 'ACT']
                    ]);
            })
            ->leftjoin('units', function ($join) use ($companyId, $tenantId) {
                $join->on('units.code', '=', 'assignments.unit_code')
                    ->where([
                        ['units.tenant_id', $tenantId],
                        ['units.company_id', $companyId]
                    ]);
            })
            ->where([
                ['persons.tenant_id', $this->requester->getTenantId()],
                ['assignments.company_id', $this->requester->getCompanyId()],
                ['persons.eff_begin', '<=', $now],
                ['persons.eff_end', '>=', $now],
            ])
            ->whereMonth('persons.birth_date', Carbon::now()->month)
            ->orderBy('persons.birth_date', 'ASC')
            ->get();
    }

    public function getAllByDateExpiring()
    {
        $params = [
            'tenantId' => $this->requester->getTenantId(),
            'companyId' => $this->requester->getCompanyId(),
            'sysdate' => Carbon::now(),
            'sysdateMonth' => Carbon::now()->subMonth(3)
        ];

        $query = "SELECT DISTINCT persons.id,
                persons.first_name as \"firstName\",
                persons.last_name as \"lastName\",
                assignments.employee_id as \"employeeId\",
                assignments.eff_end as \"effEnd\"
                from persons
                JOIN assignments on assignments.person_id=persons.id
                AND assignments.is_primary=true
                AND :sysdate between assignments.eff_begin and assignments.eff_end
                AND assignments.lov_asta = 'ACT'
                WHERE persons.tenant_id = :tenantId
                AND assignments.company_id = :companyId
                AND assignments.eff_end between :sysdateMonth and :sysdate
                ORDER BY assignments.eff_end DESC";
        return DB::select($query, $params);
    }

    public function getAllByDateExpired()
    {
        $params = [
            'tenantId' => $this->requester->getTenantId(),
            'companyId' => $this->requester->getCompanyId(),
            'sysdate' => Carbon::now()
        ];

        $query = "SELECT DISTINCT persons.id,
                persons.first_name as \"firstName\",
                persons.last_name as \"lastName\",
                assignments.employee_id as \"employeeId\",
                assignments.eff_end as \"effEnd\"
                from persons
                JOIN assignments on assignments.person_id=persons.id
                AND assignments.is_primary=true
                AND :sysdate > assignments.eff_end
                AND assignments.lov_asta = 'ACT'
                WHERE persons.tenant_id = :tenantId
                AND assignments.company_id = :companyId
                AND assignments.eff_end < :sysdate
                ORDER BY assignments.eff_end DESC";
        return DB::select($query, $params);
    }

    public function getAllLastPrimaryAssignment($companyId)
    {
        $tenantId = $this->requester->getTenantId();
        $now = Carbon::now();

        return DB::table('persons')
            ->selectRaw(
                'DISTINCT ON (persons.id) ' .
                'persons.first_name as "firstName", ' .
                'persons.last_name as "lastName", ' .
                'assignments.employee_id as "employeeId", ' .
                'assignments.lov_asta as "lovAsta", ' .
                'assignments.eff_end as "effEnd"'
            )
            ->join('assignments', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('assignments.person_id', '=', 'persons.id')
                    ->where([
                        ['assignments.is_primary', true],
                        ['assignments.tenant_id', $tenantId],
                        ['assignments.company_id', $companyId],
                        ['assignments.eff_begin', '<=', $now]
                    ]);
            })
            ->where([
                ['persons.tenant_id', $this->requester->getTenantId()],
                ['persons.eff_begin', '<=', $now],
                ['persons.eff_end', '>=', $now],
            ])
            ->orderBy('persons.id', 'ASC')
            ->orderBy('assignments.eff_end', 'DESC')
            ->get();
    }

    public function getAllByPosition($positionCode)
    {
        $params = [
            'tenantId' => $this->requester->getTenantId(),
            'positionCode' => $positionCode,
            'sysdate' => Carbon::now()
        ];
        $query = "SELECT DISTINCT persons.id,
                persons.first_name as \"firstName\",
                persons.last_name as \"lastName\",
                persons.file_photo as filePhoto
                from persons
                JOIN assignments on assignments.person_id=persons.id
                    AND assignments.is_primary=true
                    AND :sysdate between assignments.eff_begin and assignments.eff_end
                    AND assignments.lov_asta = 'ACT'
                JOIN positions on positions.code=assignments.position_code
                WHERE
                    persons.tenant_id = :tenantId AND positions.code = :positionCode
                ";
        return DB::select($query, $params);
    }

    public function getHeadOfUnit($unitCode)
    {
        $params = [
            'tenantId' => $this->requester->getTenantId(),
            'companyId' => $this->requester->getCompanyId(),
            'unitCode' => $unitCode,
            'sysdate' => Carbon::now()
        ];
        $query = "SELECT persons.id,
                persons.first_name as \"firstName\",
                persons.last_name as \"lastName\",
                persons.file_photo as \"filePhoto\",
                persons.mobile,
                persons.email,
                assignments.employee_id as \"employeeId\",
                positions.name as \"positionName\",
                positions.code as \"positionCode\",
                employee_statuses.name as \"employeeStatus\",
                supervisors.file_photo as \"supervisorPhoto\",
                supervisors.first_name as \"supervisorFirstName\",
                supervisors.last_name as \"supervisorLastName\",
                supervisor_positions.name as \"supervisorPosition\"
                from persons
                JOIN assignments
                    on assignments.person_id=persons.id
                    AND assignments.is_primary=true
                    AND :sysdate between assignments.eff_begin and assignments.eff_end
                    AND assignments.lov_asta = 'ACT'
                    AND assignments.tenant_id = :tenantId
                    AND assignments.company_id = :companyId
                LEFT JOIN employee_statuses
                    on employee_statuses.code=assignments.employee_status_code
                    AND employee_statuses.tenant_id = :tenantId
                    AND employee_statuses.company_id = :companyId
                JOIN positions
                    on positions.code=assignments.position_code
                    AND positions.tenant_id = :tenantId
                    AND positions.company_id = :companyId
                    AND positions.unit_code = :unitCode
                    AND positions.is_head = TRUE
                LEFT JOIN persons as \"supervisors\"
                    on supervisors.id=assignments.supervisor_id
                    AND supervisors.tenant_id = :tenantId
                    AND :sysdate between supervisors.eff_begin and supervisors.eff_end
                LEFT JOIN assignments as \"supervisor_assignments\"
                    on supervisor_assignments.person_id=supervisors.id
                    AND supervisor_assignments.is_primary=true
                    AND :sysdate between supervisor_assignments.eff_begin and supervisor_assignments.eff_end
                    AND supervisor_assignments.lov_asta = 'ACT'
                    AND supervisor_assignments.tenant_id = :tenantId
                    AND supervisor_assignments.company_id = :companyId
                LEFT JOIN positions as \"supervisor_positions\"
                    on supervisor_positions.code=supervisor_assignments.position_code
                    AND supervisor_positions.tenant_id = :tenantId
                    AND supervisor_positions.company_id = :companyId
                WHERE
                    persons.tenant_id = :tenantId
                ORDER BY assignments.eff_begin
                ";
        return
            collect(\DB::select($query, $params))->first();
    }

    public function getAllByUnit($unitCode)
    {
        $params = [
            'tenantId' => $this->requester->getTenantId(),
            'companyId' => $this->requester->getCompanyId(),
            'unitCode' => $unitCode,
            'sysdate' => Carbon::now()
        ];
        $query = "SELECT DISTINCT persons.id,
                persons.first_name as \"firstName\",
                persons.last_name as \"lastName\",
                persons.file_photo as filePhoto,
                positions.name as \"positionName\",
                positions.is_head as \"hou\",
                persons.mobile,
                persons.email,
                positions.name as \"positionName\",
                employee_statuses.name as \"employeeStatus\",
                supervisors.file_photo as \"supervisorPhoto\",
                supervisors.first_name as \"supervisorFirstName\",
                supervisors.last_name as \"supervisorLastName\",
                assignments.eff_begin as \"firstAssignment\",
                units.name as \"unitName\"
                from persons
                JOIN assignments
                    on assignments.person_id=persons.id
                    AND assignments.is_primary=true
                    AND :sysdate between assignments.eff_begin and assignments.eff_end
                    AND assignments.lov_asta = 'ACT'
                    AND assignments.tenant_id = :tenantId
                    AND assignments.company_id = :companyId
                LEFT JOIN employee_statuses
                    on employee_statuses.code=assignments.employee_status_code
                    AND employee_statuses.tenant_id = :tenantId
                    AND employee_statuses.company_id = :companyId
                LEFT JOIN positions
                    on positions.code=assignments.position_code
                    AND positions.tenant_id = :tenantId
                    AND positions.company_id = :companyId
                LEFT JOIN persons as \"supervisors\"
                    on supervisors.id=assignments.supervisor_id
                    AND :sysdate between supervisors.eff_begin and supervisors.eff_end
                    AND supervisors.tenant_id = :tenantId
                JOIN units
                    on units.code=assignments.unit_code
                    AND units.tenant_id = :tenantId
                    AND units.company_id = :companyId
                    AND units.code = :unitCode
                WHERE
                    persons.tenant_id = :tenantId
                    AND :sysdate between persons.eff_begin and persons.eff_end
                ORDER BY assignments.eff_begin
               ";
        return DB::select($query, $params);
    }

    /**
     * Get hierarchy by org structure id
     * @param  tenantId , companyId, orgStructureId
     */
    public function getRecursive($unitCode, $orgStructureId)
    {
        $tenantId = $this->requester->getTenantId();

        return
            DB::select(
                DB::raw(
                    "
                    WITH RECURSIVE parents AS (
                        (
                            SELECT DISTINCT
                                org_structure_id as \"orgStructureId\",
                                u.code,
                                parent_unit_code as \"parentCode\",
                                array[u.code || '']::varchar[] as path
                            FROM
                                org_structure_hierarchies o, units u
                            WHERE
                                org_structure_id = :org_structure_id AND
                                o.tenant_id = :tenant_id AND
                                u.code = :unit_code AND
                                parent_unit_code IS NULL
                        )
                        UNION
                        (
                            SELECT DISTINCT
                                e.\"orgStructureId\",
                                e.code,
                                e.\"parentCode\",
                                (parents.path || e.code)
                            FROM
                            (
                                SELECT
                                    org_structure_id as \"orgStructureId\",
                                    u.code,
                                    parent_unit_code as \"parentCode\"
                                FROM
                                    org_structure_hierarchies o, units u
                                WHERE
                                    org_structure_id = :org_structure_id AND
                                    o.tenant_id = :tenant_id AND
                                    o.unit_code = u.code
                            ) e, parents
                            WHERE
                                e.\"parentCode\" = parents.code
                        )
                    )
                    SELECT * FROM parents ORDER BY path;
                    "
                ),
                [
                    'org_structure_id' => $orgStructureId,
                    'tenant_id' => $tenantId,
                    'unit_code' => $unitCode
                ]
            );
    }

    public function getAllByUnitExHou($unitCode, $personId)
    {
        $params = [
            'tenantId' => $this->requester->getTenantId(),
            'companyId' => $this->requester->getCompanyId(),
            'unitCode' => $unitCode,
            'personId' => $personId,
            'sysdate' => Carbon::now()
        ];
        $query = "SELECT DISTINCT persons.id,
                persons.first_name as \"firstName\",
                persons.last_name as \"lastName\",
                persons.file_photo as filePhoto,
                positions.name as \"positionName\",
                positions.is_head as \"hou\",
                persons.mobile,
                persons.email,
                positions.name as \"positionName\",
                employee_statuses.name as \"employeeStatus\",
                supervisors.file_photo as \"supervisorPhoto\",
                supervisors.first_name as \"supervisorFirstName\",
                supervisors.last_name as \"supervisorLastName\",
                assignments.eff_begin as \"firstAssignment\",
                units.name as \"unitName\"
                from persons
                JOIN assignments
                    on assignments.person_id=persons.id
                    AND assignments.is_primary=true
                    AND :sysdate between assignments.eff_begin and assignments.eff_end
                    AND assignments.lov_asta = 'ACT'
                    and assignments.tenant_id= :tenantId
                    and assignments.company_id= :companyId
                LEFT JOIN employee_statuses
                    on employee_statuses.code=assignments.employee_status_code
                    and employee_statuses.tenant_id= :tenantId
                    and employee_statuses.company_id= :companyId
                LEFT JOIN positions
                    on positions.code=assignments.position_code
                    and positions.tenant_id= :tenantId
                    and positions.company_id= :companyId
                LEFT JOIN persons as \"supervisors\"
                    on supervisors.id=assignments.supervisor_id
                    AND :sysdate between supervisors.eff_begin and supervisors.eff_end
                    and supervisors.tenant_id= :tenantId
                JOIN units
                    on units.code=assignments.unit_code
                    and units.tenant_id= :tenantId
                    and units.company_id= :companyId
                    and units.code = :unitCode
                WHERE
                    persons.tenant_id = :tenantId
                    AND persons.id != :personId
                    AND :sysdate between persons.eff_begin and persons.eff_end
                ORDER BY assignments.eff_begin
               ";
        return DB::select($query, $params);
    }

    /**
     * @param
     * @return
     */
    public function getTotalRows()
    {
        return DB::table('persons')->where([['tenant_id', $this->requester->getTenantId()]])->count();
    }

    /**
     * Get person history
     */
    public function getHistory($personId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('persons')
                ->selectRaw(
                    'persons.id,' .
                    'persons.first_name as "firstName",' .
                    'persons.last_name as "lastName",' .
                    'persons.eff_begin as "effBegin",' .
                    'persons.eff_end as "effEnd",' .
                    'persons.mobile,' .
                    'persons.email,' .
                    'id_card as "idCard",' .
                    'birth_place as "birthPlace",' .
                    'age(persons.birth_date) as "age",' .
                    'persons.birth_date as "birthDate",' .
                    'genders.val_data as "gender",' .
                    'persons.lov_gndr as "lovGndr",' .
                    'marital_status.val_data as "maritalStatus",' .
                    'lov_mars as "lovMars",' .
                    'countries.nationality,' .
                    'countries.id as "countryId",' .
                    'blood_types.val_data as "bloodType",' .
                    'lov_blod as "lovBlod",' .
                    'religions.val_data as "religion",' .
                    'lov_rlgn as "lovRlgn",' .
                    'person_families.id as "emergencyContactId",' .
                    'person_families.name as "emergencyContactName",' .
                    'person_families.phone as "emergencyContactPhone",' .
                    'person_families.lov_famr as "lov_famr",' .
                    'families.val_data as "relationship",' .
                    'hobbies,' .
                    'strength,' .
                    'weakness'
                )
                ->leftJoin('person_families', function ($join) {
                    $join->on('persons.id', '=', 'person_families.person_id')
                        ->where('person_families.is_emergency', true);
                })
                ->leftJoin('lovs as families', function ($join) use ($companyId, $tenantId) {
                    $join->on('families.key_data', '=', 'person_families.lov_famr')
                        ->where([
                            ['families.tenant_id', $tenantId],
                            ['families.company_id', $companyId]
                        ]);
                })
                // ->leftJoin('countries', 'countries.code', '=', 'persons.country_code')
                // ->leftJoin('lovs as genders', 'genders.key_data', '=', 'persons.lov_gndr')
                // ->leftJoin('lovs as marital_status', 'marital_status.key_data', '=', 'persons.lov_mars')
                // ->leftJoin('lovs as blood_types', function ($join) {
                //     $join->on('blood_types.key_data', '=', 'persons.lov_blod')
                //         ->where('blood_types.lov_type_code', 'BLOD');
                // })
                // ->leftJoin('lovs as religions', 'religions.key_data', '=', 'persons.lov_rlgn')
                ->leftJoin('countries', function ($join) use ($companyId, $tenantId) {
                    $join->on('countries.code', '=', 'persons.country_code')
                        ->where([
                            ['countries.tenant_id', $tenantId],
                            ['countries.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as genders', function ($join) use ($companyId, $tenantId) {
                    $join->on('genders.key_data', '=', 'persons.lov_gndr')
                        ->where([
                            ['genders.lov_type_code', 'GNDR'],
                            ['genders.tenant_id', $tenantId],
                            ['genders.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as marital_status', function ($join) use ($companyId, $tenantId) {
                    $join->on('marital_status.key_data', '=', 'persons.lov_mars')
                        ->where([
                            ['marital_status.lov_type_code', 'MARS'],
                            ['marital_status.tenant_id', $tenantId],
                            ['marital_status.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as blood_types', function ($join) use ($companyId, $tenantId) {
                    $join->on('blood_types.key_data', '=', 'persons.lov_blod')
                        ->where([
                            ['blood_types.lov_type_code', 'BLOD'],
                            ['blood_types.tenant_id', $tenantId],
                            ['blood_types.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as religions', function ($join) use ($companyId, $tenantId) {
                    $join->on('religions.key_data', '=', 'persons.lov_rlgn')
                        ->where([
                            ['religions.lov_type_code', 'RLGN'],
                            ['religions.tenant_id', $tenantId],
                            ['religions.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['persons.tenant_id', $this->requester->getTenantId()],
                    ['persons.id', $personId]
                ])
                ->orderBy('persons.eff_end', 'desc')
                ->get();
    }

    public function getAllActiveEmployees()
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $now = Carbon::now();
        return
            DB::table('persons')
                ->selectRaw(
                    'persons.id,' .
                    'persons.first_name as "firstName",' .
                    'persons.last_name as "lastName",' .
                    'persons.email as "email",' .
                    'assignments.employee_id as "employeeId"'
                )
                ->rightJoin('assignments', function ($join) use ($companyId, $tenantId, $now) {
                    $join->on('assignments.person_id', '=', 'persons.id')
                        ->where([
                            ['assignments.is_primary', true],
                            ['assignments.eff_begin', '<=', $now],
                            ['assignments.eff_end', '>=', $now],
                            ['assignments.tenant_id', $tenantId],
                            ['assignments.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['persons.tenant_id', $tenantId],
                    ['persons.email', '!=', ''],
                    ['persons.email', '!=', null]
                ])
                ->distinct()
                ->get();
    }

    /**
     * Get person based on person id
     * @param  personId
     */
    public function getOne($personId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $now = Carbon::now();
        return
            DB::table('persons')
                ->selectRaw(
                    'persons.id,' .
                    'persons.first_name as "firstName",' .
                    'persons.last_name as "lastName",' .
                    'persons.mobile,' .
                    'persons.email,' .
                    'persons.eff_begin as "effBegin",' .
                    'age(persons.eff_begin) as "workLength",' .
                    'person_types.val_data as "personType",' .
                    'persons.lov_ptyp as "lovPtyp",' .
                    'persons.lov_rlgn as "lovRlgn",' .
                    'persons.lov_mars as "lovMars",' .
                    'persons.file_photo as "filePhoto",' .
                    'supervisors.id as "supervisorId",' .
                    'supervisors.first_name as "supervisorFirstName",' .
                    'supervisors.last_name as "supervisorLastName",' .
                    'supervisors.file_photo as "supervisorPhoto",' .
                    'supervisor_positions.name as "supervisorPosition",' .
                    'assignments.employee_id as "employeeId",' .
                    'assignments.eff_begin as "assignBegin",' .
                    'assignments.eff_end as "assignEnd",' .
                    'assignments.lov_acty as "lovActy",' .
                    'assignments.lov_asta as "lovAsta",' .
                    'assignments.employee_id as "employeeId",' .
                    'assignments.grade_code as "gradeCode",' .
                    'employee_statuses.code as "employeeStatusCode",' .
                    'employee_statuses.name as "employeeStatusName",' .
                    'employee_statuses.working_month as "workingMonth",' .
                    'positions.code as "positionCode",' .
                    'positions.name as "positionName",' .
                    'positions.is_head as "isHead",' .
                    'units.code as "unitCode",' .
                    'units.name as "unitName",' .
                    'jobs.code as "jobCode",' .
                    'jobs.name as "jobName",' .
                    'locations.code as "locationCode",' .
                    'locations.name as "locationName",' .
                    'countries.id as "countryId"'
                )
                ->leftJoin('lovs as person_types', function ($join) use ($companyId, $tenantId) {
                    $join->on('person_types.key_data', '=', 'persons.lov_ptyp')
                        ->where([
                            ['person_types.lov_type_code', 'PTYP'],
                            ['person_types.tenant_id', $tenantId],
                            ['person_types.company_id', $companyId]
                        ]);
                })
                ->leftJoin('assignments', function ($join) use ($companyId, $tenantId, $now) {
                    $join->on('assignments.person_id', '=', 'persons.id')
                        ->where([
                            ['assignments.is_primary', true],
                            ['assignments.eff_begin', '<=', $now],
                            ['assignments.eff_end', '>=', $now],
                            ['assignments.tenant_id', $tenantId],
                            ['assignments.company_id', $companyId]
                        ]);
                })
                ->leftJoin('persons as supervisors', function ($join) use ($companyId, $tenantId) {
                    $join->on('supervisors.id', '=', 'assignments.supervisor_id')
                        ->where([
                            ['supervisors.tenant_id', $tenantId]
                        ])
                        ->orderBy('persons.eff_end', 'desc');
                })
                ->leftjoin('assignments as supervisor_assignments', function ($join) use ($companyId, $tenantId, $now) {
                    $join->on('supervisor_assignments.person_id', '=', 'supervisors.id')
                        ->where([
                            ['supervisor_assignments.eff_begin', '<=', $now],
                            ['supervisor_assignments.eff_end', '>=', $now],
                            ['supervisor_assignments.tenant_id', $tenantId],
                            ['supervisor_assignments.company_id', $companyId]
                        ]);
                })
                ->leftjoin('positions as supervisor_positions', function ($join) use ($companyId, $tenantId) {
                    $join->on('supervisor_positions.code', '=', 'supervisor_assignments.position_code')
                        ->where([
                            ['supervisor_positions.tenant_id', $tenantId],
                            ['supervisor_positions.company_id', $companyId]
                        ]);
                })
                ->leftJoin('employee_statuses', function ($join) use ($companyId, $tenantId) {
                    $join->on('employee_statuses.code', '=', 'assignments.employee_status_code')
                        ->where([
                            ['employee_statuses.tenant_id', $tenantId],
                            ['employee_statuses.company_id', $companyId]
                        ]);
                })
                ->leftjoin('positions', function ($join) use ($companyId, $tenantId) {
                    $join->on('positions.code', '=', 'assignments.position_code')
                        ->where([
                            ['positions.tenant_id', $tenantId],
                            ['positions.company_id', $companyId]
                        ]);
                })
                ->leftjoin('locations', function ($join) use ($companyId, $tenantId) {
                    $join->on('locations.code', '=', 'assignments.location_code')
                        ->where([
                            ['locations.tenant_id', $tenantId],
                            ['locations.company_id', $companyId]
                        ]);
                })
                ->leftjoin('units', function ($join) use ($companyId, $tenantId) {
                    $join->on('units.code', '=', 'assignments.unit_code')
                        ->where([
                            ['units.tenant_id', $tenantId],
                            ['units.company_id', $companyId]
                        ]);
                })
                ->leftjoin('jobs', function ($join) use ($companyId, $tenantId) {
                    $join->on('jobs.code', 'assignments.job_code')
                        ->where([
                            ['jobs.tenant_id', $tenantId],
                            ['jobs.company_id', $companyId]
                        ]);
                })
                ->leftjoin('countries', function ($join) use ($companyId, $tenantId) {
                    $join->on('countries.code', '=', 'persons.country_code')
                        ->where([
                            ['countries.tenant_id', $tenantId],
                            ['countries.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['persons.tenant_id', $tenantId],
                    ['persons.id', $personId]
                ])
                ->orderBy('persons.eff_end', 'desc')
                ->first();
    }

    /**
     * Get person based on employee id
     * @param employeeId
     */
    public function getOneEmployee($employeeId)
    {
        $param = [
            'tenantd' => $this->requester->getTenantId(),
            'companyId' => $this->requester->getCompanyId(),
            'employeeId' => $employeeId,
            'sysdate' => Carbon::now()
        ];

        $query = "SELECT persons.id,
                persons.first_name as \"firstName\",
                persons.last_name as \"lastName\",
                persons.mobile,
                persons.email,
                persons.eff_begin as \"effBegin\",
                persons.eff_end as \"effEnd\",
                age(persons.eff_begin) as \"workLength\",
                person_types.val_data as \"personType\",
                persons.lov_ptyp as \"lovPtyp\",
                persons.lov_rlgn as \"lovRlgn\",
                persons.lov_mars as \"lovMars\",
                persons.file_photo as \"filePhoto\",
                supervisors.id as \"supervisorId\",
                supervisors.first_name as \"supervisorFirstName\",
                supervisors.last_name as \"supervisorLastName\",
                supervisors.file_photo as \"supervisorPhoto\",
                supervisor_positions.name as \"supervisorPosition\",
                assignments.employee_id as \"employeeId\",
                assignments.eff_begin as \"assignBegin\",
                assignments.eff_end as \"assignEnd\",
                assignments.lov_acty as \"lovActy\",
                assignments.lov_asta as \"lovAsta\",
                employee_statuses.name as \"employeeStatusName\",
                employee_statuses.working_month as \"workingMonth\",
                positions.code as \"positionCode\",
                positions.name as \"positionName\",
                jobs.code as \"jobCode\",
                jobs.name as \"jobName\",
                units.code as \"unitCode\",
                units.name as \"unitName\",
                locations.id as \"locationCode\",
                locations.name as \"locationName\",
                countries.id as \"countryId\"
                from persons
                JOIN lovs as person_types on person_types.key_data=persons.lov_ptyp
                    AND person_types.company_id = :companyId
                    AND person_types.tenant_id = :tenantd
                JOIN assignments on assignments.person_id=persons.id
                    AND assignments.is_primary=true
                    AND assignments.company_id = :companyId
                    AND assignments.tenant_id = :tenantd
                LEFT JOIN persons as supervisors on assignments.supervisor_id=supervisors.id
                    AND supervisors.tenant_id = :tenantd
                LEFT JOIN assignments as supervisor_assignments on supervisor_assignments.person_id=supervisors.id
                    AND supervisor_assignments.is_primary=true
                    AND :sysdate between supervisor_assignments.eff_begin and supervisor_assignments.eff_end
                    AND supervisor_assignments.lov_asta = 'ACT'
                    AND supervisor_assignments.company_id = :companyId
                    AND supervisor_assignments.tenant_id = :tenantd
                LEFT JOIN employee_statuses on assignments.employee_status_code= employee_statuses.code
                    AND employee_statuses.company_id = :companyId
                    AND employee_statuses.tenant_id = :tenantd
                LEFT JOIN countries on countries.code=persons.country_code
                    AND countries.company_id = :companyId
                    AND countries.tenant_id = :tenantd
                LEFT JOIN positions on positions.code=assignments.position_code
                    AND positions.company_id = :companyId
                    AND positions.tenant_id = :tenantd
                LEFT JOIN jobs on jobs.code=assignments.job_code
                    AND jobs.company_id = :companyId
                    AND jobs.tenant_id = :tenantd
                LEFT JOIN positions as supervisor_positions on supervisor_positions.code=supervisor_assignments.position_code
                    AND supervisor_positions.company_id = :companyId
                    AND supervisor_positions.tenant_id = :tenantd
                LEFT JOIN units on assignments.unit_code=units.code
                    AND units.company_id = :companyId
                    AND units.tenant_id = :tenantd
                LEFT JOIN locations on locations.code=assignments.location_code
                    AND locations.company_id = :companyId
                    AND locations.tenant_id = :tenantd
                WHERE persons.tenant_id = :tenantd
                    AND person_types.lov_type_code='PTYP'
                    AND assignments.employee_id = :employeeId
                ORDER BY persons.eff_end DESC
                ";
        return
            collect(\DB::select($query, $param))->first();
    }

    /**
     * Get person based on employee id
     * @param employeeId
     */
    public function getOneEmployeeSecure($employeeId, $menuCode)
    {
        $roleIds = $this->requester->getRoleIds();
        $roleIds_param = '{' . implode(",", $roleIds) . '}';

        $param = [
            'tenantId' => $this->requester->getTenantId(),
            'companyId' => $this->requester->getCompanyId(),
            'employeeId' => $employeeId,
            'roleIds' => $roleIds_param,
            'menuCode' => $menuCode,
            'sysdate' => Carbon::now()
        ];

        $query = "SELECT persons.id,
                persons.first_name as \"firstName\",
                persons.last_name as \"lastName\",
                persons.mobile,
                persons.email,
                persons.eff_begin as \"effBegin\",
                persons.eff_end as \"effEnd\",
                age(persons.eff_begin) as \"workLength\",
                person_types.val_data as \"personType\",
                persons.lov_ptyp as \"lovPtyp\",
                persons.lov_rlgn as \"lovRlgn\",
                persons.lov_mars as \"lovMars\",
                persons.file_photo as \"filePhoto\",
                supervisors.id as \"supervisorId\",
                supervisors.first_name as \"supervisorFirstName\",
                supervisors.last_name as \"supervisorLastName\",
                supervisors.file_photo as \"supervisorPhoto\",
                supervisor_positions.name as \"supervisorPosition\",
                assignments.employee_id as \"employeeId\",
                assignments.eff_begin as \"assignBegin\",
                assignments.eff_end as \"assignEnd\",
                assignments.lov_acty as \"lovActy\",
                assignments.lov_asta as \"lovAsta\",
                employee_statuses.name as \"employeeStatusName\",
                employee_statuses.working_month as \"workingMonth\",
                positions.code as \"positionCode\",
                positions.name as \"positionName\",
                jobs.code as \"jobCode\",
                jobs.name as \"jobName\",
                units.id as \"unitCode\",
                units.name as \"unitName\",
                locations.id as \"locationCode\",
                locations.name as \"locationName\",
                countries.id as \"countryId\"
                from persons
                JOIN f_person_lovs(:tenantId, :companyId, :menuCode, :roleIds) on f_person_lovs.person_id = persons.id
                LEFT JOIN lovs as person_types on person_types.key_data=persons.lov_ptyp
                    AND person_types.company_id = :companyId
                    AND person_types.tenant_id = :tenantId
                LEFT JOIN assignments on assignments.person_id=persons.id
                    AND assignments.is_primary=true
                    AND assignments.company_id = :companyId
                    AND assignments.tenant_id = :tenantId
                LEFT JOIN persons as supervisors on assignments.supervisor_id=supervisors.id
                    AND supervisors.tenant_id = :tenantId
                LEFT JOIN assignments as supervisor_assignments on supervisor_assignments.person_id=supervisors.id
                    AND supervisor_assignments.is_primary=true
                    AND :sysdate between supervisor_assignments.eff_begin and supervisor_assignments.eff_end
                    AND supervisor_assignments.lov_asta = 'ACT'
                    AND supervisor_assignments.company_id = :companyId
                    AND supervisor_assignments.tenant_id = :tenantId
                LEFT JOIN employee_statuses on assignments.employee_status_code= employee_statuses.code
                    AND employee_statuses.company_id = :companyId
                    AND employee_statuses.tenant_id = :tenantId
                LEFT JOIN countries on countries.code=persons.country_code
                    AND countries.company_id = :companyId
                    AND countries.tenant_id = :tenantId
                LEFT JOIN positions on positions.code=assignments.position_code
                    AND positions.company_id = :companyId
                    AND positions.tenant_id = :tenantId
                LEFT JOIN jobs on jobs.code=assignments.job_code
                    AND jobs.company_id = :companyId
                    AND jobs.tenant_id = :tenantId
                LEFT JOIN positions as supervisor_positions on supervisor_positions.code=supervisor_assignments.position_code
                    AND supervisor_positions.company_id = :companyId
                    AND supervisor_positions.tenant_id = :tenantId
                LEFT JOIN units on assignments.unit_code=units.code
                    AND units.company_id = :companyId
                    AND units.tenant_id = :tenantId
                LEFT JOIN locations on locations.code=assignments.location_code
                    AND locations.company_id = :companyId
                    AND locations.tenant_id = :tenantId
                WHERE person_types.lov_type_code='PTYP'
                    AND assignments.employee_id = :employeeId
                ORDER BY persons.eff_end DESC
                ";
        return
            collect(\DB::select($query, $param))->first();
    }

    public function getOneEmployeeByCompanyId($companyId)
    {
        $tenantId = $this->requester->getTenantId();
        $now = Carbon::now();
        Log::info($companyId);
        return
            DB::table('persons')
                ->selectRaw(
                    'persons.id'
                )
                ->distinct()
                ->join('countries', function ($join) use ($companyId, $tenantId) {
                    $join->on('countries.code', '=', 'persons.country_code')
                        ->where([
                            ['countries.tenant_id', $tenantId],
                            ['countries.company_id', $companyId]
                        ]);
                })
                ->join('assignments', function ($join) use ($companyId, $tenantId, $now) {
                    $join->on('assignments.person_id', '=', 'persons.id')
                        ->where([
                            ['assignments.tenant_id', $tenantId],
                            ['assignments.company_id', $companyId],
                            ['assignments.eff_begin', '<=', $now],
                            ['assignments.eff_end', '>=', $now],
                            ['assignments.lov_asta', 'ACT']
                        ])
                        ->orderBy('assignments.id', 'DESC');
                })
                ->where([
                    ['persons.tenant_id', $tenantId],
                ])
                ->first();
    }

    public function getBasicInfo($personId, $companyId)
    {
        //        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        Log::info($companyId);
        return
            DB::table('persons')
                ->selectRaw(
                    'persons.id,' .
                    'persons.first_name as "firstName",' .
                    'persons.last_name as "lastName",' .
                    'persons.eff_begin as "effBegin",' .
                    'persons.eff_end as "effEnd",' .
                    'persons.mobile,' .
                    'persons.email,' .
                    'id_card as "idCard",' .
                    'birth_place as "birthPlace",' .
                    'age(persons.birth_date) as "age",' .
                    'persons.birth_date as "birthDate",' .
                    'genders.val_data as "gender",' .
                    'persons.lov_gndr as "lovGndr",' .
                    'marital_status.val_data as "maritalStatus",' .
                    'lov_mars as "lovMars",' .
                    'countries.nationality,' .
                    'countries.id as "countryId",' .
                    'countries.code as "countryCode",' .
                    'blood_types.val_data as "bloodType",' .
                    'lov_blod as "lovBlod",' .
                    'religions.val_data as "religion",' .
                    'lov_rlgn as "lovRlgn",' .
                    'hobbies,' .
                    'strength,' .
                    'weakness,' .
                    'cf_person_basic_info.c1,' .
                    'cf_person_basic_info.c2,' .
                    'cf_person_basic_info.c3,' .
                    'cf_person_basic_info.c4,' .
                    'cf_person_basic_info.c5,' .
                    'cf_person_basic_info.c6,' .
                    'cf_person_basic_info.c7,' .
                    'cf_person_basic_info.c8,' .
                    'cf_person_basic_info.c9,' .
                    'cf_person_basic_info.c10'

                )
                ->leftJoin('countries', function ($join) use ($companyId, $tenantId) {
                    $join->on('countries.code', '=', 'persons.country_code')
                        ->where([
                            ['countries.tenant_id', $tenantId],
                            ['countries.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as blood_types', function ($join) use ($companyId, $tenantId) {
                    $join->on('blood_types.key_data', '=', 'persons.lov_blod')
                        ->where([
                            ['blood_types.lov_type_code', 'BLOD'],
                            ['blood_types.tenant_id', $tenantId],
                            ['blood_types.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as genders', function ($join) use ($companyId, $tenantId) {
                    $join->on('genders.key_data', '=', 'persons.lov_gndr')
                        ->where([
                            ['genders.lov_type_code', 'GNDR'],
                            ['genders.tenant_id', $tenantId],
                            ['genders.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as religions', function ($join) use ($companyId, $tenantId) {
                    $join->on('religions.key_data', '=', 'persons.lov_rlgn')
                        ->where([
                            ['religions.lov_type_code', 'RLGN'],
                            ['religions.tenant_id', $tenantId],
                            ['religions.company_id', $companyId]
                        ]);
                })
                ->leftJoin('lovs as marital_status', function ($join) use ($companyId, $tenantId) {
                    $join->on('marital_status.key_data', '=', 'persons.lov_mars')
                        ->where([
                            ['marital_status.lov_type_code', 'MARS'],
                            ['marital_status.tenant_id', $tenantId],
                            ['marital_status.company_id', $companyId]
                        ]);
                })
                ->leftJoin('cf_person_basic_info', function ($join) use ($companyId, $tenantId) {
                    $join->on('cf_person_basic_info.person_id', '=', 'persons.id')
                        ->where([
                            ['cf_person_basic_info.tenant_id', $tenantId],
                            ['cf_person_basic_info.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['persons.tenant_id', $tenantId],
                    ['persons.id', $personId]
                ])
                ->orderBy('persons.eff_end', 'desc')
                ->orderBy('cf_person_basic_info.eff_end', 'desc')
                ->first();
    }

    public function getSLov($menuCode)
    {
        $now = Carbon::now();
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        $roleIds = $this->requester->getRoleIds();

        $roleIds_param = 'array[' . implode(",", $roleIds) . ']';

        $query =
            DB::table(DB::raw('f_person_lovs(' . $tenantId . ',' . $companyId . ',\'' . $menuCode . '\',' . $roleIds_param . ')'))
                ->selectRaw(
                    'f_person_lovs.person_id as "id",' .
                    '(CONCAT(f_person_lovs.first_name,\' \', f_person_lovs.last_name)) as "fullName"'
                )
                ->join('assignments', 'assignments.person_id', '=', 'f_person_lovs.person_id')
                ->where([
                    ['assignments.eff_begin', '<=', $now],
                    ['assignments.eff_end', '>=', $now],
                    ['assignments.is_primary', '=', true]
                ]);

        return $query->get();
    }

    public function getLov()
    {
        $now = Carbon::now();
        $query =
            DB::table('persons')
                ->selectRaw(
                    'persons.id,' .
                    '(CONCAT(persons.first_name,\' \', persons.last_name)) as "fullName",' .
                    'assignments.employee_id as "employeeId"'
                )
                ->join('assignments', 'assignments.person_id', '=', 'persons.id')
                ->where([
                    ['persons.tenant_id', $this->requester->getTenantId()],
                    ['assignments.eff_begin', '<=', $now],
                    ['assignments.eff_end', '>=', $now],
                    ['assignments.is_primary', true],
                    ['assignments.company_id', $this->requester->getCompanyId()]
                ]);

        return $query->get();
    }

    public function search($menuCode, $query)
    {
        info('search');
        $now = Carbon::now();
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        $roleIds = $this->requester->getRoleIds();

        $roleIds_param = 'array[' . implode(",", $roleIds) . ']';
        $searchString = strtolower("%$query%");

        $querySQL =
            DB::table(DB::raw('f_person_lovs(' . $tenantId . ',' . $companyId . ',\'' . $menuCode . '\',' . $roleIds_param . ')'))
                ->selectRaw(
                    'f_person_lovs.person_id as "id",' .
                    '(CONCAT(f_person_lovs.first_name,\' \', f_person_lovs.last_name)) as "fullName",' .
                    'assignments.employee_id as "employeeId"'
                )
                ->join('assignments', function ($join) {
                    $join->on('f_person_lovs.person_id', '=', 'assignments.person_id')
                        ->on('f_person_lovs.company_id', '=', 'assignments.company_id')
                        ->on('f_person_lovs.tenant_id', '=', 'assignments.tenant_id');
                })
                ->where([
                    ['assignments.eff_begin', '<=', $now],
                    ['assignments.eff_end', '>=', $now],
                    ['assignments.is_primary', '=', true]
                ])
                ->whereRaw('LOWER(CONCAT(f_person_lovs.first_name,\' \', f_person_lovs.last_name)) like ?', [$searchString]);

        return $querySQL->get();
    }

    public function searchSA($query)
    {
        info('searchSA');
        $now = Carbon::now();
        $searchString = strtolower("%$query%");
        $querySQL =
            DB::table('persons')
                ->selectRaw(
                    'persons.id as "id",' .
                    '(CONCAT(persons.first_name,\' \', persons.last_name)) as "fullName",' .
                    'assignments.employee_id as "employeeId"'
                )
                ->join('assignments', function ($join) use ($now) {
                    $join->on('persons.id', '=', 'assignments.person_id')
                        ->on('persons.tenant_id', '=', 'assignments.tenant_id')
                        ->where([
                            ['assignments.eff_begin', '<=', $now],
                            ['assignments.eff_end', '>=', $now],
                            ['assignments.is_primary', '=', true],
                            ['assignments.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->where([
                    ['persons.tenant_id', $this->requester->getTenantId()],
                    ['persons.eff_begin', '<=', $now],
                    ['persons.eff_end', '>=', $now],
                ])
                ->whereRaw('LOWER(CONCAT(persons.first_name,\' \', persons.last_name)) like ?', [$searchString]);

        return $querySQL->get();
    }

    public function searchInstructor($query)
    {
        $now = Carbon::now();
        $searchString = strtolower("%$query%");
        $querySQL =
            DB::table('persons')
                ->selectRaw(
                    'persons.id as "id",' .
                    '(CONCAT(persons.first_name,\' \', persons.last_name)) as "fullName"'
                )
                ->where([
                    ['persons.tenant_id', $this->requester->getTenantId()],
                    ['persons.eff_begin', '<=', $now],
                    ['persons.eff_end', '>=', $now],
                    ['persons.lov_ptyp', '=', 'INS'],
                ])
                ->whereRaw('LOWER(CONCAT(persons.first_name,\' \', persons.last_name)) like ?', [$searchString]);

        return $querySQL->get();
    }

    public function searchCustom($menuCode, $query)
    {
        $now = Carbon::now();
        $tenantId = $this->requester->getTenantId();
        $companyId = $this->requester->getCompanyId();
        $roleIds = $this->requester->getRoleIds();

        $roleIds_param = 'array[' . implode(",", $roleIds) . ']';
        $searchString = strtolower("%$query%");
        $query =
            DB::table(DB::raw('f_person_lovs(' . $tenantId . ',' . $companyId . ',\'' . $menuCode . '\',' . $roleIds_param . ')'))
                ->selectRaw(
                    'f_person_lovs.person_id as "id",' .
                    'assignments.employee_id as "employeeId",' .
                    'assignments.eff_begin as "assignBegin",' .
                    'assignments.lov_acty as "lovActy",' .
                    'assignments.unit_code as "unitCode",' .
                    'persons.file_photo as "filePhoto",' .
                    '(CONCAT(f_person_lovs.first_name,\' \', f_person_lovs.last_name)) as "fullName",' .
                    'positions.name as "position"'
                )
                ->join('assignments', function ($join) use ($now) {
                    $join->on('assignments.person_id', '=', 'f_person_lovs.person_id')
                        ->on('assignments.company_id', '=', 'f_person_lovs.company_id')
                        ->on('assignments.tenant_id', '=', 'f_person_lovs.tenant_id')
                        ->where([
                            ['assignments.eff_begin', '<=', $now],
                            ['assignments.eff_end', '>=', $now],
                            ['assignments.is_primary', '=', true],
                            ['assignments.lov_asta', 'ACT']
                        ]);
                })
                ->join('persons', function ($join) use ($now) {
                    $join->on('persons.id', '=', 'f_person_lovs.person_id')
                        ->on('persons.tenant_id', '=', 'f_person_lovs.tenant_id')
                        ->where([
                            ['persons.eff_begin', '<=', $now],
                            ['persons.eff_end', '>=', $now]
                        ]);
                })
                ->join('positions', function ($join) use ($now) {
                    $join->on('positions.code', '=', 'assignments.position_code')
                        ->on('positions.company_id', '=', 'assignments.company_id')
                        ->on('positions.tenant_id', '=', 'assignments.tenant_id');
                })
                ->where([
                    ['f_person_lovs.tenant_id', $tenantId],
                    ['f_person_lovs.menu_code', $menuCode]

                ])
                ->whereRaw('LOWER(CONCAT(f_person_lovs.first_name,\' \', f_person_lovs.last_name)) like ?', [$searchString]);

        return $query->distinct()->get();
    }

    public function searchCustomSA($query)
    {
        $now = Carbon::now();
        $searchString = strtolower("%$query%");
        $query =
            DB::table('persons')
                ->selectRaw(
                    'persons.id as "id",' .
                    'assignments.employee_id as "employeeId",' .
                    'assignments.eff_begin as "assignBegin",' .
                    'assignments.lov_acty as "lovActy",' .
                    'assignments.unit_code as "unitCode",' .
                    'persons.file_photo as "filePhoto",' .
                    '(CONCAT(persons.first_name,\' \', persons.last_name)) as "fullName",' .
                    'positions.name as "position"'
                )
                ->join('assignments', function ($join) use ($now) {
                    $join->on('assignments.person_id', '=', 'persons.id')
                        ->on('assignments.tenant_id', '=', 'persons.tenant_id')
                        ->where([
                            ['assignments.eff_begin', '<=', $now],
                            ['assignments.eff_end', '>=', $now],
                            ['assignments.is_primary', '=', true],
                            ['assignments.lov_asta', 'ACT'],
                            ['assignments.company_id', $this->requester->getCompanyId()]
                        ]);
                })
                ->join('positions', function ($join) use ($now) {
                    $join->on('positions.code', '=', 'assignments.position_code')
                        ->on('positions.company_id', '=', 'assignments.company_id')
                        ->on('positions.tenant_id', '=', 'assignments.tenant_id');
                })
                ->where([
                    ['persons.tenant_id', $this->requester->getTenantId()],
                    ['persons.eff_begin', '<=', $now],
                    ['persons.eff_end', '>=', $now]

                ])
                ->whereRaw('LOWER(CONCAT(persons.first_name,\' \', persons.last_name)) like ?', [$searchString]);

        return $query->distinct()->get();
    }

    /**
     * Insert data Person to DB
     * @param  array obj
     */
    public function save($obj)
    {
        $obj['created_by'] = $this->requester->getUserId();
        $obj['created_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'persons', $obj);

        return DB::table('persons')->insertGetId($obj);
    }

    /**
     * Update data Person to DB
     * @param $personId
     * @param $obj
     */
    public function update($personId, $effBegin, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'persons', $obj);

        DB::table('persons')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['eff_begin', $effBegin],
                ['id', $personId]
            ])
            ->update($obj);
    }

    /**
     * Update data Person to DB
     * @param $personId
     * @param $obj
     */
    public function updatePerson($personId, $obj)
    {
        $obj['updated_by'] = $this->requester->getUserId();
        $obj['updated_at'] = Carbon::now();

        LogDao::insertLogImpact($this->requester->getLogId(), 'persons', $obj);

        DB::table('persons')
            ->where([
                ['tenant_id', $this->requester->getTenantId()],
                ['id', $personId]
            ])
            ->update($obj);
    }

    /**
     * Delete data Person from DB
     * @param  personId
     */
    public function delete($personId, $effBegin, $effEnd)
    {
        DB::table('persons')->where([
            ['tenant_id', $this->requester->getTenantId()],
            ['eff_begin', $effBegin],
            ['eff_end', $effEnd],
            ['id', $personId]
        ])->delete();
    }

    // public function advancedSearchActiveEmployee($menuCode, $searchData, $offset, $limit, $order, $orderDirection)
    // {
    //     $now = Carbon::now();
    //     $companyId = $this->requester->getCompanyId();
    //     $tenantId = $this->requester->getTenantId();
    //     $userId = $this->requester->getUserId();
    //     $roleIds = $this->requester->getRoleIds();

    //     $roleIds_param = 'array[' . implode(",", $roleIds) . ']';
    //     $builder = new SearchQueryBuilder($searchData, $this->fieldMap);
    //     $builder = $builder->table('persons')
    //         ->distinctOn('persons.id')
    //         ->select(
    //             'persons.id',
    //             'persons.eff_begin',
    //             'assignments.employee_id as "employeeId"',
    //             'assignments.eff_begin as "assignBegin"',
    //             'assignments.eff_end as "assignEnd"',
    //             'assignments.lov_acty as "lovActy"'
    //         )
    //         ->join(DB::raw('f_person_lovs(' . $tenantId . ',' . $companyId . ',\'' . $menuCode . '\',' . $roleIds_param . ')'), function ($join) {
    //             $join->on('f_person_lovs.person_id', '=', 'persons.id');
    //         })
    //         ->join('assignments', function ($join) use ($companyId, $tenantId, $now) {
    //             $join->on('assignments.person_id', '=', 'persons.id')
    //                 ->where([
    //                     ['assignments.tenant_id', $tenantId],
    //                     ['assignments.company_id', $companyId],
    //                     ['assignments.eff_begin', '<=', $now],
    //                     ['assignments.eff_end', '>=', $now],
    //                     ['assignments.lov_asta', 'ACT']
    //                 ])
    //                 ->orderBy('assignments.id', 'DESC');
    //         })
    //         ->leftJoin('person_families', function ($join) use ($companyId, $tenantId) {
    //             $join->on('person_families.person_id', '=', 'persons.id')
    //                 ->where([
    //                     ['person_families.tenant_id', $tenantId]
    //                 ]);
    //         })
    //         ->leftjoin('countries', function ($join) use ($companyId, $tenantId) {
    //             $join->on('countries.code', '=', 'persons.country_code')
    //                 ->where([
    //                     ['countries.tenant_id', $tenantId],
    //                     ['countries.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftJoin('person_languages', function ($join) use ($companyId, $tenantId) {
    //             $join->on('person_languages.person_id', '=', 'persons.id')
    //                 ->where([
    //                     ['person_languages.tenant_id', $tenantId],
    //                 ]);
    //         })
    //         ->leftJoin('lovs as languages', function ($join) use ($companyId, $tenantId) {
    //             $join->on('languages.key_data', '=', 'person_languages.lov_lang')
    //                 ->where([
    //                     ['languages.lov_type_code', 'LANG'],
    //                     ['languages.tenant_id', $tenantId],
    //                     ['languages.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftJoin('lovs as blood_types', function ($join) use ($companyId, $tenantId) {
    //             $join->on('blood_types.key_data', '=', 'persons.lov_blod')
    //                 ->where([
    //                     ['blood_types.lov_type_code', 'BLOD'],
    //                     ['blood_types.tenant_id', $tenantId],
    //                     ['blood_types.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftjoin('lovs as genders', function ($join) use ($companyId, $tenantId) {
    //             $join->on('genders.key_data', '=', 'persons.lov_gndr')
    //                 ->where([
    //                     ['genders.lov_type_code', 'GNDR'],
    //                     ['genders.tenant_id', $tenantId],
    //                     ['genders.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftjoin('lovs as religions', function ($join) use ($companyId, $tenantId) {
    //             $join->on('religions.key_data', '=', 'persons.lov_rlgn')
    //                 ->where([
    //                     ['religions.lov_type_code', 'RLGN'],
    //                     ['religions.tenant_id', $tenantId],
    //                     ['religions.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftjoin('lovs as marital_statuses', function ($join) use ($companyId, $tenantId) {
    //             $join->on('marital_statuses.key_data', '=', 'persons.lov_mars')
    //                 ->where([
    //                     ['marital_statuses.lov_type_code', 'MARS'],
    //                     ['marital_statuses.tenant_id', $tenantId],
    //                     ['marital_statuses.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftjoin('lovs as assignment_statuses', function ($join) use ($companyId, $tenantId) {
    //             $join->on('assignment_statuses.key_data', '=', 'assignments.lov_asta')
    //                 ->where([
    //                     ['assignment_statuses.lov_type_code', 'ASTA'],
    //                     ['assignment_statuses.tenant_id', $tenantId],
    //                     ['assignment_statuses.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftjoin('locations', function ($join) use ($companyId, $tenantId) {
    //             $join->on('assignments.location_code', '=', 'locations.code')
    //                 ->where([
    //                     ['locations.tenant_id', $tenantId],
    //                     ['locations.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftjoin('units', function ($join) use ($companyId, $tenantId) {
    //             $join->on('assignments.unit_code', '=', 'units.code')
    //                 ->where([
    //                     ['units.tenant_id', $tenantId],
    //                     ['units.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftjoin('jobs', function ($join) use ($companyId, $tenantId) {
    //             $join->on('assignments.job_code', '=', 'jobs.code')
    //                 ->where([
    //                     ['jobs.tenant_id', $tenantId],
    //                     ['jobs.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftjoin('positions', function ($join) use ($companyId, $tenantId) {
    //             $join->on('assignments.position_code', '=', 'positions.code')
    //                 ->where([
    //                     ['positions.tenant_id', $tenantId],
    //                     ['positions.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftJoin('employee_statuses', function ($join) use ($companyId, $tenantId) {
    //             $join->on('assignments.employee_status_code', '=', 'employee_statuses.code')
    //                 ->where([
    //                     ['employee_statuses.tenant_id', $tenantId],
    //                     ['employee_statuses.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftJoin('grades', function ($join) use ($companyId, $tenantId) {
    //             $join->on('assignments.grade_code', '=', 'grades.code')
    //                 ->where([
    //                     ['grades.tenant_id', $tenantId],
    //                     ['grades.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftJoin('cost_centers', function ($join) use ($companyId, $tenantId) {
    //             $join->on('assignments.cost_center_code', '=', 'cost_centers.code')
    //                 ->where([
    //                     ['cost_centers.tenant_id', $tenantId],
    //                     ['cost_centers.company_id', $companyId]
    //                 ]);
    //         })
    //         ->leftJoin('persons as supervisors', function ($join) use ($companyId, $tenantId) {
    //             $join->on('supervisors.id', '=', 'assignments.supervisor_id')
    //                 ->where([
    //                     ['supervisors.tenant_id', $tenantId]
    //                 ])
    //                 ->orderBy('persons.eff_end', 'DESC');
    //         })
    //         ->combineWhere([
    //             ['persons.tenant_id', $tenantId],
    //             ['genders.lov_type_code', 'GNDR'],
    //             ['religions.lov_type_code', 'RLGN'],
    //             ['marital_statuses.lov_type_code', 'MARS'],
    //             ['assignment_statuses.lov_type_code', 'ASTA']
    //         ]);

    //     $count = $builder->count();
    //     if ($order && $orderDirection) {
    //         $builder->orderBy($order, $orderDirection);
    //     }
    //     if ($offset && $limit) {
    //         $builder->limit($limit)->offset($offset);
    //     }
    //     $result = $builder->hist('persons.id', 'persons.eff_begin');
    //     return [$result, $count];
    // }

    public function resign()
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        return
            DB::table('assignments')
                ->select(
                    'units.name as unitName',
                    DB::raw('unit_code , COUNT(*) AS TOTAL')
                )
                ->leftJoin('units', function ($join) {
                    $join->on('assignments.unit_code', '=', 'units.code');
                })
                ->where([
                    ['assignments.tenant_id', $this->requester->getTenantId()],
                    ['assignments.company_id', $companyId],
                    ['assignments.lov_acty', '=', 'TERM'],
                    ['assignments.eff_begin', '<=', Carbon::now()],
                    ['assignments.eff_end', '>=', Carbon::now()]
                ])
                ->groupBy('unit_code', 'units.name')
                ->get();
    }

    public function advancedSearch($menuCode, $searchData, $offset, $limit, $order, $orderDirection, $activeStatus, $localSearch)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $userId = $this->requester->getUserId();
        $roleIds = $this->requester->getRoleIds();

        $roleIds_param = 'array[' . implode(",", $roleIds) . ']';
        $builder = new SearchQueryBuilder($searchData, $this->fieldMap);
        $now = Carbon::now();
        $searchString = strtolower("%$localSearch%");
        $builder = $builder->table('persons')
            ->distinctOn('persons.id')
            ->select(
                'persons.id',
                'persons.eff_begin',
                'assignments.employee_id as "employeeId"',
                'assignments.eff_begin as "assignBegin"',
                'assignments.eff_end as "assignEnd"',
                'assignments.lov_acty as "lovActy"'
            )
            ->join(DB::raw('f_person_lovs(' . $tenantId . ',' . $companyId . ',\'' . $menuCode . '\',' . $roleIds_param . ')'), function ($join) {
                $join->on('f_person_lovs.person_id', '=', 'persons.id');
            })
            ->join('assignments', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('assignments.person_id', '=', 'persons.id')
                    ->where([
                        ['assignments.is_primary', true],
                        ['assignments.eff_begin', '<=', $now],
                        ['assignments.eff_end', '>=', $now],
                        ['assignments.tenant_id', $tenantId],
                        ['assignments.company_id', $companyId]
                    ]);
            })
            ->leftJoin('person_families', function ($join) use ($companyId, $tenantId) {
                $join->on('person_families.person_id', '=', 'persons.id')
                    ->where([
                        ['person_families.tenant_id', $tenantId]
                    ]);
            })
            ->leftjoin('countries', function ($join) use ($companyId, $tenantId) {
                $join->on('countries.code', '=', 'persons.country_code')
                    ->where([
                        ['countries.tenant_id', $tenantId],
                        ['countries.company_id', $companyId]
                    ]);
            })
            ->leftJoin('person_languages', function ($join) use ($companyId, $tenantId) {
                $join->on('person_languages.person_id', '=', 'persons.id')
                    ->where([
                        ['person_languages.tenant_id', $tenantId],
                    ]);
            })
            ->leftJoin('lovs as languages', function ($join) use ($companyId, $tenantId) {
                $join->on('languages.key_data', '=', 'person_languages.lov_lang')
                    ->where([
                        ['languages.lov_type_code', 'LANG'],
                        ['languages.tenant_id', $tenantId],
                        ['languages.company_id', $companyId]
                    ]);
            })
            ->leftJoin('lovs as blood_types', function ($join) use ($companyId, $tenantId) {
                $join->on('blood_types.key_data', '=', 'persons.lov_blod')
                    ->where([
                        ['blood_types.lov_type_code', 'BLOD'],
                        ['blood_types.tenant_id', $tenantId],
                        ['blood_types.company_id', $companyId]
                    ]);
            })
            ->leftjoin('lovs as genders', function ($join) use ($companyId, $tenantId) {
                $join->on('genders.key_data', '=', 'persons.lov_gndr')
                    ->where([
                        ['genders.lov_type_code', 'GNDR'],
                        ['genders.tenant_id', $tenantId],
                        ['genders.company_id', $companyId]
                    ]);
            })
            ->leftjoin('lovs as religions', function ($join) use ($companyId, $tenantId) {
                $join->on('religions.key_data', '=', 'persons.lov_rlgn')
                    ->where([
                        ['religions.lov_type_code', 'RLGN'],
                        ['religions.tenant_id', $tenantId],
                        ['religions.company_id', $companyId]
                    ]);
            })
            ->leftjoin('lovs as marital_statuses', function ($join) use ($companyId, $tenantId) {
                $join->on('marital_statuses.key_data', '=', 'persons.lov_mars')
                    ->where([
                        ['marital_statuses.lov_type_code', 'MARS'],
                        ['marital_statuses.tenant_id', $tenantId],
                        ['marital_statuses.company_id', $companyId]
                    ]);
            })
            ->leftjoin('lovs as assignment_statuses', function ($join) use ($companyId, $tenantId) {
                $join->on('assignment_statuses.key_data', '=', 'assignments.lov_asta')
                    ->where([
                        ['assignment_statuses.lov_type_code', 'ASTA'],
                        ['assignment_statuses.tenant_id', $tenantId],
                        ['assignment_statuses.company_id', $companyId]
                    ]);
            })
            ->leftjoin('locations', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.location_code', '=', 'locations.code')
                    ->where([
                        ['locations.tenant_id', $tenantId],
                        ['locations.company_id', $companyId]
                    ]);
            })
            ->leftjoin('units', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.unit_code', '=', 'units.code')
                    ->where([
                        ['units.tenant_id', $tenantId],
                        ['units.company_id', $companyId]
                    ]);
            })
            ->leftjoin('jobs', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.job_code', '=', 'jobs.code')
                    ->where([
                        ['jobs.tenant_id', $tenantId],
                        ['jobs.company_id', $companyId]
                    ]);
            })
            ->leftjoin('positions', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.position_code', '=', 'positions.code')
                    ->where([
                        ['positions.tenant_id', $tenantId],
                        ['positions.company_id', $companyId]
                    ]);
            })
            ->leftJoin('employee_statuses', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.employee_status_code', '=', 'employee_statuses.code')
                    ->where([
                        ['employee_statuses.tenant_id', $tenantId],
                        ['employee_statuses.company_id', $companyId]
                    ]);
            })
            ->leftJoin('grades', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.grade_code', '=', 'grades.code')
                    ->where([
                        ['grades.tenant_id', $tenantId],
                        ['grades.company_id', $companyId]
                    ]);
            })
            ->leftJoin('cost_centers', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.cost_center_code', '=', 'cost_centers.code')
                    ->where([
                        ['cost_centers.tenant_id', $tenantId],
                        ['cost_centers.company_id', $companyId]
                    ]);
            })
            ->leftJoin('persons as supervisors', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('supervisors.id', '=', 'assignments.supervisor_id')
                    ->where([
                        ['supervisors.tenant_id', $tenantId],
                        ['supervisors.eff_begin', '<=', $now],
                        ['supervisors.eff_end', '>=', $now]
                    ])
                    ->orderBy('persons.eff_end', 'DESC');;
            })
            ->leftJoin('cf_person_basic_info as customfield', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('customfield.person_id', '=', 'persons.id')
                    ->where([
                        ['customfield.tenant_id', $tenantId],
                        ['customfield.company_id', $companyId],
                        ['customfield.eff_begin', '<=', $now],
                        ['customfield.eff_end', '>=', $now]
                    ]);
            })
            ->leftJoin('person_co', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('person_co.person_id', '=', 'persons.id')
                    ->where([
                        ['person_co.tenant_id', $tenantId],
                        ['person_co.company_id', $companyId],
                        ['person_co.eff_begin', '<=', $now],
                        ['person_co.eff_end', '>=', $now]
                    ]);
            })
            ->leftJoin('person_co_fields', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('person_co_fields.person_co_id', '=', 'person_co.id')
                    ->where([
                        ['person_co_fields.tenant_id', $tenantId],
                        ['person_co_fields.company_id', $companyId]
                    ]);
            })
            ->combineWhere([
                ['persons.tenant_id', $tenantId],
                ['persons.eff_begin', '<=', $now],
                ['persons.eff_end', '>=', $now]
            ]);

        if ($activeStatus == 'active') {
            $builder->where('assignments.lov_asta', '=', 'ACT');
        } else if ($activeStatus == 'notActive') {
            $builder->where('assignments.lov_asta', '!=', 'ACT');
        }

        if ($localSearch) {
            $builder->whereRaw('LOWER(CONCAT(persons.first_name,\' \',persons.last_name,\' \',persons.mobile,\' \',persons.email,\' \',positions.name,\' \',locations.name,\' \',assignments.employee_id)) like ?', [$searchString]);
        }
        $count = $builder->count();
        if ($order && $orderDirection) {
            $builder->orderBy($order, $orderDirection);
        }
        if (($offset && $limit) || $offset == 0) {
            $builder->limit($limit)->offset($offset);
        }
        $result = $builder->hist('persons.id', 'persons.eff_begin');
        return [$result, $count];
    }

    public function advancedSearchUnit($searchData, $offset, $limit, $order, $orderDirection, $activeStatus, $localSearch, $unitCode, $personId)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $builder = new SearchQueryBuilder($searchData, $this->fieldMap);
        $now = Carbon::now();
        $searchString = strtolower("%$localSearch%");

        $builder = $builder->table('persons')
            ->distinctOn('persons.id')
            ->select(
                'persons.id',
                'persons.eff_begin',
                'assignments.employee_id as "employeeId"',
                'assignments.eff_begin as "assignBegin"',
                'assignments.eff_end as "assignEnd"',
                'assignments.lov_acty as "lovActy"'
            )
            ->join('assignments', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('assignments.person_id', '=', 'persons.id')
                    ->where([
                        ['assignments.is_primary', true],
                        ['assignments.lov_asta', '=', 'ACT'],
                        ['assignments.eff_begin', '<=', $now],
                        ['assignments.eff_end', '>=', $now],
                        ['assignments.tenant_id', $tenantId],
                        ['assignments.company_id', $companyId]
                    ]);
            })
            ->leftJoin('employee_statuses', function ($join) use ($companyId, $tenantId) {
                $join->on('employee_statuses.code', '=', 'assignments.employee_status_code')
                    ->where([
                        ['employee_statuses.tenant_id', $tenantId],
                        ['employee_statuses.company_id', $companyId]
                    ]);
            })
            ->leftjoin('positions', function ($join) use ($companyId, $tenantId) {
                $join->on('positions.code', '=', 'assignments.position_code')
                    ->where([
                        ['positions.tenant_id', $tenantId],
                        ['positions.company_id', $companyId]
                    ]);
            })
            ->leftJoin('persons as supervisors', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('supervisors.id', '=', 'assignments.supervisor_id')
                    ->where([
                        ['supervisors.tenant_id', $tenantId],
                        ['supervisors.eff_begin', '<=', $now],
                        ['supervisors.eff_end', '>=', $now],
                    ]);
            })
            ->leftjoin('units', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.unit_code', '=', 'units.code')
                    ->where([
                        ['units.tenant_id', $tenantId],
                        ['units.company_id', $companyId]
                    ]);
            })
            ->combineWhere([
                ['units.code', $unitCode],
                ['persons.id', '!=', $personId],
                ['persons.tenant_id', $tenantId],
                ['persons.eff_begin', '<=', $now],
                ['persons.eff_end', '>=', $now]
            ]);

        if ($localSearch) {
            $builder->whereRaw('LOWER(CONCAT(persons.first_name,\' \',persons.last_name,\' \',persons.mobile,\' \',persons.email,\' \',positions.name,\' \',locations.name,\' \',assignments.employee_id)) like ?', [$searchString]);
        }
        $count = $builder->count();
        if ($order && $orderDirection) {
            $builder->orderBy($order, $orderDirection);
        }
        if (($offset && $limit) || $offset == 0) {
            $builder->limit($limit)->offset($offset);
        }
        $result = $builder->hist('persons.id', 'persons.eff_begin');
        return [$result, $count];
    }

    public function advancedSearchSA($searchData, $offset, $limit, $order, $orderDirection, $activeStatus, $localSearch)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $userId = $this->requester->getUserId();
        $builder = new SearchQueryBuilder($searchData, $this->fieldMap);
        $now = Carbon::now();
        $searchString = strtolower("%$localSearch%");
        $builder = $builder->table('persons')
            ->distinctOn('persons.id')
            ->select(
                'persons.id',
                'persons.eff_begin',
                'assignments.employee_id as "employeeId"',
                'assignments.eff_begin as "assignBegin"',
                'assignments.eff_end as "assignEnd"',
                'assignments.lov_acty as "lovActy"'
            )
            ->join('assignments', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('assignments.person_id', '=', 'persons.id')
                    ->where([
                        ['assignments.is_primary', true],
                        ['assignments.eff_begin', '<=', $now],
                        ['assignments.eff_end', '>=', $now],
                        ['assignments.tenant_id', $tenantId],
                        ['assignments.company_id', $companyId]
                    ]);
            })
            ->leftJoin('person_families', function ($join) use ($companyId, $tenantId) {
                $join->on('person_families.person_id', '=', 'persons.id')
                    ->where([
                        ['person_families.tenant_id', $tenantId]
                    ]);
            })
            ->leftjoin('countries', function ($join) use ($companyId, $tenantId) {
                $join->on('countries.code', '=', 'persons.country_code')
                    ->where([
                        ['countries.tenant_id', $tenantId],
                        ['countries.company_id', $companyId]
                    ]);
            })
            ->leftJoin('person_languages', function ($join) use ($companyId, $tenantId) {
                $join->on('person_languages.person_id', '=', 'persons.id')
                    ->where([
                        ['person_languages.tenant_id', $tenantId],
                    ]);
            })
            ->leftJoin('lovs as languages', function ($join) use ($companyId, $tenantId) {
                $join->on('languages.key_data', '=', 'person_languages.lov_lang')
                    ->where([
                        ['languages.lov_type_code', 'LANG'],
                        ['languages.tenant_id', $tenantId],
                        ['languages.company_id', $companyId]
                    ]);
            })
            ->leftJoin('lovs as blood_types', function ($join) use ($companyId, $tenantId) {
                $join->on('blood_types.key_data', '=', 'persons.lov_blod')
                    ->where([
                        ['blood_types.lov_type_code', 'BLOD'],
                        ['blood_types.tenant_id', $tenantId],
                        ['blood_types.company_id', $companyId]
                    ]);
            })
            ->leftjoin('lovs as genders', function ($join) use ($companyId, $tenantId) {
                $join->on('genders.key_data', '=', 'persons.lov_gndr')
                    ->where([
                        ['genders.lov_type_code', 'GNDR'],
                        ['genders.tenant_id', $tenantId],
                        ['genders.company_id', $companyId]
                    ]);
            })
            ->leftjoin('lovs as religions', function ($join) use ($companyId, $tenantId) {
                $join->on('religions.key_data', '=', 'persons.lov_rlgn')
                    ->where([
                        ['religions.lov_type_code', 'RLGN'],
                        ['religions.tenant_id', $tenantId],
                        ['religions.company_id', $companyId]
                    ]);
            })
            ->leftjoin('lovs as marital_statuses', function ($join) use ($companyId, $tenantId) {
                $join->on('marital_statuses.key_data', '=', 'persons.lov_mars')
                    ->where([
                        ['marital_statuses.lov_type_code', 'MARS'],
                        ['marital_statuses.tenant_id', $tenantId],
                        ['marital_statuses.company_id', $companyId]
                    ]);
            })
            ->leftjoin('lovs as assignment_statuses', function ($join) use ($companyId, $tenantId) {
                $join->on('assignment_statuses.key_data', '=', 'assignments.lov_asta')
                    ->where([
                        ['assignment_statuses.lov_type_code', 'ASTA'],
                        ['assignment_statuses.tenant_id', $tenantId],
                        ['assignment_statuses.company_id', $companyId]
                    ]);
            })
            ->leftjoin('locations', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.location_code', '=', 'locations.code')
                    ->where([
                        ['locations.tenant_id', $tenantId],
                        ['locations.company_id', $companyId]
                    ]);
            })
            ->leftjoin('units', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.unit_code', '=', 'units.code')
                    ->where([
                        ['units.tenant_id', $tenantId],
                        ['units.company_id', $companyId]
                    ]);
            })
            ->leftjoin('jobs', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.job_code', '=', 'jobs.code')
                    ->where([
                        ['jobs.tenant_id', $tenantId],
                        ['jobs.company_id', $companyId]
                    ]);
            })
            ->leftjoin('positions', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.position_code', '=', 'positions.code')
                    ->where([
                        ['positions.tenant_id', $tenantId],
                        ['positions.company_id', $companyId]
                    ]);
            })
            ->leftJoin('employee_statuses', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.employee_status_code', '=', 'employee_statuses.code')
                    ->where([
                        ['employee_statuses.tenant_id', $tenantId],
                        ['employee_statuses.company_id', $companyId]
                    ]);
            })
            ->leftJoin('grades', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.grade_code', '=', 'grades.code')
                    ->where([
                        ['grades.tenant_id', $tenantId],
                        ['grades.company_id', $companyId]
                    ]);
            })
            ->leftJoin('cost_centers', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.cost_center_code', '=', 'cost_centers.code')
                    ->where([
                        ['cost_centers.tenant_id', $tenantId],
                        ['cost_centers.company_id', $companyId]
                    ]);
            })
            ->leftJoin('persons as supervisors', function ($join) use ($companyId, $tenantId) {
                $join->on('supervisors.id', '=', 'assignments.supervisor_id')
                    ->where([
                        ['supervisors.tenant_id', $tenantId]
                    ])
                    ->orderBy('persons.eff_end', 'DESC');
            })
            ->leftJoin('cf_person_basic_info as customfield', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('customfield.person_id', '=', 'persons.id')
                    ->where([
                        ['customfield.tenant_id', $tenantId],
                        ['customfield.eff_begin', '<=', $now],
                        ['customfield.eff_end', '>=', $now]
                    ]);
            })
            ->leftJoin('person_co', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('person_co.person_id', '=', 'persons.id')
                    ->where([
                        ['person_co.tenant_id', $tenantId],
                        ['person_co.company_id', $companyId],
                        ['person_co.eff_begin', '<=', $now],
                        ['person_co.eff_end', '>=', $now]
                    ]);
            })
            ->join('person_co_fields', 'person_co_fields.person_co_id', 'person_co.id')
            ->combineWhere([
                ['persons.tenant_id', $tenantId],
                ['persons.eff_begin', '<=', $now],
                ['persons.eff_end', '>=', $now]
            ]);

        if ($activeStatus == 'active') {
            $builder->where('assignments.lov_asta', '=', 'ACT');
        } else if ($activeStatus == 'notActive') {
            $builder->where('assignments.lov_asta', '!=', 'ACT');
        }

        if ($localSearch) {
            $builder->whereRaw('LOWER(CONCAT(persons.first_name,\' \',persons.last_name,\' \',persons.mobile,\' \',persons.email,\' \',positions.name,\' \',locations.name,\' \',assignments.employee_id)) like ?', [$searchString]);
        }
        $count = $builder->count();
        if ($order && $orderDirection) {
            $builder->orderBy($order, $orderDirection);
        }
        if (($offset && $limit) || $offset == 0) {
            $builder->limit($limit)->offset($offset);
        }
        $result = $builder->hist('persons.id', 'persons.eff_begin');
        return [$result, $count];
    }

    public function advancedSearchSubordinates($searchData, $offset, $limit, $order, $orderDirection, $activeStatus, $localSearch)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $builder = new SearchQueryBuilder($searchData, $this->fieldMap);
        $now = Carbon::now();
        $searchString = strtolower("%$localSearch%");
        $builder = $builder->table('persons')
            ->distinctOn('persons.id')
            ->select(
                'persons.id',
                'persons.eff_begin',
                'assignments.employee_id as "employeeId"',
                'assignments.eff_begin as "assignBegin"',
                'assignments.eff_end as "assignEnd"',
                'assignments.lov_acty as "lovActy"'
            )
            ->join('assignments', function ($join) use ($companyId, $tenantId, $now) {
                $join->on('assignments.person_id', '=', 'persons.id')
                    ->where([
                        ['assignments.is_primary', true],
                        ['assignments.eff_begin', '<=', $now],
                        ['assignments.eff_end', '>=', $now],
                        ['assignments.tenant_id', $tenantId],
                        ['assignments.company_id', $companyId]
                    ]);
            })
            ->leftjoin('units', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.unit_code', '=', 'units.code')
                    ->where([
                        ['units.tenant_id', $tenantId],
                        ['units.company_id', $companyId]
                    ]);
            })
            ->leftjoin('positions', function ($join) use ($companyId, $tenantId) {
                $join->on('assignments.position_code', '=', 'positions.code')
                    ->where([
                        ['positions.tenant_id', $tenantId],
                        ['positions.company_id', $companyId]
                    ]);
            })
            ->combineWhere([
                ['persons.tenant_id', $tenantId],
                ['persons.eff_begin', '<=', $now],
                ['persons.eff_end', '>=', $now]
            ]);

        if ($activeStatus == 'active') {
            $builder->where('assignments.lov_asta', '=', 'ACT');
        } else if ($activeStatus == 'notActive') {
            $builder->where('assignments.lov_asta', '!=', 'ACT');
        }

        if ($localSearch) {
            $builder->whereRaw('LOWER(CONCAT(persons.first_name,\' \',persons.last_name,\' \',persons.mobile,\' \',persons.email,\' \',positions.name,\' \',locations.name,\' \',assignments.employee_id)) like ?', [$searchString]);
        }
        $count = $builder->count();
        if ($order && $orderDirection) {
            $builder->orderBy($order, $orderDirection);
        }
        if (($offset && $limit) || $offset == 0) {
            $builder->limit($limit)->offset($offset);
        }
        $result = $builder->hist('persons.id', 'persons.eff_begin');
        return [$result, $count];
    }

    public function getDirectSupervisor($personId)
    {
        $now = Carbon::now();
        return
            DB::table('persons')
                ->select(
                    'supervisors.id',
                    'supervisors.first_name as firstName',
                    'supervisors.last_name as lastName',
                    'supervisors.file_photo as filePhoto',
                    'positions.name as position',
                    'units.name as unit'
                )
                ->join('assignments as person_assignments', 'person_assignments.person_id', '=', 'persons.id')
                ->join('persons as supervisors', 'supervisors.id', 'person_assignments.supervisor_id')
                ->join('assignments as supervisor_assignments', 'supervisor_assignments.person_id', '=', 'supervisors.id')
                ->join('positions', 'positions.code', '=', 'supervisor_assignments.position_code')
                ->join('units', 'units.code', '=', 'supervisor_assignments.unit_code')
                ->where([
                    ['persons.tenant_id', $this->requester->getTenantId()],
                    ['persons.id', $personId],
                    ['person_assignments.eff_begin', '<=', $now],
                    ['person_assignments.eff_end', '>=', $now],
                    ['supervisor_assignments.eff_begin', '<=', $now],
                    ['supervisor_assignments.eff_end', '>=', $now]
                ])
                ->first();
    }

    public function getDirectSubordinates($personId)
    {
        $now = Carbon::now();
        return
            DB::table('persons')
                ->select(
                    'persons.id',
                    'persons.first_name as firstName',
                    'persons.last_name as lastName',
                    'persons.file_photo as filePhoto',
                    'positions.name as position',
                    'units.name as unit'
                )
                ->distinct()
                ->join('assignments', 'assignments.person_id', '=', 'persons.id')
                ->join('positions', 'positions.code', '=', 'assignments.position_code')
                ->join('units', 'units.code', '=', 'assignments.unit_code')
                ->where([
                    ['persons.tenant_id', $this->requester->getTenantId()],
                    ['assignments.supervisor_id', $personId],
                    ['assignments.eff_begin', '<=', $now],
                    ['assignments.eff_end', '>=', $now]
                ])
                ->get();
    }

    public function getDefaultSuperior($positionCode)
    {
        $now = Carbon::now();
        return
            DB::table('positions')
                ->select('persons.id', 'persons.first_name as firstName', 'persons.last_name as lastName')
                ->join('units', 'units.code', '=', 'positions.unit_code')
                ->join('positions as supervisor_positions', 'supervisor_positions.unit_code', '=', 'units.code')
                ->join('assignments', 'assignments.position_code', '=', 'supervisor_positions.code')
                ->join('persons', 'persons.id', '=', 'assignments.person_id')
                ->where([
                    ['persons.tenant_id', $this->requester->getTenantId()],
                    ['positions.code', '=', $positionCode],
                    ['assignments.eff_begin', '<=', $now],
                    ['assignments.eff_end', '>=', $now],
                    ['supervisor_positions.is_head', '=', true]
                ])
                ->first();
    }

    /**
     * get one data for a location
     * this function is used in education institution and
     * specialization Used By Employee
     * @param  string $locationCode
     */
    public function getOnePersonById($personId)
    {
        return
            DB::table('persons')
                ->select(
                    'persons.id',
                    'persons.first_name as firstName',
                    'persons.last_name as lastName',
                    'persons.email'
                )
                ->where([
                    ['persons.tenant_id', $this->requester->getTenantId()],
                    ['persons.id', $personId]
                ])
                ->first();
    }

    public function getMany($personIds)
    {
        $tenantId = $this->requester->getTenantId();
        $now = Carbon::now();

        return
            DB::table('persons')
                ->select(
                    'persons.id',
                    'persons.first_name as firstName',
                    'persons.last_name as lastName'
                )
                ->where([
                    ['persons.tenant_id', $tenantId],
                    ['persons.eff_begin', '<=', $now],
                    ['persons.eff_end', '>=', $now]
                ])
                ->whereIn('persons.id', $personIds)
                ->get();
    }

    public function getManyByEmployeeId($employeeIds)
    {
        $companyId = $this->requester->getCompanyId();
        $tenantId = $this->requester->getTenantId();
        $now = Carbon::now();

        return
            DB::table('persons')
                ->select(
                    'persons.id',
                    'persons.first_name as firstName',
                    'persons.last_name as lastName',
                    'assignments.employee_id as employeeId',
                    'positions.name as positionName',
                    'jobs.name as jobName',
                    'units.name as unitName',
                    'locations.name as locationName'
                )
                ->join('assignments', function ($join) use ($companyId, $tenantId, $now) {
                    $join->on('assignments.person_id', '=', 'persons.id')
                        ->where([
                            ['assignments.is_primary', true],
                            ['assignments.eff_begin', '<=', $now],
                            ['assignments.eff_end', '>=', $now],
                            ['assignments.tenant_id', $tenantId],
                            ['assignments.company_id', $companyId]
                        ]);
                })
                ->join('positions', function ($join) use ($companyId, $tenantId) {
                    $join->on('positions.code', '=', 'assignments.position_code')
                        ->where([
                            ['positions.tenant_id', $tenantId],
                            ['positions.company_id', $companyId]
                        ]);
                })
                ->join('units', function ($join) use ($companyId, $tenantId) {
                    $join->on('units.code', '=', 'assignments.unit_code')
                        ->where([
                            ['units.tenant_id', $tenantId],
                            ['units.company_id', $companyId]
                        ]);
                })
                ->join('jobs', function ($join) use ($companyId, $tenantId) {
                    $join->on('jobs.code', '=', 'assignments.job_code')
                        ->where([
                            ['jobs.tenant_id', $tenantId],
                            ['jobs.company_id', $companyId]
                        ]);
                })
                ->join('locations', function ($join) use ($companyId, $tenantId) {
                    $join->on('locations.code', '=', 'assignments.location_code')
                        ->where([
                            ['locations.tenant_id', $tenantId],
                            ['locations.company_id', $companyId]
                        ]);
                })
                ->where([
                    ['persons.tenant_id', $tenantId],
                    ['persons.eff_begin', '<=', $now],
                    ['persons.eff_end', '>=', $now]
                ])
                ->whereIn('assignments.employee_id', $employeeIds)
                ->distinct()
                ->get();
    }

    /*
    |-----------------------------------
    | get fullname employee dan posisi
    |-----------------------------------
    |
    |
    */
    public function getFullNameAndPosition($employeeId)
    {
        $now = Carbon::now();
        return DB::table('persons')
            ->selectRaw(
                '(CONCAT(persons.first_name,\' \', persons.last_name)) as "fullName",' .
                'positions.description as "position"'
            )
            ->join('assignments', 'assignments.person_id', '=', 'persons.id')
            ->join('positions', 'positions.code', '=', 'assignments.position_code')
            ->where([
                ['persons.tenant_id', $this->requester->getTenantId()],
                ['assignments.eff_begin', '<=', $now],
                ['assignments.eff_end', '>=', $now],
                ['assignments.employee_id', $employeeId],
                ['assignments.company_id', $this->requester->getCompanyId()]
            ])
            ->first();

    }
}
