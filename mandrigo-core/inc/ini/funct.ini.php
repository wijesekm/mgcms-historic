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
	if(is_array($GLOBALS['MG']['USER']['ACL'][$page])){
		if(isset($GLOBALS['MG']['USER']['ACL'][$page][$acl])){
			if((boolean)$GLOBALS['MG']['USER']['ACL'][$page][$acl]===true){
				return true;
			}
			else if($GLOBALS['MG']['USER']['ACL'][$page][$acl]=='deny'){
				return false;
			}
		}
	}
	if(is_array($GLOBALS['MG']['USER']['ACL']['*'])){
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

function mg_formatTarget($url){
	$url=ereg_replace('\/','[SLASH]',$url);
	$url=ereg_replace('\?','[Q]',$url);
	$url=ereg_replace('\&amp;','[AND]',$url);
	$url=ereg_replace('\&','[AND]',$url);
	return $url;
}

function mg_redirectTarget($target){
	$target=ereg_replace('\[SLASH\]','/',$target);
	$target=ereg_replace('\[Q\]','?',$target);
	$target=ereg_replace('\[AND\]','&amp;',$target);
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
	header('Location: '.$GLOBALS['MG']['SITE']['LOGIN_URL'].mg_formatTarget($url));
	die();
}