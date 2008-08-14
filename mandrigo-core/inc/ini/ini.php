<?php

/**
 * @file		ini.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		6-8-2008
 
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

/**
* Start Error Logger
*/
if(!include_once($GLOBALS['MG']['CFG']['PATH']['INC'].'classes/errorLogger.class'.PHPEXT)){
	die('Could not start Error Logging!');
}
$GLOBALS['MG']['ERROR']['LOGGER']=new errorLogger();
set_error_handler('mginit_errorHandler');

/**
* Load some initial packages
*/
$load=array(array('template','class','/classes/'),
			array('sql','abstract','/classes/sql/'),
			array($GLOBALS['MG']['CFG']['SQL']['METHOD'],'class','/classes/sql/'),
			array('session','class','/classes/auth/'),
			array('time','class','/classes/'),
			array('clean','ini','/ini/'),
			array('page','class','/classes/'));
mginit_loadPackage($load);


eval('$GLOBALS[\'MG\'][\'SQL\']=new '.$GLOBALS['MG']['CFG']['SQL']['METHOD'].'();');

$GLOBALS['MG']['SQL']->sql_connect($GLOBALS['MG']['CFG']['SQL']['HOST'],$GLOBALS['MG']['CFG']['SQL']['PORT_SOCKET']
								  ,$GLOBALS['MG']['CFG']['SQL']['USERNAME'],$GLOBALS['MG']['CFG']['SQL']['PASSWORD']
								  ,$GLOBALS['MG']['CFG']['SQL']['DB'],true,$GLOBALS['MG']['CFG']['SQL']['SSL']);

/**
* Load Site Data
*/
$tmp=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'config'),false,false);
for($i=0;$i<$tmp['count'];$i++){
	$GLOBALS['MG']['SITE'][(string)$tmp[$i]['cfg_var']]=$tmp[$i]['cfg_data'];
}

/**
* Load some more packages
*/
$load=array(array('bvars','ini','/ini/'),
			array('accounts','abstract','/classes/accounts/'),
			array($GLOBALS['MG']['SITE']['ACCOUNT_TYPE'],'class','/classes/accounts/'));
mginit_loadPackage($load);

/**
* User Data
*/				
$ses=new session(0);
eval('$act=new '.$GLOBALS['MG']['SITE']['ACCOUNT_TYPE'].'();');

if(!$ses->session_load($GLOBALS['MG']['COOKIE']['USER_NAME'],$GLOBALS['MG']['COOKIE']['USER_SESSION'])){
	$GLOBALS['MG']['USER']=$act->act_load($GLOBALS['MG']['SITE']['DEFAULT_ACT']);
	$GLOBALS['MG']['USER']['NOAUTH']=true;
}
else{
	$GLOBALS['MG']['USER']=$act->act_load($GLOBALS['MG']['COOKIE']['USER_NAME']);
	$GLOBALS['MG']['USER']['NOAUTH']=false;
}

/**
* Time Data
*/
$t=new time($GLOBALS['MG']['SITE']['TZ'],$GLOBALS['MG']['USER']['TZ']);
$GLOBALS['MG']['SITE']['TIME']=$t->time_server();
$GLOBALS['MG']['USER']['TIME']=$t->time_client();
$t=false;

/**
* Update Cookies
*/
$cdta=array(
	'SECURE'=>(boolean)$GLOBALS['MG']['SITE']['COOKIE_SECURE'],
	'PATH'=>$GLOBALS['MG']['SITE']['COOKIE_PATH'],
	'DOM'=>$GLOBALS['MG']['SITE']['COOKIE_DOM'],
);
if($GLOBALS['MG']['USER']['BANNED']){
	$ses->session_stop($cdta);
	$GLOBALS['MG']['USER']=$act->act_load($GLOBALS['MG']['SITE']['DEFAULT_ACT']);
	$GLOBALS['MG']['USER']['NOAUTH']=true;
}
else{
	$ses->session_loadUD($GLOBALS['MG']['USER']['TIME'],$cdta);
}
$ses=false;
$act=false;
/**
* Load Page Data
*/
$tmp=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'pages'),false,array(array(false,false,'page_path','=',strtolower($GLOBALS['MG']['GET']['PAGE']))));
$keys=array_keys($tmp[0]);
$soq=count($keys);
for($i=0;$i<$soq;$i++){
	$key=strtoupper(ereg_replace('page_','',$keys[$i]));
	switch($key){
		case 'PACKAGES':
		case 'CONTENTHOOKS':
		case 'VARHOOKS':
			$GLOBALS['MG']['PAGE'][$key]=explode(';',$tmp[0][$keys[$i]]);
		break;
		default:
			$GLOBALS['MG']['PAGE'][$key]=$tmp[0][$keys[$i]];	
		break;
	}
}
$tmp=false;
$keys=false;

