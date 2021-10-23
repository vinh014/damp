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

var out = [];
var d = new Date();
var dir = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate() + ' ' + d.getHours() + '=' + d.getMinutes() + '=' + d.getSeconds();
var itemType = [];
var itemName = [];
var itemUrl = [];
var dest = '';
var newdest = '';
dir = baseDir + dir;

out[out.length] = ':: add wget to PATH Enviroment';
out[out.length] = ':: copy and past to command line, not directly run';
out[out.length] = 'mkdir "' + dir + '"';