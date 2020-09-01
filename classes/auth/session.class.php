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

	public function session_start($uid,$cdata,$no_cookies=false,$fixed_id=-1){
		if(!$uid){
			return false;
		}
		$this->sid=md5(uniqid(rand(),true));
		$this->uid=$uid;
		if(!$this->session_startStopDB(false,$cdata['EXPIRES'],$fixed_id)){
			return false;
		}
		if(!$no_cookies){
		    if(!$this->session_setCookies($cdata)){
		        return false;
		    }
		}
		return $this->sid;
	}

	public function session_stop($cdata){
		$this->sid='';
		$this->session_startStopDB(true);
		$this->uid='';
		if($cdata != false){
		    $cdata['EXPIRES']=-600000;
		    $this->session_setCookies($cdata);
		}
	}

	public function session_load($id,$sid,$twofact=false){
	    if(empty($id)||empty($sid)){
			return false;
		}
		$this->session_dbSwitch(0);
		$conds=array(array(false,array(DB_AND),'ses_id','=',$id),array(false,false,'ses_sid','=',$sid));
		$d=$GLOBALS['MG']['SQL']->sql_fetchArray($this->table,false,$conds);
		$this->session_dbSwitch(1);
        if(!is_array($d[0]) && isset($d[0]['ses_id'])){
            return false;
        }
		$d=$d[0];
		if(!is_array($d) || !isset($d['ses_id'])){
		    return false;
		}
        foreach($d as $key=>$val){
            $GLOBALS['MG']['SESSION'][strtoupper(substr($key,strpos($key,'_')+1))] = $val;
        }
		$this->id=(int)$d['ses_id'];
		$this->uid=$d['ses_uid'];
		$this->sid=$d['ses_sid'];
		$this->length=$d['ses_length'];

		if($this->sid===$sid&&$this->id===(int)$id){
            if($twofact){
                if($d['ses_twofactor']=='1'){
                    return true;
                }
            }
            else{
                return true;
            }
		}

		$this->id=false;
		$this->uid=false;
		$this->sid=false;
		return false;
	}

	public function check_after($time, $cdata, $banned, $no_cookies=true){
	    $this->t = $time;

	    if(($this->length != 0 && $this->t > ($GLOBALS['MG']['SESSION']['RENEWED']+ $this->length)) || $banned){
	        $this->session_stop($no_cookies?false:$cdata);
	        return false;
	    }

	    if(!$no_cookies){
	        $this->session_updateDB();
	        $cdata['EXPIRES']=$this->length;
	        $this->session_setCookies($cdata);
	    }

	    return true;
	}

	public function session_getUID(){
	    return $this->uid;
	}

	private function session_setCookies($cdata){
		if($cdata['EXPIRES']!=0){
			$cdata['EXPIRES']+=$this->t;
		}

		if(!setcookie($GLOBALS['MG']['SITE']['COOKIE_PREFIX'].session::CSESSION,$this->id.';'.$this->sid,$cdata['EXPIRES'],$cdata['PATH'],$cdata['DOM'],$cdata['SECURE'],true)){
			trigger_error('(SESSION): Could not set session cookie',E_USER_WARNING);
			return false;
		}
		return true;
	}

	private function session_startStopDB($stop=false,$length=0,$fixed_id=-1){
		$r=true;
		$this->session_dbSwitch(0);
		if($stop){
			$conds=array(array(false,array(DB_AND),'ses_uid','=',$this->uid),array(false,false,'ses_id','=',$this->id));
			$GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,$this->table,$conds);
		}
		else{
			if($fixed_id == -1){
			    $conds=array(array(false,false,'ses_uid','=',$this->uid));
			    $this->id=$GLOBALS['MG']['SQL']->sql_fetchResult($this->table,array('funct'=>array('MAX','ses_id')),$conds);
			    $this->id++;
			}
            else{
                $this->id = $fixed_id;
                $conds=array(array(false,array(DB_AND),'ses_uid','=',$this->uid),array(false,false,'ses_id','=',$this->id));
                $GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,$this->table,$conds);
            }
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