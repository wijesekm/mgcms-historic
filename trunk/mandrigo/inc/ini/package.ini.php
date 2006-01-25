<?php
/**********************************************************
    package.ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 11/14/05

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
//To prevent direct script access
//
if(!defined("START_MANDRIGO")){
    die("<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width=\"300\" align=\"left\"/>\n<p>You do not have permission to access this file directly.</p>
        </html></body>");
}

for($i=0;$i<count($GLOBALS["PAGE_DATA"]["HOOKS"]);$i++){
    if(!empty($GLOBALS["PAGE_DATA"]["HOOKS"][$i])){
        if(!($sql_result=$sql_db->fetch_array("SELECT * FROM `".TABLE_PREFIX.TABLE_PACKAGE_DATA."` WHERE `package_id`='".$GLOBALS["PAGE_DATA"]["HOOKS"][$i]."';"))){
            if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                $error_log->add_error(14,"sql");
                die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                    $error_log->generate_report().$GLOBALS["HTML"]["EEND"]);
            }
        }
        else{
            if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                include_once($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].$sql_result["package_name"]."/hooks.pkg.$php_ex");
                include_once($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].$sql_result["package_name"]."/display.pkg.$php_ex");
                include_once($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].$sql_result["package_name"]."/globals.pkg.$php_ex");
            }
            else{
                if(!@include_once($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].$sql_result["package_name"]."/hooks.pkg.$php_ex")){
                    $error_log->add_error($sql_result["package_noload_error"],"script");
                    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                        $error_log->generate_report().$GLOBALS["HTML"]["EEND"]);
                }
                if(!@include_once($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].$sql_result["package_name"]."/display.pkg.$php_ex")){
                    $error_log->add_error($sql_result["package_noload_error"],"script");
                    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                        $error_log->generate_report().$GLOBALS["HTML"]["EEND"]);
                }
                if(!@include_once($GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"].$sql_result["package_name"]."/globals.pkg.$php_ex")){
                    $error_log->add_error($sql_result["package_noload_error"],"script");
                    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                        $error_log->generate_report().$GLOBALS["HTML"]["EEND"]);
                }
            }
            $GLOBALS["PAGE_DATA"]["DISPLAY_HOOK"]=$sql_result["package_name"].".display_hook();";
        }
    }
}

?>
