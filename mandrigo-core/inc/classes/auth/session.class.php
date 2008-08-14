<?php

/**
 * @file		session.class.php
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

class session{
	
	const CSESSION		= 'sessionid';
	const CUSER			= 'userid';
	
	private $sid;
	private $uid;
	private $t;
	private $length;
	
	public function __construct($time){
		$this->t=$time;
	}
	
	public function session_start($uid,$cdata){
		if(!$uid){
			return false;
		}
		$this->sid=md5(uniqid(rand(),true));
		$this->uid=$uid;
		if(!$this->session_updateDB(false,$cdata['EXPIRES'])){
			return false;
		}
		if(!$this->session_setCookies($cdata)){
			return false;
		}
		return true;
	}
	
	public function session_stop($cdata){
		$cdata['EXPIRES']=-600000;
		$this->sid='';
		$this->session_updateDB(true);
		$this->uid='';
		$this->session_setCookies($cdata);
	}
	
	public function session_load($uid,$sid){
		if(!$uid){
			return false;
		}
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		$dta=$GLOBALS['MG']['SQL']->sql_fetchArray(array($GLOBALS['MG']['SITE']['ACCOUNTS_SESSION_TBL']),false,array(array(false,false,'ses_uid','=',$uid)));
		$this->sid=$dta[0]['ses_sid'];
		$this->length=$dta[0]['ses_length'];
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		if(!($this->sid===$sid)||!$this->sid){
			return false;
		}
		$this->uid=$uid;
		return true;
	}
	
	public function session_loadUD($time,$cdata){
		$this->t=$time;
		$cdata['EXPIRES']=$this->length;
		$this->session_setCookies($cdata);
	}
	
	private function session_setCookies($cdata){
		if($cdata['EXPIRES']!=0){
			$cdata['EXPIRES']+=$this->t;
		}
		
		if(!setcookie($GLOBALS['MG']['SITE']['COOKIE_PREFIX'].session::CUSER,$this->uid,$cdata['EXPIRES'],$cdata['PATH'],$cdata['DOM'],$cdata['SECURE'])){
			trigger_error('(SESSION): Could not set user cookie',E_USER_WARNING);
			return false;
		}
		if(!setcookie($GLOBALS['MG']['SITE']['COOKIE_PREFIX'].session::CSESSION,$this->sid,$cdata['EXPIRES'],$cdata['PATH'],$cdata['DOM'],$cdata['SECURE'])){
			trigger_error('(SESSION): Could not set session cookie',E_USER_WARNING);
			return false;
		}
		return true;
	}
	
	private function session_updateDB($stop=false,$length=0){
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		$GLOBALS['MG']['SQL']->sql_dataCOmmands(DB_REMOVE,array($GLOBALS['MG']['SITE']['ACCOUNTS_SESSION_TBL']),array(array(false,false,'ses_uid','=',$this->uid)));
		if(!$stop){
			if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,array($GLOBALS['MG']['SITE']['ACCOUNTS_SESSION_TBL']),array('ses_uid','ses_sid','ses_starttime','ses_length'),array($this->uid,$this->sid,$this->t,$length))){
				$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
				trigger_error('(SESSION): Could not update database','E_USER_ERROR');
				return false;
			}			
		}
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		return true;
	}
}