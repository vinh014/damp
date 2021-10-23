<?php
/**
 * 
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license, please send an email
 * to vinhnv@live.com so i can send you a copy immediately.
 *
 * @copyright Copyright (c) 2011-2015 Nguyen Van Vinh (vinhnv@live.com)
 */
return array
(
    'active' => '1',
    'multi' => '2',
    'fixMulti' => '1',
    'field' => array
    (
        array
        (
            'type' => 'serial',
        ),
        array
        (
            'type' => 'id',
            'name' => 'company_id',
        ),
        array
        (
            'type' => 'rid',
            'name' => 'sub_company_id',
        ),
        array(
            'type' => 'function',
            'name' => 'company_code_function',
            'function' => 'Project1_Issue::createIssueCode2',
        ),
        array(
            'type' => 'master',
            'master' => 'config_issue',
            'multi' => true,
            'glue' => ',',
            'limit' => '3',
            'condition' => array(
                array(
                    'index' => '2',
                    'value' => 'keyword',
                ),
            ),
            'fieldList' => array(
                'keyword_code' => 0,
                'keyword_name' => 1,
            ),
        ),
        array(
            'type' => 'master',
            'master' => 'master_type',
            'condition' => array(
                array(
                    'index' => '2',
                    'value' => '1',
                ),
            ),
            'fieldList' => array(
                'large_type_code' => 0,
                'large_type_name' => 1,
            ),
        ),
        array(
            'type' => 'master',
            'master' => 'master_type',
            'condition' => array(
                array(
                    'index' => '2',
                    'value' => '2',
                ),
                array(
                    'index' => '3',
                    'belong' => 'large_type_code',
                ),
            ),
            'fieldList' => array(
                'medium_type_code' => 0,
                'meidum_type_name' => 1,
            ),
        ),
        array
        (
            'type' => 'datetime',
            'name' => 'company_created_datetime',
            'subtype' => 'now',
        ),
        array(
            'type' => 'datetime',
            'name' => 'company_updated_datetime',
            'subtype' => 'base',
            'base' => 'company_created_datetime',
            'datetime' => array(
                'month' => 1,
                'hour' => 2,
                'second' => 3,
            ),
        ),
        array(
            'type' => 'function',
            'function' => 'Project1_Issue::createDatetimeFromIssueCode',
            'name' => 'company_finished_datetime',
            'code' => 'company_code_function',
            'datetime' => array(
                'month' => 0,
                'hour' => 2,
                'second' => 3,
            ),
        ),
        array(
            'type' => 'function',
            'function' => 'Project1_Issue::createDateFromIssueCode',
            'name' => 'company_finished_date',
            'code' => 'company_code_function',
            'datetime' => array(
                'month' => 0,
                'hour' => 2,
                'second' => 3,
            ),
        ),
        array
        (
            'type' => 'date',
            'name' => 'company_finished_date_belong',
            'belong' => 'company_finished_datetime'
        ),
        array(
            'type' => 'function',
            'function' => 'Project1_Issue::createTimeFromIssueCode',
            'name' => 'company_finished_time',
            'code' => 'company_code_function',
            'datetime' => array(
                'month' => 0,
                'hour' => 2,
                'second' => 3,
            ),
        ),
        array
        (
            'type' => 'time',
            'name' => 'company_finished_time_belong',
            'belong' => 'company_finished_datetime'
        ),
        array(
            'type' => 'count_email',
            'name' => 'company_email',
        ),
        array(
            'type' => 'copy',
            'name' => 'company_copy',
            'fieldList' => array(
                'company_id',
                'company_code_function',
            ),
        ),
        array(
            'type' => 'number',
            'min' => '1000',
            'max' => '5000',
            'length' => '4',
            'realType' => 'int',
            'name' => 'company_address_no',
        ),
        array
        (
            'type' => 'flag',
            'name' => 'company_is_deleted',
            'rateTrue' => '0.1',
        ),
    ),
);
