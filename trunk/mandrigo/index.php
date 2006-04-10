<?php
/**********************************************************
    index.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 11/04/05

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
$GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]=dirname(__FILE__)."/";

//
//Initial includes (php extension, config vars, language array, html array)
//
require($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."config/extension.inc");
require($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."config/config.ini.$php_ex");
require($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."languages{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}".$lang["LANGUAGE"].".lang.$php_ex");
require($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."languages{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}".$lang["HTML_VER"].".lang.$php_ex");

//
//Error Logger Init
//
if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
    require_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."error_logger.class.$php_ex");
}
else{
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."error_logger.class.$php_ex"))){
	   die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
           $GLOBALS["LANGUAGE"]["EZERO"].$GLOBALS["HTML"]["EEND"]);
    }
}
$error_log = & new error_logger($log_config["LOG_LEVEL_1"],$log_config["LOG_LEVEL_2"],$log_config["ARCHIVE"]);

//
// Cleans varables, loads requires packages and starts required classes.
//
if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
    require($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}ini.$php_ex");
}
else{
    if(!(@include($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}ini.$php_ex"))){
        $error_log->add_error(1,"script");
	    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
           $error_log->generate_report().$GLOBALS["HTML"]["EEND"]);
    }
}

//
//sets up the page
//
$current_page = new page($error_log,$sql_db);

//one final check for errors
if($error_log->get_status()==2){
    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
        $error_log->generate_report().$GLOBALS["HTML"]["EEND"]);
}

if($GLOBALS["MANDRIGO_CONFIG"]["SITE_STATUS"]||$GLOBALS["HTTP_GET"]["KEY"]==$GLOBALS["SITE_DATA"]["BYPASS_CODE"]){
    echo $current_page->display();
}
else{
  	$tpl = new template();
    $tpl->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].TPL_OFF_SITE);
    $tpl->pparse(false);
    echo $tpl->return_template();
}

?>
