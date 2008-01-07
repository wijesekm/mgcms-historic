<?php
/**********************************************************
    auth.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/14/07

	Copyright (C) 2006-2007 the MandrigoCMS Group

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
    die($GLOBALS["MANDRIGO"]["CONFIG"]["DIE_STRING"]);
}

class _auth{
  
  	var $session;
	
	function auth(){
		$this->session=new session();
	}

	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	  
	
    //
    //public function  auth_login($uid,$ip,$timestamp,$expires)
    //
    //logs the user into the website
    //
    //INPUTS:
    //$uid			-	user id
    //$ip			-	users ip address
    //$timestamp	-	current time
    //$expires		-	cookies expire when?
    //
	//returns true on success or false on fail
	function auth_login($uid,$ip,$timestamp,$expires){
		$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_ACCOUNTS,array(array("ac_lastlogin",$timestamp),array("ac_lastip",$ip)),array(array("ac_id","=",$uid)));
		return $this->session->se_startnew($uid,$expires,$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_SECURE"],$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_PATH"],$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_DOMAINS"]);
	}
	
    //
    //public function auth_renew($uid,$ip,$timestamp,$expires)
    //
    //generates a random string
    //
    //INPUTS:
    //$uid			-	user id
    //$ip			-	users ip address
    //$timestamp	-	current time
    //$expires		-	cookies expire when?
    //
	//returns true on success or false on fail
	function auth_renew($uid,$ip,$timestamp,$expires){
	 	$this->session->se_load($uid);
		$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_ACCOUNTS,array(array("ac_lastlogin",$timestamp),array("ac_lastip",$ip)),array(array("ac_id","=",$uid)));
		return $this->session->se_renew($expires,$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_SECURE"],$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_PATH"],$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_DOMAINS"]);	
	}
	
    //
    //public function auth_check($uid,$session)
    //
    //generates a random string
    //
    //INPUTS:
    //$uid			-	user id
    //$session		-	given session
    //
	//returns true on success or false on fail
	function auth_checkses($uid,$session){
	 	$this->session->se_load($uid);
		return $this->session->se_check($uid,$session);
	}
		
    //
    //public function auth_logout($uid)
    //
    //generates a random string
    //
    //INPUTS:
    //$uid			-	user id
    //
	//returns true on success or false on fail
	function auth_logout($uid){
	 	$this->session->se_load($uid);
		return $this->session->se_stop(time()-100000,$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_SECURE"],$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_PATH"],$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_DOMAINS"]);
	}	
		
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################	    
 	
    //
    //private function auth_randstring($size)
    //
    //generates a random string
    //
    //INPUTS:
    //$size		-	size of string
    //
	//returns string
    function auth_randstring($size){
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
	
    //
    //private function auth_encryptpasswd($password,$type)
    //
    //encrypts a password using $type
    //
    //INPUTS:
    //$password		-	password to encrypt
    //$type			-	encryption type [crypt,blowfish_crypt,md5_crypt,ext_crypt,smd5,sha,ssha,md5]
    //
	//returns encrypted password 
	function auth_encryptpasswd($password,$type){
        switch($type){
            case 'crypt':
                if(CRYPT_STD_DES!=1){
                    return false;
                }
                $seed = $this->random_string(2);
                return crypt($password,$seed);
            break;
			case 'blowfish_crypt':
                if(CRYPT_BLOWFISH!=1){
                    return false;
                }
                $seed = '$2$' . $this->random_string(13);
                return crypt($password,$seed);
            break;
            case 'md5_crypt':
                if(CRYPT_MD5!=1){
                    return false;
                }
                $seed = '$1$' . $this->random_string(9);
                return crypt($password,$seed);
            break;
            case 'ext_crypt':
                if(CRYPT_EXT_DES!=1){
                    return false;
                }
                $seed = $this->random_string(9);
                return crypt($password,$seed);
            break;
            case 'smd5':
                if(!function_exists('mhash')){
				    return false;
                }
                $seed = $this->random_string(8);
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
                $seed = $this->random_string(8);
                $hash = mhash(MHASH_SHA1, $password . $seed);
                return '{SSHA}' . base64_encode($hash . $seed);
            break;
            case 'md5':
                return md5($password);
            break;
        }
        return false;
    }  
	
    //
    //private function auth_smd5comp($form_val,$db_val)
    //
    //checks a password using smd5
    //
    //INPUTS:
    //$form_val		-	user inputted password
    //$db_val		-	db password value
    //
	//returns true if they are equal or false if not
    function auth_smd5comp($form_val,$db_val){   
        $hash = base64_decode(substr($db_val,6));
        $orig_hash = substr($hash, 0, 16);
        $salt = substr($hash, 16);
        $new_hash = mhash(MHASH_MD5,$form_val . $salt);
        if(strcmp($orig_hash,$new_hash) == 0){
            return true;
        }
        return false;
    }

    //
    //private function auth_shacomp($form_val,$db_val)
    //
    //checks a password using sha
    //
    //INPUTS:
    //$form_val		-	user inputted password
    //$db_val		-	db password value
    //
	//returns true if they are equal or false if not
    function auth_shacomp($form_val,$db_val){
        $hash = base64_decode(substr($db_val,5));
        $new_hash = mhash(MHASH_SHA1,$form_val);
        if(strcmp($hash,$new_hash) == 0){
            return true;
        }
        return false;
    }

    //
    //private function auth_sshacomp($form_val,$db_val)
    //
    //checks a password using ssha
    //
    //INPUTS:
    //$form_val		-	user inputted password
    //$db_val		-	db password value
    //
	//returns true if they are equal or false if not
    function auth_sshacomp($form_val,$db_val){
        $hash = base64_decode(substr($db_val, 6));
        $orig_hash = substr($hash, 0, 20);
        $salt = substr($hash, 20);
        $new_hash = mhash(MHASH_SHA1, $form_val . $salt);
        if(strcmp($orig_hash,$new_hash) == 0){
            return true;
        }
        return false;
    }

    //
    //private function auth_md5comp($form_val,$db_val)
    //
    //checks a password using md5
    //
    //INPUTS:
    //$form_val		-	user inputted password
    //$db_val		-	db password value
    //
	//returns true if they are equal or false if not
    function auth_md5comp($form_val,$db_val){
        if(md5($form_val)==$db_val){
            return true;
        }
        return false;
    }

    //
    //private function auth_cryptcomp($form_val,$db_val,$type)
    //
    //checks a password using crypt
    //
    //INPUTS:
    //$form_val		-	user inputted password
    //$db_val		-	db password value
    //$type			-	crypt type to use [blowfish_crypt,md5_crypt,ext_crypt,crypt]
    //
	//returns true if they are equal or false if not
    function auth_cryptcomp($form_val,$db_val,$type){
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