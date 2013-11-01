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
			array('funct','ini','/ini/'));
            
if(!defined('AJAX')){
    $load[]=array('page','class','/classes/');
}
else{
    $load[]=array('ajax','class','/classes/');
}
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
	die();
}

$t=$GLOBALS['MG']['SQL']->sql_connect($GLOBALS['MG']['CFG']['SQL']['HOST'],$GLOBALS['MG']['CFG']['SQL']['PORT_SOCKET']
								  ,$GLOBALS['MG']['CFG']['SQL']['USERNAME'],$GLOBALS['MG']['CFG']['SQL']['PASSWORD']
								  ,$GLOBALS['MG']['CFG']['SQL']['DB'],$GLOBALS['MG']['CFG']['SQL']['PERSISTENT'],$GLOBALS['MG']['CFG']['SQL']['SSL']);
if(!$t){
	trigger_error('(INI): Could not connect to SQL database!', E_USER_ERROR);
	die();
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
			array($GLOBALS['MG']['SITE']['ACCOUNT_TYPE'],'class','/classes/accounts/'));
mginit_loadPackage($load);

/**
* User Data
*/
$ses=new session(0);
eval('$act=new '.$GLOBALS['MG']['SITE']['ACCOUNT_TYPE'].'();');
if(!$act){
	trigger_error('(INI): Invalid account type or not account type set!', E_USER_ERROR);
	die();
}

if(isset($GLOBALS['MG']['POST']['USER_NAME'])&&preg_match('/;/',$GLOBALS['MG']['POST']['USER_NAME'])){
		$GLOBALS['MG']['COOKIE']['USER_NAME']=explode(';',$GLOBALS['MG']['POST']['USER_NAME']);
		$GLOBALS['MG']['COOKIE']['USER_SESSION']=$GLOBALS['MG']['POST']['USER_SESSION'];
}
else{
	$GLOBALS['MG']['COOKIE']['USER_NAME']=explode(';',$GLOBALS['MG']['COOKIE']['USER_NAME']);
}
if(!isset($GLOBALS['MG']['COOKIE']['USER_NAME'][1])){
    $GLOBALS['MG']['COOKIE']['USER_NAME'][1]='';
}
if(!$ses->session_load($GLOBALS['MG']['COOKIE']['USER_NAME'][0],$GLOBALS['MG']['COOKIE']['USER_NAME'][1],$GLOBALS['MG']['COOKIE']['USER_SESSION'])){

	$GLOBALS['MG']['USER']=$act->act_load($GLOBALS['MG']['SITE']['DEFAULT_ACT']);
	$GLOBALS['MG']['USER']=$GLOBALS['MG']['USER'][$GLOBALS['MG']['SITE']['DEFAULT_ACT']];
	$GLOBALS['MG']['USER']['NOAUTH']=true;
}
else{
	$GLOBALS['MG']['USER']=$act->act_load($GLOBALS['MG']['COOKIE']['USER_NAME'][1]);
	$GLOBALS['MG']['USER']=$GLOBALS['MG']['USER'][$GLOBALS['MG']['COOKIE']['USER_NAME'][1]];
	$GLOBALS['MG']['USER']['NOAUTH']=false;
}

/**
 * Impersonate Settings
 */
 
 $GLOBALS['MG']['REAL_USER']=array();
 
if(isset($GLOBALS['MG']['SITE']['ALLOW_IMPERSONATE']) && isset($GLOBALS['MG']['COOKIE']['ALTERNATE_UID'])){
    if($GLOBALS['MG']['SITE']['ALLOW_IMPERSONATE'] == '1' && $GLOBALS['MG']['COOKIE']['ALTERNATE_UID'] != '' && mg_checkACL('*','admin')){
        if($act->act_isAccount($GLOBALS['MG']['COOKIE']['ALTERNATE_UID'])){
            $GLOBALS['MG']['REAL_USER'] = $GLOBALS['MG']['USER'];
        	$GLOBALS['MG']['USER']=$act->act_load($GLOBALS['MG']['COOKIE']['ALTERNATE_UID']);
        	$GLOBALS['MG']['USER']=$GLOBALS['MG']['USER'][$GLOBALS['MG']['COOKIE']['ALTERNATE_UID']];
        	$GLOBALS['MG']['USER']['NOAUTH']=false;
        }
    }
}

