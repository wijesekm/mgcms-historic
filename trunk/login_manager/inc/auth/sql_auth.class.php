<?php
/**********************************************************
    auth.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/17/06

	Copyright (C) 2005  Kevin Wijesekera

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
    die("<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width=\"300\" align=\"left\"/>\n<p>You do not have permission to access this file directly.</p>
        </html></body>");
}

class auth extends _auth{
  
  	var $sql_db;
  
	function auth($db){
	 	$this->sql_db=$db; 
	}
	function auth_validate($user_name,$user_password,$crypt_type){
	  
	  	//username verification
	  	if(!$this->auth_cleanusername($user_name)){
			return false;
		}
		$user_data=$this->sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_name","=",$user_name)));
		if(!$user_data["user_id"]||$user_data["user_id"]==1){
			return false;
		}
		
		//password verification
		if(!$this->auth_cleanpassword($user_password)){
			return false;
		}
		switch($crypt_type){
			case 'smd5':
				return $this->auth_smd5comp($user_password,$user_data["user_password"]);
			break;
			case 'sha':
				return $this->auth_shacomp($user_password,$user_data["user_password"]);
			break;
			case 'ssha':
				return $this->auth_sshacomp($user_password,$user_data["user_password"]);
			break;			
			case 'md5':
				return $this->auth_md5comp($user_password,$user_data["user_password"]);
			break;
			default:
				return $this->auth_cryptcomp($user_password,$user_data["user_password"],$crypt_type);
			break;
		};
		return false;
	}
	function auth_loguserin($user_name,$ip,$timestamp){
		return $this->sql_db->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_USER_DATA,array(array("user_last_login",$timestamp),array("user_last_ip",$ip)),array(array("user_name","=",$user_name))));
	}
	function auth_validsession($uid,$session){
	  	if(!$this->auth_cleanuid($uid)){
			return false;
		}	  	
		if(!$uid||$uid==1){
			return false;
		}
		$user_data=$this->sql_db->db_fetcharray(TABLE_PREFIX.TABLE_USER_DATA,"",array(array("user_id","=",$uid)));
		if($user_data["user_session"]===$session){
			return true;
		}
		return false;
	}
}
?>