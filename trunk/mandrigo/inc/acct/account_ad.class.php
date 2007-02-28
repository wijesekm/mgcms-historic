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

class account extends _account{
	
	var $ad;
	
	function account($uname){
		$this->ad=new ad_ldap();
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
		if($r=$this->ad->ad_userinfo($uname,array("samaccountname"))){
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
		return $this->u_data[0]["mssfu30uidnumber"][0];
	}
	
	//
	//public ac_userdata()
	//
	//gets the user data of the current user
	//
	//returns user data on success or false on fail	
	function ac_userdata(){}

	//
	//public ac_lastlogin()
	//
	//gets the last login time and ip of the current user
	//
	//returns last login data on success or false on fail	
	function ac_lastlogin(){}

	//
	//public ac_timezone()
	//
	//gets the timezone/dst of the current user
	//
	//returns timezone/dst on success or false on fail	
	function ac_timezone(){}

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
	function ac_groups(){}
	
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
		if(!$this->isuse){
			return false;
		}
		$this->u_data=$this->ad->ad_userinfo($uname,array("msSFU30UidNumber",""))
		
	}	
}