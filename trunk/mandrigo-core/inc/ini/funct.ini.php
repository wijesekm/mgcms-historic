<?php

/**
 * @file                funct.ini.php
 * @author              Kevin Wijesekera
 * @copyright   		2008
 * @edited              8-24-2009
 
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

function mg_checkACL($page,$acl='read'){
	if(!$page){
		$page=$GLOBALS['MG']['PAGE']['PATH'];

	}
	$ret=false;
	$deny=false;
	$adm=false;
	foreach($GLOBALS['MG']['USER']['ACL'] as $key=>$val){
		if($key==='*'){
			if((boolean)$val[$acl]===true){
				$ret=true;
			}
			if((boolean)$val['admin']===true){
				$adm=true;
			}
		}
		else if($key===$page){
			if((string)$val[$acl]==='deny'){
				$deny=true;
			}
			if((boolean)$val[$acl]===true){
				$ret=true;
			}
			if((boolean)$val['admin']===true){
				$adm=true;
			}
		}
		else if(preg_match('/\*/',$key)){
			$search='/^'.substr($key,0,-1).'/';
			if(preg_match($search,$page)){
				if((string)$val[$acl]==='deny'){
					$deny=true;
				}
				if((boolean)$val[$acl]===true){
					$ret=true;
				}
				if((boolean)$val['admin']===true){
					$adm=true;
				}

			}
		}
	}
	if($adm===true){
		return true;
	}
	else if($deny===true){
		return false;
	}
	return $ret;
}

function mg_mergeArrays($ar1,$ar2){
	$keys=array_keys($ar2);
	$soq=count($ar2);
	for($i=0;$i<$soq;$i++){
		$ar1[$keys[$i]]=$ar2[$keys[$i]];
	}
	return $ar1;
}

function mg_genUrl($urlParts,$base=false,$ssl='auto'){
	if($ssl=='auto'){
		if(isset($_SERVER['HTTPS'])){
			if($_SERVER['HTTPS']=='off'){
				$ssl=false;
			}
			else{
				$ssl=true;
			}
		}
		else{
			$ssl=false;
		}		
	}
	else if($ssl=='false'){
		$ssl=false;
	}
	else if($ssl == 'true'){
		$ssl=true;
	}

	if($ssl){
		$url='https://';
	}
	else{
		$url='http://';
	}
	if(!$base){
		$url.=$GLOBALS['MG']['SITE']['URI'];
	}
	else{
		$url.=$base;
	}
	$url.='/';
	
	switch($GLOBALS['MG']['SITE']['URLTYPE']){
		case 3:
			$url.=implode('/',$urlParts);
		break;
		case 2:
			$url.=$GLOBALS['MG']['SITE']['INDEX_NAME'].'/'.implode('/',$urlParts);
		break;
		case 1:
			$url.='?';
			$soq=count($urlParts);
			for($i=0;$i<$soq;$i+=2){
				$url.=$urlParts[$i].'='.$urlParts[$i+1];
				if($i+2<$soq){
					$url.='&';
				}
			}
		break;
	};
	return $url;
}

function mg_redirectTarget($target){
	if(!$target){
		trigger_error('(FUNCT): Invalid Redirect Target',E_USER_WARNING);
		if(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!='off'){
			$port=($_SERVER['SERVER_PORT']!='443')?':'.$_SERVER['SERVER_PORT']:'';
			header('Location: https://'.$_SERVER['SERVER_NAME'].$port);
			die();
		}
		else{
			$port=($_SERVER['SERVER_PORT']!='80')?':'.$_SERVER['SERVER_PORT']:'';
			header('Location: http://'.$_SERVER['SERVER_NAME'].$port);
			die();
		}
	}
	header('Location: '.$target);
	die();
}

function mg_redirectToLogin(){
	if(!$GLOBALS['MG']['SITE']['LOGIN_URL']){
		return false;
	}
	if(isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!='off'){
		$url='https://'.$_SERVER['SERVER_NAME'];
		if($_SERVER['SERVER_PORT']!='443'){
			$url.=':'.$_SERVER['SERVER_PORT'];
		}
	}
	else{
		$url='http://'.$_SERVER['SERVER_NAME'];
		if($_SERVER['SERVER_PORT']!='80'){
			$url.=':'.$_SERVER['SERVER_PORT'];
		}
	}

	if($_SERVER['REQUEST_URI']){
		$url.='/'.$_SERVER['REQUEST_URI'];
	}
	mg_redirectTarget($GLOBALS['MG']['SITE']['LOGIN_URL'].base64_encode($url));
}

