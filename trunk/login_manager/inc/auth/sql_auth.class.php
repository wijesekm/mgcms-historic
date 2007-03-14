<?php
/**********************************************************
    pam_auth.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/14/07

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

@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["LOGIN_ROOT_PATH"]."auth{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}auth.class.php");

class auth extends _auth{
  
	function auth(){
		$this->session=new session();
	}
	
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	    
 	
    //
    //public function auth_check($user_name,$user_password,$crypt_type)
    //
    //checks the users credentials
    //
    //INPUTS:
    //$user_name		-	users login name
    //$user_password	-	users login password
    //$crypt_type		-	password crypt type
    //
	//returns string
	function auth_check($user_name,$user_password,$crypt_type){
		$user_data=$this->sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"ac_id,ac_passwd",array(array("ac_username","=",$user_name)));
		if(!$user_data["ac_id"]||$user_data["ac_id"]<=1||!$user_password||$user_password==BAD_DATA){
			return false;
		}
		$status=false;
		switch($crypt_type){
			case 'smd5':
				$status=$this->auth_smd5comp($user_password,$user_data["user_password"]);
			break;
			case 'sha':
				$status=$this->auth_shacomp($user_password,$user_data["user_password"]);
			break;
			case 'ssha':
				$status=$this->auth_sshacomp($user_password,$user_data["user_password"]);
			break;			
			case 'md5':
				$status=$this->auth_md5comp($user_password,$user_data["user_password"]);
			break;
			default:
				$status=$this->auth_cryptcomp($user_password,$user_data["user_password"],$crypt_type);
			break;
		};
		if($status){
			return $user_data["user_id"];
		}
		return false;
	}
}