<?php
/**********************************************************
    admin.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/21/07

	Copyright (C) 2006-2007 the MandrigoCMS Group

    ##########################################################
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

	###########################################################

**********************************************************/

//
//site manager definition
//
define("START_MANDRIGO",true);
define("CORE_NAME","mg_admin");
$GLOBALS["MANDRIGO"]=array();
$GLOBALS["MANDRIGO"]["CONFIG"]["ADMIN_ROOT_PATH"]=dirname(__FILE__)."/";

//
//Initial includes (php extension, config vars, language array, html array)
//
require($GLOBALS["MANDRIGO_CONFIG"]["ADMIN_ROOT_PATH"]."config/config.admin.inc");

//
//Error Logger Init
//
if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
    require_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."error_logger.class.$php_ex");
}
else{
    if(!(@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."error_logger.class.$php_ex"))){
	   die($GLOBALS["MANDRIGO"]["ELOG"]["HTMLHEAD"].$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"].$GLOBALS["MANDRIGO"]["ELOG"]["HTMLBODY"].
           $GLOBALS["MANDRIGO"]["ELOG"]["ZERO"].$GLOBALS["MANDRIGO"]["ELOG"]["HTMLEND"]);
    }
}
$GLOBALS["MANDRIGO"]["ERROR_LOGGER"] = new error_logger($log_config["LOG_LEVEL_1"],$log_config["LOG_LEVEL_2"],$log_config["ARCHIVE"],$log_config["ERROR_LOGS"],$log_config["FATAL_TYPES"]);

//
// Cleans varables, loads requires packages and starts required classes.
//
if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
    require($GLOBALS["MANDRIGO"]["CONFIG"]["ADMIN_ROOT_PATH"]."ini{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}admin.ini.$php_ex");
}
else{
    if(!(@include($GLOBALS["MANDRIGO"]["CONFIG"]["ADMIN_ROOT_PATH"]."ini{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}admin.ini.$php_ex"))){
        $GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(2,"core");
	   	die($GLOBALS["MANDRIGO"]["ELOG"]["HTMLHEAD"].$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"].$GLOBALS["MANDRIGO"]["ELOG"]["HTMLBODY"].
        	$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport().$GLOBALS["MANDRIGO"]["ELOG"]["HTMLEND"]);
    }
}

if(!$GLOBALS["MANDRIGO"]["CURRENTUSER"]["AUTHENTICATED"]){
	header("Location: ".$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_URL"].$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_NAME"]."/a/display/t/admin");
}

$current_page = new admin_page();

//one final check for errors
if($GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_getstatus()==2){
	die($GLOBALS["MANDRIGO"]["ELOG"]["HTMLHEAD"].$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"].$GLOBALS["MANDRIGO"]["ELOG"]["HTMLBODY"].
        $GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport().$GLOBALS["MANDRIGO"]["ELOG"]["HTMLEND"]);
}

echo $current_page->ap_display();

?>