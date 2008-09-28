<?php

/**
 * @file		sqlact.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		8-3-2008
 
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

class sqlact extends accounts{

	private $user;
	private $lastLength;
	
	const	GEN_PASSWORD_LENGTH = 8;

	public function __construct(){
		$this->lastLength=0;
	}

	final public function act_load($uid=false,$search=false,$start=false,$length=false,$acl=true,$ob='ASC'){
		
		$this->user=array();
		
		$parms=array();
		$additParams=array();
		if($uid){
			$parms[]=array(false,false,'user_uid','=',$uid);
		}
		else if($search){
			$parms[]=array(DB_LIKE,false,'user_uid',$search);
		}
		else{
			$parms=false;
		}
		
		if($start!==false){
			$additParams['limit']=array($start,$length);
		}
		$additParams['orderby']=array(array('user_uid'),array($ob));
		
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		if(!$users=$GLOBALS['MG']['SQL']->sql_fetchArray(array($GLOBALS['MG']['SITE']['ACCOUNT_TBL']),false,$parms,DB_ASSOC,DB_ALL_ROWS,$additParams)){
			trigger_error('(SQLACT): Could not load user data',E_USER_ERROR);
			return false;
		}
		
		$this->lastLength=$GLOBALS['MG']['SQL']->sql_numRows(array($GLOBALS['MG']['SITE']['ACCOUNT_TBL']),$parms);

		for($i=0;$i<$users['count'];$i++){
			$dta=$users[$i];
			$keys=array_keys($dta);
			$soq=count($dta);
			for($j=0;$j<$soq;$j++){
				$nkey=strtoupper(ereg_replace('user_','',$keys[$j]));
				switch($nkey){
					case 'IM':
						$tmp=explode(';',$dta[$keys[$j]]);
						$sot=count($tmp);
						for($k=0;$k<$sot;$k++){
							$curim=explode(':',$tmp[$k]);
							if(isset($curim[1])){
								$this->user[$dta['user_uid']]['IM'][$curim[0]]=$curim[1];	
							}
						}
					break;
					case 'BANNED':
						$this->user[$dta['user_uid']]['BANNED']=(boolean)$dta[$keys[$j]];
					break;
					case 'NAME':
					case 'FULLNAME':
						$this->user[$dta['user_uid']]['NAME']=explode(';',$dta[$keys[$j]]);
					break;
					default:
						$this->user[$dta['user_uid']][$nkey]=$dta[$keys[$j]];
					break;
				}
			}
		}
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		for($i=0;$i<$users['count'];$i++){
			if($acl){
				$this->act_getGroupMembership($users[$i]['user_uid']);
				$this->act_getACL($users[$i]['user_uid']);				
			}
		}
		return $this->user;
	}
	
	final public function act_isAccount($uid){
		$c=false;
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		if($GLOBALS['MG']['SQL']->sql_fetchResult(array($GLOBALS['MG']['SITE']['ACCOUNT_TBL']),array(array('user_uid')),array(array(false,false,'user_uid','=',$uid)))){
			$c=true;
		}
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		return $c;
	}
	
	final public function act_getLastLength(){
		return $this->lastLength;
	}
	
	final public function act_remove($uid){
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		if(!$uid){
			return false;
		}
		$params=array(array(false,false,'user_uid','=',$uid));
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,array($GLOBALS['MG']['SITE']['ACCOUNT_TBL']),$params)){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
			return false;
		}
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		return true;
	}
	
	final public function act_add($uid,$name,$email,$type){
		if(!$uid){
			return false;
		}
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);

		$params=array('user_uid','user_fullname','user_email','user_auth','user_account_created','user_account_modified');
		$data=array($uid,$name,$email,$type,$GLOBALS['MG']['SITE']['TIME'],$GLOBALS['MG']['SITE']['TIME']);
		$runNewPass=false;
		if($type=='sqlauth'){
			$runNewPass=true;
			$params[]='user_pass_expired';
			$data[]='1';
		}
		
		if($type==$GLOBALS['MG']['SITE']['DEFAULT_AUTH']){
			$type=false;
		}
			
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,array($GLOBALS['MG']['SITE']['ACCOUNT_TBL']),$params,$data)){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
			return false;
		}
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		$newPass=true;
		if($runNewPass){
			mginit_loadPackage(array(array('auth','abstract','/classes/auth/'),array('sqlauth','class','/classes/auth/')));
			$auth=new sqlauth();
			$newPass=substr(md5(rand().rand()), 0, sqlact::GEN_PASSWORD_LENGTH);
			$auth->auth_changePass($uid,$newPass,$GLOBALS['MG']['SITE']['PASS_ENCODING']);
		}
		return $newPass;
	}
	
	final private function act_getGroupMembership($uid){
		$groups=$GLOBALS['MG']['SQL']->sql_fetchArray(array(TABLE_PREFIX.'groups'),false,array(array(DB_LIKE,array(DB_OR),'group_members','%;'.$uid.';%'),array(false,false,'group_members','=','*')));
		$this->user[$uid]['GROUPS']['COUNT']=$groups['count'];
		for($i=0;$i<$groups['count'];$i++){
			if($groups[$i]['group_members']=='*'&&$uid==$GLOBALS['MG']['SITE']['DEFAULT_ACT']){
				$this->user[$uid]['GROUPS']['COUNT']--;
			}
			else{
				$this->user[$uid]['GROUPS'][]=$groups[$i]['group_gid'];	
			}	
		}
		$this->user[$uid]['GROUPS'][]='*';
		$this->user[$uid]['GROUPS']['COUNT']++;
	}
	
	final private function act_getACL($uid){
		$this->user[$uid]['ACL']=array();
		for($i=0;$i<$this->user[$uid]['GROUPS']['COUNT'];$i++){
			$acls=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'acl'),false,array(array(false,false,'acl_group','=',$this->user[$uid]['GROUPS'][$i])));	
			for($k=0;$k<$acls['count'];$k++){
				if($acls[$k]['acl_page']){
					if(!isset($this->user[$uid]['ACL'][$acls[$k]['acl_page']])){
						$this->user[$uid]['ACL'][$acls[$k]['acl_page']]=array();
						$this->user[$uid]['ACL'][$acls[$k]['acl_page']]['read']=false;
						$this->user[$uid]['ACL'][$acls[$k]['acl_page']]['modify']=false;
						$this->user[$uid]['ACL'][$acls[$k]['acl_page']]['write']=false;
						$this->user[$uid]['ACL'][$acls[$k]['acl_page']]['admin']=false;
					}
					
					if($this->user[$uid]['ACL'][$acls[$k]['acl_page']]['admin']==true||(boolean)$acls[$k]['acl_admin']==true){
						$this->user[$uid]['ACL'][$acls[$k]['acl_page']]['admin']=true;
						$this->user[$uid]['ACL'][$acls[$k]['acl_page']]['write']=true;
						$this->user[$uid]['ACL'][$acls[$k]['acl_page']]['modify']=true;
						$this->user[$uid]['ACL'][$acls[$k]['acl_page']]['read']=true;				
					}
					else{
						$this->user[$uid]['ACL'][$acls[$k]['acl_page']]['read']=$this->act_aclItem($this->user[$uid]['ACL'][$acls[$k]['acl_page']]['read'],$acls[$k]['acl_read']);
						$this->user[$uid]['ACL'][$acls[$k]['acl_page']]['modify']=$this->act_aclItem($this->user[$uid]['ACL'][$acls[$k]['acl_page']]['modify'],$acls[$k]['acl_modify']);
						$this->user[$uid]['ACL'][$acls[$k]['acl_page']]['write']=$this->act_aclItem($this->user[$uid]['ACL'][$acls[$k]['acl_page']]['write'],$acls[$k]['acl_write']);					
					}
				}
			}
		}
		$this->user[$uid]['ACL']['count']=count($this->user[$uid]['ACL']);
	}
	final private function act_aclItem($old,$new){
		
		switch($new){
			case '-':
				return 'deny';
			break;
			case '+':
				if($old!='deny'){
					return true;
				}
				return $old;	
			break;
			default:
				return $old;
			break;
		}
	}
}