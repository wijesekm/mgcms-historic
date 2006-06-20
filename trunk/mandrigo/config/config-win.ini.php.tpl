<?php
/**********************************************************
    config.ini.php
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

//////////////////////
// SQL array
/////////////////////

//Enter in the information for your SQL database:
//Type is the sql type (supported types: mysql(mysql 4.0),mysqli(mysql 4.1 and up. supports ssl),pgsql
//mssql(not ready yet))
//
//Host is the server it is hosted on (ex sql.kevinwijesekera.net, localhost)
//
//Port is the port of the server (leave blank for default)
//
//User is the username of a user who has access to the database
//
//Password is that users password
//
//Database is the name of the database where mandrigo was set up
//
//Use ssl is set to true if you wish to use a secure connection.  This is only available for the mysqli package
//

$sql_config["SQL_TYPE"]="mysql";
$sql_config["SQL_HOST"]="localhost";
$sql_config["SQL_PORT"]="";
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
// Error Logging
/////////////////////

//Level One Logging: all errors that are non system critical ie. page does not
//exist, permission denyed,etc
//
//Level Two Logging: all errors that are system critical ie. could not access
//sql server, could not find config file, etc
//
//Archive - date format on the log files (ex m_d_h will archive by hour, m_d will archive by day)
//
$log_config["LOG_LEVEL_1"]=true;
$log_config["LOG_LEVEL_2"]=true;
$log_config["ARCHIVE"]="m_d_h";

//////////////////////
// Main Config Global Array
/////////////////////


//
//NOTE: Only the IMG_PATH has to be accessibly by the webserver.  All other paths can be to non web locations.
//
//Root Path - path to the inc folder ex: /var/www/htdocs/inc/
//
//Plugin Path - path to the plugins folder (usually a subdir of inc) ex: /var/www/htdocs/inc/packages
//
//Template Path - path to the templates folders ex /var/www/templates/
//
//Log Path - path to the logs folder ex /var/www/logs/
//
//Img Path - path to the mandrigo images folder ex /var/www/htdocs/images
//
//

$GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]=ereg_replace("[\\]config[\\]config.ini.php","",__FILE__)."\\inc\\";
$GLOBALS["MANDRIGO_CONFIG"]["PLUGIN_PATH"]=ereg_replace("[\\]config[\\]config.ini.php","",__FILE__)."\\inc\\packages\\";
$GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"]=ereg_replace("[\\]config[\\]config.ini.php","",__FILE__)."\\templates\\";
$GLOBALS["MANDRIGO_CONFIG"]["LOG_PATH"]=ereg_replace("[\\]config[\\]config.ini.php","",__FILE__)."\\logs\\";
$GLOBALS["MANDRIGO_CONFIG"]["IMG_PATH"]=ereg_replace("[\\]config[\\]config.ini.php","",__FILE__)."\\htdocs\\images\\mg_images\\";

//
//Site Status - false if you want to redirect to the off_site page, true if you want
//page to display normally
//
$GLOBALS["MANDRIGO_CONFIG"]["SITE_STATUS"]=true;

//
//Debug Mode - Shows php errors instead of Mandrigo errors.
//Print SQL Mode - prints sql queries.
//DO NOT SET THESE TO TRUE FOR PRODUCTION RELEASES.  THIS IS FOR DEVELOPMENT ONLY AND INTRODUCES
//SECURITY HOLES!
//
$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]=false;
$GLOBALS["MANDRIGO_CONFIG"]["SQL_PRINT_MODE"]=false;

//
//Index name is the name of what ever your main access file is ex (index.$php_ex)
//
$GLOBALS["MANDRIGO_CONFIG"]["INDEX"]="index.$php_ex";

//
//For auto installer:  If not using auto install or script has already been set up
//this should be set to true.
//
$GLOBALS["MANDRIGO_CONFIG"]["IS_INSTALLED"]=true;

//
//Path Style for includes.  Set to either win or unix
//
$path_style="win";

//////////////////////
// Language Config
/////////////////////

//
//Language - language script is in.  See documentation for valid types (ex en-US)
//
//Html Version - html version that the script is in (ex xhtml)
//

$default_lang["LANGUAGE"]="en-US";
$default_lang["HTML_VER"]="xhtml_1_0_trans";

//
//DO NOT EDIT BELOW THIS
//
if($path_style=="win"){
	$GLOBALS["MANDRIGO_CONFIG"]["PATH"]="\\";
}
else{
	$GLOBALS["MANDRIGO_CONFIG"]["PATH"]="/";	
}
?>
