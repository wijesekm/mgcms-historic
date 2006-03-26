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
$GLOBALS["SITE_DATA"]["CRYPT_TYPE"]=$GLOBALS["SITE_DATA"]["CRYPT_TYPE"];
$GLOBALS["SITE_DATA"]["LOGIN_TYPE"]=$GLOBALS["SITE_DATA"]["LOGIN_TYPE"];
$GLOBALS["SITE_DATA"]["REMEMBERED_SESSION_LEN"]=$GLOBALS["SITE_DATA"]["REMEMBERED_SESSION_LEN"];

$GLOBALS["USER_DATA"]["USER_NAME"]="";
$GLOBALS["USER_DATA"]["IP"]=(!empty($HTTP_SERVER_VARS["REMOTE_ADDR"]))?$HTTP_SERVER_VARS["REMOTE_ADDR"]:((!empty($HTTP_ENV_VARS["REMOTE_ADDR"]))?$HTTP_ENV_VARS["REMOTE_ADDR"]:getenv("REMOTE_ADDR"));
$GLOBALS["SITE_DATA"]["REDIRECT_PATH"]=$GLOBALS["MANDRIGO_CONFIG"]["INDEX"];
?>