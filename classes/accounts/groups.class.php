<?php

/**
 * @file		group.class.php
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

class group{
	
	private $table;
    private $tableDesc;
	
	public function __construct(){
		$this->table=(isset($GLOBALS['MG']['SITE']['GROUP_TBL']))?array($GLOBALS['MG']['SITE']['GROUP_TBL']):array(TABLE_PREFIX.'groups');
        $this->tableDesc=(isset($GLOBALS['MG']['SITE']['GROUP_TBL']))?array($GLOBALS['MG']['SITE']['GROUP_TBL'].'_description'):array(TABLE_PREFIX.'groups_description');
	}	
	
	public function group_getMembership($uid){
		if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		}		
		$groups=$GLOBALS['MG']['SQL']->sql_fetchArray($this->table,false,array(array(DB_LIKE,array(DB_OR),'group_members','%;'.$uid.';%'),array(DB_LIKE,false,'group_members','%;*;%')));
		if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		}
		$userGroups=array();
		$userGroups['COUNT']=$groups['count'];
		for($i=0;$i<$groups['count'];$i++){
			if(preg_match('/;\*;/',$groups[$i]['group_members'])&&preg_match('/-'.preg_quote($uid).'/',$groups[$i]['group_members'])){
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
    
    public function group_getAdmin($uid){
		if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		}		
		$groups=$GLOBALS['MG']['SQL']->sql_fetchArray($this->table,false,array(array(DB_LIKE,array(DB_OR),'group_admins','%;'.$uid.';%'),array(DB_LIKE,false,'group_admins','%;*;%')));
		if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		}
		$userGroups=array();
		$userGroups['COUNT']=$groups['count'];
		for($i=0;$i<$groups['count'];$i++){
			if(preg_match('/;\*;/',$groups[$i]['group_admins'])&&preg_match('/-'.preg_quote($uid).'/',$groups[$i]['group_admins'])){
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
	
	public function group_isValid($group){
		$conds=array(array(false,false,'group_gid','=',$group));
		if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		}
		$groups=$GLOBALS['MG']['SQL']->sql_fetchArray($this->table,false,$conds);
		if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		}
		$groups=$groups[0];
		if(strtolower($groups['group_gid'])==strtolower($group)){
			return true;
		}
		return false;
	}
	
	public function group_getGroup($start=0,$length=10,$search=false,$loadOnly=false,$searchadm=false,$anchorSearch=3){
		if(!empty($loadOnly)){
			$conds=array(array(false,false,'group_gid','=',$loadOnly));
			if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
				$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
			}
			$groups=$GLOBALS['MG']['SQL']->sql_fetchArray($this->table,false,$conds);
			if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
				$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
			}
            if(!is_array($groups[0])){
                return false;
            }
			$output = array();
            $output['GID'] = $groups[0]['group_gid'];
			$output['MEMBERS']=explode(';',$groups[0]['group_members']);
            $output['ADMINS']=explode(';',$groups[0]['group_admins']);
			return $output;
		}
		else{
			$addit['orderby']=array(array('group_gid'),array('DESC'));
			if($start && $length){
				$addit['limit']=array($start,$length);	
			}
			$conds=false;
			$totalLength=0;
            
            $before='';
            $after='';
            if($anchorSearch ==1 || $anchorSearch==3){
                $before='%';
            }
            if($anchorSearch ==2 || $anchorSearch==3){
                $after='%';
            }            
			if($search){
				$conds=array(array(DB_LIKE,false,'group_gid',$before.$search.$after));
			}
			if($searchadm){
				if(is_array($conds)){
					$conds[]=array(DB_LIKE,false,'group_admins',$before.$searchadm.$after);
				}
				else{
					$conds=array(array(DB_LIKE,false,'group_admins',$before.$searchadm.$after));
				}
			}		
			if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
				$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
			}
			$totalLength=$GLOBALS['MG']['SQL']->sql_numRows($this->table,$conds);
			$groups=$GLOBALS['MG']['SQL']->sql_fetchArray($this->table,false,$conds,DB_ASSOC,DB_ALL_ROWS,$addit);
			if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
				$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
			}		
			if(!$groups){
				return false;
			}
			$ret=array();
			for($i=0;$i<$groups['count'];$i++){
                if(!empty($groups[$i])){
                    $ret[$groups[$i]['group_gid']] = array();
                    foreach($groups[$i] as $key=>$val){
                        $key = preg_replace('/group_/','',$key);
                        if($key == 'members' || $key == 'admins'){
                            $ret[$groups[$i]['group_gid']][$key] = explode(';',$val);
                        }
                        else{
                            $ret[$groups[$i]['group_gid']][$key] = $val;
                        }
                    }
                }
			}
			$ret['count']=count($ret);
			return $ret;		
		}
	}
    
    public function group_getAcl($gids){
        $ac = new acl();
        if(!is_array($gids)){
            $gids= array($gids);
        }
        return $ac->acl_getAll($gids,true);
    }
	
	public function group_add($gid,$members,$admins,$desc=''){
		if(!$gid){
			return false;
		}
        if(is_array($members)){
            $members=implode(';',$members);
        }
        if(is_array($admins)){
            $admins=implode(';',$admins);
        }		
		if(substr($members,0,1)!=';'){
			$members=';'.$members;
		}
		if(substr($members,-1,1)!=';'){
			$members.=';';
		}
		if(substr($admins,0,1)!=';'){
			$admins=';'.$admins;
		}
		if(substr($admins,-1,1)!=';'){
			$admins.=';';
		}
		$dta=array($gid,$members,$admins);
		$fields=array('group_gid','group_members','group_admins');
		if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		}
		$r=true;
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,$this->table,$fields,$dta)){
			trigger_error('(GROUPS): Could not add group to database: '.$gid,E_USER_ERROR);
			$r=false;
		}
        $dta=array($gid,$desc);
		$fields=array('group_gid','group_description');
        if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,$this->tableDesc,$fields,$dta)){
			trigger_error('(GROUPS): Could not add group description to database: '.$gid,E_USER_ERROR);
			$r=false;
		}
		if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		}		
		return $r;
	}
	public function group_remove($gid){
		if(!$gid){
			return false;
		}
		if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		}
		$r=true;
		$conds=array(array(false,false,'group_gid','=',$gid));
		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,$this->table,$conds)){
			trigger_error('(GROUPS): Could not remove group from database: '.$gid,E_USER_ERROR);
			$r =false;
		}

		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_REMOVE,$this->tableDesc,$conds)){
			trigger_error('(GROUPS): Could not remove group description from database: '.$gid,E_USER_ERROR);
			$r =false;
		}

		if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		}
		if(!$r){
			return false;
		}
		$ac= new acl();
		if(!$ac->acl_deleteGroupAcl($gid)){
			$ac=false;
			return false;
		}
		$ac=false;
		return true;
	}
	
	public function group_addUser($gid,$uid){
		$udata=$this->group_getGroup(false,false,false,$gid);
		$udata=$udata['MEMBERS'];
		if(in_array($uid,$udata)){
			trigger_error('(GROUPS): User '.$uid.' already in group '.$gid.'. Not adding.',E_USER_NOTICE);
			return true;
		}
		if($udata['count']==1){
			$udata=array('','','count'=>2);
		}
		$udata[$udata['count']-1]=$uid;
        unset($udata['count']);
		$udata[]='';
		return $this->group_modify($gid,$udata);
	}
	
	public function group_removeUser($gid,$uid){
		$udata=$this->group_getGroup(false,false,false,$gid);
		$udata=$udata['MEMBERS'];
		$key=array_search($uid,$udata);
		if($key===false){
			trigger_error('GROUPS: User '.$uid.' not in group '.$gid.'. Not removing.',E_USER_NOTICE);
			return true;
		}

		$soq=count($udata);
		$newarr=array_slice($udata,0,$key);
		for($i=$key;$i<$soq;$i++){
			if($udata[$i]==$uid||!$udata[$i]){

			}
			else{
				$newarr[]=$udata[$i];
			}
			
		}
		$newarr[]='';
		return $this->group_modify($gid,$newarr);
	}
	
	public function group_isUserValid($uid){
		if($uid=='*'){
			return true;
		}
		if(substr($uid,0,1)=='-'){
			$uid=substr($uid,1);
		}
		eval('$act=new '.$GLOBALS['MG']['SITE']['ACCOUNT_TYPE'].'();');
		$r=$act->act_isAccount($uid);
		$act=false;
		return $r;
	}
    	
	public function group_modify($gid,$newUsersList,$newAdminList=false,$newDesc=false){
		if(!$gid){
			return false;
		}
        if(is_array($newUsersList)){
            $newUsersList=implode(';',$newUsersList);
        }
		if(substr($newUsersList,0,1)!=';'){
			$newUsersList=';'.$newUsersList;
		}
		if(substr($newUsersList,-1,1)!=';'){
			$newUsersList.=';';
		}
		$conds=array(array(false,false,'group_gid','=',$gid));
		$ud=array(array('group_members',$newUsersList));
		if(!empty($newAdminList)){
            if($newAdminList != ''){
                if(is_array($newAdminList)){
                    $newAdminList=implode(';',$newAdminList);
                }
    			if(substr($newAdminList,0,1)!=';'){
    				$newAdminList=';'.$newAdminList;
    			}
    			if(substr($newAdminList,-1,1)!=';'){
    				$newAdminList.=';';
    			}
            }
			$ud[]=array('group_admins',$newAdminList);	
		}
		
		if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
		}
		$r=true;

		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,$this->table,$conds,$ud)){
			trigger_error('(GROUPS): Could not update group in database: '.$gid,E_USER_ERROR);
			$r= false;
		}

        if(!empty($newDesc)){
            $ud=array(array('group_description',$newDesc));
    		if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,$this->tableDesc,$conds,$ud)){
    			trigger_error('(GROUPS): Could not update group description in database: '.$gid,E_USER_ERROR);
    			$r= false;
    		}

        }

		if(isset($GLOBALS['MG']['SITE']['GROUP_TBL'])){
			$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
		}
		return $r;
	}
}