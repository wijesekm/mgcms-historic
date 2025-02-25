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
		if(!is_array($GLOBALS['MG']['SITE']['AD_DOMAINS'])){
			$GLOBALS['MG']['SITE']['AD_DOMAINS']=explode(';',$GLOBALS['MG']['SITE']['AD_DOMAINS']);
		}

	}

	final public function auth_authenticate($username,$password){
	   $ret = false;
        $size = count($GLOBALS['MG']['SITE']['AD_DOMAIN_CONTROLLERS']);
        for($i=0;$i <$size;$i++){
            if(!empty($GLOBALS['MG']['SITE']['AD_BASE_DN'][$i])){
      		    $options=array('base_dn'=>$GLOBALS['MG']['SITE']['AD_BASE_DN'][$i],'account_suffix'=>'@'.$GLOBALS['MG']['SITE']['AD_DOMAINS'][$i],'domain_controllers'=>array($GLOBALS['MG']['SITE']['AD_DOMAIN_CONTROLLERS'][$i]));
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
                $ret = $this->ad->authenticate($username,$password);
                if($ret){
                    $i = $size+1;
                }
            }
        }
		return $ret;
	}

	final public function auth_canChangePass(){
	    return false;
	}

	final public function auth_changePass($uid,$newPass){
		return false;
	}

	final public function auth_getAutoReg($uid,$password){

	}

}