<?php

/**
 * @file		ini.php
 * @author 		Kevin Wijesekera
 * @copyright 	2012
 * @edited		10-9-2012
 
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

date_default_timezone_set('America/New_York');//this is to prevent errors and will be reset later

/*
* Setup a few arrays to remove warnings
*/
$GLOBALS['MG']['HDR']=array();
$GLOBALS['MG']['PAGE']=array('PATH'=>'','PACKAGES'=>array(),'CONTENTHOOKS'=>array(),'ALLOWCACHE'=>'0');

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
			array('parser','class','/classes/parsers/'),
			array('mgcache','class','/classes/'),
			array('sql','abstract','/classes/sql/'),
			array($GLOBALS['MG']['CFG']['SQL']['METHOD'],'class','/classes/sql/'),
			array('session','class','/classes/auth/'),
			array('mgtime','class','/classes/'),
			array('clean','ini','/ini/'),
			array('funct','ini','/ini/'),
            array('ajax','class','/classes/'));
mginit_loadPackage($load);

/**
 * Path Creation
 */
if($GLOBALS['MG']['CFG']['PATH']['TMP']){
	if(!is_dir($GLOBALS['MG']['CFG']['PATH']['TMP'])){
		mkdir($GLOBALS['MG']['CFG']['PATH']['TMP'],0777,true);
	}
}

/**
 * Sql Database Init
 */

eval('$GLOBALS[\'MG\'][\'SQL\']=new '.$GLOBALS['MG']['CFG']['SQL']['METHOD'].'();');
if(!$GLOBALS['MG']['SQL']){
	trigger_error('(INI): Invalid SQL method or no method set!', E_USER_ERROR);
	die('500: Sql init');
}

$t=$GLOBALS['MG']['SQL']->sql_connect($GLOBALS['MG']['CFG']['SQL']['HOST'],$GLOBALS['MG']['CFG']['SQL']['PORT_SOCKET']
								  ,$GLOBALS['MG']['CFG']['SQL']['USERNAME'],$GLOBALS['MG']['CFG']['SQL']['PASSWORD']
								  ,$GLOBALS['MG']['CFG']['SQL']['DB'],$GLOBALS['MG']['CFG']['S<br />QL']['PERSISTENT'],$GLOBALS['MG']['CFG']['SQL']['SSL']);
if(!$t){
	trigger_error('(INI): Could not connect to SQL database!', E_USER_ERROR);
	die('500: Sql init');
}

/**
* Load Site Data
*/
$tmp=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'config'),false,false);
for($i=0;$i<$tmp['count'];$i++){
	$GLOBALS['MG']['SITE'][(string)$tmp[$i]['cfg_var']]=$tmp[$i]['cfg_data'];
}

/**
 * Mime Types
 */
$table = false;
$GLOBALS['MG']['MIME']=array();
if(isset($GLOBALS['MG']['SITE']['MIME_TABLE'])){
	$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['SITE']['ACCOUNT_DB']);
	$table = $GLOBALS['MG']['SITE']['MIME_TABLE'];
}
else{
	$table = TABLE_PREFIX.'mime_types';
}
$dta=$GLOBALS['MG']['SQL']->sql_fetchArray(array($table),false,false);

foreach($dta as $val){
	if($val['mime_ext']){
		$GLOBALS['MG']['MIME'][$val['mime_ext']]=array();
		$GLOBALS['MG']['MIME'][$val['mime_ext']]['img']=$val['mime_img'];
		$GLOBALS['MG']['MIME'][$val['mime_ext']]['type']=$val['mime_type'];
        //$GLOBALS['MG']['MIME'][$val['mime_ext']]['display']=($val['mime_canView']=='1')?true:false;
	}
}
if(isset($GLOBALS['MG']['SITE']['MIME_TABLE'])){
	$GLOBALS['MG']['SQL']->sql_switchDB($GLOBALS['MG']['CFG']['SQL']['DB']);
}

/**
* Load some more packages
*/
$load=array(array('bvars','ini','/ini/'),
			array('accounts','abstract','/classes/accounts/'),
			array('groups','class','/classes/accounts/'),
			array('acl','class','/classes/accounts/'),
			array($GLOBALS['MG']['SITE']['ACCOUNT_TYPE'],'class','/classes/accounts/'),
            array('auth','abstract','/classes/auth/'),
            array($GLOBALS['MG']['SITE']['EXT_AUTH'],'class','/classes/auth/'));
mginit_loadPackage($load);

/**
* User Data
*/
eval('@$act=new '.$GLOBALS['MG']['SITE']['ACCOUNT_TYPE'].'();');
eval('@$auth=new '.$GLOBALS['MG']['SITE']['EXT_AUTH'].'();');
if(!$act || !$auth){
	trigger_error('(INI): Invalid account type or not account type set!', E_USER_ERROR);
	die('500: Invalid account type');
}
if(!preg_match('/;/',$GLOBALS['MG']['POST']['EXT_AUTH'])){
	trigger_error('(INI): External Auth Not Set!', E_USER_ERROR);
	die('401: No auth token set'); 
}
$GLOBALS['MG']['POST']['EXT_AUTH'] = explode(';',$GLOBALS['MG']['POST']['EXT_AUTH']);

$GLOBALS['MG']['USER']=$act->act_load($GLOBALS['MG']['POST']['EXT_AUTH'][0]);
if(!isset($GLOBALS['MG']['USER'][$GLOBALS['MG']['POST']['EXT_AUTH'][0]])){
	trigger_error('(INI): External Auth Not Set!', E_USER_ERROR);
	die('401: Invalid Auth Token');   
}
$GLOBALS['MG']['USER']=$GLOBALS['MG']['USER'][$GLOBALS['MG']['POST']['EXT_AUTH'][0]];
$GLOBALS['MG']['USER']['NOAUTH']=false;

