<?php

/**
 * @file		email_tf.class.php
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

class email_tf extends twofactor{
    
	public function __construct($t){
		$this->time=$t;
		$this->twofactor_getTableName();
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
        
        $conds=array(array(false,false,'ses_sid','=',$GLOBALS['MG']['COOKIE']['USER_SESSION']));
        $up=array(array('ses_twofactor','1'));
        
        $GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,$ses,$conds,$up);
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
        $code = $this->twofactor_genhotp(16,8);
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

    
}