<?php
/**********************************************************
    login.globals.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/21/06

	Copyright (C) 2006  Kevin Wijesekera

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
//Gets User Data
if($GLOBALS["HTTP_POST"]["USER_NAME"]&&$GLOBALS["HTTP_POST"]["USER_NAME"]!=BAD_DATA){
    if(!$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_name","=",$GLOBALS["HTTP_POST"]["USER_NAME"])))){
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
			$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=","1")));	
		}
		else{
			if(!$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=","1")))){
				$error_log->add_error(10,"sql");
		    	die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
		        	$error_log->generate_report().$GLOBALS["HTML"]["EEND"]);
			}
		}
    }
}
else if($GLOBALS["HTTP_COOKIE"]["UID"]&&$GLOBALS["HTTP_COOKIE"]["UID"]!=BAD_DATA){
    if(!$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=",$GLOBALS["HTTP_COOKIE"]["UID"])))){
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
			$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=","1")));	
		}
		else{
			if(!$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=","1")))){
				$error_log->add_error(10,"sql");
		    	die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
		        	$error_log->generate_report().$GLOBALS["HTML"]["EEND"]);
			}
		}
    }
}
else{
    if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
		$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=","1")));	
	}
	else{
		if(!$sql_result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=","1")))){
			$error_log->add_error(10,"sql");
		    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
		    	$error_log->generate_report().$GLOBALS["HTML"]["EEND"]);
		}
	}
}

$GLOBALS["SITE_DATA"]["CRYPT_TYPE"]=($GLOBALS["SITE_DATA"]["UC_CRYPT_TYPE"]==1)?((isset($sql_result["user_crypt_type"]))?$sql_result["user_crypt_type"]:$GLOBALS["SITE_DATA"]["CRYPT_TYPE"]):$GLOBALS["SITE_DATA"]["CRYPT_TYPE"];
$GLOBALS["SITE_DATA"]["LOGIN_TYPE"]=($GLOBALS["SITE_DATA"]["UC_LOGIN_TYPE"]==1)?((isset($sql_result["user_login_type"]))?$sql_result["user_login_type"]:$GLOBALS["SITE_DATA"]["LOGIN_TYPE"]):$GLOBALS["SITE_DATA"]["LOGIN_TYPE"];
$rsession=($GLOBALS["SITE_DATA"]["UC_REMEMBERED_SESSION_LEN"]==1)?((isset($sql_result["user_cookie_exp"]))?$sql_result["user_cookie_exp"]:$GLOBALS["SITE_DATA"]["REMEMBERED_SESSION_LEN"]):$GLOBALS["SITE_DATA"]["REMEMBERED_SESSION_LEN"];
$GLOBALS["SITE_DATA"]["SESSION_LEN"]=(isset($GLOBALS["HTTP_POST"]["RSESSION"]))?$rsession:$GLOBALS["SITE_DATA"]["STANDARD_SESSION_LEN"];
$GLOBALS["USER_DATA"]["IP"]=(!empty($HTTP_SERVER_VARS["REMOTE_ADDR"]))?$HTTP_SERVER_VARS["REMOTE_ADDR"]:((!empty($HTTP_ENV_VARS["REMOTE_ADDR"]))?$HTTP_ENV_VARS["REMOTE_ADDR"]:getenv("REMOTE_ADDR"));

//Retrieves login specific info from the mg_config data table
$GLOBALS["SITE_DATA"]["LOGIN_URL"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","admin_login_url")));

?>