<?php
/**********************************************************
    login_router.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 04/09/06

	Copyright (C) 2005  Kevin Wijesekera

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
define("CORE_NAME","mg_login");
$GLOBALS["MANDRIGO_CONFIG"]["LOGIN_PATH"]=dirname(__FILE__)."/";

//
//Initial includes (php extension, config vars, language array, html array)
//
require($GLOBALS["MANDRIGO_CONFIG"]["LOGIN_PATH"]."config/extension.inc");
require($GLOBALS["MANDRIGO_CONFIG"]["LOGIN_PATH"]."config/config.login.$php_ex");
require($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."config/elog.globals.$php_ex");

//
//Error Logger Init
//
if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
    require_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."error_logger.class.$php_ex");
}
else{
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."error_logger.class.$php_ex"))){
	   die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
           $GLOBALS["ELOG"]["ZERO"].$GLOBALS["ELOG"]["HTMLEND"]);
    }
}
$GLOBALS["error_log"] = & new error_logger($log_config["LOG_LEVEL_1"],$log_config["LOG_LEVEL_2"],$log_config["ARCHIVE"]);

//
// Cleans varables, loads requires packages and starts required classes.
//
if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
    require($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}login.ini.$php_ex");
}
else{
    if(!(@include($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}login.ini.$php_ex"))){
        $GLOBALS["error_log"]->add_error(1,"script");
	   	die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
        	$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
    }
}

//one final check for errors
if($error_log->get_status()==2){
    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
        $error_log->generate_report().$GLOBALS["HTML"]["EEND"]);
}

$tpl = new template();

if($GLOBALS["MANDRIGO_CONFIG"]["SITE_STATUS"]||$GLOBALS["HTTP_GET"]["KEY"]==$GLOBALS["SITE_DATA"]["BYPASS_CODE"]){
 	if(!$tpl->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].TPL_MAIN_SITE)){
		if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
			GLOBALS["error_log"]->add_error(30,"script");
	   		die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
        		$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
        }
        die();
 	}
    $page_parse_vars = array(
         "REQUESTED_URI",$GLOBALS["HTTP_SERVER"]["URI"]
        ,"SITE_NAME",$GLOBALS["SITE_DATA"]["SITE_NAME"]
        ,"SITE_URL",$GLOBALS["SITE_DATA"]["SITE_URL"]
        ,"IMG_URL",$GLOBALS["SITE_DATA"]["IMG_URL"]
        ,"WEBMASTER_NAME",$GLOBALS["SITE_DATA"]["WEBMASTER_NAME"]
        ,"LAST_UPDATED",$GLOBALS["SITE_DATA"]["LAST_UPDATED"]
        ,"MANDRIGO_VER",$GLOBALS["SITE_DATA"]["MANDRIGO_VER"]
    ); 	
 	$tpl->pparse($page_parse_vars);
 	echo $tpl->return_template();
}
else{
    $tpl->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].TPL_OFF_SITE);
    $tpl->pparse(false);
    echo $tpl->return_template();
}
?>