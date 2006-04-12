<?php
/**********************************************************
    login.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/20/06

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
class login{

	var $login_db;
	var $error_log;

	
	function login(&$error,&$sql){
		$this->error_log=$error;
		$this->login_db=$sql;
	}
	function display($login=false,$error=""){
		$tpl=new template();
		if($login){
			return $this->check_login();
		}
		if(!$tpl->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].TPL_LOGIN)){
		  	if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
				$this->error_log->add_error(31,"script");	
				die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                	$this->error_log->generate_report().$GLOBALS["HTML"]["EEND"]);
            }
            else{
				die();
			}
		}
		$action="";
		if($GLOBALS["SITE_DATA"]["URL_FORMAT"]){
			$action=$GLOBALS["SITE_DATA"]["LOGIN_URL"].$GLOBALS["MANDRIGO_CONFIG"]["LOGIN"]."/a/li"; 
		}
		else{
	  		$action=$GLOBALS["SITE_DATA"]["LOGIN_URL"].$GLOBALS["MANDRIGO_CONFIG"]["LOGIN"]."?a=li"; 
		}
		$pparse_vars=array("ACTION",$action,"USER_NAME",$GLOBALS["USER_DATA"]["USER_NAME"],"ERROR",$error);
		$tpl->pparse($pparse_vars);
		return $tpl->return_template();
	}
	function check_login(){
		$auth=new auth($this->login_db);
		if($uid=$auth->auth_validate($GLOBALS["HTTP_POST"]["USER_NAME"],$GLOBALS["HTTP_POST"]["USER_PASSWORD"],$GLOBALS["SITE_DATA"]["CRYPT_TYPE"])){
			if($uid>1){
				if(!$auth->auth_loguserin($uid,time(),$GLOBALS["USER_DATA"]["IP"],$GLOBALS["SITE_DATA"]["SESSION_LEN"],$GLOBALS["SITE_DATA"]["COOKIE_SECURE"],$GLOBALS["SITE_DATA"]["COOKIE_PATH"],$GLOBALS["SITE_DATA"]["COOKIE_DOMAINS"])){
					if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
						die();
					}
					else{
						$this->error_log->add_error(300,"script");	
						die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
	                		$this->error_log->generate_report().$GLOBALS["HTML"]["EEND"]);
	                	}
				}
				$chdir=ereg_replace("&q;","?",$GLOBALS["HTTP_GET"]["REDIRECT"]);
				$chdir=ereg_replace("&s;","/",$GLOBALS["HTTP_GET"]["REDIRECT"]);
				$chdir=ereg_replace("&a;","&",$GLOBALS["HTTP_GET"]["REDIRECT"]);
				header("Location: ".$chdir);
				die();
			}
			else{
				return $this->display(false,$GLOBALS["LANGUAGE"]["BAD_LOGIN"]);
			}
		}
		else{
			return $this->display(false,$GLOBALS["LANGUAGE"]["BAD_LOGIN"]);
		}
		return false;	
	}
}