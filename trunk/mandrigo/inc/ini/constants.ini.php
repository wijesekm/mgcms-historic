<?php
/**********************************************************
    constants.ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/18/07

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
//To prevent direct script access
//
if(!defined("START_MANDRIGO")){
    die($GLOBALS["MANDRIGO"]["CONFIG"]["DIE_STRING"]);
}

//
//SQL Tables
//
define("TABLE_MAIN_DATA","config");
define("TABLE_SERVER_GLOBALS","server_globals");
define("TABLE_ACCOUNTS","accounts");
define("TABLE_GROUPS","groups");

define("TABLE_GROUP_PERMISSIONS","group_permissions");
define("TABLE_PAGE_DATA","page_data");
define("TABLE_RESTRICTED_PAGE_DATA","restricted_page_data");
define("TABLE_PACKAGE_DATA","packages");
define("TABLE_SITE_STATS","site_stats");
define("TABLE_CAPTCHA","captcha");

define("TABLE_LANG","lang_");
define("TABLE_LANG_MAIN","langsets");
define("TABLE_CAPTCHA_DATA","captcha_data");
define("TABLE_ENVELOPE_DATA","envelope_data");

//
//Templates
//
define("TPL_OFF_SITE","off_site.".TPL_EXT);
define("TPL_AUTH_SITE","auth_site.".TPL_EXT);
define("TPL_MAIN_SITE","main_site.".TPL_EXT);
define("TPL_OFF_PAGE","off_page.".TPL_EXT);

//
//Server Globals
//
define("METHOD_GET","http_get");
define("METHOD_POST","http_post");
define("METHOD_COOKIE","http_cookie");
define("METHOD_SERVER","http_server");
define("CORE_PACKAGES","package_core");

//
//Hooks
//
define("HOOK_DISPLAY","_display_hook($"."this->page_db,$"."i);");
define("HOOK_VARS","_vars_hook($"."this->page_db,$"."i);");
define("HOOK_CLASS","_hook();");

//
//Defaults
//
define("DEFAULT_ACTION","D");
define("DEFAULT_ID",0);
define("DEFAULT_PN",0);

//
//Paths
//
define("TMP_IMG","/tmp/");
define("TTF_FOLDER","/fonts/");
define("ICONS_IMG","icons");

//
//Misc
//
define("MANDRIGO_CODE_BLOCK","<?MG_CODE>");
define("BAD_DATA","&ERROR_IN_DATA;");
define("MULTIPART_ALT","multipart/alternative");
define("TEXT_PLAIN","text/plain");
define("TEXT_HTML","text/html");
define("MULTI_ALT","multipart/alternative");

