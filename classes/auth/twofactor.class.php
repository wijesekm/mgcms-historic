<?php

/**
 * @file		twofactor.abstract.php
 * @author 		Kevin Wijesekera
 * @copyright 	2012
 * @edited		10-8-2012
 
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

class twofactor{
    
    protected $table;
    protected $time;
    
	public function __construct($t){
		$this->time=$t;
		$this->twofactor_getTableName();
	}
    
    public function twofactor_getKeyIp($key){
        $this->twofactor_changeDb();
        $ip = $GLOBALS['MG']['SQL']->sql_fetchResult($this->table,array(array('ip')),array(array(false,false,'token','=',$key)));
        $this->twofactor_changeDb(true);
        return $ip;
    }
    
    public function twofactor_removeKey($key){
        $this->twofactor_changeDb();
        $ip = $GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,$this->table,array(array(false,false,'token','=',$key)));
        $this->twofactor_changeDb(true);
    }

   	public function twofactor_check($user){
        if(!$user){
            return false;
        }
   	    $this->twofactor_changeDb();
        $ret = false;
        
        
        $conds = array(array(false,array(DB_AND),'user','=',$user),array(false,false,'ip','=',$_SERVER['REMOTE_ADDR']));
        $data = $GLOBALS['MG']['SQL']->sql_fetchArray($this->table,false,$conds);
        $data = $data[0];
        if($data['user'] === $user && $data['ip'] === $_SERVER['REMOTE_ADDR'] && $data['expires'] != '0'){
            if($this->time < $data['expires']){
                $ret = true;
            }
            else{
                $GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,$this->table,$conds);
            }
        }
		if(isset($GLOBALS['MG']['SITE']['ACCOUNTS_SESSION_TBL'])){
			$ses=array($GLOBALS['MG']['SITE']['ACCOUNTS_SESSION_TBL']);
		}
		else{
			$ses=array(TABLE_PREFIX.'sessions');
		}
        if($ret){
            $conds=array(array(false,false,'ses_sid','=',$GLOBALS['MG']['COOKIE']['USER_SESSION']));
            $up=array(array('ses_twofactor','1'));
            $GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,$ses,$conds,$up);
        }

        $this->twofactor_changeDb(true);
        return $ret;
   	}
    
    public function twofactor_update($user,$token,$remember){
        if(!$user || !$token){
            return false;
        }
        $this->twofactor_changeDb();
        $conds=array(array(false,false,'token','=',$token));
        $data = $GLOBALS['MG']['SQL']->sql_fetchArray($this->table,false,$conds);
        if($data[0]['token'] !== $token){
            $this->twofactor_changeDb(true);
            return false;
        }
        $update = array(array('ip',$_SERVER['REMOTE_ADDR']),array('expires',$this->time+$remember));
        if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,$this->table,$conds,$update)){
            $GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,$this->table,array(array(false,array(DB_AND),'user','=',$user),array(false,false,'ip','=',$_SERVER['REMOTE_ADDR'])));
            if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,$this->table,$conds,$update)){
                trigger_error('(TF-EMAIL): Could not update database',E_USER_ERROR);
                $this->twofactor_changeDb(true);
                return false;
            }
        }
		if(isset($GLOBALS['MG']['SITE']['ACCOUNTS_SESSION_TBL'])){
			$ses=array($GLOBALS['MG']['SITE']['ACCOUNTS_SESSION_TBL']);
		}
		else{
			$ses=array(TABLE_PREFIX.'sessions');
		}
        
        $conds=array(array(false,false,'ses_sid','=',$GLOBALS['MG']['COOKIE']['USER_SESSION']));
        $up=array(array('ses_twofactor','1'));
        
        $GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,$ses,$conds,$up);
        
        $this->twofactor_changeDb(true);
        return true;
    }

    public function twofactor_create($user){
        $this->twofactor_changeDb();
        $code = $this->twofactor_genhotp(16,6);
        if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,$this->table,array('token','user','ip'),array($code,$user,'new'))){
            $GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,$this->table,array(array(false,array(DB_AND),'user','=',$user),array(false,false,'ip','=','new')));
            if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,$this->table,array('token','user','ip'),array($code,$user,'new'))){
                trigger_error('(EMAIL_TF): Could not insert code into database',E_USER_ERROR);
                return false;
            }
        }
        $this->twofactor_changeDb(true);
        return $code;
    }
    
    protected function twofactor_getTableName(){
		if(isset($GLOBALS['MG']['SITE']['ACCOUNTS_TWOFACTOR_TBL'])){
			$this->table=array($GLOBALS['MG']['SITE']['ACCOUNTS_TWOFACTOR_TBL']);
		}
		else{
			$this->table=array(TABLE_PREFIX.'twofactor');
		}
    }
    
    protected function twofactor_changeDb($revert=false){
		if(!isset($GLOBALS['MG']['SITE']['ACCOUNT_DB'])){
			return;
		}
		if(!$revert){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		}
		else{
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		}
    }
    
    protected function twofactor_genhotp($key_len,$length){
        if($key_len < 8){
            trigger_error('(TWOFACTOR): Key length is too short to gen hotp',E_USER_WARNING);
        }
        if($length < 4){
            trigger_error('(TWOFACTOR):Length is too short to gen hotp',E_USER_WARNING); 
        }
        $key = $this->twofactor_genKey();
        $counter = $this->time/30;
        $bin = pack('N*', 0) . pack('N*', $counter);
        $hash = hash_hmac('sha1',$bin,$key,true);
        return str_pad($this->twofactor_truncate($hash,$length),$length,'0',STR_PAD_LEFT);
    }
    
    protected function twofactor_truncate($hash,$length){
	    $offset = ord($hash[19]) & 0xf;
	    return (
	        ((ord($hash[$offset+0]) & 0x7f) << 24 ) |
	        ((ord($hash[$offset+1]) & 0xff) << 16 ) |
	        ((ord($hash[$offset+2]) & 0xff) << 8 ) |
	        (ord($hash[$offset+3]) & 0xff)
	    ) % pow(10, $length);
    }
    
    protected function twofactor_base32Conv($enc,$reverse){
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $ret = '';
        $enc = strtoupper($enc);
        if(!$reverse){
            $ar = str_split($enc, 5);
            
            foreach($ar as $var) {
                $ret .= str_pad(base_convert($var, 16, 2), 20, '0', STR_PAD_LEFT);
            }
            $ar = str_split($ret, 5);
            $ret = '';
            foreach($ar as $var2) {
                $ret .= $characters[ base_convert($var2, 2, 10)];
            }
        }
        else{
            $len = strlen($enc);
            $n=0;$j=0;$i=0;
            for($i=0;$i<$len;$i++){
                $n = $n << 5;
                $n = $n + $characters[$enc[$i]];
                $j = $j + 5;
                if($j >= 8){
                    $j = $j - 8;
                    $ret .= chr(($n & (0xFF << $j)) >> $j);
                }
            }
        }
    }
    
    protected function twofactor_genKey($length = 16){
        $characters = '234567QWERTYUIOPASDFGHJKLZXCVBNM';
        $ret = '';
        for($i=0;$i<$length;$i++){
            $ret.= $characters[rand(0,31)];
        }
        return $ret;
    }
}