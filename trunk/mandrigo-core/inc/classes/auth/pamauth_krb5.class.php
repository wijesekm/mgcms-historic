<?php

/**
 * @file		pamauth_krb5.class.php
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

class pamauth_krb5 extends auth{
	
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
	
	final public function auth_changePass($uid,$newPass,$encoding='md5'){
		if(!function_exists('kadm5_init_with_password')){
			trigger_error('(PAMAUTH_KRB5) : Install PECL Kerberos package to use this auth type',E_USER_ERROR);
			return false;
		}
		if(!$h=kadm5_init_with_password($this->data['krb5_adminserver'],$this->data['krb5_realm'],$this->data['krb5_princ'],$this->data['krb5_princpass'])){
			trigger_error('(PAMAUTH_KRB5): Could not connect to Kerberos server for password change!',E_USER_ERROR);
			return false;
		}
		if(!kadm5_chpass_principal($h,$uid.'@'.$this->data['krb5_realm'],$newPass)){
			trigger_error('(PAMAUTH_KRB5): Could not change Kerberos password!',E_USER_ERROR);
			return false;
		}
		kadm5_destroy($h);
		return true;
	}	
}