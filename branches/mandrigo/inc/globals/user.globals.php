<?php
/**********************************************************
    user.globals.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 12/13/05

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

//
//Checks to see if there is a user logged in
//
$GLOBALS["USER_DATA"]["AUTHENTICATED"] = false;
if(user_is_logged_in($GLOBALS["HTTP_COOKIE"]["SESID"],$GLOBALS["HTTP_COOKIE"]["UID"],$sql_db)){
    $GLOBALS["USER_DATA"]["AUTHENTICATED"] = true;
    if($GLOBALS["HTTP_COOKIE"]["U_SESID"]!=0){
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            setcookie("SMX_SESID",$GLOBALS["HTTP_COOKIE"]["U_SESID"].":".$GLOBALS["HTTP_COOKIE"]["SESID"],$GLOBALS["SCRIPT"]["LOCAL_TIME"]+$GLOBALS["HTTP_COOKIE"]["U_SESID"],"/");
        }
        else{
            if(!(@setcookie("SMX_SESID",$GLOBALS["HTTP_COOKIE"]["U_SESID"].":".$GLOBALS["HTTP_COOKIE"]["SESID"],$GLOBALS["SCRIPT"]["LOCAL_TIME"]+$GLOBALS["HTTP_COOKIE"]["U_SESID"],"/"))){
                $GLOBALS["error_log"]->add_error(20,"script");
	   			die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
           			$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
            }
        }
    }
    if($GLOBALS["HTTP_COOKIE"]["U_UID"]!=0){
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            setcookie("SMX_UID",$GLOBALS["HTTP_COOKIE"]["U_UID"].":".$GLOBALS["HTTP_COOKIE"]["UID"],$GLOBALS["SCRIPT"]["LOCAL_TIME"]+$GLOBALS["HTTP_COOKIE"]["U_UID"],"/");
        }
        else{
            if(!(@setcookie("SMX_UID",$GLOBALS["HTTP_COOKIE"]["U_UID"].":".$GLOBALS["HTTP_COOKIE"]["UID"],$GLOBALS["SCRIPT"]["LOCAL_TIME"]+$GLOBALS["HTTP_COOKIE"]["U_UID"],"/"))){
                 $GLOBALS["error_log"]->add_script_error(21,"script");
	   			die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
           			$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
            }
        }
    }
}
if($GLOBALS["SCRIPT"]["AUTHENTICATED"]){
    if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
    	$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=",$GLOBALS["HTTP_COOKIE"]["UID"])));
	}
    else{
        if(!$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=",$GLOBALS["HTTP_COOKIE"]["UID"])))){
            $GLOBALS["error_log"]->add_error(10,"sql");
	   		die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
           		$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
        }
    }
}
else{
    if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
      	$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=",1)));

    }
    else{
        if(!$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=",1)))){
            $GLOBALS["error_log"]->add_error(10,"sql");
	   		die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
           		$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
        }
    }
}
$GLOBALS["USER_DATA"]["ID"]=$sql_result["user_id"];
$GLOBALS["USER_DATA"]["NAME"]=$sql_result["user_name"];
$GLOBALS["USER_DATA"]["GROUPS"]=explode(";",$sql_result["user_group"]);
$tmp=explode(";",$sql_result["user_real_name"]);
$GLOBALS["USER_DATA"]["FIRST_NAME"]=$tmp[0];
$GLOBALS["USER_DATA"]["MIDDLE_NAME"]=$tmp[1];
$GLOBALS["USER_DATA"]["LAST_NAME"]=$tmp[2];
$GLOBALS["USER_DATA"]["EMAIL"]=$sql_result["user_email"];
$GLOBALS["USER_DATA"]["IM"]=$sql_result["user_im"];
$GLOBALS["USER_DATA"]["WEBSITE"]=$sql_result["user_website"];
$GLOBALS["USER_DATA"]["ABOUT"]=$sql_result["user_about"];
$GLOBALS["USER_DATA"]["WEBSITE"]=$sql_result["user_website"];
$GLOBALS["USER_DATA"]["LAST_LOGIN"]=$sql_result["user_last_login"];
$GLOBALS["USER_DATA"]["LAST_IP"]=$sql_result["user_last_ip"];
$GLOBALS["USER_DATA"]["TIMEZONE"]=$sql_result["user_timezone"];
$GLOBALS["USER_DATA"]["DST"]=$sql_result["user_DST"];
$GLOBALS["USER_DATA"]["CRYPT_TYPE"]=$sql_result["user_crypt_type"];
$GLOBALS["USER_DATA"]["LOGIN_TYPE"]=$sql_result["user_login_type"];
$GLOBALS["USER_DATA"]["COOKIE_EXP"]=$sql_result["user_cookie_exp"];
$GLOBALS["USER_DATA"]["SITE_ADMIN"]=false;
$GLOBALS["USER_DATA"]["GROUP_NAMES"]="";
$GLOBALS["USER_DATA"]["IP"] = (!empty($HTTP_SERVER_VARS['REMOTE_ADDR']))?$HTTP_SERVER_VARS['REMOTE_ADDR']:((!empty($HTTP_ENV_VARS['REMOTE_ADDR']))?$HTTP_ENV_VARS['REMOTE_ADDR']:getenv('REMOTE_ADDR'));


for($i=0;$i<count($GLOBALS["USER_DATA"]["GROUPS"]);$i++){
    if(!$sql_result=$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_GROUPS,"",array(array("group_id","=",$GLOBALS["USER_DATA"]["GROUPS"]["$i"])))){
        if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $GLOBALS["error_log"]->add_error(11,"sql");
	   		die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
           		$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
        }
    }
    else{
        if($sql_result["group_admin"]==1){
            $GLOBALS["USER_DATA"]["SITE_ADMIN"]=true;
        }
        $GLOBALS["USER_DATA"]["GROUP_NAMES"][$i]=$sql_result["group_name"];
    }
}

?>
