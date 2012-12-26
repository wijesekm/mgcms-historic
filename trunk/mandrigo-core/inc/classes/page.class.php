<?php

/**
 * @file		page.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		8-24-2009
 
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
			'USER_NAME'=>preg_replace('/  /',' ',implode(' ',$GLOBALS['MG']['USER']['NAME'])),
			'USER_EMAIL'=>$GLOBALS['MG']['USER']['EMAIL'],
			'USER_BANNED'=>$GLOBALS['MG']['USER']['BANNED'],
			'USER_TZ'=>$GLOBALS['MG']['USER']['TZ'],
			'USER_NOAUTH'=>$GLOBALS['MG']['USER']['NOAUTH'],
			'USER_TIME'=>date($GLOBALS['MG']['SITE']['TIME_FORMAT'],$GLOBALS['MG']['USER']['TIME']),
			'USER_DATE'=>date($GLOBALS['MG']['SITE']['DATE_FORMAT'],$GLOBALS['MG']['USER']['TIME']),
            'USER_GROUPS'=>implode(';',$GLOBALS['MG']['USER']['GROUPS']),
			'ACL_ADMIN'=>(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'admin'))?'1':'0',
			'ACL_MODIFY'=>(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'modify'))?'1':'0',
			'ACL_WRITE'=>(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'write'))?'1':'0',
			'ACL_READ'=>(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'read'))?'1':'0'
		);
        
        if(!empty($GLOBALS['MG']['PAGE']['PATH'])){

            $GLOBALS['MG']['PAGE']['VARS'] = array_merge_recursive($GLOBALS['MG']['PAGE']['VARS'],
                    array(			
                        'PAGE_PATH'=>$GLOBALS['MG']['PAGE']['PATH'],
            			'PAGE_PATH_BASE'=>$base,
            			'PAGE_NAME'=>$GLOBALS['MG']['PAGE']['NAME'],
            			'PAGE_NAME_SIMPLE'=>$GLOBALS['MG']['PAGE']['SIMPLENAME'],
            			'PAGE_CREATOR'=>$GLOBALS['MG']['PAGE']['CREATEDBY'],
            			'PAGE_MODIFIER'=>$GLOBALS['MG']['PAGE']['MODIFIEDBY'],
            			'PAGE_CREATED_DATE'=>date($GLOBALS['MG']['SITE']['DATE_FORMAT'],$GLOBALS['MG']['PAGE']['CREATED']),
            			'PAGE_MODIFIED_DATE'=>date($GLOBALS['MG']['SITE']['DATE_FORMAT'],$GLOBALS['MG']['PAGE']['MODIFIED']),
            			'PAGE_ROOT'=>$GLOBALS['MG']['PAGE']['ROOT'])
                    );
            
        }
        $GLOBALS['MG']['SITE']['TPL'] = 'site.tpl';
		$this->content='';
		$this->error=false;
	}
	
	public function page_generate(){
		$cache=false;
		$gdd=$GLOBALS['MG']['SQL']->sql_fetchArray(array(TABLE_PREFIX.'pages'),false,array(array(false,false,'page_path','=','*')));
		mginit_loadCustomPackages(explode(';',$gdd[0]['page_packages']));		
		/**
		 * Load cache if it is enabled and the package says its ok to use it
		 */
		if($GLOBALS['MG']['PAGE']['ALLOWCACHE']=='1'&&!$GLOBALS['MG']['GET']['FLUSHCACHE']){
			$this->page_getModified();
			$globalCacheHooks=explode(';',$gdd[0]['page_cachehooks']);
			$soq=count($globalCacheHooks);
			for($i=0;$i<$soq;$i++){
				$this->page_hookEval($globalCacheHooks[$i]);
			}
			if(!$GLOBALS['MG']['CFG']['STOPCACHE']){
				$cache=new mgcache();  
				$content=$cache->mgc_readcache(filemtime($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/'.page::PAGE_TPL_NAME));
				if($content!=false){
					return $content;
				}				
			}
		}
		$GLOBALS['MG']['PAGE']['NOSITETPL']=false;
		$GLOBALS['MG']['PAGE']['STOPCUSTOMPARSERS']=false;
		$GLOBALS['MG']['PAGE']['NOERRORPARSE']=false;
        $GLOBALS['MG']['PAGE']['STOPPARSERS'] = false;
        
		$tpl=new template();
		if(!$tpl->tpl_load($GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/'.$GLOBALS['MG']['SITE']['TPL'],'main')){
			trigger_error('(PAGE): Could not load site template',E_USER_ERROR);
			return false;
		}

		$this->page_getContent();
		
		/**
		* Get Global Page Vars/Cache Info
		*/

		$globalPageVars=explode(';',$gdd[0]['page_contenthooks']);
		$soq=count($globalPageVars);
		for($i=0;$i<$soq;$i++){
            if(!$GLOBALS['MG']['PAGE']['STOPPARSERS']){
    			$t=$this->page_hookEval($globalPageVars[$i]);
    			if(!$this->page_error($t)){
    				break 1;
    			}
            }
		}

        $GLOBALS['MG']['ERROR']['LOGGER']->el_setErrorVar();

		/**
		 * Set Page
		 */ 
		if(!$GLOBALS['MG']['PAGE']['NOSITETPL']){
			
			$GLOBALS['MG']['PAGE']['VARS']['CONTENT']=$this->content;
			$tpl->tpl_parse($GLOBALS['MG']['PAGE']['VARS'],'main',2,false);
			if(!$GLOBALS['MG']['PAGE']['STOPCUSTOMPARSERS'] && isset($gdd[0]['page_customHooks'])){
				$tpl->tpl_parseCustom($gdd[0]['page_customHooks'],'main');
			}
			$tpl->tpl_parse($GLOBALS['MG']['PAGE']['VARS'],'main',2,true);
			if($GLOBALS['MG']['PAGE']['ALLOWCACHE']=='1'&&!$GLOBALS['MG']['CFG']['STOPCACHE']){
				if(!is_object($cache)){
					$cache=new mgcache();
				}
				$cache->mgc_cache($tpl->tpl_return('main'));
			}	
			return $tpl->tpl_return('main');
		}
		else{
			if($GLOBALS['MG']['PAGE']['ALLOWCACHE']=='1'&&!$GLOBALS['MG']['CFG']['STOPCACHE']){
				if(!is_object($cache)){
					$cache=new mgcache();
				}
				$cache->mgc_cache($this->content);
			}
			return $this->content;
		}
	}

	private function page_getModified(){
		$GLOBALS['MG']['PAGE']['CACHEHOOKS']=explode(';',$GLOBALS['MG']['PAGE']['CACHEHOOKS']);
		$soq=count($GLOBALS['MG']['PAGE']['CACHEHOOKS']);
		for($i=0;$i<$soq;$i++){
			$tmp=$this->page_hookEval($GLOBALS['MG']['PAGE']['CACHEHOOKS'][$i]);
		}
	}
	
	private function page_getContent(){
		if(!$GLOBALS['MG']['PAGE']['CONTENTHOOKS']){
			$this->page_error('404');
			trigger_error('(PAGE): No content hooks.',E_USER_WARNING);
			return false;
		}
		$soq=count($GLOBALS['MG']['PAGE']['CONTENTHOOKS']);
		for($i=0;$i<$soq;$i++){
			if($GLOBALS['MG']['PAGE']['CONTENTHOOKS'][$i]){
				$tmp=$this->page_hookEval($GLOBALS['MG']['PAGE']['CONTENTHOOKS'][$i]);
				$tmp=$this->page_error($tmp);
				if($tmp){
					$this->content.=$tmp;
				}
				else{
					trigger_error('(PAGE): No content or error during hook evaluation. ('.$GLOBALS['MG']['PAGE']['CONTENTHOOKS'][$i].')',E_USER_NOTICE);
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
		switch((int)$content){
			case 500:
				$GLOBALS['MG']['PAGE']['VARS']['NO']='no';
				$GLOBALS['MG']['CFG']['STOPCACHE']=true;
				$this->content=$GLOBALS['MG']['LANG']['E500_CONTENT'];
				$GLOBALS['MG']['PAGE']['VARS']['TITLE']=$GLOBALS['MG']['LANG']['E500_TITLE'];
                $GLOBALS['MG']['PAGE']['VARS']['PAGE_NAME_SIMPLE']=$GLOBALS['MG']['LANG']['E500_TITLE'];
				$this->error=true;
				return false;			
			break;
			case 404:
				$GLOBALS['MG']['PAGE']['VARS']['NO']='no';
				$GLOBALS['MG']['CFG']['STOPCACHE']=true;
				$this->content=$GLOBALS['MG']['LANG']['E404_CONTENT'];
				$GLOBALS['MG']['PAGE']['VARS']['TITLE']=$GLOBALS['MG']['LANG']['E404_TITLE'];
                $GLOBALS['MG']['PAGE']['VARS']['PAGE_NAME_SIMPLE']=$GLOBALS['MG']['LANG']['E404_TITLE'];
				$this->error=true;
				return false;
			break;
			case 403:
				$GLOBALS['MG']['PAGE']['VARS']['NO']='no';
				$GLOBALS['MG']['CFG']['STOPCACHE']=true;
				$this->content=$GLOBALS['MG']['LANG']['E403_CONTENT'];
				$GLOBALS['MG']['PAGE']['VARS']['TITLE']=$GLOBALS['MG']['LANG']['E403_TITLE'];
                $GLOBALS['MG']['PAGE']['VARS']['PAGE_NAME_SIMPLE']=$GLOBALS['MG']['LANG']['E403_TITLE'];
				$this->error=true;
				return false;			
			break;
			case 401:
				$GLOBALS['MG']['PAGE']['VARS']['NO']='no';
				$GLOBALS['MG']['CFG']['STOPCACHE']=true;
				$this->content=$GLOBALS['MG']['LANG']['E401_CONTENT'];
				$GLOBALS['MG']['PAGE']['VARS']['TITLE']=$GLOBALS['MG']['LANG']['E401_TITLE'];
                $GLOBALS['MG']['PAGE']['VARS']['PAGE_NAME_SIMPLE']=$GLOBALS['MG']['LANG']['E401_TITLE'];
				$this->error=true;
				return false;			
			break;
			default:
				return $content;
			break;
		};
	}

}