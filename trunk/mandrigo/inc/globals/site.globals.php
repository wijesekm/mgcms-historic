<?php
/**********************************************************
    site.globals.php
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

$GLOBALS["SITE_DATA"]["SITE_NAME"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","site_name")));
$GLOBALS["SITE_DATA"]["SITE_URL"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","site_url")));
$GLOBALS["SITE_DATA"]["IMG_URL"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","img_url")));
$GLOBALS["SITE_DATA"]["IMG_URL"]=(empty($GLOBALS["SITE_DATA"]["IMG_URL"]))?$GLOBALS["SITE_DATA"]["SITE_URL"]:$GLOBALS["SITE_DATA"]["IMG_URL"];
$GLOBALS["SITE_DATA"]["URL_FORMAT"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","url_format")));
$GLOBALS["SITE_DATA"]["MAIN_PAGE"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","main_page")));
$GLOBALS["SITE_DATA"]["FORM_MAIL_PAGE"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","form_mail_page")));
$GLOBALS["SITE_DATA"]["PROFILE_PAGE"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","profile_page")));
$GLOBALS["SITE_DATA"]["BYPASS_CODE"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","bypass_code")));
$GLOBALS["SITE_DATA"]["WEBMASTER_NAME"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","webmaster_name")));
$GLOBALS["SITE_DATA"]["WEBMASTER_EMAIL"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","webmaster_email")));
$GLOBALS["SITE_DATA"]["LAST_UPDATED"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","last_updated")));
$GLOBALS["SITE_DATA"]["MANDRIGO_VER"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","mandrigo_ver")));
$GLOBALS["SITE_DATA"]["SERVER_ZONE"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","server_timezone")));
$GLOBALS["SITE_DATA"]["SERVER_DST"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","server_dst")));
$GLOBALS["SITE_DATA"]["PAGE_INPUT_TYPE"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","page_type")));
$GLOBALS["SITE_DATA"]["CRYPT_TYPE"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","crypt_type")));
$GLOBALS["SITE_DATA"]["UC_CRYPT_TYPE"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","uc_crypt_type")));
$GLOBALS["SITE_DATA"]["LOGIN_TYPE"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","login_type")));
$GLOBALS["SITE_DATA"]["UC_LOGIN_TYPE"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","uc_login_type")));
$GLOBALS["SITE_DATA"]["STANDARD_SESSION_LEN"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","standard_session_len")));
$GLOBALS["SITE_DATA"]["REMEMBERED_SESSION_LEN"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","remembered_session_len")));
$GLOBALS["SITE_DATA"]["UC_REMEMBERED_SESSION_LEN"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","uc_remembered_session_len")));
$GLOBALS["SITE_DATA"]["COOKIE_PATH"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","cookie_path")));
$GLOBALS["SITE_DATA"]["COOKIE_DOMAINS"]=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","cookie_domains")));
$GLOBALS["SITE_DATA"]["COOKIE_SECURE"]=($sql_db->db_fetchresult(TABLE_PREFIX.TABLE_MAIN_DATA,"data_value",array(array("data_name","=","secure_cookie")))=="true")?true:false;;


?>
