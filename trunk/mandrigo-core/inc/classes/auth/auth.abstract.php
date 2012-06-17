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
	
	abstract public function auth_changePass($uid,$newPass,$encoding='md5');
	
	abstract public function auth_supported();

    protected function act_randstring($size){
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
    
    protected function act_encryptpasswd($pwd,$type){

    	switch(strtoupper($type)){
        	case 'CRYPT':
                if(CRYPT_STD_DES!=1){
                    return false;
                }
                $seed = $this->act_randstring(2);
                return '{CRYPT}'.crypt($pwd,$seed);
            break;
			case 'BLOWFISH_CRYPT':
                if(CRYPT_BLOWFISH!=1){
                    return false;
                }
                $seed = '$2$' . $this->act_randstring(13);
                return '{CRYPTBF}'.crypt($pwd,$seed);
            break;
            case 'MD5_CRYPT':
                if(CRYPT_MD5!=1){
                    return false;
                }
                $seed = '$1$' . $this->act_randstring(9);
                return '{CRYPTMD5}'.crypt($pwd,$seed);
            break;
            case 'EXT_CRYPT':
                if(CRYPT_EXT_DES!=1){
                    return false;
                }
                $seed = $this->act_randstring(9);
                return '{CRYPTEXT}'.crypt($pwd,$seed);
            break;
            case 'SMD5':
                $seed = $this->act_randstring(8);
                $hash = hash('md5', $pwd . $seed);
                return '{SMD5}' . base64_encode($hash . $seed);
            break;
            case 'SHA':
                return '{SHA}' . base64_encode(hash('sha1',$pwd));
            break;
            case 'SHA256':
                return '{SHA256}' . base64_encode(bin2hex(hash('sha256',$pwd)));
            break;
            case 'SSHA':
                $seed = $this->act_randstring(8);
                $hash = hash('sha1', $pwd . $seed);
                return '{SSHA}' . base64_encode($hash . $seed);
            break;
            case 'MD5':
                return '{MD5}'.md5($pwd);
            break;
  
        }
        return false;		
	}

	protected function auth_passcomp($form_val,$db_val){
		preg_match('/\{+([A-Z0-9])+\}/',$db_val,$type);
		$type=$type[0];
		$db_val=preg_replace('/\{+([A-Z0-9])+\}/','',$db_val);
		$type=preg_replace('/[\{\}]/','',$type);
		switch($type){
          	case 'CRYPT':
		        $salt = substr($db_val, 0, 2);
		        $new_hash = crypt($form_val, $salt);
		
		        if(strcmp($db_val,$new_hash) == 0){
		            return true;
		        }
		        return false;           
            break;
            case 'CRYPTBF':
  		        $salt = substr($db_val, 0, 16);
		        $new_hash = crypt($form_val, $salt);
		
		        if(strcmp($db_val,$new_hash) == 0){
		            return true;
		        }
		        return false;            
            break;
            case 'CRYPTEXT':
		        $salt = substr($db_val, 0, 9);
		        $new_hash = crypt($form_val, $salt);
		
		        if(strcmp($db_val,$new_hash) == 0){
		            return true;
		        }
		        return false;              
            break;
            case 'CRYPTMD5':
 		        $salt = substr($db_val, 0, 12);
		        $new_hash = crypt($form_val, $salt);
		
		        if(strcmp($db_val,$new_hash) == 0){
		            return true;
		        }
		        return false;
            break;
			case 'SHA256':
		        $hash = base64_decode($db_val);
		        $new_hash =bin2hex(hash('sha256',$form_val));
		        if(strcmp($hash,$new_hash) == 0){
		            return true;
		        }
		        return false;
			break;
			case 'SSHA':
		        $hash = base64_decode($db_val);
		        $orig_hash = substr($hash, 0, 20);
		        $salt = substr($hash, 20);
		        $new_hash = hash('sha1', $form_val . $salt);
		        if(strcmp($orig_hash,$new_hash) == 0){
		            return true;
		        }
		        return false;
			break;
			case 'SHA':
		        $hash = base64_decode($db_val);
		        $new_hash = hash('sha1',$form_val);
		        if(strcmp($hash,$new_hash) == 0){
		            return true;
		        }
		        return false;
			break;
			case 'SMD5':
		        $salt = substr($hash, 16);
		        $new_hash = hash('md5',$form_val . $salt);
		        if(strcmp($hash,$new_hash) == 0){
		            return true;
		        }
		        return false;
			break;
			case 'MD5':
		    	if(md5($form_val)==$db_val){
		        	return true;
		        }
		        return false;			
			break;
			default:
				return false;
			break;
		}
	}

    
    protected function auth_cryptcomp($form_val,$db_val,$type){

    }    
}