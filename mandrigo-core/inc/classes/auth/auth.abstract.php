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
	
	abstract public function auth_authenticate($username,$password,$encoding='md5');
	
	abstract public function auth_changePass($uid,$newPass,$encoding='md5');

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
    	switch($type){
        	case 'crypt':
                if(CRYPT_STD_DES!=1){
                    return false;
                }
                $seed = $this->act_randstring(2);
                return crypt($password,$seed);
            break;
			case 'blowfish_crypt':
                if(CRYPT_BLOWFISH!=1){
                    return false;
                }
                $seed = '$2$' . $this->act_randstring(13);
                return crypt($password,$seed);
            break;
            case 'md5_crypt':
                if(CRYPT_MD5!=1){
                    return false;
                }
                $seed = '$1$' . $this->act_randstring(9);
                return crypt($password,$seed);
            break;
            case 'ext_crypt':
                if(CRYPT_EXT_DES!=1){
                    return false;
                }
                $seed = $this->act_randstring(9);
                return crypt($password,$seed);
            break;
            case 'smd5':
                if(!function_exists('mhash')){
				    return false;
                }
                $seed = $this->act_randstring(8);
                $hash = mhash(MHASH_MD5, $password . $seed);
                return '{SMD5}' . base64_encode($hash . $seed);
            break;
            case 'sha':
                if(!function_exists('mhash')){
                    return false;
                }
                return '{SHA}' . base64_encode(mhash(MHASH_SHA1,$password));
            break;
            case 'ssha':
                if(!function_exists('mhash')){
                    return false;
                }
                $seed = $this->act_randstring(8);
                $hash = mhash(MHASH_SHA1, $password . $seed);
                return '{SSHA}' . base64_encode($hash . $seed);
            break;
            case 'md5':
                return md5($password);
            break;
        }
        return false;		
	}
	
    protected function auth_smd5comp($form_val,$db_val){   
        $hash = base64_decode(substr($db_val,6));
        $orig_hash = substr($hash, 0, 16);
        $salt = substr($hash, 16);
        $new_hash = mhash(MHASH_MD5,$form_val . $salt);
        if(strcmp($orig_hash,$new_hash) == 0){
            return true;
        }
        return false;
    }
    
    protected function auth_shacomp($form_val,$db_val){
        $hash = base64_decode(substr($db_val,5));
        $new_hash = mhash(MHASH_SHA1,$form_val);
        if(strcmp($hash,$new_hash) == 0){
            return true;
        }
        return false;
    }
    
    protected function auth_sshacomp($form_val,$db_val){
        $hash = base64_decode(substr($db_val, 6));
        $orig_hash = substr($hash, 0, 20);
        $salt = substr($hash, 20);
        $new_hash = mhash(MHASH_SHA1, $form_val . $salt);
        if(strcmp($orig_hash,$new_hash) == 0){
            return true;
        }
        return false;
    }
    
    protected function auth_md5comp($form_val,$db_val){
    	if(md5($form_val)==$db_val){
        	return true;
        }
        return false;
    }
    
    protected function auth_cryptcomp($form_val,$db_val,$type){
        $saltlen = array(
            'blowfish_crypt' => 16,
            'md5_crypt' => 12,
            'ext_crypt' => 9,
            'crypt' => 2
        );
        $salt = substr($db_val, 0, (int)$saltlen[$type]);
        $new_hash = crypt($form_val, $salt);

        if(strcmp($db_val,$new_hash) == 0){
            return true;
        }
        return false;
    }    
}