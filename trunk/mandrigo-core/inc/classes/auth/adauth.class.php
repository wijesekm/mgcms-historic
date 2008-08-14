<?php

/**
 * @file		adauth.class.php
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

class adauth extends auth{
	
	final public function auth_authenticate($username,$password,$encoding='md5'){
		if($GLOBALS['MG']['LDAP']->ad_authenticate($username,$password)===true){
			return true;
		}
		return false;
	}
	
	final public function auth_changePass($newPass){
		return false;
	}

}