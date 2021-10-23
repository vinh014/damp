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
COPY project1_product_category (product_category_id,product_category_is_deleted) FROM 'C:\Users\Public\output\performance_testing\Project Name\10\#2\project1_product_category.csv'
WITH DELIMITER AS ',' CSV QUOTE  AS  '"' ESCAPE AS '\';
