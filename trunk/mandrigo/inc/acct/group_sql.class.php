<?php
/**********************************************************
    group_sql.class.php
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

class _group{
	
	var $name;
	var $gid;
	var $isgroup;
	var $g_data;
	
	function _group($gname){}

	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	
		
	//
	//public gp_setname()
	//
	//sets the current group name	
	function gp_setname($gname){
		$r=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_GROUPS,"gp_name,gp_id",array(array("gp_name","=",$gname)));
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
	//public gp_id()
	//
	//gets the id of the current group
	//
	//returns id on success or false on fail	
	function gp_id(){}
	//
	//public gp_name()
	//
	//gets the id of the current group
	//
	//returns id on success or false on fail	
	function gp_name(){}	
	//
	//public gp_data()
	//
	//gets the data for the current group
	//
	//returns data on success or false on fail	
	function gp_data(){}	

	//
	//public gp_data()
	//
	//gets the usernames of the admins of the group
	//
	//returns array of unames on success or false on fail		
	function gp_admins(){}

	//
	//public gp_members()
	//
	//gets the usernames of the members of the group
	//
	//returns array of unames on success or false on fail		
	function gp_members(){}	
	
	
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
	function gp_getgpdata(){}	
}
