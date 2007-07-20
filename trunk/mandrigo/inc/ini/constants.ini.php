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
define("TABLE_PAGES","pages");
define("TABLE_ACL","acl");
define("TABLE_LANGSETS","langsets");
define("TABLE_LANG","lang");
define("TABLE_PACKAGES","packages");
define("TABLE_STATS_HITS","stats_hits");
define("TABLE_STATS_IPS","stats_ips");
define("TABLE_STATS_USERAGENTS","stats_uagents");
define("TABLE_CLASSES","classes");
define("TABLE_CAPTCHA","captcha");
define("TABLE_CAPTCHA_DATA","captchad");
define("TABLE_ENVELOPE_DATA","envelopedata");

//
//Templates
//
define("TPL_MAINSITE","main_site.".TPL_EXT);
define("TPL_OFFSITE","off_site.".TPL_EXT);
define("TPL_OFFPAGE","off_page.".TPL_EXT);
define("TPL_LOGIN","login.".TPL_EXT);
define("PACKAGE_TEMPLATE_PATH","/templates/");

//
//Server Globals
//
define("METHOD_GET","http_get");
define("METHOD_POST","http_post");
define("METHOD_COOKIE","http_cookie");
define("METHOD_SERVER","http_server");
define("CORE_PACKAGES","mg_packages");

//
//Hooks
//
define("HOOK_DISPLAY","_display_hook($"."i);");
define("HOOK_VARS","_vars_hook($"."i);");
define("HOOK_CLASS","_hook();");

//
//Paths
//
define("TMP_IMG","/tmp/");
define("TTF_FOLDER","/fonts/");
define("ICONS_IMG","icons");

//
//Misc
//
define("BAD_DATA","&ERROR_IN_DATA;");
define("MULTIPART_ALT","multipart/alternative");
define("TEXT_PLAIN","text/plain");
define("TEXT_HTML","text/html");
define("MULTI_ALT","multipart/alternative");
