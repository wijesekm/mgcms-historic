<?php
/**********************************************************
    login.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 04/13/06

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

	var $page_parse_vars;
	var $tpl;
	
	function login(){
        $this->page_parse_vars = array(
            "SITE_NAME",$GLOBALS["MANDRIGO"]["SITE"]["SITE_NAME"],
            "SITE_URL",$GLOBALS["MANDRIGO"]["SITE"]["SITE_URL"],
            "IMG_URL",$GLOBALS["MANDRIGO"]["SITE"]["IMG_URL"],
            "SERVER_DATE",date($GLOBALS["MANDRIGO"]["SITE"]["DATE_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"]),
            "SERVER_TIME",date($GLOBALS["MANDRIGO"]["SITE"]["TIME_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"]),
            "GMT_DATE",date($GLOBALS["MANDRIGO"]["SITE"]["DATE_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["GMT"]),
            "GMT_TIME",date($GLOBALS["MANDRIGO"]["SITE"]["TIME_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["GMT"]),
            "WEBMASTER_NAME",$GLOBALS["MANDRIGO"]["SITE"]["WEBMASTER_NAME"],
            "WEBMASTER_EMAIL",$GLOBALS["MANDRIGO"]["SITE"]["WEBMASTER_EMAIL"],
            "SITE_LAST_UPDATED",$GLOBALS["MANDRIGO"]["SITE"]["LAST_UPDATED"],
            "MG_VER",$GLOBALS["MANDRIGO"]["SITE"]["MANDRIGO_VER"],
            "INDEX_NAME",$GLOBALS["MANDRIGO"]["SITE"]["INDEX_NAME"],
            "CUSER_DATE",date($GLOBALS["MANDRIGO"]["SITE"]["DATE_FORMAT"],$GLOBALS["MANDRIGO"]["CURRENTUSER"]["TIME"]),
            "CUSER_TIME",date($GLOBALS["MANDRIGO"]["SITE"]["TIME_FORMAT"],$GLOBALS["MANDRIGO"]["CURRENTUSER"]["TIME"]),
		);
	}
	
	function li_display(){
		$auth=new auth();
		if($auth->auth_checkses($GLOBALS["MANDRIGO"]["VARS"]["COOKIE_USER"],$GLOBALS["MANDRIGO"]["VARS"]["COOKIE_SESSION"])){
			header("Location: ".$GLOBALS["MANDRIGO"]["SITE"]["SITE_URL"].$GLOBALS["MANDRIGO"]["VARS"]["TARGET"]);
			die();
		}
		$this->tpl=new template();
		$this->tpl->tpl_load($GLOBALS["MANDRIGO"]["CONFIG"]["TEMPLATE_PATH"].TPL_LOGIN,"main");
		$content="";
		$title="";
		
		switch($GLOBALS["MANDRIGO"]["VARS"]["ACTION"]){
		 	case "login":
		 		$user_name=trim($GLOBALS["MANDRIGO"]["VARS"]["LI_USER"]);
				$user_password=trim($GLOBALS["MANDRIGO"]["VARS"]["LI_PASSWORD"]);
				$crypt_type=$GLOBALS["MANDRIGO"]["SITE"]["CRYPT_TYPE"];
				$result=$auth->auth_check($user_name,$user_password,$crypt_type);
		 		if($result===2){
					if($GLOBALS["MANDRIGO"]["SITE"]["AUTO_REG"]=="1"){
					 	$params=array("ac_username","ac_created","ac_lastchange");
					 	$set=array($user_name,$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"],$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"]);
							

						if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.TABLE_ACCOUNTS,$set,$params)){
							$content=$this->li_displaymain($GLOBALS["MANDRIGO"]["LANGUAGE"]["LI_INERROR"]);
							$title=$GLOBALS["MANDRIGO"]["LANGUAGE"]["LI_TITLE"]." - {SITE_NAME}";								
						}
						else{
							$this->li_login($auth,$user_name);		
						}
					}
					else{
						$content=$this->li_displaymain($GLOBALS["MANDRIGO"]["LANGUAGE"]["LI_NOREG"]);
						$title=$GLOBALS["MANDRIGO"]["LANGUAGE"]["LI_TITLE"]." - {SITE_NAME}";						
					}
				}
				else if($result===true){
					$this->li_login($auth,$user_name);	
				}
				else{
					$content=$this->li_displaymain($GLOBALS["MANDRIGO"]["LANGUAGE"]["LI_BADCRED"]);
					$title=$GLOBALS["MANDRIGO"]["LANGUAGE"]["LI_TITLE"]." - {SITE_NAME}";					
				}
		 		$user_password="";
		 		$user_name="";
		 		$crypt_type="";
		 	break;
			default:
				$content=$this->li_displaymain();
				$title=$GLOBALS["MANDRIGO"]["LANGUAGE"]["LI_TITLE"]." - {SITE_NAME}";
			break;
		}
		$this->tpl->tpl_parse($this->li_appendarray(array("CONTENT",$content,"PAGE_TITLE",$title),$this->page_parse_vars),"main");
		return $this->tpl->tpl_return("main");
	}
	
	function li_displaymain($error=""){
	 	if($GLOBALS['MANDRIGO']['SITE']['URL_FORMAT']==1){
			$action=$GLOBALS['MANDRIGO']['SITE']['LOGIN_URL'].$GLOBALS['MANDRIGO']['SITE']['LOGIN_NAME']."/a/login";
		}
		else{
			$action=$GLOBALS['MANDRIGO']['SITE']['LOGIN_URL'].$GLOBALS['MANDRIGO']['SITE']['LOGIN_NAME']."?a=login";
		} 
		$this->tpl->tpl_load($GLOBALS["MANDRIGO"]["CONFIG"]["TEMPLATE_PATH"].TPL_LOGIN,"login");
		$this->tpl->tpl_parse(array("ACTION",$action,"ERROR",$error),"login",1,false);
		return $this->tpl->tpl_return("login");	
	}
	
	function li_login(&$auth,$uname){
	 	$uid=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_ACCOUNTS,"ac_id",array(array("ac_username","=",$uname)));
		$ip=$GLOBALS["MANDRIGO"]["CURRENTUSER"]["IP"];
		$timestamp=$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"];
		$expires=$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_EXPIRES"];
		if(!$auth->auth_login($uid,$ip,$timestamp,$expires)){
			return $this->li_displaymain($GLOBALS["MANDRIGO"]["LANGUAGE"]["LI_INERROR"]);	
		}
		header("Location: ".$GLOBALS["MANDRIGO"]["SITE"]["SITE_URL"].$GLOBALS["MANDRIGO"]["VARS"]["TARGET"]);
		die();
	}    

	//
    //private function pg_mergearrays($a1,$a2)
    //
    //appends $a2 onto the end of $a1
    //
    //INPUTS:
    //$a1		-	array
    //$a2		-	array
    //
	//returns the combined array	
    function li_appendarray($a1,$a2){
		$size1=count($a1);
		$size2=count($a2);
		$soq=$size1+$size2;
		for($i=$size1;$i<$soq;$i++){
			$a1[$i]=$a2[$i-($size1)];
		}
		return $a1;
	}
}