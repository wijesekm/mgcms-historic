<?php

/**
 * @file		ajax.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2012
 * @edited		10-8-2012
 
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

class page{
	
	private $content;
	private $error;
	private $obj;

	public function __construct(){
		if(preg_match('/'.preg_quote($GLOBALS['MG']['SITE']['URL_DELIM'],'/').'/',$GLOBALS['MG']['PAGE']['PATH'])){
			$base=explode($GLOBALS['MG']['SITE']['URL_DELIM'],$GLOBALS['MG']['PAGE']['PATH']);
			$base=$base[0];
		}
		else{
			$base=$GLOBALS['MG']['PAGE']['PATH'];
		}
		$GLOBALS['MG']['PAGE']['VARS']['NO']='';
		$GLOBALS['MG']['PAGE']['VARS']=array(
			'URI'=>$GLOBALS['MG']['SITE']['URI'],
			'SERVER_NAME'=>$_SERVER['SERVER_NAME'],
			'SSL'=>(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!='off')?'1':'0',
			'REQUEST_URI'=>$_SERVER['REQUEST_URI'],
			'SERVER_SOFTWARE'=>$_SERVER['SERVER_SOFTWARE'],
			'SERVER_NAME'=>$_SERVER['SERVER_NAME'],
			'SERVER_SIGNATURE'=>$_SERVER['SERVER_SIGNATURE'],
			'DEFAULT_ACT'=>$GLOBALS['MG']['SITE']['DEFAULT_ACT'],
			'SERVER_TZ'=>$GLOBALS['MG']['SITE']['TZ'],
			'URLTYPE'=>$GLOBALS['MG']['SITE']['URLTYPE'],
			'INDEX_NAME'=>$GLOBALS['MG']['SITE']['INDEX_NAME'],
			'LANGUAGE'=>$GLOBALS['MG']['LANG']['NAME'],
			'SERVER_TIME'=>date($GLOBALS['MG']['SITE']['TIME_FORMAT'],$GLOBALS['MG']['SITE']['TIME']),
			'SERVER_DATE'=>date($GLOBALS['MG']['SITE']['DATE_FORMAT'],$GLOBALS['MG']['SITE']['TIME']),
			'COPYRIGHT_YEAR'=>date('o',$GLOBALS['MG']['SITE']['TIME']),
			'USER_UID'=>$GLOBALS['MG']['USER']['UID'],
			'USER_SESSION'=>$GLOBALS['MG']['COOKIE']['USER_SESSION'],
			'USER_NAME'=>implode(' ',$GLOBALS['MG']['USER']['NAME']),
			'USER_EMAIL'=>$GLOBALS['MG']['USER']['EMAIL'],
			'USER_BANNED'=>$GLOBALS['MG']['USER']['BANNED'],
			'USER_TZ'=>$GLOBALS['MG']['USER']['TZ'],
			'USER_NOAUTH'=>$GLOBALS['MG']['USER']['NOAUTH'],
			'USER_TIME'=>date($GLOBALS['MG']['SITE']['TIME_FORMAT'],$GLOBALS['MG']['USER']['TIME']),
			'USER_DATE'=>date($GLOBALS['MG']['SITE']['DATE_FORMAT'],$GLOBALS['MG']['USER']['TIME']),
            'USER_GROUPS'=>implode(';',$GLOBALS['MG']['USER']['GROUPS']),
			'PAGE_PATH'=>$GLOBALS['MG']['PAGE']['PATH'],
			'PAGE_PATH_BASE'=>$base,
			'PAGE_NAME'=>$GLOBALS['MG']['PAGE']['NAME'],
			'PAGE_NAME_SIMPLE'=>$GLOBALS['MG']['PAGE']['SIMPLENAME'],
			'PAGE_CREATOR'=>$GLOBALS['MG']['PAGE']['CREATEDBY'],
			'PAGE_MODIFIER'=>$GLOBALS['MG']['PAGE']['MODIFIEDBY'],
			'PAGE_CREATED_DATE'=>date($GLOBALS['MG']['SITE']['DATE_FORMAT'],$GLOBALS['MG']['PAGE']['CREATED']),
			'PAGE_MODIFIED_DATE'=>date($GLOBALS['MG']['SITE']['DATE_FORMAT'],$GLOBALS['MG']['PAGE']['MODIFIED']),
			'PAGE_ROOT'=>$GLOBALS['MG']['PAGE']['ROOT'],
			'ACL_ADMIN'=>(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'admin'))?'1':'0',
			'ACL_MODIFY'=>(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'modify'))?'1':'0',
			'ACL_WRITE'=>(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'write'))?'1':'0',
			'ACL_READ'=>(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'read'))?'1':'0'
		);

		$this->content='';
		$this->error=false;
	}
	
	public function page_generate($v=false){
		$cache=false;
		$gdd=$GLOBALS['MG']['SQL']->sql_fetchArray(array(TABLE_PREFIX.'pages'),false,array(array(false,false,'page_path','=','*')));
		mginit_loadCustomPackages(explode(';',$gdd[0]['page_packages']));		


		$GLOBALS['MG']['PAGE']['STOPCUSTOMPARSERS']=false;
		$GLOBALS['MG']['PAGE']['NOERRORPARSE']=false;
        $GLOBALS['MG']['PAGE']['STOPPARSERS'] = false;
		$tpl=new template();

		$this->page_getContent($v);
		
		/**
		* Get Global Page Vars/Cache Info
		*/
		$globalPageVars=explode(';',$gdd[0][($v)?'page_extHooks':'page_ajaxHooks']);
		$soq=count($globalPageVars);
		for($i=0;$i<$soq;$i++){
            if(!$GLOBALS['MG']['PAGE']['STOPPARSERS']){
    			$t=$this->page_hookEval($globalPageVars[$i]);
    			if(!$this->page_error($t)){
    				break 1;
    			}
            }
		}
        return $this->content;
		
	}

	private function page_getContent($v){
        $hooks = $GLOBALS['MG']['PAGE']['AJAXHOOKS'];
        if($v){
            $hooks = $GLOBALS['MG']['PAGE']['EXTHOOKS'];
        }
		if(!$hooks){
			$this->page_error('404');
			trigger_error('(PAGE): No content hooks.',E_USER_WARNING);
			return false;
		}

		$soq=count($hooks);
		for($i=0;$i<$soq;$i++){
			if($hooks[$i]){
				$tmp=$this->page_hookEval($hooks[$i]);
				if($tmp){
				    $tmp=$this->page_error($tmp);
					$this->content.=$tmp;
				}
				else{
					trigger_error('(PAGE): No content or error during hook evaluation. ('.$hooks[$i].')',E_USER_NOTICE);
				}
			}
		}

	}

	private function page_hookEval($hook){
		if(!$hook){
			return false;
		}
		if(preg_match('/\:\:/',$hook)){
			
			$hook=explode('::',$hook);
			if(!isset($this->obj[$hook[0]])){
				$this->obj[$hook[0]]=true;
			}
			if(!is_object($this->obj[$hook[0]])){
				eval('$this->obj[\''.$hook[0].'\']=new '.$hook[0].'();');
				if(!is_object($this->obj[$hook[0]])){
					trigger_error('(PAGE): Could not create page class: '.$hook[0],E_USER_WARNING);
				}			
			}
			eval('$ret=$this->obj[\''.$hook[0].'\']->'.$hook[1].'();');
			if(!$ret){
				trigger_error('(PAGE): Could not evaluate hook: '.$hook[1],E_USER_WARNING);
			}
		}
		else{
			eval('$ret='.$hook.'();');
			if(!$ret){
				trigger_error('(PAGE): Could not evaluate hook: '.$hook,E_USER_WARNING);
			}
		}
		return $ret;
	}
	
	private function page_error($content){
		if($GLOBALS['MG']['PAGE']['NOERRORPARSE']){
			return $content;
		}
        if(preg_match('/:/',$content)){
            return $content;
        }
		switch((int)$content){
            case 200:
				$this->content='200: Success';
				$this->error=true;
				return false;	
            break;
			case 500:
				$this->content='500: Internal Server Error';
				$this->error=true;
				return false;			
			break;
			case 404:
				$this->content='404: Page Not Found';
				$this->error=true;
				return false;	
			break;
			case 403:
				$this->content='403: Authorization Required';
				$this->error=true;
				return false;			
			break;
			case 401:
				$this->content='401: Access Denied';
				$this->error=true;
				return false;		
			break;
			default:
				return $content;
			break;
		};
	}

}