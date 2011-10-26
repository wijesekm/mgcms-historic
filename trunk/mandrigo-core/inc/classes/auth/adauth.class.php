<?php

/**
 * @file		adauth.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		6-9-2008
 
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

class adauth extends auth{
	
	private $ad;
	
	public function __construct(){
		$domains=explode(';',$GLOBALS['MG']['SITE']['AD_DOMAINS']);
		if(!is_array($GLOBALS['MG']['SITE']['AD_BASE_DN'])){
			$GLOBALS['MG']['SITE']['AD_BASE_DN']=explode(';',$GLOBALS['MG']['SITE']['AD_BASE_DN']);	
		}
		if(!is_array($GLOBALS['MG']['SITE']['AD_DOMAIN_CONTROLLERS'])){
			$GLOBALS['MG']['SITE']['AD_DOMAIN_CONTROLLERS']=explode(';',$GLOBALS['MG']['SITE']['AD_DOMAIN_CONTROLLERS']);
		}
		
		$index=array_search($GLOBALS['MG']['POST']['AD_DOMAIN'],$domains);
		if($index===false){
			trigger_error('(ADAUTH): Bad Domain Selected',E_USER_ERROR);
			return false;
		}
		$options=array('base_dn'=>$GLOBALS['MG']['SITE']['AD_BASE_DN'][$index],'account_suffix'=>'@'.$domains[$index],'domain_controllers'=>array($GLOBALS['MG']['SITE']['AD_DOMAIN_CONTROLLERS'][$index]));
		
		switch($GLOBALS['MG']['SITE']['AD_TLS_SSL']){
			case 'tls':
				$options['use_tls']=true;
			break;
			case 'ssl':
				$options['use_ssl']=true;
			break;
			default:
			
			break;
		}
		
		try {
			$this->ad=new adLDAP($options);
		}
		catch (adLDAPException $e) {
			trigger_error('(ADAUTH): Could not start adLDAP class: '.$e,E_USER_ERROR);
			return false;
		}
	}
	
	final public function auth_authenticate($username,$password){
		return $this->ad->authenticate($username,$password);
	}
	
	final public function auth_supported(){
		return array('change_pass'=>false);
	}	
	
	final public function auth_changePass($uid,$newPass,$encoding='md5'){
		return false;
	}

}