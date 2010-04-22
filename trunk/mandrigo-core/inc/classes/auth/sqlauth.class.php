<?php

/**
 * @file		sqlauth.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		6-9-2008
 
 ###################################
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program.  If not, see http://www.gnu.org/licenses/.
 ###################################
 */

if(!defined('STARTED')){
	die();
}

class sqlauth extends auth{
	
	final public function auth_authenticate($username,$password,$encoding='md5'){
		if($username!=$GLOBALS['MG']['USER']['UID']){
			trigger_error('(SQLAUTH): Script did not initialize GLOBALS array with new user data for account!',E_USER_WARNING);
			return false;
		}
		if(!$GLOBALS['MG']['USER']['PASSWORD']){
			trigger_error('(SQLAUTH): No user password set in database!',E_USER_NOTICE);
			return false;
		}
		switch($encoding){
			case 'smd5':
				return $this->auth_smd5comp($password,$GLOBALS['MG']['USER']['PASSWORD']);
			break;
			case 'sha':
				return $this->auth_shacomp($password,$GLOBALS['MG']['USER']['PASSWORD']);
			break;
			case 'ssha':
				return $this->auth_sshacomp($password,$GLOBALS['MG']['USER']['PASSWORD']);
			break;
			case 'md5':
				return $this->auth_md5comp($password,$GLOBALS['MG']['USER']['PASSWORD']);
			break;
			default:
				return $this->auth_cryptcomp($password,$GLOBALS['MG']['USER']['PASSWORD'],$encoding);
			break;
		};
		return false;
	}
	
	final public function auth_supported(){
		return array('change_pass'=>true);
	}	

	final public function auth_changePass($uid,$newPass,$encoding='md5'){
		echo $uid.$newPass.$encoding;
		$encPass=$this->act_encryptpasswd($newPass,$encoding);
		$params=array(array(false,false,'user_uid','=',$uid));
		$dta=array(array('user_password',$encPass));
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		if(!$c=$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,array($GLOBALS['MG']['SITE']['ACCOUNT_TBL']),$params,$dta)){
			trigger_error('(SQLAUTH): Could not set new password!',E_USER_ERROR);
			return false;
		}
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		return $c;
	}	
}