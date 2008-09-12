<?php

/**
 * @file                account_mgr.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2008
 * @edited              8-27-2008
 
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

class account_mgr{
	
	private $vars;
	private $mgr;
	
	const TPL_NAME	= 'account_mgr.tpl';
	
	public function __construct(){
		$this->vars=array();
		eval('$this->mgr=new '.$GLOBALS['MG']['SITE']['ACCOUNT_TYPE'].'();');
		$GLOBALS['MG']['GET']['QUERY']=(eregi("^[a-z0-9\\@\*$._-]+$",$GLOBALS['MG']['GET']['QUERY']))?$GLOBALS['MG']['GET']['QUERY']:'';
	}
	
	public function am_titleHook(){
		if($GLOBALS['MG']['GET']['UID']&&$GLOBALS['MG']['GET']['ACTION']!='delete'){
			return $GLOBALS['MG']['PAGE']['NAME'].' ( '.$GLOBALS['MG']['GET']['UID'].' )- '.$GLOBALS['MG']['SITE']['NAME'];	
		}
		return $GLOBALS['MG']['PAGE']['NAME'].' - '.$GLOBALS['MG']['SITE']['NAME'];
	}
	
	public function am_contentHook(){
		if(!mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'read')){
			return 403;
		}
		if($GLOBALS['MG']['GET']['UID']){
			if($GLOBALS['MG']['SITE']['AM_PROFILES_PRIVATE']=='1'&&$GLOBALS['MG']['GET']['UID']!=$GLOBALS['MG']['USER']['UID']&&!mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'admin')){
				return 403;
			}
			switch($GLOBALS['MG']['GET']['ACTION']){
				case 'delete':
					if(!mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'admin')){
						return 403;
					}
					return $this->am_delUser();
				break;
				default:
					return $this->am_profile();
				break;
			}
			
		}
		else{
			if($GLOBALS['MG']['SITE']['AM_PROFILES_PRIVATE']=='1'&&!mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'admin')){
				return 403;
			}
			if($GLOBALS['MG']['GET']['ACTION']=='add'){
				if(!mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'admin')){
					return 403;
				}
				return $this->am_addUser();
			}
			else{
				return $this->am_genList();
			}
		}
		return false;
	}
	
	public function am_varHook(){
		return $this->vars;
	}
	
	private function am_delUser($mail=true){
		if(!$GLOBALS['MG']['GET']['UID']){
			trigger_error('(ACCOUNT_MGR): No UID specified for delete!',E_USER_NOTICE);
			return $this->am_genList($GLOBALS['MG']['LANG']['AM_INT_ERROR']);
		}
		if(strtolower($GLOBALS['MG']['GET']['UID'])==strtolower($GLOBALS['MG']['SITE']['DEFAULT_ACT'])){
			trigger_error('(ACCOUNT_MGR): Cannot delete default account!',E_USER_NOTICE);
			return $this->am_genList($GLOBALS['MG']['LANG']['AM_INT_ERROR']);
		}
		$udta=$this->mgr->act_load($GLOBALS['MG']['GET']['UID'],false,false,false,false);
		$parse=array(
			'NAME'=>implode(' ',$udta[$GLOBALS['MG']['GET']['UID']]['NAME']),
			'USERNAME'=>$GLOBALS['MG']['GET']['UID'],
			'E-MAIL'=>$udta[$GLOBALS['MG']['GET']['UID']]['EMAIL']
		);
		$tpl=new template();
		$tpl->tpl_load($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/'.account_mgr::TPL_NAME,'removeactmail');
		$tpl->tpl_parse($parse,'removeactmail');
		$ppm=new phpmailer();
		$ppm->phpm_addSubject($GLOBALS['MG']['LANG']['AM_EMAIL_SUBJECT']);
		$ppm->phpm_addBody($tpl->tpl_return('removeactmail'),(boolean)$GLOBALS['MG']['SITE']['EMAIL-HTML'],(boolean)$GLOBALS['MG']['SITE']['EMAIL-ALT']);
		$s=explode(';',$GLOBALS['MG']['SITE']['AM-MAIL-SENDER']);
		$ppm->phpm_addSender($s[0],$s[1]);
		$ppm->phpm_addAddress(implode(' ',$udta[$GLOBALS['MG']['GET']['UID']]['NAME']),$udta[$GLOBALS['MG']['GET']['UID']]['EMAIL']);		
		if(!$ppm->phpm_send()){
			return $this->am_genList($GLOBALS['MG']['LANG']['AM_INT_ERROR']);
		}
		if(!$this->mgr->act_remove($GLOBALS['MG']['GET']['UID'])){
			return $this->am_genList($GLOBALS['MG']['LANG']['AM_INT_ERROR']);
		}
		return $this->am_genList($GLOBALS['MG']['LANG']['AM_DEL_DELETED']);
	}
	
	private function am_addUser(){
		if(!$GLOBALS['MG']['POST']['AM-ADD-UID']){
			return $this->am_genList($GLOBALS['MG']['LANG']['AM_ADD_BADUID']);
		}
		if(!in_array($GLOBALS['MG']['POST']['AM-ADD-ACTYPE'],$GLOBALS['MG']['CFG']['AUTH']['SUPPORTED'])){
			return $this->am_genList($GLOBALS['MG']['LANG']['AM_ADD_BADTYPE']);	
		}
		if($this->mgr->act_isAccount($GLOBALS['MG']['POST']['AM-ADD-UID'])){
			return $this->am_genList($GLOBALS['MG']['LANG']['AM_ADD_UIDTAKEN']);
		}
		if(!$newPass=$this->mgr->act_add($GLOBALS['MG']['POST']['AM-ADD-UID'],implode(';',explode(' ',$GLOBALS['MG']['POST']['AM-ADD-NAME'])),$GLOBALS['MG']['POST']['AM-ADD-EMAIL'],$GLOBALS['MG']['POST']['AM-ADD-ACTYPE'])){
			return $this->am_genList($GLOBALS['MG']['LANG']['AM_INT_ERROR']);
		}
		$tpl=new template();
		$tpl->tpl_load($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/'.account_mgr::TPL_NAME,'newactemail');
		$parse=array(
			'USERNAME'=>$GLOBALS['MG']['POST']['AM-ADD-UID'],
			'E-MAIL'=>$GLOBALS['MG']['POST']['AM-ADD-EMAIL'],
			'NAME'=>$GLOBALS['MG']['POST']['AM-ADD-NAME'],
			'PASSWORD'=>(string)$newPass
		);
		$tpl->tpl_parse($parse,'newactemail');
		$ppm=new phpmailer();
		$ppm->phpm_addSubject($GLOBALS['MG']['LANG']['AM_EMAIL_SUBJECT']);
		$ppm->phpm_addBody($tpl->tpl_return('newactemail'),(boolean)$GLOBALS['MG']['SITE']['EMAIL-HTML'],(boolean)$GLOBALS['MG']['SITE']['EMAIL-ALT']);
		$s=explode(';',$GLOBALS['MG']['SITE']['AM-MAIL-SENDER']);
		$ppm->phpm_addSender($s[0],$s[1]);
		$ppm->phpm_addAddress($GLOBALS['MG']['POST']['AM-ADD-NAME'],$GLOBALS['MG']['POST']['AM-ADD-EMAIL']);
		if(!$ppm->phpm_send()){
			$GLOBALS['MG']['GET']['UID']=$GLOBALS['MG']['POST']['AM-ADD-UID'];
			$this->am_delUser(false);
			return $this->am_genList($GLOBALS['MG']['LANG']['AM_INT_ERROR']);
		}
		return $this->am_genList($GLOBALS['MG']['LANG']['AM_ADD_ADDED']);
	}
	
	private function am_profile(){
		
	}
	
	private function am_genList($msg='',$ob='ASC'){
		$tpl=new template();
		
		$start=$GLOBALS['MG']['GET']['PAGE_NUMBER']*$GLOBALS['MG']['SITE']['AM_UPP'];
		$users=$this->mgr->act_load(false,ereg_replace('\*','%',$GLOBALS['MG']['GET']['QUERY']),$start,$GLOBALS['MG']['SITE']['AM_UPP'],false,$ob);
		$length=$this->mgr->act_getLastLength();

		$actstr='';
		$keys=array_keys($users);
		$soq=count($keys);
		for($i=0;$i<$soq;$i++){
			if($users[$keys[$i]]['UID']){
				$tpl->tpl_load($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/'.account_mgr::TPL_NAME,'actitem');
				$parse['UID']=$users[$keys[$i]]['UID'];
				$parse['E-MAIL']=$users[$keys[$i]]['EMAIL'];
				$parse['USER_NAME']=implode(' ',$users[$keys[$i]]['NAME']);
				$parse['URL']=mg_genUrl(array('p',$GLOBALS['MG']['PAGE']['PATH'],'uid',$users[$keys[$i]]['UID']));
				$parse['DELETEURL']=mg_genUrl(array('p',$GLOBALS['MG']['PAGE']['PATH'],'uid',$users[$keys[$i]]['UID'],'a','delete'));
				$tpl->tpl_parse($parse,'actitem');
				$actstr.=$tpl->tpl_return('actitem');			
			}
		}

		$tpl->tpl_load($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/'.account_mgr::TPL_NAME,'actmgrmain');
		$this->vars=mg_mergeArrays($this->vars,array('AM_MSG'=>$msg,'ACCOUNTS'=>$actstr,'NAVBAR'=>$this->am_navBar($length)));
		return $tpl->tpl_return('actmgrmain');
	}

	private function am_navBar($length){

		$base=array('p',$GLOBALS['MG']['PAGE']['PATH']);
		
		if($GLOBALS['MG']['GET']['QUERY']){
			$base[]='q';
			$base[]=$GLOBALS['MG']['GET']['QUERY'];
		}
		
		if($length==0){
			return false;
		}
		$pages=ceil($length/$GLOBALS['MG']['SITE']['AM_UPP']);
		if($pages <= 1){
			return false;
		}
		$tpl=new template();
		$pstr='';
		for($i=0;$i<$pages;$i++){
			$tpl->tpl_load($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/'.account_mgr::TPL_NAME,'pdelim');
			$url=mg_genUrl(array_merge($base,array('pn',(string)$i)));
			$tpl->tpl_parse(array('URL'=>$url,'PG'=>(string)($i+1),'INDEX'=>(string)$i,'LENGTH'=>(string)$pages),'pdelim');
			$pstr.=$tpl->tpl_return('pdelim');
		}
		
		$back = $GLOBALS['MG']['GET']['PAGE_NUMBER']-1;
		$next = $GLOBALS['MG']['GET']['PAGE_NUMBER']+1;
		
		if($back >= 0){
			$urlb=mg_genUrl(array_merge($base,array('pn',(string)$back)));
			$back="yes";
		}
		if($next < $pages){
			$urln=mg_genUrl(array_merge($base,array('pn',(string)$next)));
			$next="yes";
		}
		
		$tpl->tpl_load($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/'.account_mgr::TPL_NAME,'subnav');
		$tpl->tpl_parse(array('BACK'=>$back,'NEXT'=>$next,'BACK_URL'=>$urlb,'NEXT_URL'=>$urln),'subnav');
		$nstr=$tpl->tpl_return('subnav');
		
		$tpl->tpl_load($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/'.account_mgr::TPL_NAME,'navbar');
		$tpl->tpl_parse(array('PAGES'=>$pstr,'SUBNAV'=>$nstr),'navbar');
		return $tpl->tpl_return('navbar');
	}
}