<?php
/**********************************************************
    account_ad.class.php
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
	
	var $u_data_sql;
	
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
		if((int)$r["ac_id"]===(int)$uid){
			$n=$GLOBALS["MANDRIGO"]["AD"]->ad_userinfo($r["ac_username"],array("samaccountname"));
			if((string)$n[0]["samaccountname"][0]===(string)$r["ac_username"]){
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
		return $this->uid;
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
		return $this->name;
	}	
	
	//
	//public ac_userdata()
	//
	//gets the user data of the current user
	//
	//returns user data on success or false on fail	
	function ac_userdata(){
	 	if(!$this->isuser){
			return false;
		}
		$sn=array();
		$count=0;
		$info_ar=explode("<info>",$this->u_data["info"][0]);
		$info_ar=explode("<im>",$info_ar[1]);
		$about=$info_ar[0];
		$info_ar=explode("<pic>",$info_ar[1]);
		$sn=$info_ar[0];
		$pic=explode("<tz>",$info_ar[1]);
		$pic=$pic[0];
		return array("FNAME"=>trim($this->u_data["givenname"][0]),
					 "MNAME"=>substr($this->u_data["initials"][0],1,1),
					 "LNAME"=>trim($this->u_data["sn"][0]),
					 "EMAIL"=>trim($this->u_data["mail"][0]),
					 "IM"=>trim(explode(";",$sn)),
					 "WEBSITE"=>trim($this->u_data["wwwhomepage"][0]),
					 "ABOUT"=>trim($about),
					 "PICTURE_PATH"=>trim($pic),
					 "UID"=>$this->ac_id(),
					 "USERNAME"=>$this->ac_uname());
	}

	//
	//public ac_lastlogin()
	//
	//gets the last login time and ip of the current user
	//
	//returns last login data on success or false on fail	
	function ac_last(){
	 	if(!$this->isuser){
			return false;
		}
		return array("LT"=>$this->u_data_sql["ac_lastlogin"],
					 "LIP"=>$this->u_data_sql["ac_lastip"],
					 "PCT"=>$this->u_data_sql["ac_lastpwdchg"]);
	}

	//
	//public ac_timezone()
	//
	//gets the timezone/dst of the current user
	//
	//returns timezone/dst on success or false on fail	
	function ac_timezone(){
	 	if(!$this->isuser){
			return false;
		}
		$tz=explode("<tz>",$this->u_data["info"][0]);
		$tz=explode("<dst>",$tz[1]);
		return array("TZ"=>trim($tz[0]),
					 "DST"=>trim($tz[1]));		
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
		$tgroups=array();
		$groups=$GLOBALS["MANDRIGO"]["AD"]->ad_usergroups($this->name);
		$mg_groups=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_GROUPS,"gp_name,gp_id","","ASSOC",DB_ALL_ROWS);
		if(!is_array($mg_groups)){
			$mg_groups=array(array("gp_name"=>$mg_groups));
		}
		$soq=count($mg_groups);
		$tmp_groups=array();
		$count=0;
		for($k=0;$k<$soq;$k++){
			if(in_array($mg_groups[$k]["gp_name"],$groups)&&!in_array($groups[$k],$tmp_groups)){
				$tmp_groups[$count]=$mg_groups[$k]["gp_id"];
				$count++;
			}
		}
		return $tmp_groups;
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
		$this->u_data=$GLOBALS["MANDRIGO"]["AD"]->ad_userinfo($this->name,array("givenname","info","sn","initials","mail","wWWHomePage"));
		$this->u_data=$this->u_data[0];
		$this->u_data_sql=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_ACCOUNTS,"ac_lastlogin,ac_lastip,ac_lastpwdchg",array(array("ac_username","=",$this->name)));
		return true;
	}
}
