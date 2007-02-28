<?php
/**********************************************************
    account_sql.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/18/07

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

@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."acct{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}account.class.".PHP_EXT);

class account extends _account{
	
	function account($uid){
		$this->ac_setuser($uid);
	}

	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	
		
	//
	//public ac_setuser($uid)
	//
	//sets the current username	
	function ac_setuser($uid){
		$r=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_ACCOUNTS,"ac_username,ac_id",array(array("ac_id","=",$uid)));
		print_r($r);
		if((int)$r["ac_id"]===(int)$uid){
			$this->name=(string)$r["ac_username"];
			$this->uid=(int)$r["ac_id"];
			$this->isuser=true;
			$this->ac_getuserdata();
		}
		else{
			$this->isuser=false;
			$this->uid=false;
			return false;
		}
		return true;
	}
		
	//
	//public ac_id()
	//
	//gets the id of the current user
	//
	//returns id on success or false on fail	
	function ac_id(){
	 	if(!$this->isuser){
			return false;
		}
		return $this->u_data["ac_id"];
	}
	
	//
	//public ac_uname()
	//
	//gets the uname of the current user
	//
	//returns uname on success or false on fail	
	function ac_uname(){
		if(!$this->isuser){
			return false;
		}
		return $this->uname;
	}
		
	//
	//public ac_userdata()
	//
	//gets the user data of the current user
	//
	//returns user data
	function ac_userdata(){
	 	if(!$this->isuser){
			return false;
		}
	 	$tmp=explode(";",$this->u_data["ac_fullname"]);
		return array("FNAME"=>$tmp[0],
					 "MNAME"=>$tmp[1],
					 "LNAME"=>$tmp[2],
					 "EMAIL"=>$this->u_data["ac_email"],
					 "IM"=>explode(";",$this->u_data["ac_im"]),
					 "WEBSITE"=>$this->u_data["ac_website"],
					 "ABOUT"=>$this->u_data["ac_about"],
					 "PICTURE_PATH"=>$this->u_data["ac_picture"],
					 "UID"=>$this->ac_id(),
					 "USERNAME"=>$this->ac_uname());
	}
	//
	//public ac_last()
	//
	//gets the last time and ip of various actions of the current user
	//
	//returns data	
	function ac_last(){
	 	if(!$this->isuser){
			return false;
		}
		return array("LT"=>$this->u_data["ac_lastlogin"],
					 "LIP"=>$this->u_data["ac_lastip"],
					 "PCT"=>$this->u_data["ac_lastpwdchg"]);
	}

	//
	//public ac_timezone()
	//
	//gets the timezone/dst of the current user
	//
	//returns timezone/dst
	function ac_timezone(){
	 	if(!$this->isuser){
			return false;
		}
		return array("TZ"=>$this->u_data["ac_tz"],
					 "DST"=>$this->u_data["ac_dst"]);		
	}

	//
	//public ac_logindata()
	//
	//gets the login data of the current user
	//
	//returns login data on success or false on fail	
	function ac_logindata(){}

	//
	//public ac_groups()
	//
	//gets the groups of the current user
	//
	//returns groups on success or false on fail	
	function ac_groups(){
	 	if(!$this->isuser){
			return false;
		}
		return explode(";",$this->u_data["ac_groups"]);
	}
	
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################	
	
	//
	//private ac_getuserdata()
	//
	//gets the userdata from the database
	//
	//returns true on success or false on fail
	function ac_getuserdata(){
		if(!$this->isuser){
			return false;
		}
		$this->u_data=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_ACCOUNTS,"",array(array("ac_username","=",$this->name)));		
		return true;
	}
}
