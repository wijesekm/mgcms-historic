<?php
/**********************************************************
    login_router.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 01/30/07

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
switch($GLOBALS["MANDRIGO"]["VARS"]["ACTION"]){
	case "check":
		if($GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["CONFIG"]<1){
			$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(2,"access");
		}
	break;
}
$tpl_m=new template();
if(!$tpl_m->tpl_load($GLOBALS["MANDRIGO"]["CONFIG"]["TEMPLATE_PATH"].TPL_ADMINPATH.TPL_ADMIN,"main")){
	$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(5,"display");
	die($GLOBALS["MANDRIGO"]["ELOG"]["HTMLHEAD"].$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"].$GLOBALS["MANDRIGO"]["ELOG"]["HTMLBODY"].
        $GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport().$GLOBALS["MANDRIGO"]["ELOG"]["HTMLEND"]);
}

$page_parse_vars = array(
            "SITE_NAME",$GLOBALS["MANDRIGO"]["SITE"]["SITE_NAME"],
            "SITE_URL",$GLOBALS["MANDRIGO"]["SITE"]["SITE_URL"],
            "ADMIN_URL",$GLOBALS["MANDRIGO"]["SITE"]["ADMIN_URL"],
            "ADMIN_NAME",$GLOBALS["MANDRIGO"]["SITE"]["ADMIN_NAME"],
            "LOGIN_URL",$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_URL"],
            "LOGIN_NAME",$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_NAME"],
            "IMG_URL",$GLOBALS["MANDRIGO"]["SITE"]["IMG_URL"],
            "SERVER_DATE",date($GLOBALS["MANDRIGO"]["SITE"]["DATE_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"]),
            "SERVER_TIME",date($GLOBALS["MANDRIGO"]["SITE"]["TIME_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"]),
            "GMT_DATE",date($GLOBALS["MANDRIGO"]["SITE"]["DATE_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["GMT"]),
            "GMT_TIME",date($GLOBALS["MANDRIGO"]["SITE"]["TIME_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["GMT"]),
            "WEBMASTER_NAME",$GLOBALS["MANDRIGO"]["SITE"]["WEBMASTER_NAME"],
            "WEBMASTER_EMAIL",$GLOBALS["MANDRIGO"]["SITE"]["WEBMASTER_EMAIL"],
            "SITE_LAST_UPDATED",$GLOBALS["MANDRIGO"]["SITE"]["LAST_UPDATED"],
            "MG_VER",$GLOBALS["MANDRIGO"]["SITE"]["MANDRIGO_VER"],
            "INDEX_NAME",$GLOBALS["MANDRIGO"]["SITE"]["INDEX_NAME"],
            "CUSER_ID",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UID"],
            "CUSER_FNAME",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["FNAME"],
            "CUSER_MNAME",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["MNAME"],
            "CUSER_LNAME",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["LNAME"],
            "CUSER_LANG",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["LANGUAGE"],
            "CUSER_DATE",date($GLOBALS["MANDRIGO"]["SITE"]["DATE_FORMAT"],$GLOBALS["MANDRIGO"]["CURRENTUSER"]["TIME"]),
            "CUSER_TIME",date($GLOBALS["MANDRIGO"]["SITE"]["TIME_FORMAT"],$GLOBALS["MANDRIGO"]["CURRENTUSER"]["TIME"]),
);

error_log_check($tpl_m,$page_parse_vars);

switch($GLOBALS["MANDRIGO"]["VARS"]["ACTION"]){
 	case "check":
 		$check=new checkinstall();
 		$content=$check->ci_display();
		error_log_check($tpl_m,$page_parse_vars);
 		$tpl_m->tpl_parse(appendarray(array("CONTENT",$content,"PAGE_TITLE",$GLOBALS["MANDRIGO"]["LANGUAGE"]["ADMIN_CHECKTITLE"]),$page_parse_vars));
 	
 	break;
	default:
		$tpl=new template();
		if(!$tpl->tpl_load($GLOBALS["MANDRIGO"]["CONFIG"]["TEMPLATE_PATH"].TPL_ADMINPATH.TPL_ADMINMAIN,"main")){
			$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(6,"display");
			error_log_check($tpl_m,$page_parse_vars);
		}
		$tpl_m->tpl_parse(appendarray(array("CONTENT",$tpl->tpl_return("main"),"PAGE_TITLE",$GLOBALS["MANDRIGO"]["LANGUAGE"]["ADMIN_MAINTITLE"]),$page_parse_vars));
	break;
}
echo $tpl_m->tpl_return("main");

function error_log_check($tpl_m,$page_parse_vars){
	if($GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_getstatus()!=0){
		$tpl_m->tpl_parse(appendarray(array("CONTENT",$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport(),"PAGE_TITLE",$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"]),$page_parse_vars));	
		echo $tpl_m->tpl_return("main");
		die();
	}
}
?>