<?php

/**
 * @file		bvars.ini.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		6-4-2008
 
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

$GLOBALS['MG']['CFG']['USEINCACHE']=array();
$GLOBALS['MG']['CFG']['STOPCACHE']=false;
$GLOBALS['MG']['CFG']['ALLOWWRITECACHE']=false;

mginit_loadVars();

//print_r($GLOBALS['MG']['GET']);

function mginit_loadVars(){
	
	$vars=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'vars'),false,false);
	
	if($GLOBALS['MG']['SITE']['URLTYPE']==3){
		$url = mginit_genURLType3();
	}
	else if($GLOBALS['MG']['SITE']['URLTYPE']==2){
		$url=mginit_genURLType2();
	}
	else{
		$url= & $_GET;
	}

	for($i=0;$i<$vars['count'];$i++){

		$name=$vars[$i]['var_callname'];
		$uname=$vars[$i]['var_getname'];

		if($vars[$i]['var_useInCache']=='1'){
			$GLOBALS['MG']['CACHE']['USEINCACHE'][]=$name;
		}
		
		if($name){
			$clean=explode(',',$vars[$i]['var_clean']);
			switch($vars[$i]['var_type']){
				case 'GET':
					$GLOBALS['MG']['GET'][$name]=isset($url[$uname])?mginit_cleanVar($url[$uname],$clean):$vars[$i]['var_default'];
					if($vars[$i]['var_stopCache']=='1'&&$GLOBALS['MG']['GET'][$name]&&$GLOBALS['MG']['GET'][$name]!=$vars[$i]['var_default']){
						$GLOBALS['MG']['CFG']['STOPCACHE']=true;
					}
				break;
				case 'POST':
					$GLOBALS['MG']['POST'][$name]=isset($_POST[$uname])?mginit_cleanVar($_POST[$uname],$clean):$vars[$i]['var_default'];
					if($vars[$i]['var_stopCache']=='1'&&$GLOBALS['MG']['POST'][$name]&&$GLOBALS['MG']['POST'][$name]!=$vars[$i]['var_default']){
						$GLOBALS['MG']['CFG']['STOPCACHE']=true;
					}
				break;
				case 'COOKIE':
					$GLOBALS['MG']['COOKIE'][$name]=isset($_COOKIE[$uname])?mginit_cleanVar($_COOKIE[$uname],$clean):$vars[$i]['var_default'];		
				break;	
			};
		}
	}
}
function mginit_genURLType3(){
	$raw_url=(isset($_GET['url']))?$_GET['url']:'';
	$raw_url=ereg_replace('\/\/','/',$raw_url);
    $raw_url = explode('/',$raw_url);
    $url=array();
    
	$soq=count($raw_url);
    for($i=0;$i<$soq;$i=$i+2){
    	if($raw_url[$i]){
    		if($raw_url[$i+1]){
				$url = array_merge_recursive($url, array($raw_url[$i]=>$raw_url[$i+1]));	
			}
			else{
				$url = array_merge_recursive($url, array($raw_url[$i]=>''));
			}		
		}
    }
	return $url;
}
function mginit_genURLType2(){
    if(!ereg($GLOBALS['MG']['SITE']['INDEX_NAME']."/",$_SERVER["REQUEST_URI"])){
		$raw_url=$GLOBALS['MG']['SITE']['INDEX_NAME'].$_SERVER["REQUEST_URI"];
    }
	else{
		$raw_url=$_SERVER["REQUEST_URI"];
    }

    $raw_url = eregi_replace("^.*".$GLOBALS['MG']['SITE']['INDEX_NAME']."/p","p",$raw_url);
    $raw_url=ereg_replace('\/\/','/',$raw_url);
    $raw_url = explode("/",$raw_url);
    $url=array();
    
	$soq=count($raw_url);
    for($i=0;$i<$soq;$i=$i+2){
    	if($raw_url[$i]){
    		if($raw_url[$i+1]){
				$url = array_merge_recursive($url, array($raw_url[$i]=>$raw_url[$i+1]));	
			}
			else{
				$url = array_merge_recursive($url, array($raw_url[$i]=>''));
			}			
		}
    }
	return $url;
}