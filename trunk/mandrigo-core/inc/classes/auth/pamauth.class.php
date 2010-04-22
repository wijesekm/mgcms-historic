<?php

/**
 * @file		pamauth.class.php
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

class pamauth extends auth{
	
	final public function auth_authenticate($username,$password,$encoding='md5'){
		$desc = array(
                0 => array('pipe','r'),
                1 => array('pipe','w'),
                2 => array('file',$GLOBALS['MG']['CFG']['PATH']['LOG'].'pam.log','a')
        );
        if(!$proc = proc_open($GLOBALS['MG']['CFG']['PATH']['INC'].'c/pam_auth -o -1',$desc, $pipes)){
			trigger_error('(PAMAUTH): Could not open new process!',E_USER_ERROR);
			return false;
		}
        if (is_resource($proc)) {
                fwrite($pipes[0],$username);
                fwrite($pipes[0],' ');
                fwrite($pipes[0],$password);
                fwrite($pipes[0],"\n");
                fclose($pipes[0]);
                $stat= stream_get_contents($pipes[1]);
                fclose($pipes[1]);
        }
        return (trim($stat)=='OK')?true:false;
	}
	
	final public function auth_supported(){
		return array('change_pass'=>false);
	}
	
	final public function auth_changePass($uid,$newPass,$encoding='md5'){
		return false;
	}
}