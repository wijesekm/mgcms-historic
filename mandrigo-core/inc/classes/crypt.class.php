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
	private $useIv;
	private $secret;
	private $mode;
	private $type;
	
	public function cr_encrypt($text){
		$this->cr_cryptType();
		return trim(mcrypt_encrypt($this->type, $this->secret, $text, $this->mode, $this->iv));
	}
	
	public function cr_decrypt($text){
		$this->cr_cryptType();
		return trim(mcrypt_decrypt($this->type, $this->secret, $text, $this->mode, $this->iv));
	}
	
	private function cr_genIV(){
		$iv_size = mcrypt_get_iv_size($this->type, $this->mode);
 		$this->iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);					
	}

	private function cr_genSecret(){
		$this->secret=md5(uniqid(rand(),true)).md5(uniqid(rand(),true));
		switch($this->type){
			case MCRYPT_RIJNDAEL_128:
				$this->secret=substr($this->secret,0,32);
			break;
			case MCRYPT_RIJNDAEL_256:
				$this->secret=substr($this->secret,0,56);
			break;
			case MCRYPT_BLOWFISH:
			default:
				$this->secret=substr($this->secret,0,56);
			break;
		};
	}	
	
	private function cr_cryptType(){
		if(!is_array($GLOBALS['MG']['SITE']['CRYPT_TYPE'])){
			$GLOBALS['MG']['SITE']['CRYPT_TYPE']=explode(';',$GLOBALS['MG']['SITE']['CRYPT_TYPE']);	
		}
		$this->useIv=false;
		switch($GLOBALS['MG']['SITE']['CRYPT_TYPE'][0]){
			case 'stream':
				$this->mode=MCRYPT_MODE_STREAM;
			break;
			case 'ebc':
				$this->mode=MCRYPT_MODE_EBC;
			break;
			case 'cfb':
				$this->useIv=true;
				$this->mode=MCRYPT_MODE_CFB;
			break;
			case 'ofb':
				$this->useIv=true;
				$this->mode=MCRYPT_MODE_OFB;
			break;
			case 'nofb':
				$this->mode=MCRYPT_MODE_NOFB;
			break;
			case 'cbc':
			default:
				$this->useIv=true;
				$this->mode=MCRYPT_MODE_CBC;
			break;			
		};
		switch($GLOBALS['MG']['SITE']['CRYPT_TYPE'][0]){
			case 'aes128':
				$this->type=MCRYPT_RIJNDAEL_128;
			break;
			case 'aes256':
				$this->type=MCRYPT_RIJNDAEL_256;
			break;
			case 'blowfish':
			default:
				$this->type=MCRYPT_BLOWFISH;
			break;
		};

		$this->iv=$GLOBALS['MG']['SITE']['CRYPT_IV'];
		if(!$this->iv&&$this->useIv){
			$this->cr_genIV();
			$GLOBALS['MG']['SITE']['CRYPT_IV']=$this->iv;
			if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,TABLE_PREFIX.'config',array('cfg_var','cfg_data'),array('CRYPT_IV',$this->iv))){
				$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,TABLE_PREFIX.'config',array(array(false,false,'cfg_var','=','CRYPT_IV')),array(array('cfg_data',$this->iv)));
			}
		}
		else if(!$this->useIv){
			$this->iv=false;
		}
		$this->secret=$GLOBALS['MG']['SITE']['CRYPT_SECRET'];
		if(!$this->secret){
			$this->cr_genSecret();
			$GLOBALS['MG']['SITE']['CRYPT_SECRET']=$this->secret;
			if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,TABLE_PREFIX.'config',array('cfg_var','cfg_data'),array('CRYPT_SECRET',$this->secret))){
				$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,TABLE_PREFIX.'config',array(array(false,false,'cfg_var','=','CRYPT_SECRET')),array(array('cfg_data',$this->secret)));
			}
		}
		
	}
}