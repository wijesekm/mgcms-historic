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

	final public function act_load($uid){
		$this->user=array();
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		if(!$dta=$GLOBALS['MG']['SQL']->sql_fetchArray(array($GLOBALS['MG']['SITE']['ACCOUNT_TBL']),array(),array(array(false,false,'user_uid','=',$uid)))){
			trigger_error('(SQLACT): Could not load user data',E_USER_ERROR);
			return false;
		}
		$dta=$dta[0];
		$keys=array_keys($dta);
		$soq=count($dta);
		for($i=0;$i<$soq;$i++){
			$nkey=strtoupper(ereg_replace('user_','',$keys[$i]));
			switch($nkey){
				case 'BANNED':
					$this->user=array_merge_recursive($this->user,array('BANNED'=>(boolean)$dta[$keys[$i]]));
				break;
				case 'NAME':
				case 'FULLNAME':
					$this->user=array_merge_recursive($this->user,array('NAME'=>explode(';',$dta[$keys[$i]])));
				break;
				default:
					$this->user=array_merge_recursive($this->user,array($nkey=>$dta[$keys[$i]]));
				break;
			}
		}
		$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		$this->act_getGroupMembership($uid);
		$this->act_getACL();
		
		return $this->user;
	}
	
	
	final private function act_getGroupMembership($uid){
		$groups=$GLOBALS['MG']['SQL']->sql_fetchArray(array(TABLE_PREFIX.'groups'),false,array(array(DB_LIKE,array(DB_OR),'group_members','%;'.$uid.';%'),array(false,false,'group_members','=','*')));
		$this->user['GROUPS']['COUNT']=$groups['count'];
		for($i=0;$i<$groups['count'];$i++){
			if($groups[$i]['group_members']=='*'&&$uid==$GLOBALS['MG']['SITE']['DEFAULT_ACT']){
				$this->user['GROUPS']['COUNT']--;
			}
			else{
				$this->user['GROUPS'][]=$groups[$i]['group_gid'];	
			}	
		}
		$this->user['GROUPS'][]='*';
		$this->user['GROUPS']['COUNT']++;
	}
	
	final private function act_getACL(){
		$this->user['ACL']=array();
		for($i=0;$i<$this->user['GROUPS']['COUNT'];$i++){
			$acls=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'acl'),false,array(array(false,false,'acl_group','=',$this->user['GROUPS'][$i])));	
			for($k=0;$k<$acls['count'];$k++){
				if($acls[$k]['acl_page']){
					if(!is_array($this->user['ACL'][$acls[$k]['acl_page']])){
						$this->user['ACL'][$acls[$k]['acl_page']]=array();
						$this->user['ACL'][$acls[$k]['acl_page']]['read']=false;
						$this->user['ACL'][$acls[$k]['acl_page']]['modify']=false;
						$this->user['ACL'][$acls[$k]['acl_page']]['write']=false;
						$this->user['ACL'][$acls[$k]['acl_page']]['admin']=false;
					}
					
					if($this->user['ACL'][$acls[$k]['acl_page']]['admin']==true||(boolean)$acls[$k]['acl_admin']==true){
						$this->user['ACL'][$acls[$k]['acl_page']]['admin']=true;
						$this->user['ACL'][$acls[$k]['acl_page']]['write']=true;
						$this->user['ACL'][$acls[$k]['acl_page']]['modify']=true;
						$this->user['ACL'][$acls[$k]['acl_page']]['read']=true;				
					}
					else{
						$this->user['ACL'][$acls[$k]['acl_page']]['read']=$this->act_aclItem($this->user['ACL'][$acls[$k]['acl_page']]['read'],$acls[$k]['acl_read']);
						$this->user['ACL'][$acls[$k]['acl_page']]['modify']=$this->act_aclItem($this->user['ACL'][$acls[$k]['acl_page']]['modify'],$acls[$k]['acl_modify']);
						$this->user['ACL'][$acls[$k]['acl_page']]['write']=$this->act_aclItem($this->user['ACL'][$acls[$k]['acl_page']]['write'],$acls[$k]['acl_write']);					
					}
				}
			}
		}
		$this->user['ACL']['count']=count($this->user['ACL']);
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