/**
* Time Data
*/
$t=new mgtime($GLOBALS['MG']['SITE']['TZ'],$GLOBALS['MG']['USER']['TZ']);
$GLOBALS['MG']['SITE']['TIME']=$t->time_server();
$GLOBALS['MG']['USER']['TIME']=$t->time_client();
$t=false;

/**
* Update Cookies
*/
if(!defined('AJAX')){
    $cdta=array(
    	'SECURE'=>(boolean)$GLOBALS['MG']['SITE']['COOKIE_SECURE'],
    	'PATH'=>$GLOBALS['MG']['SITE']['COOKIE_PATH'],
    	'DOM'=>$GLOBALS['MG']['SITE']['COOKIE_DOM'],
    );
    if($GLOBALS['MG']['USER']['BANNED']){
    	$ses->session_stop($cdta);
    	$GLOBALS['MG']['USER']=$act->act_load($GLOBALS['MG']['SITE']['DEFAULT_ACT']);
    	$GLOBALS['MG']['USER']=$GLOBALS['MG']['USER'][$GLOBALS['MG']['SITE']['DEFAULT_ACT']];
    	$GLOBALS['MG']['USER']['NOAUTH']=true;
    }
    else{
    	$ses->session_loadUD($GLOBALS['MG']['USER']['TIME'],$cdta);
    }
}

$ses=false;

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

if(mg_checkACL($GLOBALS['MG']['PAGE']['PATH'],'admin')&&isset($GLOBALS['MG']['GET']['USER_NAME'])&&$GLOBALS['MG']['GET']['USER_NAME']!=''){
	$GLOBALS['MG']['USER']=$act->act_load($GLOBALS['MG']['GET']['USER_NAME']);
	$GLOBALS['MG']['USER']=$GLOBALS['MG']['USER'][$GLOBALS['MG']['GET']['USER_NAME']];
	$GLOBALS['MG']['USER']['NOAUTH']=false;
}
$act=false;
/**
* Load Language Data
*/
$lang=false;
if($GLOBALS['MG']['SITE']['LANG_ALLOW_OVERRIDE']=='1'){
	if(isset($GLOBALS['MG']['COOKIE']['LANGUAGE'])){
		$lang=$GLOBALS['MG']['COOKIE']['LANGUAGE'];	
	}
	else if(isset($GLOBALS['MG']['GET']['LANGUAGE'])){
		$lang=$GLOBALS['MG']['GET']['LANGUAGE'];
		$s=$GLOBALS['MG']['SITE'];
		@setcookie($s['COOKIE_PREFIX'].'lang',$GLOBALS['MG']['GET']['LANGUAGE'],0,$s['COOKIE_PATH'],$s['COOKIE_DOM'],$s['COOKIE_SECURE']);
	}
	else if($GLOBALS['MG']['USER']['LANG']){
		$lang=$GLOBALS['MG']['USER']['LANG'];
		if(isset($GLOBALS['MG']['SITE']['MOBILE_SUPPORT'])){
			if(mginit_mobileDeviceCheck()){
				$lang.=$GLOBALS['MG']['SITE']['MOBILE_SUPPORT'];
			}			
		}
	}

}
if(!$lang){
	$lang=$GLOBALS['MG']['SITE']['DEFAULT_LANGUAGE'];
	if(isset($GLOBALS['MG']['SITE']['MOBILE_SUPPORT'])){
		if(mginit_mobileDeviceCheck()){
			$lang.=$GLOBALS['MG']['SITE']['MOBILE_SUPPORT'];
		}			
	}
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

function mginit_mobileDeviceCheck(){
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	if(preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
		return true;
	}
	return false;	
}