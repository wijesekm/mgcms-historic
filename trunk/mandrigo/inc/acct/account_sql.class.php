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
					 "UID"=>$this->uid,
					 "USERNAME"=>$this->name);
	}
		
	//
	//public ac_updateuserdata()
	//
	//updates the user data for the current user
	//INPUTS:
	//$user_data		-	array of user data
	//
	//returns true on success or false on fail
	function ac_updateuserdata($user_data){
	 	if(!$this->isuser){
			return false;
		}
		$update=array(
						array("ac_fullname",$user_data["FNAME"].";".$user_data["MNAME"].";".$user_data["LNAME"]),
						array("ac_email",$user_data["EMAIL"]),
						array("ac_im",implode(";",$user_data["IM"])),
						array("ac_website",$user_data["WEBSITE"]),
						array("ac_about",$user_data["ABOUT"]),
						array("ac_picture",$user_data["PICTURE_PATH"])
					 );
		if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_ACCOUNTS,$update,array(array("ac_id","=",$this->uid)))){
			return false;
		}
		return true;
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
	//public ac_language()
	//
	//gets the lang of the current user
	//
	//returns lang
	function ac_language(){
	 	if(!$this->isuser){
			return false;
		}
		return $this->u_data["ac_lang"];
	}
	
	//
	//public ac_updatelanguage()
	//
	//updates the user language for the current user
	//INPUTS:
	//$new_lang		-	new language
	//
	//returns true on success or false on fail	
	function ac_updatelanguage($new_lang){
	 	if(!$this->isuser){
			return false;
		}		
		if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_ACCOUNTS,array(array("ac_lang",$new_lang)),array(array("ac_id","=",$this->uid)))){
			return false;
		}
		return true;
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
	//public ac_updatetz()
	//
	//updates the user language for the current user
	//INPUTS:
	//$tz		-	new time zone
	//$dst		-	new dst
	//
	//returns true on success or false on fail	
	function ac_updatetz($tz,$dst){
	 	if(!$this->isuser){
			return false;
		}		
		if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_ACCOUNTS,array(array("ac_tz",$tz),array("ac_dst",$dst)),array(array("ac_id","=",$this->uid)))){
			return false;
		}
		return true;
	}

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
	
	//
	//public ac_groups()
	//
	//gets the names of the groups of the current user
	//
	//returns groups on success or false on fail	
	function ac_groupnames(){
	 	if(!$this->isuser){
			return false;
		}
		$q=array();
		$gid=explode(";",$this->u_data["ac_groups"]);
		if(!$gid[0]){
			return array();
		}
		$soq=count($gid);
		for($i=0;$i<$soq;$i++){
			if($gid[$i]&&$gid[$i+1]){
				$q[$i]=array("gp_id","=",$gid[$i],DB_OR);
			}
			else if($gid[$i]){
				$q[$i]=array("gp_id","=",$gid[$i]);
			}
		}
		$groups=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_GROUPS,"gp_id,gp_name",$q,"ASSOC",DB_ALL_ROWS);
		$soq=count($groups);
		$retgroups=array();
		for($i=0;$i<$soq;$i++){
			$retgroups=array_merge($retgroups,array($groups[$i]["gp_id"]=>$groups[$i]["gp_name"]));
		}
		return $retgroups;
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
