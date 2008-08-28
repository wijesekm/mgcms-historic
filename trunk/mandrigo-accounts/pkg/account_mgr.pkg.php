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
	
	const TPL_NAME	= 'account_mgr.tpl';
	
	public function __construct(){
		$this->vars=array('LM_MSG'=>'');
		$GLOBALS['MG']['GET']['QUERY']=(eregi("^[a-z0-9\\@\*$._-]+$",$GLOBALS['MG']['GET']['QUERY']))?$GLOBALS['MG']['GET']['QUERY']:'';
	}
	
	public function am_titleHook(){
		if($GLOBALS['MG']['GET']['UID']){
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
			return $this->am_profile();
		}
		else{
			if($GLOBALS['MG']['SITE']['AM_PROFILES_PRIVATE']=='1'&&!mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'admin')){
				return 403;
			}
			return $this->am_genList();
		}
		return false;
	}
	
	public function am_varHook(){
		return $this->vars;
	}
	
	private function am_profile(){
		
	}
	
	private function am_genList($msg=''){
		$tpl=new template();
		
		$start=$GLOBALS['MG']['GET']['PAGE_NUMBER']*$GLOBALS['MG']['SITE']['AM_UPP'];
		$additParams=array();
		$additParams['limit']=array($start,$GLOBALS['MG']['SITE']['AM_UPP']);
		$additParams['orderby']=array(array('user_uid'),array('ASC'));
		$parms=false;
		if($GLOBALS['MG']['GET']['QUERY']){
			$parms=array(array(DB_LIKE,false,'user_uid',ereg_replace('\*','%',$GLOBALS['MG']['GET']['QUERY'])));
		}
		$users=$GLOBALS['MG']['SQL']->sql_fetchArray(array(TABLE_PREFIX.'users'),false,$parms,DB_ASSOC,DB_ALL_ROWS,$additParams);
		$length=$GLOBALS['MG']['SQL']->sql_numRows(array(TABLE_PREFIX.'users'),$parms);
		$actstr='';
		for($i=0;$i<$users['count'];$i++){
			if($users[$i]['user_uid']){
				$tpl->tpl_load($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/'.account_mgr::TPL_NAME,'actitem');
				$parse['UID']=$users[$i]['user_uid'];
				$parse['E-MAIL']=$users[$i]['user_email'];
				$parse['USER_NAME']=implode(' ',explode(';',$users[$i]['user_fullname']));
				$parse['URL']=mg_genUrl(array('p',$GLOBALS['MG']['PAGE']['PATH'],'uid',$users[$i]['user_uid']));
				$parse['DELETEURL']=mg_genUrl(array('p',$GLOBALS['MG']['PAGE']['PATH'],'uid',$users[$i]['user_uid'],'a','delete'));
				$tpl->tpl_parse($parse,'actitem');
				$actstr.=$tpl->tpl_return('actitem');			
			}
		}
		
		$tpl->tpl_load($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/'.account_mgr::TPL_NAME,'actmgrmain');
		$this->vars=mg_mergeArrays($this->vars,array('LM_MSG'=>$msg,'ACCOUNTS'=>$actstr,'NAVBAR'=>$this->am_navBar($length)));
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
		if($next < $length){
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