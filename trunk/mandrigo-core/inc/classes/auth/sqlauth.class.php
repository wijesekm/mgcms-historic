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
		eval('$act=new '.$GLOBALS['MG']['SITE']['ACCOUNT_TYPE'].'();');
		$user=$act->act_load($username);
		switch($encoding){
			case 'smd5':
				return $this->auth_smd5comp($password,$user['PASSWORD']);
			break;
			case 'sha':
				return $this->auth_shacomp($password,$user['PASSWORD']);
			break;
			case 'ssha':
				return $this->auth_sshacomp($password,$user['PASSWORD']);
			break;
			case 'md5':
				return $this->auth_md5comp($password,$user['PASSWORD']);
			break;
			default:
				return $this->auth_cryptcomp($password,$user['PASSWORD'],$encoding);
			break;
		};
		return false;
	}
	
}