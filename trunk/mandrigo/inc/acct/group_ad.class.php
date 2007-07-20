<?php
/**********************************************************
    group_ad.class.php
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

class _group{
	
	var $name;
	var $gid;
	var $isgroup;
	var $g_data;
	
	function group($gid){
		return $this->gp_setid($gid)
	}

	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	
		
	//
	//public gp_setname()
	//
	//sets the current group name	
	function gp_setid($gid){
		$r=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_GROUPS,"gp_name,gp_id",array(array("gp_id","=",$gid)));
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
					 "GID"=>$this->gid);
	}
	
	//
	//public gp_updatedata()
	//
	//updates the group data
	//$new_data			-	array of new group data
	//
	//returns true on sucess or false on fail
	function gp_updatedata($new_data){
		return false;
	}
	//
	//public gp_data()
	//
	//gets the usernames of the admins of the group
	//
	//returns array of unames on success or false on fail		
	function gp_admins(){

	}
	
	//
	//public gp_updateadmins()
	//
	//replaces the current admins list with the new list
	//$new_admins		-	array of new admins uids
	//
	//returns true on sucess or false on fail
	function gp_updateadmins($new_admins){
		return false;
	}
	
	//
	//public gp_members()
	//
	//gets the usernames of the members of the group
	//
	//returns array of unames on success or false on fail		
	function gp_members(){
	
	}	
	
	//
	//public gp_updatemembers()
	//
	//replaces the current users list with the new list
	//$new_users		-	array of new members uids
	//
	//returns true on sucess or false on fail
	function gp_updatemembers($new_users){
		return false;
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

	}	
}