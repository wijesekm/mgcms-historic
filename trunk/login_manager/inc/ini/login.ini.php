<?php
/**********************************************************
    ini.php
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
    die($GLOBALS["HTML"]["DOCTYPE"].$GLOBALS["HTML"]["HTML"].
		$GLOBALS["HTML"]["TITLE"].$GLOBALS["LANGUAGE"]["INSTALLTITLE"].
		$GLOBALS["HTML"]["TITLE!"].$GLOBALS["HTML"]["HEAD!"].$GLOBALS["HTML"]["BODY"].
		$GLOBALS["LANGUAGE"]["INSTALLWARN"].$GLOBALS["HTML"]["BODY!"].$GLOBALS["HTML"]["HTML!"]);
}
