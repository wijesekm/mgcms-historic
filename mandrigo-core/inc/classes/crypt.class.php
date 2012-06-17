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
	
	private $iv;
	private $secret;
	private $mode;
    private $cipher;
	
	public function cr_encrypt($text){
		return trim(mcrypt_encrypt($this->cipher, $this->secret, $text, $this->mode, $this->iv));
	}
	
	public function cr_decrypt($text){
		return trim(mcrypt_decrypt($this->cipher, $this->secret, $text, $this->mode, $this->iv));
	}
    
    public function cr_getIv(){
        return trim($this->iv);
    }
    
	public function cr_genNewKey(){
		$this->secret=md5(uniqid(rand(),true)).md5(uniqid(rand(),true));
		$this->secret=substr($this->secret,0,mcrypt_get_block_size($this->cipher, $this->mode)-1);
        return $this->secret;
	}	

    public function cr_setup($key,$cipher,$mode,$iv=false){
        $this->mode = $mode;
        $this->cipher = $cipher;
        $block = mcrypt_get_block_size($cipher, $mode);
        $pad = $block - (strlen($key) % $block);
        $key .= str_repeat(chr($pad), $pad);
        $this->secret=$key;
        $this->iv=false;
        if($mode != MCRYPT_MODE_STREAM && $mode !=MCRYPT_MODE_ECB && $mode !=MCRYPT_MODE_NOFB && !$iv){
            $this->iv = mcrypt_create_iv(mcrypt_get_iv_size($this->cipher, $this->mode), MCRYPT_RAND);	
        }
        else if($iv){
            $this->iv=$iv;
        }
    }
}