<?php

/**
 * @file		group.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		2-12-2009
 
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

class group{
	
	public function group_getMembership($uid){
		$groups=$GLOBALS['MG']['SQL']->sql_fetchArray(array(TABLE_PREFIX.'groups'),false,array(array(DB_LIKE,array(DB_OR),'group_members','%;'.$uid.';%'),array(DB_LIKE,false,'group_members','%;*;%')));
		$userGroups=array();
		$userGroups['COUNT']=$groups['count'];
		for($i=0;$i<$groups['count'];$i++){
			if(eregi(';*;',$groups[$i]['group_members'])&&eregi('-'.$uid,$groups[$i]['group_members'])){
				$userGroups['COUNT']--;
			}
			else{
				$userGroups[]=$groups[$i]['group_gid'];	
			}	
		}
		$userGroups[]='*';
		$userGroups['COUNT']++;
		return $userGroups;			
	}
	
	public function group_getGroup($start=0,$length=10,$search=false,$loadOnly=false){
		$addit['orderby']=array(array('group_name'),array('DESC'));
		$addit['limit']=array($start,$length);
		$conds=false;
		if($search){
			$conds=array(array(DB_LIKE,false,'group_name','%;'.$search.';%'));
		}
		if($loadOnly){
			$conds=array(array(false,false,'group_name','=',$loadOnly));
		}
		$groups=$GLOBALS['MG']['SQL']->sql_fetchArray(array(TABLE_PREFIX.'groups'),$attrs,$conds,DB_ASSOC,DB_ALL_ROWS,$addit);
		if(!$groups){
			return false;
		}
		for($i=0;$i<$soq;$i++){
			$groups[$i]['group_members']=explode(';',$groups[$i]['group_members']);
		}
		return $groups;
	}
	
	public function group_add($gid,$members){
		if(!$gid){
			return false;
		}
		$members=implode(';',$members);	
		$dta=array($gid,$members);
		$fields=array('group_gid','group_members');
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,array(TABLE_PREFIX.'groups'),$fields,$dta)){
			trigger_error('(SQLACT): Could not add group to database: '.$gid,E_USER_ERROR);
			return false;
		}
		return true;
	}
	public function group_remove($gid){
		if(!$gid){
			return false;
		}
		$conds=array(array(false,false,'group_gid','=',$gid));
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,array(TABLE_PREFIX.'groups'),$conds)){
			trigger_error('(SQLACT): Could not remove group from database: '.$gid,E_USER_ERROR);
			return false;
		}
		$conds=array(array(false,false,'acl_group','=',$gid));
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,array(TABLE_PREFIX.'acl'),$conds)){
			trigger_error('(SQLACT): Could not remove group acl from database: '.$gid,E_USER_ERROR);
			return false;
		}
		return true;
	}
	public function group_modify($gid,$newUsersList){
		if(!$gid){
			return false;
		}
		$members=implode(';',$members);	
		$conds=array(array(false,false,'group_gid','=',$gid));
		$ud=array(array('group_members',$members));
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,array(TABLE_PREFIX.'groups'),$conds,$ud)){
			trigger_error('(SQLACT): Could not update group in database: '.$gid,E_USER_ERROR);
			return false;
		}
		return true;
	}
}