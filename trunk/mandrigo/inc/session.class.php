<?php
/**********************************************************
    session.class.php
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

class session{

	var $session_db;

	function session($db){
		$this->session_db=$db;
	}
	function session_start($uid,$expires,$secure,$path,$domains){
	  	if($uid<=1){
			return false;
		}
		$sessionid=md5(uniqid(rand(),true));
		if(!$this->sql_db->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_USER_DATA,array(array("user_session",$sessionid)),array(array("user_id","=",$uid)))){
			return false;
		}
		
		$domains=explode(";",$domains);
		for($i=0;$i<count($domains);$i++){
		  	if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
				setcookie(SESSION_COOKIE,$sessionid,$expires,$path,$domains[$i],$secure);
				setcookie(USER_COOKIE,$uid,$expires,$path,$domains[$i],$secure);
			}	
			else{
				if(!(@setcookie(SESSION_COOKIE,$sessionid,$expires,$path,$domains[$i],$secure))){
					return false;
				}
				if(!(@setcookie(USER_COOKIE,$uid,$expires,$path,$domains[$i],$secure))){
					return false;
				}				
			}
		}

	  	return $sessionid;
	}
	function session_renew($sesid,$uid,$expires,$secure,$path,$domains,$db=false){
	  	if($db){
			if(!$this->sql_db->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_USER_DATA,array(array("user_session",$sesid)),array(array("user_id","=",$uid)))){
				return false;
			}		
		}
		$domains=explode(";",$domains);
		for($i=0;$i<count($domains);$i++){
		  	if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
				setcookie(SESSION_COOKIE,$sessionid,$expires,$path,$domains[$i],$secure);
				setcookie(USER_COOKIE,$uid,$expires,$path,$domains[$i],$secure);
			}	
			else{
				if(!(@setcookie(SESSION_COOKIE,$sessionid,$expires,$path,$domains[$i],$secure))){
					return false;
				}
				if(!(@setcookie(USER_COOKIE,$uid,$expires,$path,$domains[$i],$secure))){
					return false;
				}				
			}
		}
		return true;		
	}
	function session_stop($uid){
	  	if($uid<=1){
			return false;
		}
		if(!$this->sql_db->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_USER_DATA,array(array("user_session","")),array(array("user_id","=",$uid)))){
			return false;
		}	
		return true;	  
	}
	function session_id($uid){
		return $this->sql_db->db_fetchresult(TABLE_PREFIX.TABLE_USER_DATA,"user_session",array(array("user_id","=",$uid)));  
	} 
}

?>