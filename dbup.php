<?php
/******************************************************************************
*
* DailyboothBackup - backup your dailybooth pictures
* Copyright (C) 2010 Philipp Waldhauer
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
* more details.
*
* You should have received a copy of the GNU General Public License along with
* this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
*
*****************************************************************************/

$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];

if ($argc < 4) {
    echo "Usage: php dbup.php [-v] -u username -t directory\n";
    echo "Example: php dbup.php -v -u knuspermagier -t pics\n";
    exit(-1);
}

require_once 'DailyboothBackup.php';

$db = new DailyboothBackup();
$directory = '';

for ($i = 1; $i <= $argc; $i++) {
    switch ($argv[$i]) {
        case '-v':
            $db->setVerbose(true);
            break;
    
        case '-u':
            $i++;
            $db->setUsername($argv[$i]);
            break;
        
        case '-t':
            $i++;
            $directory = $argv[$i];
            break;
    }
}

/** 
 * Deprecated in favor of new, faster way of fetching

$pages = $db->getPages();

if (count($pages) == 0) {
    $db->log('Nothing found!', true);
    exit;
}

$pictures = $db->getPictures($pages);
*/

$pictures = $db->getPicturesFast();

if (count($pictures) == 0) {
    $db->log('Nothing found!', true);
    exit;
}

$db->downloadPictures($pictures, $directory);
