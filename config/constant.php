<?php

//define any constants value here.
//for using in code, just simply use: config('constant.key')
return [
    'admin_app_id' => 1,
    'ess_app_id' => 2,
    'defaultEndDate' => '9999-12-31',
    'fieldMap' => [
        'PI1' => [
            'table' => 'persons',
            'name' => 'name',
            'column' => 'CONCAT(!!.first_name, \' \', !!.last_name)',
            'rawWhere' => true
        ],
        'PI2' => [
            'table' => 'persons',
            'name' => 'birthDay',
            'column' => 'DATE_PART(\'day\', !!.birth_date)',
            'rawWhere' => true
        ],
        'PI3' => [
            'table' => 'persons',
            'name' => 'birthMonth',
            'column' => 'DATE_PART(\'month\', !!.birth_date)',
            'rawWhere' => true
        ],
        'PI4' => [
            'table' => 'persons',
            'name' => 'age',
            'column' => 'DATE_PART(\'year\', now()) - DATE_PART(\'year\', !!.birth_date)',
            'rawWhere' => true
        ],
        'PI5' => [
            'table' => 'persons',
            'name' => 'nationality',
            'column' => '!!.country_code'
        ],
        'PI6' => [
            'table' => 'persons',
            'name' => 'bloodType',
            'column' => '!!.lov_blod'
        ],
        'PI7' => [
            'table' => 'persons',
            'name' => 'gender',
            'column' => '!!.lov_gndr'
        ],
        'PI8' => [
            'table' => 'persons',
            'name' => 'religion',
            'column' => '!!.lov_rlgn'
        ],
        'PI9' => [
            'table' => 'persons',
            'name' => 'maritalStatus',
            'column' => '!!.lov_mars'
        ],
        'PI10' => [
            'table' => 'persons',
            'name' => 'birthDate',
            'column' => '!!.birth_date'
        ],
        'PI11' => [
            'table' => 'persons',
            'name' => 'mobile',
            'column' => '!!.mobile'
        ],
        'PI12' => [
            'table' => 'persons',
            'name' => 'email',
            'column' => '!!.email'
        ],
        'PI13' => [
            'table' => 'supervisors',
            'name' => 'supervisorName',
            'column' => '!!.first_name || \' \' || !!.last_name',
            'rawWhere' => true
        ],
        'PF1' => [
            'table' => 'person_families',
            'name' => 'children',
            'column' => '(select count(!!.id) from !! where !!.lov_famr = \'SON\' or !!.lov_famr = \'DAU\')',
            'having' => true
        ],
        'PL1' => [
            'table' => 'person_languages',
            'name' => 'language',
            'column' => '!!.lov_lang'
        ],
        'CT1' => [
            'table' => 'countries',
            'name' => 'nationality',
            'column' => '!!.nationality'
        ],
        'LV1' => [
            'table' => 'blood_types',
            'name' => 'bloodType',
            'column' => '!!.val_data'
        ],
        'LV2' => [
            'table' => 'genders',
            'name' => 'gender',
            'column' => '!!.val_data'
        ],
        'LV3' => [
            'table' => 'religions',
            'name' => 'religion',
            'column' => '!!.val_data'
        ],
        'LV4' => [
            'table' => 'marital_statuses',
            'name' => 'maritalStatus',
            'column' => '!!.val_data'
        ],
        'LV5' => [
            'table' => 'languages',
            'name' => 'language',
            'column' => '!!.val_data'
        ],
        'LV6' => [
            'table' => 'assignment_statuses',
            'name' => 'assignmentStatus',
            'column' => '!!.val_data'
        ],
        'AS2' => [
            'table' => 'assignments',
            'name' => 'location',
            'column' => '!!.location_code'
        ],
        'AS4' => [
            'table' => 'assignments',
            'name' => 'unit',
            'column' => '!!.unit_code'
        ],
        'AS5' => [
            'table' => 'assignments',
            'name' => 'job',
            'column' => '!!.job_code'
        ],
        'AS7' => [
            'table' => 'assignments',
            'name' => 'grade',
            'column' => '!!.grade_code'
        ],
        'AS8' => [
            'table' => 'assignments',
            'name' => 'costCenter',
            'column' => '!!.cost_center_code'
        ],
        'AS9' => [
            'table' => 'assignments',
            'name' => 'assignmentStatus',
            'column' => '!!.lov_asta'
        ],
        'AS13' => [
            'table' => 'assignments',
            'name' => 'employeeStatus',
            'column' => '!!.employee_status_code'
        ],
        'AS14' => [
            'table' => 'assignments',
            'name' => 'employeeId',
            'column' => '!!.employee_id'
        ],
        'LC1' => [
            'table' => 'locations',
            'name' => 'location',
            'column' => '!!.name'
        ],
        'UN1' => [
            'table' => 'units',
            'name' => 'unit',
            'column' => '!!.name'
        ],
        'JB1' => [
            'table' => 'jobs',
            'name' => 'job',
            'column' => '!!.name'
        ],
        'PS1' => [
            'table' => 'positions',
            'name' => 'position',
            'column' => '!!.name'
        ],
        'GR1' => [
            'table' => 'grades',
            'name' => 'grade',
            'column' => '!!.name'
        ],
        'CC1' => [
            'table' => 'cost_centers',
            'name' => 'costCenter',
            'column' => '!!.name'
        ],
        'ET1' => [
            'table' => 'employee_statuses',
            'name' => 'employeeStatus',
            'column' => '!!.name'
        ],
        'c1' => [
            'table' => 'customfield',
            'name' => 'c1',
            'column' => '!!.c1'
        ],
        'c2' => [
            'table' => 'customfield',
            'name' => 'c2',
            'column' => '!!.c2'
        ],
        'c3' => [
            'table' => 'customfield',
            'name' => 'c3',
            'column' => '!!.c3'
        ],
        'c4' => [
            'table' => 'customfield',
            'name' => 'c4',
            'column' => '!!.c4'
        ],
        'c5' => [
            'table' => 'customfield',
            'name' => 'c5',
            'column' => '!!.c5'
        ],
        'c6' => [
            'table' => 'customfield',
            'name' => 'c6',
            'column' => '!!.c6'
        ],
        'c7' => [
            'table' => 'customfield',
            'name' => 'c7',
            'column' => '!!.c7'
        ],
        'c8' => [
            'table' => 'customfield',
            'name' => 'c8',
            'column' => '!!.c8'
        ],
        'c9' => [
            'table' => 'customfield',
            'name' => 'c9',
            'column' => '!!.c9'
        ],
        'c10' => [
            'table' => 'customfield',
            'name' => 'c10',
            'column' => '!!.c10'
        ],
        'value' => [
            'table' => 'person_co_fields',
            'name' => 'value',
            'column' => '!!.value'
        ],
    ]
];
