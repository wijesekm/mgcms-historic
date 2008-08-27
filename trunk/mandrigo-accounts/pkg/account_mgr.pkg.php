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
	
	public function __construct(){
		$this->vars=array('LM_MSG'=>'');
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
		return array();
	}
	
	private function am_profile(){
		
	}
	
	private function am_genList(){
		
	}
}