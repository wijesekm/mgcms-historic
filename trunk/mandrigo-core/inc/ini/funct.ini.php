<?php

/**
 * @file                funct.ini.php
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



function mg_checkACL($page,$acl){
	if(isset($GLOBALS['MG']['USER']['ACL'][$page])){
		if(isset($GLOBALS['MG']['USER']['ACL'][$page][$acl])){
			if($GLOBALS['MG']['USER']['ACL'][$page][$acl]=='deny'){
				return false;
			}
			else if((boolean)$GLOBALS['MG']['USER']['ACL'][$page][$acl]===true){
				return true;
			}
		}
	}
	if(isset($GLOBALS['MG']['USER']['ACL']['*'])){
		if((boolean)$GLOBALS['MG']['USER']['ACL']['*'][$acl]===true){
			return true;
		}		
	}
	return false;
}

function mg_mergeArrays($ar1,$ar2){
	$keys=array_keys($ar2);
	$soq=count($ar2);
	for($i=0;$i<$soq;$i++){
		$ar1[$keys[$i]]=$ar2[$keys[$i]];
	}
	return $ar1;
}

function mg_genUrl($urlParts,$base=false,$ssl=false){
	if($GLOBALS['MG']['SITE']['URI_SSL']=='always'||($GLOBALS['MG']['SITE']['URI_SSL']=='allow'&&$ssl)){
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
				if($urlParts+2<$soq){
					$url.='$amp;';
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
			header('Location: https://'.$_SERVER['SERVER_NAME']);
			die();
		}
		else{
			header('Location: http://'.$_SERVER['SERVER_NAME']);
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
		$url='https://'.$_SERVER['SERVER_NAME'].'/'.$_SERVER['REQUEST_URI'];
	}
	else{
		$url='http://'.$_SERVER['SERVER_NAME'].'/'.$_SERVER['REQUEST_URI'];
	}
	header('Location: '.$GLOBALS['MG']['SITE']['LOGIN_URL'].base64_encode($url));
	die();
}

function mg_mkdir($path,$sep='/',$rights = 0775) {
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
		$base=array('p',$GLOBALS['MG']['PAGE']['PATH']);	
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
		
	$tpl->tpl_load($GLOBALS['MG']['PAGE']['TPL'],'mgnb_subnav');
	$tpl->tpl_parse(array('BACK'=>$back,'NEXT'=>$next,'BACK_URL'=>$urlb,'NEXT_URL'=>$urln),'mgnb_subnav');
	$nstr=$tpl->tpl_return('mgnb_subnav');
		
	$tpl->tpl_load($GLOBALS['MG']['PAGE']['TPL'],'mgnb_navbar');
	$tpl->tpl_parse(array('NAV_PAGES'=>$pstr,'NAV_SUB'=>$nstr),'mgnb_navbar');
	$ret=$tpl->tpl_return('mgnb_navbar');
	$tpl=false;
	return $ret;
}

function mg_updateSiteTime($timestamp){
	if(!$GLOBALS['MG']['SQL']->sql_dataCommands(DB_UPDATE,array(TABLE_PREFIX.'pages'),array(array(false,false,'page_path','=',$GLOBALS['MG']['PAGE']['PATH'])),array(array('page_modified',$timestamp)))){
		trigger_error('(MGFUNCT): Could not updated page timestamp.',E_USER_WARNING);
		return false;
	}
	return true;
}