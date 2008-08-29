<?php

/**
 * @file		index.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		5-22-2008

 ###################################
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program.  If not, see http://www.gnu.org/licenses/.
 ###################################
 */

define('STARTED',true);

include(dirname(__FILE__).'/conf/ini.conf');
include(dirname(__FILE__).'/conf/conf'.PHPEXT);

include($GLOBALS['MG']['CFG']['PATH']['INC'].'/ini/ini.'.PHPEXT);

$page=new page();

$content=$page->page_generate();

header('Content-Type: '.$GLOBALS['MG']['LANG']['CONTENT_TYPE'].'; charset='.$GLOBALS['MG']['LANG']['ENCODING']);

//echo '<pre>';
//print_r($GLOBALS['MG']);

echo $content;

?>