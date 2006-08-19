<?php
/**********************************************************
    ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 06/27/06

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

//
//PHP INI varables
//
set_magic_quotes_runtime(0);//we dont really want this since it will mess a bunch of stuff up
error_reporting(E_ERROR | E_WARNING | E_PARSE);//only allow certain error messages

//
//Add slashes to the input varable arrays POST, GET, and COOKIE
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
if(!$GLOBALS["MANDRIGO_CONFIG"]["IS_INSTALLED"]){
    if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
        header("Location: install/install.$php_ex");
    }
    else{
        @header("Location: install/install.$php_ex");
    }
    die();
}

//
// Now we will start up SQL and connect to the server/database
//
if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
    require_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."sql{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}".$sql_config["SQL_TYPE"].".class.$php_ex");
}
else{
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."sql{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}".$sql_config["SQL_TYPE"].".class.$php_ex"))){
        $GLOBALS["error_log"]->add_error(2,"script");
	   	die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
           	$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
    }
}
$sql_db = & new db();

if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
	$sql_db->db_connect($sql_config["SQL_HOST"],$sql_config["SQL_PORT"],$sql_config["SQL_SOCKET"],$sql_config["SQL_USER"],
						$sql_config["SQL_PASSWORD"],$sql_config["SQL_DATABASE"],true,$sql_config["USE_SSL"],$sql_config["SSL"]);
}
else{
    if(!$sql_db->db_connect($sql_config["SQL_HOST"],$sql_config["SQL_PORT"],$sql_config["SQL_SOCKET"],$sql_config["SQL_USER"],
		$sql_config["SQL_PASSWORD"],$sql_config["SQL_DATABASE"],true,$sql_config["USE_SSL"],$sql_config["SSL"])){
        $GLOBALS["error_log"]->add_error(1,"sql");
	   	die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
           	$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
    }
}

//
//Now we will load a few essential packages such as constants
//
if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}constants.ini.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}clean_functions.ini.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."server_time.class.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}funct.ini.$php_ex");
}
else{
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}constants.ini.$php_ex"))){
        $GLOBALS["error_log"]->add_error(3,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}clean_functions.ini.$php_ex"))){
        $GLOBALS["error_log"]->add_error(4,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."server_time.class.$php_ex"))){
        $GLOBALS["error_log"]->add_error(5,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}funct.ini.$php_ex"))){
        $GLOBALS["error_log"]->add_error(6,"script");
    }
    if($GLOBALS["error_log"]->get_status()==2){
	   	die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
           	$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
    }
}
//
//Gets rid of unneeded config vars
//
unset($sql_config);
unset($log_config);
unset($lang);

if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}site.globals.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}server.globals.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}user.globals.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}page.globals.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}script.globals.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}lang.globals.$php_ex");
}
else{
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}site.globals.$php_ex"))){
        $GLOBALS["error_log"]->add_error(7,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}server.globals.$php_ex"))){
        $GLOBALS["error_log"]->add_error(8,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}user.globals.$php_ex"))){
        $GLOBALS["error_log"]->add_error(9,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}page.globals.$php_ex"))){
        $GLOBALS["error_log"]->add_error(10,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}script.globals.$php_ex"))){
        $GLOBALS["error_log"]->add_error(7,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}lang.globals.$php_ex"))){
        $GLOBALS["error_log"]->add_error(17,"script");
    }    
    if($GLOBALS["error_log"]->get_status()==2){
	   	die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
           	$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
    }
}

//
//Loads remaining classes and loads page display classes
//
if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}package.ini.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."page.class.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."template.class.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."word_filter.class.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}package.globals.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."captcha.class.$php_ex");
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."phpmailer.class.$php_ex");
}
else{
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}package.ini.$php_ex"))){
        $GLOBALS["error_log"]->add_error(11,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."globals{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}package.globals.$php_ex"))){
        $GLOBALS["error_log"]->add_error(8,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."page.class.$php_ex"))){
        $GLOBALS["error_log"]->add_error(12,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."template.class.$php_ex"))){
        $GLOBALS["error_log"]->add_error(13,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."word_filter.class.$php_ex"))){
        $GLOBALS["error_log"]->add_error(14,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."captcha.class.$php_ex"))){
        $GLOBALS["error_log"]->add_error(16,"script");
    }
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."phpmailer.class.$php_ex"))){
        $GLOBALS["error_log"]->add_error(17,"script");
    }
    if($GLOBALS["error_log"]->get_status()==2){
		die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
           	$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
    }
}

//
//Seeds random number generator
//
srand(((int)((double)microtime()*1000003)));

//
//Stats and Banning
//
if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
    include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}stats.ini.$php_ex");

}
else{
    if(!(@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."ini{$GLOBALS["MANDRIGO_CONFIG"]["PATH"]}stats.ini.$php_ex"))){
        $GLOBALS["error_log"]->add_error(15,"script");
    }
    if($GLOBALS["error_log"]->get_status()==2){
	   	die($GLOBALS["ELOG"]["HTMLHEAD"].$GLOBALS["ELOG"]["TITLE"].$GLOBALS["ELOG"]["HTMLBODY"].
           	$GLOBALS["error_log"]->generate_report().$GLOBALS["ELOG"]["HTMLEND"]);
    }
}
?>
