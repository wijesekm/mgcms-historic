<?php

/**
 * @file		login_manager.pkg.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		8-5-2008
 
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

class login_manager{	
	
	private $vars;

	public function __construct(){
		$this->vars=array('LM_MSG'=>'');
	}
	
	public function lm_titleHook(){
		return $GLOBALS['MG']['PAGE']['NAME'].' - '.$GLOBALS['MG']['SITE']['NAME'];
	}
	
	public function lm_displayHook(){
		if(!mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'read')){
			return 403;
		}
		switch($GLOBALS['MG']['GET']['ACTION']){
			case 'logout':
				$ses=new session($GLOBALS['MG']['USER']['TIME']);
				$ses->session_load($GLOBALS['MG']['COOKIE']['USER_NAME'],$GLOBALS['MG']['COOKIE']['USER_SESSION']);
				$cdta=array(
					'SECURE'=>(boolean)$GLOBALS['MG']['SITE']['COOKIE_SECURE'],
					'PATH'=>$GLOBALS['MG']['SITE']['COOKIE_PATH'],
					'DOM'=>$GLOBALS['MG']['SITE']['COOKIE_DOM'],
				);
				$ses->session_stop($cdta);
				mg_redirectTarget($GLOBALS['MG']['GET']['TARGET']);				
			break;
			case 'login':
			default:
				if(!$GLOBALS['MG']['USER']['NOAUTH']){
					mg_redirectTarget($GLOBALS['MG']['GET']['TARGET']);
				}
				if($GLOBALS['MG']['POST']['LOGIN_NAME']&&$GLOBALS['MG']['POST']['LOGIN_PASSWORD']){
					return $this->lm_doLogin();
				}
				else{
					return $this->lm_loginTemplate();
				}
			break;
		}
	}
	
	public function lm_varHook(){
		if($GLOBALS['MG']['POST']['LOGIN_NAME']){
			$this->vars=mg_mergeArrays($this->vars,array('LM_USERNAME'=>$GLOBALS['MG']['POST']['LOGIN_NAME']));
		}
		else if($GLOBALS['MG']['COOKIE']['USER_NAME']&&$GLOBALS['MG']['COOKIE']['USER_NAME']!=$GLOBALS['MG']['SITE']['DEFAULT_ACT']){
			$this->vars=mg_mergeArrays($this->vars,array('LM_USERNAME'=>$GLOBALS['MG']['COOKIE']['USER_NAME']));
		}
		return $this->vars;
	}
	
	private function lm_loginTemplate(){
		$tpl=new template();
		if(!$tpl->tpl_load($GLOBALS['MG']['PAGE']['TPL'],'loginscreen')){
			trigger_error('(LOGIN_MANAGER): Could not load site template',E_USER_ERROR);
			return false;
		}
		return $tpl->tpl_return('loginscreen');	
	}
	
	private function lm_doLogin(){
		eval('$act=new '.$GLOBALS['MG']['SITE']['ACCOUNT_TYPE'].'();');
		$GLOBALS['MG']['USER']=$act->act_load($GLOBALS['MG']['POST']['LOGIN_NAME']);
		$GLOBALS['MG']['USER']=$GLOBALS['MG']['USER'][$GLOBALS['MG']['POST']['LOGIN_NAME']];
		if(!$GLOBALS['MG']['USER']['UID']){
			$this->vars=mg_mergeArrays($this->vars,array('LM_MSG'=>$GLOBALS['MG']['LANG']['LM_BAD_ACCOUNT']));
		}
		else{
			/**
			* Time Data
			*/
			$t=new mgtime($GLOBALS['MG']['SITE']['TZ'],$GLOBALS['MG']['USER']['TZ']);
			$GLOBALS['MG']['SITE']['TIME']=$t->time_server();
			$GLOBALS['MG']['USER']['TIME']=$t->time_client();
			$t=false;
			if($GLOBALS['MG']['SITE']['AUTH_OVERRIDE']&&$GLOBALS['MG']['USER']['AUTH']){
				mginit_loadCustomPackages(array($GLOBALS['MG']['USER']['AUTH']));
				eval('$auth=new '.$GLOBALS['MG']['USER']['AUTH'].'();');
			}
			else{
				mginit_loadCustomPackages(array($GLOBALS['MG']['SITE']['DEFAULT_AUTH']));
				eval('$auth=new '.$GLOBALS['MG']['SITE']['DEFAULT_AUTH'].'();');
			}
			
			if($GLOBALS['MG']['USER']['BANNED']){
				$this->vars=mg_mergeArrays($this->vars,array('LM_MSG'=>$GLOBALS['MG']['LANG']['LM_BANNED']));
			}
			else{
				if(!$auth->auth_authenticate($GLOBALS['MG']['POST']['LOGIN_NAME'],$GLOBALS['MG']['POST']['LOGIN_PASSWORD'],$GLOBALS['MG']['SITE']['PASS_ENCODING'])){
					$this->vars=mg_mergeArrays($this->vars,array('LM_MSG'=>$GLOBALS['MG']['LANG']['LM_BAD_PASSWORD']));
				}
				else{
					$expires=$GLOBALS['MG']['SITE']['COOKIE_EXPIRES_DEFAULT'];
					if($GLOBALS['MG']['POST']['REMEMBER_SESSION']=='1'){
						$expires=$GLOBALS['MG']['SITE']['COOKIE_EXPIRES_REMEMBER'];
					}
					$cdta=array(
						'SECURE'=>(boolean)$GLOBALS['MG']['SITE']['COOKIE_SECURE'],
						'PATH'=>$GLOBALS['MG']['SITE']['COOKIE_PATH'],
						'DOM'=>$GLOBALS['MG']['SITE']['COOKIE_DOM'],
						'EXPIRES'=>$expires
					);
					$ses= new session($GLOBALS['MG']['USER']['TIME']);
					if(!$ses->session_start($GLOBALS['MG']['POST']['LOGIN_NAME'],$cdta)){
						$this->vars=mg_mergeArrays($this->vars,array('LM_MSG'=>$GLOBALS['MG']['LANG']['LM_INT_ERROR']));
					}
					else{
						$GLOBALS['MG']['SQL']->sql_dataCommands(DB_INSERT,array(TABLE_PREFIX.'auth_log'),array('auth_uid','auth_ip','auth_time'),array($GLOBALS['MG']['POST']['LOGIN_NAME'],$_SERVER['REMOTE_ADDR'],$GLOBALS['MG']['SITE']['TIME']));
						mg_redirectTarget($GLOBALS['MG']['GET']['TARGET']);
					}
				}			
			}
		}
		return $this->lm_loginTemplate();
	}
}