/**
* Load Language Data
*/
if($GLOBALS['MG']['SITE']['LANG_ALLOW_OVERRIDE']=='true'&&$GLOBALS['MG']['USER']['LANG']){
	$lang=$GLOBALS['MG']['USER']['LANG'];
}
else{
	$lang=$GLOBALS['MG']['SITE']['DEFAULT_LANGUAGE'];
}
$lang=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'langsets'),false,array(array(false,false,'lang_name','=',strtolower($lang))));
$GLOBALS['MG']['LANG']=mginit_loadLang($lang[0]['lang_id']);
$GLOBALS['MG']['LANG']['ENCODING']=$lang[0]['lang_encoding'];
$GLOBALS['MG']['LANG']['NAME']=$lang[0]['lang_name'];
$GLOBALS['MG']['LANG']['CONTENT_TYPE']='text/html';
$lang=false;

/**
* Load Packages
*/
mginit_loadCustomPackages($GLOBALS['MG']['PAGE']['PACKAGES']);

/**
* MISC Functions
*/
function mginit_errorHandler($errno, $errmsg, $filename, $linenum, $vars){
	$GLOBALS['MG']['ERROR']['LOGGER']->el_addError($errno, $errmsg, $filename, $linenum, $vars);
}

function mginit_loadLang($lang_id){
	$langs=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'lang'),false,array(array(false,false,'lang_id','=',$lang_id)));
	$ret=array();
	for($i=0;$i<$langs['count'];$i++){
		$ret[(string)$langs[$i]['lang_callname']]=(string)$langs[$i]['lang_value'];
	}
	return $ret;
}

function mginit_loadPackage($data){
	$soq=count($data);
	for($i=0;$i<$soq;$i++){
		$path=$data[$i][2].$data[$i][0].'.'.$data[$i][1].PHPEXT;
		if(!include_once($GLOBALS['MG']['CFG']['PATH']['INC'].$path)){
			trigger_error('(WLINIT): Could not load package: '.$data[$i][0].'.'.$data[$i][1].PHPEXT,E_USER_ERROR);
		}
	}
}

function mginit_loadCustomPackages($pkgs){
	$soq=count($pkgs);
	for($i=0;$i<$soq;$i++){
		if($pkgs[$i]){
			$pkgdta=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'packages'),false,array(array(false,false,'pkg_name','=',strtolower($pkgs[$i]))));
			if($pkgdta[0]['pkg_deps']){
				mginit_loadCustomPackages(explode(';',$pkgdta[0]['pkg_deps']));
			}
			if($pkgdta[0]['pkg_type']=='pkg'){
				if(!include_once($GLOBALS['MG']['CFG']['PATH']['PKG'].$pkgs[$i].'.pkg'.PHPEXT)){
					trigger_error('(WLINIT): Could not load custom package: '.$pkgs[$i].'.pkg'.PHPEXT,E_USER_ERROR);
				}
			}
			else{
				if(!include_once($GLOBALS['MG']['CFG']['PATH']['INC'].$pkgdta[0]['pkg_mandrigoInc'].$pkgs[$i].'.'.$pkgdta[0]['pkg_type'].PHPEXT)){
					trigger_error('(WLINIT): Could not load custom package: '.$pkgs[$i].'.'.$pkgdta[0]['pkg_type'].PHPEXT,E_USER_ERROR);
				}
			}
		}
	}
}

function mg_checkACL($page,$acl){
	if(is_array($GLOBALS['MG']['USER']['ACL'][$page])){
		if($GLOBALS['MG']['USER']['ACL'][$page][$acl]===true){
			return true;
		}		
	}
	else{
		if($GLOBALS['MG']['USER']['ACL']['*'][$acl]===true){
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

function mg_formatTarget($url){
	$url=ereg_replace('\/','[SLASH]',$url);
	$url=ereg_replace('\?','[Q]',$url);
	$url=ereg_replace('\&amp;','[AND]',$url);
	$url=ereg_replace('\&','[AND]',$url);
	return $url;
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
	return $GLOBALS['MG']['SITE']['LOGIN_URL'].mg_formatTarget($url);
}