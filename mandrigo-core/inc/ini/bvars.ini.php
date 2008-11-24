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

	$fileUploadErrors = array(
	    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
	    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
	    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
	    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
	    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
	    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
	    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
	);
	
	$GLOBALS['MG']['SITE']['KNOWN_EXTENSIONS']=explode(';',$GLOBALS['MG']['SITE']['KNOWN_EXTENSIONS']);
	
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
				case 'FILE':
					$GLOBALS['MG']['FILE'][$name]=array();
					$GLOBALS['MG']['FILE'][$name]['ERROR']='';
					if($_FILES[$uname]['name']){
						if($_FILES[$uname]['error'] !=  UPLOAD_ERR_OK){
							trigger_error('(BVARS): File upload error: '.$fileUploadErrors($_FILES[$uname]['error']),E_USER_WARNING);
							$GLOBALS['MG']['FILE'][$name]['ERROR']=$fileUploadErrors($_FILES[$uname]['error']);
						}
						else{
						 	$GLOBALS['MG']['FILE'][$name]['HOST_FILENAME']=stripslashes($_FILES[$uname]['name']);
							$GLOBALS['MG']['FILE'][$name]['EXT']=getExtension($GLOBALS['MG']['FILE'][$name]['HOST_FILENAME']);
						 	if(!in_array($GLOBALS['MG']['FILE'][$name]['EXT'],$GLOBALS['MG']['SITE']['KNOWN_EXTENSIONS'])){
								trigger_error('(BVARS): File upload unknown filetype',E_USER_WARNING);
								$GLOBALS['MG']['FILE'][$name]['ERROR']='UNKNOWNTYPE';
							}
							else{
								if($_FILES[$uname]['size'] > $clean[0]){
									trigger_error('(BVARS): File too large',E_USER_WARNING);
									$GLOBALS['MG']['FILE'][$name]['ERROR']='TOOLARGE';
								}
								else{
									$GLOBALS['MG']['FILE'][$name]['SIZE']=$_FILES[$uname]['size'];
									$GLOBALS['MG']['FILE'][$name]['SERVER_FILENAME']=$_FILES[$uname]['tmp_name'];	
								}
							}
						}
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