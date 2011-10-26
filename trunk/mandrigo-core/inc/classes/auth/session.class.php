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
	private $id;
	private $uid;
	private $t;
	private $length;
	private $table;
	
	public function __construct($time){
		$this->t=$time;
		if(isset($GLOBALS['MG']['SITE']['ACCOUNTS_SESSION_TBL'])){
			$this->table=array($GLOBALS['MG']['SITE']['ACCOUNTS_SESSION_TBL']);
		}
		else{
			$this->table=array(TABLE_PREFIX.'sessions');
		}
	}
	
	public function session_start($uid,$cdata){
		if(!$uid){
			return false;
		}
		$this->sid=md5(uniqid(rand(),true));
		$this->uid=$uid;
		if(!$this->session_startStopDB(false,$cdata['EXPIRES'])){
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
		$this->session_startStopDB(true);
		$this->uid='';
		$this->session_setCookies($cdata);
	}
	
	public function session_load($idC,$uidC,$sidC){
		if(!$uidC||!$idC||!$sidC){
			return false;
		}
		$this->session_dbSwitch(0);
		$conds=array(array(false,array(DB_AND),'ses_id','=',$idC),array(false,false,'ses_uid','=',$uidC));
		$d=$GLOBALS['MG']['SQL']->sql_fetchArray($this->table,false,$conds);
		$this->session_dbSwitch(1);
		$d=$d[0];
		$this->id=$d['ses_id'];
		$this->uid=$d['ses_uid'];
		$this->sid=$d['ses_sid'];
		$this->length=$d['ses_length'];
		if($this->sid===$sidC&&$this->uid===$uidC&&$this->id===$idC){
			return true;
		}
		
		$this->id=false;
		$this->uid=false;
		$this->sid=false;
		return false;
	}
	
	public function session_loadUD($time,$cdata){
		if(!$this->uid||!$this->sid){
			return false;
		}
		$this->t=$time;
		$this->session_updateDB();
		$cdata['EXPIRES']=$this->length;
		$this->session_setCookies($cdata);
	}
	
	private function session_setCookies($cdata){
		if($cdata['EXPIRES']!=0){
			$cdata['EXPIRES']+=$this->t;
		}
		
		if(!setcookie($GLOBALS['MG']['SITE']['COOKIE_PREFIX'].session::CUSER,$this->id.';'.$this->uid,$cdata['EXPIRES'],$cdata['PATH'],$cdata['DOM'],$cdata['SECURE'])){
			trigger_error('(SESSION): Could not set user cookie',E_USER_WARNING);
			return false;
		}
		if(!setcookie($GLOBALS['MG']['SITE']['COOKIE_PREFIX'].session::CSESSION,$this->sid,$cdata['EXPIRES'],$cdata['PATH'],$cdata['DOM'],$cdata['SECURE'])){
			trigger_error('(SESSION): Could not set session cookie',E_USER_WARNING);
			return false;
		}
		return true;
	}
	
	private function session_startStopDB($stop=false,$length=0){
		$r=true;
		$this->session_dbSwitch(0);
		if($stop){	
			$conds=array(array(false,array(DB_AND),'ses_uid','=',$this->uid),array(false,false,'ses_id','=',$this->id));
			$GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,$this->table,$conds);
		}
		else{
			$conds=array(array(false,false,'ses_uid','=',$this->uid));
			$this->id=$GLOBALS['MG']['SQL']->sql_fetchResult($this->table,array('funct'=>array('MAX','ses_id')),$conds);
			$this->id++;
			$fields=array('ses_id','ses_uid','ses_sid','ses_starttime','ses_renewed','ses_length','ses_ip');
			$data=array($this->id,$this->uid,$this->sid,$this->t,$this->t,$length,$_SERVER['REMOTE_ADDR']);
			if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,$this->table,$fields,$data)){
				trigger_error('(SESSION): Could not add session to database',E_USER_ERROR);
				$r=false;
			}
		}
		$this->session_dbSwitch(1);
		return $r;
	}
	
	private function session_updateDB(){
		$r=true;
		$this->session_dbSwitch(0);
		$up=array(array('ses_renewed',$this->t),array('ses_ip',$_SERVER['REMOTE_ADDR']));
		$conds=array(array(false,array(DB_AND),'ses_uid','=',$this->uid),array(false,false,'ses_id','=',$this->id));
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,$this->table,$conds,$up)){
				trigger_error('(SESSION): Could not update session database',E_USER_ERROR);
				$r=false;
		}
		$this->session_dbSwitch(1);
		return $r;
	}
	
	private function session_dbSwitch($mode=0){
		if(!isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
			return;
		}
		if($mode==0){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		}
		else{
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		}
	}
}