if(!$auth->auth_authenticate($GLOBALS['MG']['POST']['EXT_AUTH'][0],$GLOBALS['MG']['POST']['EXT_AUTH'][1])){
	trigger_error('(INI): External Auth Not Set!', E_USER_ERROR);
	die('401: Invalid Auth Token'); 
}



/**
* Time Data
*/
$t=new mgtime($GLOBALS['MG']['SITE']['TZ'],$GLOBALS['MG']['USER']['TZ']);
$GLOBALS['MG']['SITE']['TIME']=$t->time_server();
$GLOBALS['MG']['USER']['TIME']=$t->time_client();
$t=false;

/**
* Load Page Data
*/
$tmp=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'pages'),false,array(array(false,false,'page_path','=',strtolower($GLOBALS['MG']['GET']['PAGE']))));
if(is_array($tmp[0])){
	$keys=array_keys($tmp[0]);
	$soq=count($keys);
	for($i=0;$i<$soq;$i++){
		$key=strtoupper(preg_replace('/page_/','',$keys[$i]));
		switch($key){
			case 'PACKAGES':
			case 'CONTENTHOOKS':
			case 'CACHEHOOKS':
            case 'AJAXHOOKS':
            case 'EXTHOOKS':
				$GLOBALS['MG']['PAGE'][$key]=explode(';',$tmp[0][$keys[$i]]);
			break;
			default:
				$GLOBALS['MG']['PAGE'][$key]=$tmp[0][$keys[$i]];	
			break;
		}
	}
	$tmp=false;
	$keys=false;	
}
$tmp=false;
$GLOBALS['MG']['PAGE']['REDIRECT']=false;
$act=false;

/**
* Load Language Data
*/
$lang=false;
if($GLOBALS['MG']['SITE']['LANG_ALLOW_OVERRIDE']=='1'){
	 if($GLOBALS['MG']['USER']['LANG']){
		$lang=$GLOBALS['MG']['USER']['LANG'];
	}
}
if(!$lang){
	$lang=$GLOBALS['MG']['SITE']['DEFAULT_LANGUAGE'];
}
$lang=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'langsets'),false,array(array(false,false,'lang_name','=',strtolower($lang))));

if(!$lang[0]['lang_id']){
	$lang=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'langsets'),false,array(array(false,false,'lang_name','=',strtolower($GLOBALS['MG']['SITE']['DEFAULT_LANGUAGE']))));	
}

mginit_loadLang($lang[0]['lang_name']);
$GLOBALS['MG']['LANG']['ENCODING']=$lang[0]['lang_encoding'];
$GLOBALS['MG']['LANG']['NAME']=$lang[0]['lang_name'];
$GLOBALS['MG']['LANG']['CONTENT_TYPE']='text/html';
$lang=false;

$GLOBALS['MG']['PAGE']['TPL']=$GLOBALS['MG']['CFG']['PATH']['TPL'].$GLOBALS['MG']['LANG']['NAME'].'/pages/'.implode('/',explode($GLOBALS['MG']['SITE']['URL_DELIM'],$GLOBALS['MG']['PAGE']['PATH'])).'.tpl';


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
    if(!include_once($GLOBALS['MG']['CFG']['PATH']['TPL'].'/'.$lang_id.'/lang.ini'.PHPEXT)){
        trigger_error('(INI): Could not load language file',E_USER_ERROR);
    }
}

function mginit_loadPackage($data){
	$soq=count($data);
	for($i=0;$i<$soq;$i++){
		$path=$data[$i][2].$data[$i][0].'.'.$data[$i][1].PHPEXT;
		if(!include_once($GLOBALS['MG']['CFG']['PATH']['INC'].$path)){
			trigger_error('(INI): Could not load package: '.$data[$i][0].'.'.$data[$i][1].PHPEXT,E_USER_ERROR);
		}
	}
}

function mginit_loadCustomPackages($pkgs){
	$soq=count($pkgs);
	for($i=0;$i<$soq;$i++){
		if($pkgs[$i]){
			$pkgdta=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'packages'),false,array(array(false,false,'pkg_filename','=',strtolower($pkgs[$i]))));
            if($pkgdta[0]['pkg_deps']){
				mginit_loadCustomPackages(explode(';',$pkgdta[0]['pkg_deps']));
			}
			if($pkgdta[0]['pkg_type']=='pkg'){
				if(!include_once($GLOBALS['MG']['CFG']['PATH']['PKG'].$pkgdta[0]['pkg_package'].'/'.$pkgs[$i].'.pkg'.PHPEXT)){
					trigger_error('(INI): Could not load package file: '.$pkgs[$i].'.pkg'.PHPEXT,E_USER_ERROR);
				}
			}
			else{
				if(!include_once($GLOBALS['MG']['CFG']['PATH']['INC'].'/classes/'.$pkgdta[0]['pkg_package'].'/'.$pkgs[$i].'.'.$pkgdta[0]['pkg_type'].PHPEXT)){
					trigger_error('(INI): Could not load mandrigo package file: '.$pkgs[$i].'.'.$pkgdta[0]['pkg_type'].PHPEXT,E_USER_ERROR);
				}
			}
		}
	}
}

function mginit_setHeader($name,$value){
	if($value=='none'){
		header($name.': ');
	}
	else{
		header($name.': '.$value);
	}
}
