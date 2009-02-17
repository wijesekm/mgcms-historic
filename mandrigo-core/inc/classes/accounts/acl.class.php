<?php

/**
 * @file		acl.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		2-17-2009
 
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

class acl{
		
	public function acl_load($uid,$groups){
		$userACL=array();
		for($i=0;$i<$groups['COUNT'];$i++){
			$acls=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'acl'),false,array(array(false,false,'acl_group','=',$groups[$i])));	
			for($k=0;$k<$acls['count'];$k++){
				if($acls[$k]['acl_page']){
					if(!isset($userACL[$acls[$k]['acl_page']])){
						$userACL[$acls[$k]['acl_page']]=array();
						$userACL[$acls[$k]['acl_page']]['read']=false;
						$userACL[$acls[$k]['acl_page']]['modify']=false;
						$userACL[$acls[$k]['acl_page']]['write']=false;
						$userACL[$acls[$k]['acl_page']]['admin']=false;
					}
					
					if($userACL[$acls[$k]['acl_page']]['admin']==true||(boolean)$acls[$k]['acl_admin']==true){
						$userACL[$acls[$k]['acl_page']]['admin']=true;
						$userACL[$acls[$k]['acl_page']]['write']=true;
						$userACL[$acls[$k]['acl_page']]['modify']=true;
						$userACL[$acls[$k]['acl_page']]['read']=true;				
					}
					else{
						$userACL[$acls[$k]['acl_page']]['read']=$this->acl_comp($userACL[$acls[$k]['acl_page']]['read'],$acls[$k]['acl_read']);
						$userACL[$acls[$k]['acl_page']]['modify']=$this->acl_comp($userACL[$acls[$k]['acl_page']]['modify'],$acls[$k]['acl_modify']);
						$userACL[$acls[$k]['acl_page']]['write']=$this->acl_comp($userACL[$acls[$k]['acl_page']]['write'],$acls[$k]['acl_write']);					
					}
				}
			}
		}
		$userACL['count']=count($userACL);
		return $userACL;
	}
	
	public function acl_deleteGroupAcl($gid){
		if(!$gid){
			return false;
		}
		$conds=array(array(false,false,'acl_group','=',$gid));
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,array(TABLE_PREFIX.'acl'),$conds)){
			trigger_error('(ACL): Could not remove group acl from database: '.$gid,E_USER_ERROR);
			return false;
		}
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_RESETAUTO,array(TABLE_PREFIX.'acl',false))){
			trigger_error('(ACL): Could not reset auto increment',E_USER_NOTICE);
		}
		return true;
	}
		
	private function acl_comp($old,$new){
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