function mg_mkdir($path,$sep='/',$rights = 0775 ) {
	$path=ereg_replace('//','/',$path);
    $dirs = explode($sep , $path);
    $count = count($dirs);
    $path = '';
    for ($i = 0; $i < $count; $i++) {
    	$path .= $sep . $dirs[$i];
    	if(!is_dir($path)){
			if(!mkdir($path,$rights)){
	        	trigger_error('(MKDIR): Could not create directory: '.$path,E_USER_WARNING);
	            return false;				
			}
		}
    }
    return true;
}

function mg_checkPackageMod($package){
	$dir=$GLOBALS['MG']['CFG']['PATH']['PKG'].'/'.$package.'/';
	if(!is_dir($dir)){
		return false;
	}
	$f=scandir($dir);
	if(!$f){
		return false;
	}
	$last_mod_time=0;
	foreach($f as $file){	
		if(substr($file,0,1)!='.'&&$file!='manifest.php'){
			$t=filemtime($dir.$file);
			if($t > $last_mod_time){
				$last_mod_time=$t;
			}
		}
	}
	return $last_mod_time;
}

function mg_rmdir($path,$sep='/'){
 	if(!is_dir($path)){
		return true;
	}
	$items=scandir($path);
	$soq=count($items);
	for($i=0;$i<$soq;$i++){
	 	if($items[$i]!='.'&&$items[$i]!='..'){
		 	$tmp=$path.$sep.$items[$i];
			if(is_file($tmp)){
				if(!unlink($tmp)){
					return false;
				}
			}
			else if(is_dir($tmp)){
				mg_rmdir($tmp,$sep);
			}		
		}
	}
	if(!rmdir($path)){
		return false;
	}
	return true;
}
function mg_navBar($length,$ppp,$base=false){
	if(!$base){
		$base=array('p',$GLOBALS['MG']['GET']['PAGE']);
	}
	if($length==0){
		return false;
	}
	$pages=ceil($length/$ppp);
	if($pages <= 1){
		return false;
	}
	$tpl=new template();
	$pstr='';
	for($i=0;$i<$pages;$i++){
		$tpl->tpl_load($GLOBALS['MG']['PAGE']['TPL'],'mgnb_pdelim');
		$url=mg_genUrl(array_merge($base,array('pn',(string)$i)));
		$tpl->tpl_parse(array('URL'=>$url,'PG'=>(string)($i+1),'INDEX'=>(string)$i,'LENGTH'=>(string)$pages),'mgnb_pdelim');
		$pstr.=$tpl->tpl_return('mgnb_pdelim');
	}

	$back = $GLOBALS['MG']['GET']['PAGE_NUMBER']-1;
	$next = $GLOBALS['MG']['GET']['PAGE_NUMBER']+1;
	$urlb=false;
	$urln=false;
	if($back >= 0){
		$urlb=mg_genUrl(array_merge($base,array('pn',(string)$back)));
		$back="true";
	}
	if($next < $pages){
		$urln=mg_genUrl(array_merge($base,array('pn',(string)$next)));
		$next="true";
	}
		
	$tpl->tpl_load($GLOBALS['MG']['PAGE']['TPL'],'mgnb_navbar');
	$tpl->tpl_parse(array('NAV_PAGES'=>$pstr,'BACK'=>$back,'NEXT'=>$next,'BACK_URL'=>$urlb,'NEXT_URL'=>$urln),'mgnb_navbar');
	$ret=$tpl->tpl_return('mgnb_navbar');
	$tpl=false;
	return $ret;
}

function mg_updateSiteTime($page,$timestamp,$user){
	$conds=array(array(false,false,'page_path','=',$page));
	$up=array(array('page_modified',$timestamp),array('page_modifiedby',$user));
	if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,array(TABLE_PREFIX.'pages'),$conds,$up)){
		trigger_error('(MGFUNCT): Could not updated page timestamp.',E_USER_WARNING);
		return false;
	}
	return true;
}
