<?php

/**
 * @file		index.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		9-29-2008

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

include($GLOBALS['MG']['CFG']['PATH']['INC'].'/ini/ini'.PHPEXT);

$page=new page();

$content=$page->page_generate();

/**
*
* Cleanup
*
*/
$GLOBALS['MG']['SQL']->sql_close();

/**
*
*Header Information
*
*/
if($GLOBALS['MG']['PAGE']['REDIRECT']){
	mginit_setHeader('Location',$GLOBALS['MG']['PAGE']['REDIRECT']);
	die();
}
if($GLOBALS['MG']['LANG']['CACHE_CONTROL']){
	mginit_setHeader('Cache-Control',$GLOBALS['MG']['LANG']['CACHE_CONTROL']);
}
if($GLOBALS['MG']['LANG']['PRAGMA']){
	mginit_setHeader('Pragma',$GLOBALS['MG']['LANG']['PRAGMA']);
}
if($GLOBALS['MG']['LANG']['ENCODING'] != 'none'){
	mginit_setHeader('Content-Type',$GLOBALS['MG']['LANG']['CONTENT_TYPE'].'; charset='.$GLOBALS['MG']['LANG']['ENCODING']);
}
else{
	mginit_setHeader('Content-Type',$GLOBALS['MG']['LANG']['CONTENT_TYPE']);
}
if($GLOBALS['MG']['LANG']['CONTENT_DISPOSITION']){
	mginit_setHeader('Content-Disposition',$GLOBALS['MG']['LANG']['CONTENT_DISPOSITION']);
}
if($GLOBALS['MG']['LANG']['CONTENT_LENGTH']){
	mginit_setHeader('Content-Length',$GLOBALS['MG']['LANG']['CONTENT_LENGTH']);
}

echo $content;

?>