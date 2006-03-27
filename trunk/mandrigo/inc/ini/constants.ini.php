<?php
/**********************************************************
    constants.ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 11/24/05

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
//SQL Tables
//
define("TABLE_PREFIX",$sql_config["TABLE_PREFIX"]);
define("TABLE_USER_DATA","user_data");
define("TABLE_USER_GROUPS","user_groups");
define("TABLE_GROUP_PERMISSIONS","group_permissions");
define("TABLE_MAIN_DATA","config");
define("TABLE_PAGE_DATA","page_data");
define("TABLE_RESTRICTED_PAGE_DATA","restricted_page_data");
define("TABLE_PACKAGE_DATA","packages");
define("TABLE_SITE_STATS","site_stats");
define("TABLE_TEMP","tmp");

//
//DB Constants
//
define("DB_UPDATE","UPDATE");
define("DB_INSERT","INSERT");
define("DB_DELETE","DELETE");
define("DB_REMOVE","DELETE");
define("DB_DROP","DROP");
define("DB_ADD","ADD");
define("DB_CREATE","CREATE");
define("DB_ALTER","ALTER");
define("DB_TRUNCATE","TRUNCATE");
define("DB_DATABASE","DATABASE");
define("DB_PRIMARY","PRIMARY");
define("DB_KEY","KEY");
define("DB_TABLE","TABLE");
define("DB_UINDEX","UINDEX");
define("DB_AND","AND");
define("DB_OR","OR");
define("DB_IN","IN");
define("DB_BETWEEN","BETWEEN");
define("DB_NULL","NULL");
define("DB_AUTO_INC","AUTO");
//
//Templates
//
define("TPL_EXT",$tpl_ex);
define("TPL_ERROR_LOG","error_log.".TPL_EXT);
define("TPL_OFF_SITE","off_site.".TPL_EXT);
define("TPL_AUTH_SITE","auth_site.".TPL_EXT);
define("TPL_MAIN_SITE","main_site.".TPL_EXT);
define("TPL_OFF_PAGE","off_page.".TPL_EXT);

//
//Hooks
//
define("HOOK_DISPLAY","_display_hook($"."this->page_db,$"."this->page_error_logger,$"."i);");
define("HOOK_VARS","_vars_hook($"."this->page_db,$"."this->page_error_logger,$"."i);");
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
define("TMP_IMG","tmp");

//
//Misc
//
define("MANDRIGO_CODE_BLOCK","<?MG_CODE>");
define("BAD_DATA","&ERROR_IN_DATA;");
define("SESSION_COOKIE","mg_sesid");
define("USER_COOKIE","mg_uid");

?>
