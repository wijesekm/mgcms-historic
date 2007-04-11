<?php
/**********************************************************
    admin.ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 04/11/07

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
    header("Location: ../install/install.$php_ex");
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

package_init(array(array("ini{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}constants.ini.$php_ex",28,false),
			 array("ini{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}constants.admin.ini.$php_ex",28,true),
			 array("ini{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}clean_functions.ini.$php_ex",4,false),
			 array("server_time.class.$php_ex",5,false),
			 array("session.class.$php_ex",10,false),
			 array("adminpage.class.$php_ex",19,true),
			 array("template.class.$php_ex",20,false)));
package_init(array(array("globals{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}site.globals.$php_ex",6,false),
			 array("globals{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}server.globals.$php_ex",8,true)));

//Now we will initialize some extra database packages if needed
switch($GLOBALS["MANDRIGO"]["SITE"]["ACCOUNT_TYPE"]){
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

//
//Now we will load the user data/permissions, page data, lang data, and packages
//
package_init(array(array("acct{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}account_".$GLOBALS["MANDRIGO"]["SITE"]["ACCOUNT_TYPE"].".class.$php_ex",11,false),
			 array("globals{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}user.globals.$php_ex",9,false),
			 array("globals{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}page.globals.$php_ex",12,true),
			 array("globals{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}page.globals.$php_ex",12,false),
			 array("globals{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}acl.globals.$php_ex",13,false),
			 array("globals{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}lang.globals.$php_ex",14,false),
			 array("ini{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}adminpkg.ini.$php_ex",23,true),
		     array("globals{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}package.globals.$php_ex",17,false)));

//
//Gets rid of unneeded config vars
//
$sql_config="";
$log_config="";
$adldap_config="";
//
//Seeds random number generator
//
srand(((int)((double)microtime()*1000003)));
mt_srand(doubleval(microtime()) * 1000003);

//
//Init Script
//
function package_init($pkg){
	$pkg_size=count($pkg);
	for($pkg_c=0;$pkg_c<$pkg_size;$pkg_c++){
	 	if($pkg[$pkg_c][0]){
	 	 	if(!$pkg[$pkg_c][2]){
				$base=$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"];
			}
			else{
				$base=$GLOBALS["MANDRIGO"]["CONFIG"]["ADMIN_ROOT_PATH"];
			}
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