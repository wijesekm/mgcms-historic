<?php
/**********************************************************
    config.ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 01/30/07

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
//DIE_STRING - this will be displayed if someone tries to access one of the mandrigo include files directly
//
$GLOBALS["MANDRIGO"]["CONFIG"]["DIE_STRING"]=
"<html><head>
	<title>Forbidden</title>
</head><body>
	<h1>Forbidden</h1><hr width=\"300\" align=\"left\"/>\n<p>You do not have permission to access this file directly.</p>
</html></body>";

//
//To prevent direct script access
//
if(!defined("START_MANDRIGO")){
    die($GLOBALS["MANDRIGO"]["CONFIG"]["DIE_STRING"]);
}

//////////////////////
// SQL array
/////////////////////

//Enter in the information for your SQL database:
//SQL_TYPE is the sql type (supported types:
//
//mysql - mysql 4.0 and lower.  No ssl support. 
//mysqli - mysql 4.1 and up. ssl support.  not ready yet!
//pgsql - PostgreSQL.  Not ready yet!
//mssql - Microsoft SQL. Not ready yet!
//oraclesql - Oracle SQL DB. Not Ready Yet!
//
//SQL_HOST - Sql server address or ip address (ex. sql.kevinwijesekera.net, localhost)
//
//SQL_PORT - remove port of the sql server (if host is not localhost).
//
//SQL_SOCKET - location of the socket file if using the localhost connection method
//
//SQL_USER - username with permission to read, write, lookup, insert, and delete from the data
//
//SQL_PASSWORD - password of that user
//
//SQL_DATABASE - name of the database where the mandrigo data is stored
//
//TABLE_PREFIX - prefix to table names (ex for table mg_users the prefix would be mg_)
//
//USE_SSL - Use a secure connection.  Still testing so not ready yet!
//
//SSL - array of ssl data
$sql_config="";
$sql_config["SQL_TYPE"]="mysql";
$sql_config["SQL_HOST"]="localhost";
$sql_config["SQL_PORT"]="3306";
$sql_config["SQL_SOCKET"]="";
$sql_config["SQL_USER"]="";
$sql_config["SQL_PASSWORD"]="";
$sql_config["SQL_DATABASE"]="";
$sql_config["TABLE_PREFIX"]="mg_";
$sql_config["USE_SSL"]=false;
$sql_config["SSL"]=array("KEY"=>"",
						 "CERT"=>"",
						 "CA"=>"",
						 "CAPATH"=>"" 
						);
						
//////////////////////
// AD/LDAP array
/////////////////////

//
//Enter in the information for your AD/LDAP database (if used)
//Currently this only works for Active Directory.  General LDAP support 
//is in the works.
//
//DN				-	Base Domain for LDAP Server (ie DC=MS,DC=TEST,DC=ORG)
//DC				-	Array of Domain Controllers []specify more then one for load balancing] 
//						(ie dc1.test.org or 192.168.1.12)
//ACCT_SUFFIX		-	Account Suffix [generally just the DN in general notation] (ie @ms.test.org)
//CONTROL_USER		-	Username with ability to search on the domain
//CONTROL_PASSWORD	-	That users password
//USE_SSL			-	This MUST be on for Active Directory if you are using ad for accounts
//
$adldap_config="";
$adldap_config["DN"]="";
$adldap_config["DC"]=array("");
$adldap_config["ACCT_SUFFIX"]="";
$adldap_config["CONTROL_USER"]="";
$adldap_config["CONTROL_PASSWORD"]="";
$adldap_config["USE_SSL"]=true;

//////////////////////
// Error Logging
/////////////////////

//LOG_LEVEL_1 - all errors that are non system critical ie. page does not
//exist, permission denyed,etc
//
//LOG_LEVEL_2 - all errors that are system critical ie. could not access
//sql server, could not find config file, etc
//
//ARCHIVE - date format on the log files (ex m_d_h will archive by hour, m_d will archive by day)
//
//ERROR_LOGS - the name of all types of errors (DO NOT CHANGE THIS UNLESS YOU KNOW WHAT YOU ARE DOING!!)
//
//FATAL_TYPES - the types of errors which will cause mandrigo to stop execution (DO NOT CHANGE THIS UNLESS YOU KNOW WHAT YOU ARE DOING!!!)
//

$log_config="";
$log_config["LOG_LEVEL_1"]=false;
$log_config["LOG_LEVEL_2"]=true;
$log_config["ARCHIVE"]="m_d_h";
$log_config["ERROR_LOGS"]=array("sql","core","display","access","ldap");
$log_config["FATAL_TYPES"]=array("sql"=>1,"core"=>1,"ldap"=>1);


//////////////////////
// Main Config Global Array
/////////////////////


//
//NOTE: Only the IMG_PATH has to be accessibly by the webserver.  All other paths can be to non web locations.
//
//ROOT_PATH - path to the index.php folder ex: /var/www/htdocs/
//
//ROOT_PATH - path to the inc folder ex: /var/www/htdocs/inc/
//
//PLUGIN_PATH - path to the plugins folder (usually a subdir of inc) ex: /var/www/htdocs/inc/packages/
//
//TEMPLATE_PATH - path to the templates folders ex: /var/www/templates/ (it is recommended that this path not be web accessible)
//
//LOG_PATH - path to the logs folder ex: /var/www/logs/ (it is recommended that this path not be web accessible)
//
//IMG_PATH - path to the mandrigo images folder ex: /var/www/htdocs/images/
//
//ADMIN_ROOT_PATH -  path to the admin inc folder ex: /var/www/htdocs/admin/inc/
//
//LOGIN_ROOT_PATH - path to the login inc folder ex: /var/www/htdocs/login_manager/inc/
//
//TMP_PATH - path to the tmp folder ex /tmp/
//

$GLOBALS["MANDRIGO"]["CONFIG"]["BASE_PATH"]=ereg_replace("/config/config.ini.php","",__FILE__);
$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]=$GLOBALS["MANDRIGO"]["CONFIG"]["BASE_PATH"]."/inc/";
$GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"]=$GLOBALS["MANDRIGO"]["CONFIG"]["BASE_PATH"]."/inc/packages/";
$GLOBALS["MANDRIGO"]["CONFIG"]["IMG_PATH"]=$GLOBALS["MANDRIGO"]["CONFIG"]["BASE_PATH"]."/images/mg_images/";
$GLOBALS["MANDRIGO"]["CONFIG"]["ADMIN_ROOT_PATH"]=$GLOBALS["MANDRIGO"]["CONFIG"]["BASE_PATH"]."/admin/inc/";
$GLOBALS["MANDRIGO"]["CONFIG"]["LOGIN_ROOT_PATH"]=$GLOBALS["MANDRIGO"]["CONFIG"]["BASE_PATH"]."/login_manager/inc/";
$GLOBALS["MANDRIGO"]["CONFIG"]["EXTERNAL_PATH"]=ereg_replace("/htdocs/config/config.ini.php","",__FILE__);
$GLOBALS["MANDRIGO"]["CONFIG"]["TEMPLATE_PATH"]=$GLOBALS["MANDRIGO"]["CONFIG"]["EXTERNAL_PATH"]."/templates/";
$GLOBALS["MANDRIGO"]["CONFIG"]["LOG_PATH"]=$GLOBALS["MANDRIGO"]["CONFIG"]["EXTERNAL_PATH"]."/logs/";
$GLOBALS["MANDRIGO"]["CONFIG"]["TMP_PATH"]="/tmp/";
$GLOBALS["MANDRIGO"]["CONFIG"]["CACHE_PATH"]=$GLOBALS["MANDRIGO"]["CONFIG"]["EXTERNAL_PATH"]."/cache/";
//
//DEBUG_MODE- Shows php errors instead of Mandrigo errors.
//
//SQL_PRINT_MODE - prints sql queries.
//
//DO NOT SET THESE TO TRUE FOR PRODUCTION RELEASES.  THIS IS FOR DEVELOPMENT ONLY AND INTRODUCES
//SECURITY HOLES!
//
$GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]=false;
$GLOBALS["MANDRIGO"]["CONFIG"]["SQL_PRINT_MODE"]=false;

//
//IS_INSTALLED:  If not using auto install or script has already been set up
//this should be set to true.
//
$GLOBALS["MANDRIGO"]["CONFIG"]["IS_INSTALLED"]=false;

//
//path_style - Path Style for includes.  Set to either win or unix
//
$path_style="unix";

//////////////////////
// Language Config
/////////////////////

//
//LANGUAGE - language script is in.  See documentation for valid types (ex en)
//
//Html Version - html version that the script is in (ex xhtml_1_0_trans)
//

$GLOBALS["MANDRIGO"]["CONFIG"]["LANGUAGE"]="en";
$GLOBALS["MANDRIGO"]["CONFIG"]["HTML_VER"]="xhtml_1_0_trans";

//
//DO NOT EDIT BELOW THIS
//
if($path_style=="win"){
	$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]="\\";
}
else{
	$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]="/";	
}

//define table prefix
define("TABLE_PREFIX",$sql_config["TABLE_PREFIX"]);