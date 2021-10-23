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
    'multi' => '1',
    'fixMulti' => '1',
    'isMaster' => '1',
    'totalRecord' => '100',
    'field' => array
    (
        array
        (
            'type' => 'serial',
        ),
        array
        (
            'type' => 'id',
            'name' => 'product_category_id',
        ),
        array
        (
            'type' => 'flag',
            'name' => 'product_category_is_deleted',
            'rateTrue' => '0.1',
        ),
    ),
);