<?php

/**
 * @file		auth.abstract.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		6-8-2008

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

abstract class auth{

	abstract public function auth_authenticate($username,$password);

	public function auth_sessionStart($uid,$expires){
	    $cdta=array(
	            'SECURE'=>(boolean)$GLOBALS['MG']['SITE']['COOKIE_SECURE'],
	            'PATH'=>$GLOBALS['MG']['SITE']['COOKIE_PATH'],
	            'DOM'=>$GLOBALS['MG']['SITE']['COOKIE_DOM'],
	            'EXPIRES'=>$expires
	    );

	    $ses= new session($GLOBALS['MG']['USER']['TIME']);
	    if(!$ses_tok = $ses->session_start($uid,$cdta)){
	        trigger_error('(AUTH): Could not start session',E_USER_ERROR);
	        return false;
	    }
	    return $ses_tok;
	}

	abstract public function auth_canChangePass();

	abstract public function auth_changePass($uid,$newPass);

	abstract public function auth_getAutoReg($uid,$password);

    final public function auth_randstring($size){
        $str = "";
        $char = array(
            '0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f',
            'g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v',
            'w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L',
            'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
        );
        for ($i=0; $i<$size; $i++){
            $str .= $char[mt_rand(1,61)];
        }
        return $str;
    }

    protected function act_encryptpasswd($pwd){
		return password_hash($pwd,PASSWORD_DEFAULT);
	}

	protected function auth_passcomp($form_val,$db_val){
		return password_verify($form_val,$db_val);
	}

}