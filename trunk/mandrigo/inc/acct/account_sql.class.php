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

class account extends _account{
	
	function account($uname){
		$this->ac_setname($uname);
	}

	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	
		
	//
	//public ac_setname()
	//
	//sets the current username	
	function ac_setname($uname){
		if($r=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_ACCOUNTS,"ac_username",array(array("ac_username","=",$uname)))){
			if((string)$r===(string)$uname){
				$this->name=$uname;
				$this->isuser=true;
				$this->ac_getuserdata();
			}
			else{
			 	$this->isuser=false;
				return false;
			}
		}
		else{
			$this->isuser=false;
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
		return $this->u_data["ac_id"]
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
		return array("fname"=>$tmp[0],
					 "mname"=>$tmp[1],
					 "lname"=>$tmp[2],
					 "email"=>$this->u_data["ac_email"],
					 "im"=>explode(";",$this->u_data["ac_im"]),
					 "website"=>$this->u_data["ac_website"],
					 "about"=>$this->u_data["ac_about"],
					 "picture_path"=>$this->u_data["ac_picture"]);
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
		return array("login_time"=>$this->u_data["ac_lastlogin"],
					 "login_ip"=>$this->u_data["ac_lastip"]
					 "pchange_time"=>$this->u_data["ac_lastpwdchg"]);
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
		return array("timezone"=>$this->u_data["ac_tz"],
					 "dst"=>$this->u_data["ac_dst"]);		
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
