<?php
/**********************************************************
    script.globals.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 12/14/05

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

$GLOBALS["SITE_DATA"]["LOGIN_TYPE"]=($GLOBALS["SITE_DATA"]["UC_LOGIN_TYPE"]==1)?((!empty($GLOBALS["USER_DATA"]["LOGIN_TYPE"]))?$GLOBALS["USER_DATA"]["LOGIN_TYPE"]:$GLOBALS["SITE_DATA"]["LOGIN_TYPE"]):$GLOBALS["SITE_DATA"]["LOGIN_TYPE"];
$GLOBALS["SITE_DATA"]["CRYPT_TYPE"]=($GLOBALS["SITE_DATA"]["UC_CRYPT_TYPE"]==1)?((!empty($GLOBALS["USER_DATA"]["CRYPT_TYPE"]))?$GLOBALS["USER_DATA"]["CRYPT_TYPE"]:$GLOBALS["SITE_DATA"]["CRYPT_TYPE"]):$GLOBALS["SITE_DATA"]["CRYPT_TYPE"];
$GLOBALS["SITE_DATA"]["REMEMBERED_SESSION_LEN"]=($GLOBALS["SITE_DATA"]["UC_REMEMBERED_SESSION_LEN"]==1)?((!empty($GLOBALS["USER_DATA"]["COOKIE_EXP"]))?$GLOBALS["USER_DATA"]["COOKIE_EXP"]:$GLOBALS["SITE_DATA"]["REMEMBERED_SESSION_LEN"]):$GLOBALS["SITE_DATA"]["REMEMBERED_SESSION_LEN"];



//
//sets up time class
//
$s_time = new server_time($GLOBALS["SITE_DATA"]["SERVER_ZONE"],$GLOBALS["SITE_DATA"]["SERVER_DST"]);
$GLOBALS["SITE_DATA"]["GMT"]=$s_time->gmt();
$GLOBALS["SITE_DATA"]["SERVER_TIME"]=time();
$GLOBALS["SITE_DATA"]["LOCAL_TIME"]=$s_time->local_time($GLOBALS["USER_DATA"]["TIMEZONE"],$GLOBALS["USER_DATA"]["DST"]);
$s_time="";

?>
