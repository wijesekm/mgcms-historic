<?php

/**
 * @file		crypt.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2010
 * @edited		7-8-2010
 
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

class crypt{
	
	private $secret;
	private $mode;
    private $cipher;
    private $setup;
	
	public function cr_encrypt($text,$salt_len=0){
        $iv = mcrypt_create_iv(mcrypt_get_iv_size($this->cipher,$this->mode),MCRYPT_RAND);
        $salt = $this->cr_genSalt($salt_len);
		return base64_encode($iv.mcrypt_encrypt($this->cipher, $this->secret, $salt.$text, $this->mode, $iv));
	}
	
	public function cr_decrypt($text,$salt_len=0){
        $text = base64_decode($text);
        $iv = substr($text,0,mcrypt_get_iv_size($this->cipher,$this->mode));
        $text = substr($text,mcrypt_get_iv_size($this->cipher,$this->mode));
		return trim(substr(mcrypt_decrypt($this->cipher, $this->secret, $text, $this->mode, $iv),$salt_len));
	}

    public function cr_setup($key,$cipher,$mode){
        if(!$cipher || !$mode){
            $cipher = MCRYPT_RIJNDAEL_256;
            $mode = MCRYPT_MODE_CBC;
        }
        if($mode == MCRYPT_MODE_ECB){
            trigger_error('(CRYPT): ECB mode is not secure and not supported.  Switching to CBC mode',E_USER_NOTICE);
            $mode = MCRYPT_MODE_CBC;
        }

        if(!@mcrypt_get_block_size($cipher,$mode)){
            trigger_error('(CRYPT): Cipher not supported.  Switching to RIJNDAEL 256 with CBC',E_USER_NOTICE);
            $cipher = MCRYPT_RIJNDAEL_256;
            $mode = MCRYPT_MODE_CBC;
        }
        $this->mode = $mode;
        $this->cipher = $cipher;
        $this->secret = $this->cr_genKey($key);
        return $this->secret;
        
    }
    
    private function cr_genKey($key_base=""){
        $length = mcrypt_get_block_size($this->cipher,$this->mode);
        $base_len = strlen($key_base);
        if($base_len > $length){
            return $key_base;
        }
        for($length-=$base_len;$length > 0; $length--){
            $key_base .= chr(rand(33,255));
        }
        return $key_base;
    }
    
    private function cr_genSalt($length){
        $salt = "";
        for($length;$length > 0; $length--){
            $salt .= chr(rand(33,255));
        }
        return $salt;
    }
}