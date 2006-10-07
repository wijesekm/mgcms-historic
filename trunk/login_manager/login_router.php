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

if($GLOBALS["MANDRIGO_CONFIG"]["SITE_STATUS"]||$GLOBALS["HTTP_GET"]["KEY"]==$GLOBALS["SITE_DATA"]["BYPASS_CODE"]){
  	$tpl=new template();
	if(!$tpl->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].TPL_MAIN_SITE)){
        if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $this->page_error_logger->add_error(30,"script");
            die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                $this->page_error_logger->generate_report().$GLOBALS["HTML"]["EEND"]);
        }
    }
    $page_parse_vars = array(
        "SITE_NAME",$GLOBALS["SITE_DATA"]["SITE_NAME"]
        ,"SITE_URL",$GLOBALS["SITE_DATA"]["SITE_URL"]
        ,"WEBMASTER_NAME",$GLOBALS["SITE_DATA"]["WEBMASTER_NAME"]
        ,"MANDRIGO_VER",$GLOBALS["SITE_DATA"]["MANDRIGO_VER"]
    );
	switch($GLOBALS["HTTP_GET"]["ACTION"]){
		case "lo":
			$act=new logout($error_log,$sql_db);
			$page_parse_vars=merge_arrays($page_parse_vars,array("CONTENT",$act->display(),"PAGE_TITLE",$GLOBALS["LANGUAGE"]["LOGIN"]));
		break;
		case "rg":
			$act=new regester($error_log,$sql_db);
			$page_parse_vars=merge_arrays($page_parse_vars,array("CONTENT",$act->display(),"PAGE_TITLE",$GLOBALS["LANGUAGE"]["LOGIN"]));
		break;
		case "pi":
			$act=new reset($error_log,$sql_db);
			$page_parse_vars=merge_arrays($page_parse_vars,array("CONTENT",$act->display(),"PAGE_TITLE",$GLOBALS["LANGUAGE"]["LOGIN"]));
		break;
		case "li":
			$act=new login($error_log,$sql_db);
			$page_parse_vars=merge_arrays($page_parse_vars,array("CONTENT",$act->display(true),"PAGE_TITLE",$GLOBALS["LANGUAGE"]["LOGIN"]));
		break;
		default:
			$act=new login($error_log,$sql_db);
			$page_parse_vars=merge_arrays($page_parse_vars,array("CONTENT",$act->display(),"PAGE_TITLE",$GLOBALS["LANGUAGE"]["LOGIN"]));
		break;		 
	};
	$tpl->pparse($page_parse_vars);
	echo $tpl->return_template();
}
else{
  	$tpl = new template();
    $tpl->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].TPL_OFF_SITE);
    $tpl->pparse();
    echo $tpl->return_template();
}
?>