<?php
/**********************************************************
    session.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/27/07

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

define("SESSION_COOKIE","mg_sesid");
define("USER_COOKIE","mg_uid");

class session{

	var $sesid;
	var $uid;

	function session(){}
	
	function se_startnew($uid,$expires,$secure,$path,$domains){
	  	if((int)$uid<=1){
			return false;
		}
		$this->sesid=md5(uniqid(rand(),true));
		$this->uid=$uid;
		if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_ACCOUNTS,array(array("ac_session",$this->sesid)),array(array("ac_id","=",$uid)))){
			return false;
		}
		
		$domains=explode(";",$domains);
		$secure=explode(";",$secure);
		$paths=explode(";",$path);
		for($i=0;$i<count($domains);$i++){
		  	if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
				setcookie(SESSION_COOKIE,$this->sesid,$expires,$paths[$i],$domains[$i],$secure[$i]);
				setcookie(USER_COOKIE,$this->uid,$expires,$path,$domains[$i],$secure);
				return false;
			}	
			else{
				if(!(@setcookie(SESSION_COOKIE,$this->sesid,$expires,$paths[$i],$domains[$i],$secure[$i]))){
					return false;
				}
				if(!(@setcookie(USER_COOKIE,$this->uid,$expires,$paths[$i],$domains[$i],$secure[$i]))){
					return false;
				}				
			}
		}
		return true;	
	}
	function se_load($uid){
	 	if($this->uid>=1){
			return true;
		}
		if((int)$uid<=1){
			return false;
		}
		$this->sesid=(string)$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_ACCOUNTS,"ac_session",array(array("ac_id","=",$uid)));  
		$this->uid=(int)$uid;
		if(!$this->uid&&!$this->sesid){
			return false;
		}
		return true;
	}
	function se_check($uid,$sesid){
		if((int)$uid<=1||!$sesid){
			return false;
		}
		if(!$this->uid&&!$this->sesid){
			return false;
		}
		if((string)$sesid===$this->sesid&&(int)$uid===$this->uid){
			return $this->se_checkstatus();
		}
		return false;
	}
	function se_checkstatus(){
		$s=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_ACCOUNTS,"ac_status,ac_expires",array(array("ac_id","=",$uid)));
		if($s["ac_status"]=="D"||$s["ac_expires"]<$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"]){
			return false;
		}
		return true;
	}
	function se_renew($expires,$secure,$path,$domains){
		if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_ACCOUNTS,array(array("ac_session",$this->sesid)),array(array("ac_id","=",$this->uid)))){
			return false;
		}		
		$domains=explode(";",$domains);
		$secure=explode(";",$secure);
		$paths=explode(";",$path);
		for($i=0;$i<count($domains);$i++){
		  	if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
				setcookie(SESSION_COOKIE,$this->sesid,$expires,$paths[$i],$domains[$i],$secure[$i]);
				setcookie(USER_COOKIE,$this->uid,$expires,$path,$domains[$i],$secure);
				return false;
			}	
			else{
				if(!(@setcookie(SESSION_COOKIE,$this->sesid,$expires,$paths[$i],$domains[$i],$secure[$i]))){
					return false;
				}
				if(!(@setcookie(USER_COOKIE,$this->uid,$expires,$paths[$i],$domains[$i],$secure[$i]))){
					return false;
				}				
			}
		}
		return true;		
	}
	function se_stop($expires,$domains,$secure,$path){
		if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_ACCOUNTS,array(array("ac_session","")),array(array("ac_id","=",$this->uid)))){
			return false;
		}
		$this->uid=false;
		$this->sesid=false;	
		$domains=explode(";",$domains);
		$secure=explode(";",$secure);
		$paths=explode(";",$path);
		for($i=0;$i<count($domains);$i++){
		  	if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
				setcookie(SESSION_COOKIE,"none",$expires,$paths[$i],$domains[$i],$secure[$i]);
				setcookie(USER_COOKIE,"1",$expires,$path,$domains[$i],$secure);
				return false;
			}	
			else{
				if(!(@setcookie(SESSION_COOKIE,"none",$expires,$paths[$i],$domains[$i],$secure[$i]))){
					return false;
				}
				if(!(@setcookie(USER_COOKIE,"1",$expires,$paths[$i],$domains[$i],$secure[$i]))){
					return false;
				}
			}
		}
		return true;	  
	}
	function se_uid(){
		return $this->uid;
	}
}