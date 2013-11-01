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
		$acls=$this->acl_getAll($groups,false);
		foreach($acls as $acl){
			if($acl['acl_page']){
				if(!isset($userACL[$acl['acl_page']])){
					$userACL[$acl['acl_page']]=array();
					$userACL[$acl['acl_page']]['read']=false;
					$userACL[$acl['acl_page']]['modify']=false;
					$userACL[$acl['acl_page']]['write']=false;
					$userACL[$acl['acl_page']]['admin']=false;
				}				
				if($userACL[$acl['acl_page']]['admin']==true||(boolean)$acl['acl_admin']==true){
					$userACL[$acl['acl_page']]['admin']=true;
					$userACL[$acl['acl_page']]['write']=true;
					$userACL[$acl['acl_page']]['modify']=true;
					$userACL[$acl['acl_page']]['read']=true;				
				}
				else{
					$userACL[$acl['acl_page']]['read']=$this->acl_comp($userACL[$acl['acl_page']]['read'],$acl['acl_read']);
					$userACL[$acl['acl_page']]['modify']=$this->acl_comp($userACL[$acl['acl_page']]['modify'],$acl['acl_modify']);
					$userACL[$acl['acl_page']]['write']=$this->acl_comp($userACL[$acl['acl_page']]['write'],$acl['acl_write']);					
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
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_RESETAUTO,array(TABLE_PREFIX.'acl'),false)){
			trigger_error('(ACL): Could not reset auto increment',E_USER_NOTICE);
		}
		return true;
	}
	
	public function acl_delete($acl_id){
		if(!$acl_id){
			return false;
		}
		$conds=array(array(false,false,'acl_id','=',$acl_id));
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,array(TABLE_PREFIX.'acl'),$conds)){
			trigger_error('(ACL): Could not remove acl item from database: '.$acl_id,E_USER_ERROR);
			return false;
		}
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_RESETAUTO,array(TABLE_PREFIX.'acl'),false)){
			trigger_error('(ACL): Could not reset auto increment',E_USER_NOTICE);
		}
		return true;		
	}

	public function acl_add($gid,$page='*',$r='+',$m='',$w='',$a='0'){
		if(!$gid||!$page){
			return false;
		}
		$fields=array('acl_group','acl_page','acl_read','acl_modify','acl_write','acl_admin');
		$data=array($gid,$page,$r,$m,$w,$a);
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,array(TABLE_PREFIX.'acl'),$fields,$data)){
			trigger_error('(ACL): Could not add acl to database for group: '.$gid,E_USER_ERROR);
			return false;
		}
		return true;
	}
    
    public function acl_getAll($groups,$exact){	
		$conds=array();
        foreach($groups as $key=>$val){
            $conds[]=array(false,array(DB_OR),'acl_group','=',$val);
            if(!$exact && preg_match('/-/',$val)){
                $tmp = preg_split('/-/',$val);
                $conds[]=array(DB_LIKE,array(DB_OR),'acl_group','*-'.$tmp[1].'%');
            }
        }
        $conds[count($conds)-1][1]=false;
        $dta = $GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'acl'),false,$conds);
		return $dta;
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