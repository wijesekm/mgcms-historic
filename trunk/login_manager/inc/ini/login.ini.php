<?php
/**********************************************************
    ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 01/31/07

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
//PHP INI varables
//

//turns off magic quotes runtime
@set_magic_quotes_runtime(0);

//sets php error reporting
if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
	@error_reporting(E_ALL);
}
else{
	@error_reporting(0);
}

//
//Add slashes to the input varable arrays POST, GET, and COOKIE if magic_quotes_gpc not set
//
if(!get_magic_quotes_gpc()){
	if(is_array($HTTP_GET_VARS)){
		while(list($k, $v)=each($HTTP_GET_VARS)){
			if(is_array($HTTP_GET_VARS[$k])){
				while(list($k2, $v2)=each($HTTP_GET_VARS[$k])){
					$HTTP_GET_VARS[$k][$k2]=addslashes($v2);
				}
				@reset($HTTP_GET_VARS[$k]);
			}
			else{
				$HTTP_GET_VARS[$k]=addslashes($v);
			}
		}
		@reset($HTTP_GET_VARS);
	}

	if(is_array($HTTP_POST_VARS)){
		while(list($k, $v)=each($HTTP_POST_VARS)){
			if(is_array($HTTP_POST_VARS[$k])){
				while(list($k2, $v2)=each($HTTP_POST_VARS[$k])){
					$HTTP_POST_VARS[$k][$k2]=addslashes($v2);
				}
				@reset($HTTP_POST_VARS[$k]);
			}
			else{
				$HTTP_POST_VARS[$k]=addslashes($v);
			}
		}
		@reset($HTTP_POST_VARS);
	}

	if(is_array($HTTP_COOKIE_VARS)){
		while(list($k, $v)=each($HTTP_COOKIE_VARS)){
			if(is_array($HTTP_COOKIE_VARS[$k])){
				while(list($k2, $v2)=each($HTTP_COOKIE_VARS[$k])){
					$HTTP_COOKIE_VARS[$k][$k2]=addslashes($v2);
				}
				@reset($HTTP_COOKIE_VARS[$k]);
			}
			else{
				$HTTP_COOKIE_VARS[$k]=addslashes($v);
			}
		}
		@reset($HTTP_COOKIE_VARS);
	}
}

//
// If install has not been compleated we will forward to the install page
//
if(!$GLOBALS["MANDRIGO"]["CONFIG"]["IS_INSTALLED"]){
    header("Location: install/install.$php_ex");
    die();
}

//
// Now we will start up SQL and connect to the server/database
//
if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
    require_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."db{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}".$sql_config["SQL_TYPE"].".class.$php_ex");
}
else{
    if(!(@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."db{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}".$sql_config["SQL_TYPE"].".class.$php_ex"))){
        $GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(3,"core");
	   	die($GLOBALS["MANDRIGO"]["ELOG"]["HTMLHEAD"].$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"].$GLOBALS["MANDRIGO"]["ELOG"]["HTMLBODY"].
           	$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport().$GLOBALS["MANDRIGO"]["ELOG"]["HTMLEND"]);
    }
}
$GLOBALS["MANDRIGO"]["DB"] = & new db();

if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
	$GLOBALS["MANDRIGO"]["DB"]->db_connect($sql_config["SQL_HOST"],$sql_config["SQL_PORT"],$sql_config["SQL_SOCKET"],$sql_config["SQL_USER"],
						$sql_config["SQL_PASSWORD"],$sql_config["SQL_DATABASE"],true,$sql_config["USE_SSL"],$sql_config["SSL"]);
}
else{
    if(!$GLOBALS["MANDRIGO"]["DB"]->db_connect($sql_config["SQL_HOST"],$sql_config["SQL_PORT"],$sql_config["SQL_SOCKET"],$sql_config["SQL_USER"],
		$sql_config["SQL_PASSWORD"],$sql_config["SQL_DATABASE"],true,$sql_config["USE_SSL"],$sql_config["SSL"])){
        $GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(2,"sql");
	   	die($GLOBALS["MANDRIGO"]["ELOG"]["HTMLHEAD"].$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"].$GLOBALS["MANDRIGO"]["ELOG"]["HTMLBODY"].
           	$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport().$GLOBALS["MANDRIGO"]["ELOG"]["HTMLEND"]);
    }
}
//
//Need to set this up for user init
//
$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"]=time();

//
//Some User INIT
//
$GLOBALS["MANDRIGO"]["CURRENTUSER"]["IP"]=(!empty($HTTP_SERVER_VARS['REMOTE_ADDR']))?$HTTP_SERVER_VARS['REMOTE_ADDR']:((!empty($HTTP_ENV_VARS['REMOTE_ADDR']))?$HTTP_ENV_VARS['REMOTE_ADDR']:getenv('REMOTE_ADDR'));
$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UAGENT"]=(!empty($HTTP_SERVER_VARS['HTTP_USER_AGENT']))?$HTTP_SERVER_VARS['HTTP_USER_AGENT']:((!empty($HTTP_ENV_VARS['HTTP_USER_AGENT']))?$HTTP_ENV_VARS['HTTP_USER_AGENT']:getenv('HTTP_USER_AGENT'));
if(!$GLOBALS["MANDRIGO"]["CURRENTUSER"]["IP"]){
	$GLOBALS["MANDRIGO"]["CURRENTUSER"]["IP"]="000.000.000.000";
}

//
//Now we will load the first set of packages/globals
//

$init1=array(array("ini{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}constants.ini.$php_ex",3),
				  array("ini{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}clean_functions.ini.$php_ex",4),
				  array("server_time.class.$php_ex",5),
				  array("session.class.$php_ex",10),
				  array("stats.class.$php_ex",18),
				  array("template.class.$php_ex",20));			  
$init2=array(array("globals{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}site.globals.$php_ex",6),
			 array("globals{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}lang.globals.$php_ex",14));
package_init($init1);
package_init($init2);

$init3=array(array("acct{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}account_".$GLOBALS["MANDRIGO"]["SITE"]["ACCOUNT_TYPE"].".class.$php_ex",11));
package_init($init3);

//Now we will initialize some extra database packages if needed
switch($GLOBALS["MANDRIGO"]["SITE"]["AUTH_TYPE"]){
	case "ad":
		if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
    		require_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."db{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}"."ad.class.$php_ex");
		}
		else{
    		if(!(@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."db{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}"."ad.class.$php_ex"))){
        		$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(6,"core");
    		}
		}
		
		$GLOBALS["MANDRIGO"]["AD"] = & new ad();
		
		if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
			$GLOBALS["MANDRIGO"]["AD"]->ad_connect($adldap_config["DN"],$adldap_config["DC"],$adldap_config["ACCT_SUFFIX"],$adldap_config["CONTROL_USER"],$adldap_config["CONTROL_PASSWORD"]);
		}
		else{
		    if(!$GLOBALS["MANDRIGO"]["AD"]->ad_connect($adldap_config["DN"],$adldap_config["DC"],$adldap_config["ACCT_SUFFIX"],$adldap_config["CONTROL_USER"],$adldap_config["CONTROL_PASSWORD"])){
		        $GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(2,"ldap");
		    }
		}
	break;
	case "ldap":
		//no support yet (use sql)
	break;
	default:
	
	break;	
};

$init4=array(array("globals{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}server.globals.$php_ex",8),
			 array("auth{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}{$GLOBALS["MANDRIGO"]["SITE"]["AUTH_TYPE"]}_auth.class.$php_ex",21),
			 array("login.class.$php_ex",22));
package_init($init4,false);

//
//Gets rid of unneeded config vars
//
$sql_config="";
$log_config="";
$lang="";
$adldap_config="";
//
//Seeds random number generator
//
srand(((int)((double)microtime()*1000003)));
mt_srand(doubleval(microtime()) * 1000003);

//
//Init Script
//
function package_init($pkg,$root=true){
	$pkg_size=count($pkg);
	if($root){
		$base=$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"];
	}
	else{
		$base=$GLOBALS["MANDRIGO"]["CONFIG"]["LOGIN_ROOT_PATH"];
	}
	for($pkg_c=0;$pkg_c<$pkg_size;$pkg_c++){
	 	if($pkg[$pkg_c][0]){
			if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
				include_once($base.$pkg[$pkg_c][0]);
			}
			else{
				if(!(@include_once($base.$pkg[$pkg_c][0]))){
					$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror($pkg[$pkg_c][1],"core");
				}
			}	
		}
	}
    if($GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_getstatus()==2){
	   	die($GLOBALS["MANDRIGO"]["ELOG"]["HTMLHEAD"].$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"].$GLOBALS["MANDRIGO"]["ELOG"]["HTMLBODY"].
           	$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport().$GLOBALS["MANDRIGO"]["ELOG"]["HTMLEND"]);
    }
}
