<?php

/**
 * @file		bvars.ini.php
 * @author 		Kevin Wijesekera
 * @copyright 	2009
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

$GLOBALS['MG']['CFG']['USEINCACHE']=array();
$GLOBALS['MG']['CFG']['STOPCACHE']=false;
$GLOBALS['MG']['CFG']['ALLOWWRITECACHE']=false;

mginit_loadVars();

function mginit_loadVars(){
	$mime_keys = array_keys($GLOBALS['MG']['MIME']);

	$fileUploadErrors = array(
	    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
	    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
	    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
	    UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
	    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
	    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
	    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
	);

	$addit=array();
	$addit['orderby']=array(array('var_id'),array('ASC'));
	$vars=$GLOBALS['MG']['SQL']->sql_fetcharray(array(TABLE_PREFIX.'vars'),false,false,DB_ASSOC,DB_ALL_ROWS,$addit);

	if($GLOBALS['MG']['SITE']['URLTYPE']==3){
		$url = mginit_genURLType3();
	}
	else if($GLOBALS['MG']['SITE']['URLTYPE']==2){
		$url=mginit_genURLType2();
	}
	else{
		$url= & $_GET;
	}
	if(defined('EXT_AUTH')){
	    $GLOBALS['MG']['EAUTH']['USER'] = isset($_SERVER['PHP_AUTH_USER'])?mginit_cleanVar($_SERVER['PHP_AUTH_USER'],array('username',1,0,1,0,0,0)):'';
	    $GLOBALS['MG']['EAUTH']['KEY'] = isset($_SERVER['PHP_AUTH_PW'])?mginit_cleanVar($_SERVER['PHP_AUTH_PW'],array('username',1,0,1,0,0,0)):'';
	}

	for($i=0;$i<$vars['count'];$i++){

		if(mginit_loadVarOnlyOnePage($vars[$i]['var_loadOnPageOnly'])){
			$name=$vars[$i]['var_callname'];
			$uname=$vars[$i]['var_getname'];

			if($vars[$i]['var_useInCache']=='1'){
				$GLOBALS['MG']['CACHE']['USEINCACHE'][]=$name;
			}

			if($name){
				$clean=explode(',',$vars[$i]['var_clean']);
				switch($vars[$i]['var_type']){
					case 'GET':
						if(substr($uname,-1)=='*'){
							$uname=substr($uname,0,-1);
							foreach($url as $key=>$val){
								if(preg_match("/^".$uname."/",$key)){
									$store_name=strtoupper(preg_replace("/^".$uname."/",'',$key));
									$GLOBALS['MG']['GET'][$name.$store_name]=isset($val)?mginit_cleanVar($val,$clean):$vars[$i]['var_default'];
									if($vars[$i]['var_stopCache']=='1'&&$GLOBALS['MG']['POST'][$name.$store_name]&&$GLOBALS['MG']['GET'][$name.$store_name]!=$vars[$i]['var_default']){
										$GLOBALS['MG']['CFG']['STOPCACHE']=true;
									}
								}
							}
						}
						else if(preg_match('/\[\*\]/',$uname)){
							$uname=preg_replace('/\[\*\]/','',$uname);
							foreach($url as $key=>$val){
								if(preg_match("/^".$uname."/",$key)){
									$store_name=strtoupper(preg_replace("/^".$uname."/",'',$key));
									$GLOBALS['MG']['GET'][$name][$store_name]=isset($val)?mginit_cleanVar($val,$clean):$vars[$i]['var_default'];
									if($vars[$i]['var_stopCache']=='1'&&$GLOBALS['MG']['POST'][$name][$store_name]&&$GLOBALS['MG']['GET'][$name][$store_name]!=$vars[$i]['var_default']){
										$GLOBALS['MG']['CFG']['STOPCACHE']=true;
									}
								}
							}
						}
						else{
							$GLOBALS['MG']['GET'][$name]=isset($url[$uname])?mginit_cleanVar($url[$uname],$clean):$vars[$i]['var_default'];
							if($vars[$i]['var_stopCache']=='1'&&$GLOBALS['MG']['GET'][$name]&&$GLOBALS['MG']['GET'][$name]!=$vars[$i]['var_default']){
								$GLOBALS['MG']['CFG']['STOPCACHE']=true;
							}
						}
					break;
                    case 'JSON':
                        $data = '';
                        if($_SERVER["CONTENT_TYPE"] == 'application/json'){
                            $data = file_get_contents('php://input');
                            $data = json_decode($data,TRUE);
                            if(isset($data[$uname])){
                                $data = $data[$uname];
                            }
                            else{
                                $data = '';
                            }
                        }
                        else if(isset($_POST[$uname])){
                            $data = $_POST[$uname];
                        }

                        if(!empty($data)){
                            $GLOBALS['MG']['POST'][$name]=mginit_cleanArray($data,$clean);
                            if($vars[$i]['var_stopCache']=='1'&&$GLOBALS['MG']['POST'][$name]&&$GLOBALS['MG']['POST'][$name]!=$vars[$i]['var_default']){
    						  $GLOBALS['MG']['CFG']['STOPCACHE']=true;
                            }
                        }
                    break;
					case 'POST':
						if(substr($uname,-1)=='*'){
							$uname=substr($uname,0,-1);
							foreach($_POST as $key=>$val){
								if(preg_match("/^".$uname."/",$key)){
									$store_name=strtoupper(preg_replace("/^".$uname."/",'',$key));
                                    $GLOBALS['MG']['POST'][$name.$store_name]=isset($val)?mginit_cleanVar($val,$clean):$vars[$i]['var_default'];
									if($vars[$i]['var_stopCache']=='1'&&$GLOBALS['MG']['POST'][$name.$store_name]&&$GLOBALS['MG']['POST'][$name.$store_name]!=$vars[$i]['var_default']){
										$GLOBALS['MG']['CFG']['STOPCACHE']=true;
									}
								}
							}
						}
						else{
							$GLOBALS['MG']['POST'][$name]=isset($_POST[$uname])?mginit_cleanVar($_POST[$uname],$clean):$vars[$i]['var_default'];
							if($vars[$i]['var_stopCache']=='1'&&$GLOBALS['MG']['POST'][$name]&&$GLOBALS['MG']['POST'][$name]!=$vars[$i]['var_default']){
								$GLOBALS['MG']['CFG']['STOPCACHE']=true;
							}
						}
					break;
					case 'FILE':
						$GLOBALS['MG']['FILE'][$name]=array();
                        if(!empty($_FILES[$uname]['name'])){
                            if(is_array($_FILES[$uname]['name'])){
                                $fsize = count($_FILES[$uname]['name']);;
    							for($j=0;$j<$fsize;$j++){
    								$GLOBALS['MG']['FILE'][$name][$j]=array();
    								$GLOBALS['MG']['FILE'][$name][$j]['ERROR']='';
    								$GLOBALS['MG']['FILE'][$name][$j]['HOST_FILENAME']=stripslashes($_FILES[$uname]['name'][$j]);
        							if($_FILES[$uname]['error'][$j] !=  UPLOAD_ERR_OK){
                                        if(isset($fileUploadErrors[$_FILES[$uname]['error'][$j]])){
                                            trigger_error('(BVARS): File upload error: '.$fileUploadErrors[$_FILES[$uname]['error'][$j]],E_USER_WARNING);
        								    $GLOBALS['MG']['FILE'][$name][$j]['ERROR']=$fileUploadErrors[$_FILES[$uname]['error'][$j]];
                                        }
                                        else{
                                            trigger_error('(BVARS): File upload error: Unknown Error',E_USER_WARNING);
        								    $GLOBALS['MG']['FILE'][$name][$j]['ERROR']='Unknown Error';
                                        }

        							}
        							else{
        								$GLOBALS['MG']['FILE'][$name][$j]['EXT']=pathinfo($GLOBALS['MG']['FILE'][$name][$j]['HOST_FILENAME'],PATHINFO_EXTENSION);
        							 	if(!in_array('.'.strtolower($GLOBALS['MG']['FILE'][$name][$j]['EXT']),$mime_keys) && !in_array('*',$mime_keys)){
                                            trigger_error('(BVARS): File upload unknown filetype .'.$GLOBALS['MG']['FILE'][$name][$j]['EXT'],E_USER_WARNING);
        				    	            $GLOBALS['MG']['FILE'][$name][$j]['ERROR']='UNKNOWNTYPE';
        								}
        								else{
        									if($_FILES[$uname]['size'][$j] > $clean[0]){
        										trigger_error('(BVARS): File too large',E_USER_WARNING);
        										$GLOBALS['MG']['FILE'][$name][$j]['ERROR']='TOOLARGE';
        									}
        									else{
        										$GLOBALS['MG']['FILE'][$name][$j]['SIZE']=$_FILES[$uname]['size'][$j];
        										$GLOBALS['MG']['FILE'][$name][$j]['SERVER_FILENAME']=$_FILES[$uname]['tmp_name'][$j];
        									}
        								}
        							}
    							}
                            }
                            else{
                                $GLOBALS['MG']['FILE'][$name]['ERROR']='';
    							$GLOBALS['MG']['FILE'][$name]['HOST_FILENAME']=stripslashes($_FILES[$uname]['name']);
    							if($_FILES[$uname]['error'] !=  UPLOAD_ERR_OK){
                                    if(isset($fileUploadErrors[$_FILES[$uname]['error']])){
                                        trigger_error('(BVARS): File upload error: '.$fileUploadErrors[$_FILES[$uname]['error']],E_USER_WARNING);
    								    $GLOBALS['MG']['FILE'][$name]['ERROR']=$fileUploadErrors[$_FILES[$uname]['error']];
                                    }
                                    else{
                                        trigger_error('(BVARS): File upload error: Unknown Error',E_USER_WARNING);
    								    $GLOBALS['MG']['FILE'][$name]['ERROR']='Unknown Error';
                                    }

    							}
    							else{
    								$GLOBALS['MG']['FILE'][$name]['EXT']=pathinfo($GLOBALS['MG']['FILE'][$name]['HOST_FILENAME'],PATHINFO_EXTENSION);
    							 	if(!in_array('.'.strtolower($GLOBALS['MG']['FILE'][$name]['EXT']),$mime_keys) && !in_array('*',$mime_keys)){
                                        trigger_error('(BVARS): File upload unknown filetype .'.$GLOBALS['MG']['FILE'][$name]['EXT'],E_USER_WARNING);
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

                        }
					break;
					case 'COOKIE':
						$GLOBALS['MG']['COOKIE'][$name]=isset($_COOKIE[$uname])?mginit_cleanVar($_COOKIE[$uname],$clean):$vars[$i]['var_default'];
					break;
				};
			}
		}
	}
}

function mginit_loadVarOnlyOnePage($loadOnly){
	if(isset($GLOBALS['MG']['GET']['PAGE'])&&$loadOnly){
		if($GLOBALS['MG']['GET']['PAGE']==$loadOnly){
			return true;
		}
		else{
			return false;
		}
	}
	return true;
}

function mginit_genURLType3(){
	if(preg_match('/\//',$GLOBALS['MG']['SITE']['URI'])){
		$base_uri = explode('/',$GLOBALS['MG']['SITE']['URI']);
		array_splice($base_uri,0,1);
		$base_uri = implode('/',$base_uri);
		if(isset($_GET['url'])){
			$_GET['url'] = preg_replace('/'.preg_quote($base_uri,'/').'/','',$_GET['url']);
		}

	}
    if(isset($_GET['url']) && $_GET['url'][0] == '/'){
        $_GET['url'] = substr($_GET['url'],1);
    }
	$raw_url=(isset($_GET['url']))?$_GET['url']:'';
	$raw_url=preg_replace('/\/\//','/',$raw_url);
    $raw_url = explode('/',$raw_url);
    $url=array();

	$soq=count($raw_url);
    for($i=0;$i<$soq;$i=$i+2){
    	if($raw_url[$i]){
    		if(isset($raw_url[$i+1])){
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
    if(!preg_match('/'.preg_quote($GLOBALS['MG']['SITE']['INDEX_NAME']."/",$_SERVER["REQUEST_URI"],'/').'/')){
		$raw_url=$GLOBALS['MG']['SITE']['INDEX_NAME'].$_SERVER["REQUEST_URI"];
    }
	else{
		$raw_url=$_SERVER["REQUEST_URI"];
    }

    $raw_url = eregi_replace("/^.*".preg_quote($GLOBALS['MG']['SITE']['INDEX_NAME'],'/')."\/p/","p",$raw_url);
    $raw_url=preg_replace('/\/\//','/',$raw_url);
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