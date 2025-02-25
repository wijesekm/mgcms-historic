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

	final public function auth_authenticate($username,$password){
		if($username!=$GLOBALS['MG']['USER']['UID']){
			trigger_error('(SQLAUTH): Script did not initialize GLOBALS array with new user data for account!',E_USER_WARNING);
			return false;
		}
		if(!$GLOBALS['MG']['USER']['PASSWORD']){
			trigger_error('(SQLAUTH): No user password set in database!',E_USER_NOTICE);
			return false;
		}
		return $this->auth_passcomp($password,$GLOBALS['MG']['USER']['PASSWORD']);
	}

	final public function auth_canChangePass(){
	    return true;
	}

	final public function auth_changePass($uid,$newPass){

		$encPass=$this->act_encryptpasswd($newPass);
        if(!$encPass){
			trigger_error('(SQLAUTH): Could not set new password. Encrpytion failure!',E_USER_WARNING);
			return false;
        }
        eval('$act=new '.$GLOBALS['MG']['SITE']['ACCOUNT_TYPE'].'();');
        if(!$act->act_updateField($uid,'user_password',$encPass)){
			trigger_error('(SQLAUTH): Could not set new password. Database Failure!',E_USER_WARNING);
			return false;
        }
		return true;
	}

	final public function auth_getAutoReg($uid,$password){
		return false;
	}
}