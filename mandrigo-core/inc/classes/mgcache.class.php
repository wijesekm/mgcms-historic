<?php

/**
 * @file                mgcache.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2008
 * @edited              9-29-2008
 
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

class mgcache{
	
	private $cache_base;
	
	public function __construct(){
		$this->cache_base=$GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/cache/';
	}
	
	public function mgc_readcache($site_tpl_up){
		$cache='';
		$new_path=$this->cache_base.implode('/',explode($GLOBALS['MG']['SITE']['URL_DELIM'],$GLOBALS['MG']['PAGE']['PATH']));
		$new_path.='.'.$GLOBALS['MG']['USER']['UID'].'.'.$this->mgc_varsIntoName().'cache';
		if(is_file($new_path)){
			$ftime=filemtime($new_path);
			if($ftime < $GLOBALS['MG']['PAGE']['MODIFIED'] || $ftime < $site_tpl_up){
				return false;
			}
			if($f=fopen($new_path,'r')){
				while(!feof($f)){
					$cache.=fgets($f);
				}
				fclose($f);
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
		return $cache;
	}
	
	public function mgc_cache($content){
		$new_path=explode($GLOBALS['MG']['SITE']['URL_DELIM'],$GLOBALS['MG']['PAGE']['PATH']);
		$page_name=$new_path[count($new_path)-1];
		$new_path[count($new_path)-1]='';
		$new_path=implode('/',$new_path);
		if(!$this->mgc_mkdirrec($new_path)){
			return false;
		}
		$new_path=$this->cache_base.$new_path;
		$new_path.=$page_name.'.'.$GLOBALS['MG']['USER']['UID'].'.'.$this->mgc_varsIntoName().'cache';
		if($f=fopen($new_path,'w')){
			fwrite($f,$content);
			fclose($f);
		}
		else{
			trigger_error('(MGCACHE): Could not open cache file: '.$new_path,E_USER_ERROR);
			return false;
		}
		return true;
	}
	
	private function mgc_mkdirrec($new_path,$permissions=0775){
		$new_path=explode('/',$new_path);
		$base=$this->cache_base;
		foreach($new_path as $item){
			$base.='/'.$item;
			if(!is_dir($base)&&!is_file($base)){
				if(!mkdir($base,$permissions)){
					trigger_error('(MGCACHE): Could not create new directory: '.$base,E_USER_ERROR);
					return false;
				}
			}
		}
		return true;
	}
	
	private function mgc_varsIntoName(){
		$soq=count($GLOBALS['MG']['GET']);
		$keys=array_keys($GLOBALS['MG']['GET']);
		$str='';
		for($i=0;$i<$soq;$i++){
			if(in_array($keys[$i],$GLOBALS['MG']['CACHE']['USEINCACHE'])&&$GLOBALS['MG']['GET'][$keys[$i]]&&$GLOBALS['MG']['GET'][$keys[$i]]!='0'){
				$str=$keys[$i].$GLOBALS['MG']['GET'][$keys[$i]];
			}
		}
		if($str){
			$str.='.';
		}
		return $str;
	}
}
