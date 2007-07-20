<?php
/**********************************************************
    group_sql.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/19/07

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


@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."acct{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}group.class.".PHP_EXT);

class group extends _group{
	
	var $name;
	var $gid;
	var $isgroup;
	var $g_data;
	
	function group($gid){
		return $this->gp_setid($gid);
	}

	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	
		
	//
	//public gp_setid()
	//
	//loads a group given its id
	//INPUT:
	//$gid		-	group id
	//
	//returns id on success or false on fail	
	function gp_setid($gid){
		$r=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_GROUPS,"gp_id,gp_name",array(array("gp_id","=",$gid)));
		if((int)$r["gp_id"]===(int)$gid){
			$this->name=(string)$r["gp_name"];
			$this->gid=(int)$r["gp_id"];
			$this->isgroup=true;
			return $this->gp_getgpdata();
		}
		else{
			$this->isgroup=false;
			$this->gid=false;
			return false;
		}
		return true;
	}
		
	//
	//public gp_id()
	//
	//gets the id of the current group
	//
	//returns id on success or false on fail	
	function gp_id(){
		if(!$this->isgroup){
			return false;
		}
		return $this->gid;
	}
	//
	//public gp_name()
	//
	//gets the id of the current group
	//
	//returns id on success or false on fail	
	function gp_name(){
		if(!$this->isgroup){
			return false;
		}
		return $this->name;
	}	
	//
	//public gp_data()
	//
	//gets the data for the current group
	//
	//returns data on success or false on fail	
	function gp_data(){
		if(!$this->isgroup){
			return false;
		}
		return array("NAME"=>$this->name,
					 "GID"=>$this->gid,
					 "GP_ABOUT"=>$this->g_data["gp_about"],
					 "GP_PICTURE"=>$this->g_data["gp_picture"]);
	}
	
	//
	//public gp_updatedata()
	//
	//updates the group data
	//$new_data			-	array of new group data
	//
	//returns true on sucess or false on fail
	function gp_updatedata($new_data){
		if(!$this->isgroup){
			return false;
		}
		$update=array(
						array("gp_name",$new_data["NAME"]),
						array("gp_about",$new_data["ABOUT"]),
						array("gp_picture",$new_data["PICTURE"])	
					 );
		if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_GROUPS,$update,array(array("gp_id","=",$this->gid)))){
			return false;
		}
		return true;	
	}
	
	//
	//public gp_admins()
	//
	//gets the usernames of the admins of the group
	//
	//returns array of unames on success or false on fail		
	function gp_admins(){
	 	if(!$this->isgroup){
			return false;
		}
		$q=array();
		$uid=explode(";",$this->g_data["gp_admins"]);
		if(!$uid[0]){
			return array();
		}
		$soq=count($uid);
		for($i=0;$i<$soq;$i++){
			if($uid[$i]&&$uid[$i+1]){
				$q[$i]=array("ac_id","=",$uid[$i],DB_OR);
			}
			else if($gid[$i]){
				$q[$i]=array("ac_id","=",$uid[$i]);
			}
		}
		$users=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_ACCOUNTS,"ac_id,ac_username",$q,"ASSOC",DB_ALL_ROWS);
		$soq=count($users);
		$retusers=array();
		for($i=0;$i<$soq;$i++){
			$retusers=array_merge($retusers,array($users[$i]["ac_id"]=>$users[$i]["ac_username"]));
		}
		return $retusers;
	}
	
	//
	//public gp_updateadmins()
	//
	//replaces the current admins list with the new list
	//$new_admins		-	array of new admins uids
	//
	//returns true on sucess or false on fail
	function gp_updateadmins($new_admins){
	 	if(!$this->isgroup){
			return false;
		}
		$admin_string=implode(";",$new_admins);
		if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_GROUPS,array(array("gp_admins",$new_admins)),array(array("gp_id","=",$this->gid)))){
			return false;
		}
		return true;			
	}
	
	//
	//public gp_members()
	//
	//gets the usernames of the members of the group
	//
	//returns array of unames on success or false on fail		
	function gp_members(){
	 	if(!$this->isgroup){
			return false;
		}
		$q=array();
		$uid=explode(";",$this->g_data["gp_users"]);
		if(!$uid[0]){
			return array();
		}
		$soq=count($uid);
		for($i=0;$i<$soq;$i++){
			if($uid[$i]&&$uid[$i+1]){
				$q[$i]=array("ac_id","=",$uid[$i],DB_OR);
			}
			else if($gid[$i]){
				$q[$i]=array("ac_id","=",$uid[$i]);
			}
		}
		$users=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_ACCOUNTS,"ac_id,ac_username",$q,"ASSOC",DB_ALL_ROWS);
		$soq=count($users);
		$retusers=array();
		for($i=0;$i<$soq;$i++){
			$retusers=array_merge($retusers,array($users[$i]["ac_id"]=>$users[$i]["ac_username"]));
		}
		return $retusers;
	}
	
	//
	//public gp_updatemembers()
	//
	//replaces the current users list with the new list
	//$new_users		-	array of new members uids
	//
	//returns true on sucess or false on fail
	function gp_updatemembers($new_users){
	 	if(!$this->isgroup){
			return false;
		}
		$user_string=implode(";",$new_users);
		if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_GROUPS,array(array("gp_users",$user_string)),array(array("gp_id","=",$this->gid)))){
			return false;
		}
		return true;
	}	
		
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################	
	
	//
	//private gp_getgpdata()
	//
	//gets the groupdata from the database
	//
	//returns true on success or false on fail
	function gp_getgpdata(){
		if(!$this->isgroup){
			return false;
		}
		$this->g_data=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_GROUPS,"",array(array("gp_id","=",$this->gid)));		
		return true;
	}	
}
