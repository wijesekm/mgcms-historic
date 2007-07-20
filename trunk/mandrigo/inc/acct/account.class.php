<?php
/**********************************************************
    account.class.php
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

class _account{
	
	var $uid;
	var $isuser;
	var $name;
	var $u_data;
	
	function _account($uname){}

	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	
		
	//
	//public ac_setname()
	//
	//sets the current username	
	function ac_setname($uname){}
		
	//
	//public ac_id()
	//
	//gets the id of the current user
	//
	//returns id on success or false on fail	
	function ac_id(){}
	
	//
	//public ac_userdata()
	//
	//gets the user data of the current user
	//
	//returns user data on success or false on fail	
	function ac_userdata(){}
	
	//
	//public ac_updateuserdata()
	//
	//updates the user data for the current user
	//INPUTS:
	//$user_data		-	array of user data
	//
	//returns true on success or false on fail
	function ac_updateuserdata($user_data){}
	 
	//
	//public ac_lastlogin()
	//
	//gets the last login time and ip of the current user
	//
	//returns last login data on success or false on fail	
	function ac_lastlogin(){}
	
	//
	//public ac_language()
	//
	//gets the lang of the current user
	//
	//returns lang
	function ac_language(){}
	
	//
	//public ac_updatelanguage()
	//
	//updates the user language for the current user
	//INPUTS:
	//$new_lang		-	new language
	//
	//returns true on success or false on fail	
	function ac_updatelanguage($new_lang){}
		
	//
	//public ac_timezone()
	//
	//gets the timezone/dst of the current user
	//
	//returns timezone/dst on success or false on fail	
	function ac_timezone(){}
	
	//
	//public ac_updatetz()
	//
	//updates the user language for the current user
	//INPUTS:
	//$tz		-	new time zone
	//$dst		-	new dst
	//
	//returns true on success or false on fail	
	function ac_updatetz($tz,$dst){}
	
	//
	//public ac_groups()
	//
	//gets the groups of the current user
	//
	//returns groups on success or false on fail	
	function ac_groups(){}
	
	//
	//public ac_groups()
	//
	//gets the names of the groups of the current user
	//
	//returns groups on success or false on fail	
	function ac_groupnames(){}
	
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
	function ac_getuserdata(){}	
}
