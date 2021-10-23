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

for (var i in itemUrl) {
    dest = dir + '\\' + 'file' + i;
    out[out.length] = wget + ' -O "' + dest + '" ' + itemUrl[i] + '';
    newdest = itemName[i] + '.' + itemType[i];
    out[out.length] = 'REN "' + dest + '" "' + newdest + '"';
}
out[out.length] = 'REN "' + dir + '" "' + album + '"';
out[out.length] = 'pause';
out[out.length] = 'exit';
out[out.length] = '::';
var rs = out.join('<br/>');