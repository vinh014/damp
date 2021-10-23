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
    'multi' => '5',
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
            'name' => 'product_id',
        ),
        array(
            'type' => 'master',
            'master' => 'project1_product_category',
            'fieldList' => array(
                'product_category_id' => 0,
            ),
        ),
        array(
            'type' => 'reference2',
            'reference' => array(
                'table' => 'project1_company',
                'fieldList' => array(
                    'product_company_id' => 'company_id',
                    'product_sub_company_id' => 'sub_company_id',
                ),
            )
        ),
        array(
            'type' => 'multi',
            'name' => 'product_order',
        ),
        array
        (
            'type' => 'flag',
            'name' => 'product_is_deleted',
            'rateTrue' => '0.1',
        ),
    ),
);