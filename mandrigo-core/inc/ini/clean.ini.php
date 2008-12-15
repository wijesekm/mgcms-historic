<?php

/**
 * @file		clean.ini.php
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

function mginit_cleanVar($value,$clean){
	
	if((boolean)$clean[1]){
		$value=urldecode($value);
	}	
	if(!(boolean)$clean[2]){
		$value=strip_tags($value);
	}
	if((boolean)$clean[5]){
		$value=base64_decode($value);
	}
	if((boolean)$clean[3]){
		$value=trim($value);
	}
	if((boolean)$clean[4]){
		$value=mginit_RLD($value);
	}

	
	switch ($clean[0]){
		case 'boolean':
			return ($value!="")?1:0;
		break;
		case 'file_path':
			return (eregi("[<|>?%\*\:\|\"]",$value))?'':$value;
		break;
		case 'url':
			return (preg_match("/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i",$value))?$value:'';
		break;
		case 'file_name':
			return (eregi("[<|>?%\*\:\|\"\/\\]",$value))?'':$value;
		break;
		case 'e-mail':
			return (eregi("^[a-z\\+0-9._-]+@[a-z.0-9-]+\.[a-z.]{2,5}$",$value))?$value:'';
		break;
		case 'id':
			return (eregi("^[0-9a-z]+$",$value))?$value:'';	
		break;
		case 'letter':
			return (eregi("^[a-z]+$",$value))?$value:'';
		break;
		case 'int':
			return (eregi("^[0-9]+$",$value))?$value:'';
		break;
		case 'float':
			return (preg_match('[-+]?\b[0-9]+(\.[0-9]+)?\b',$value))?$value:'';
		break;
		case 'groupname':
			return (eregi("^[a-z0-9._-]+$",$value))?$value:'';
		break;
		case 'username':
			return (eregi("^[a-z0-9\\@._-]+$",$value))?$value:'';
		break;
		case 'page_title':
			return (eregi("^[[:space:]a-z0-9_-]+$",$value))?$value:'';
		break;
		case 'title':
			return (eregi("^[[:space:]a-z0-9_-|:,\.]+$",$value))?$value:'';
		break;
		case 'target':
			return (eregi("^[a-z\/0-9:|._-]+$",$value))?$value:'';
		break;
		case 'name':
			return (eregi("^[[:space:]a-z.,]+$",$value))?$value:'';
		break;
		default:
			return $value;
		break;
	}
}

function mginit_RLD($value){
	if(substr($value,strlen($value)-1,1)==$GLOBALS['MG']['SITE']['URL_DELIM']){
		$value=substr($value,0,strlen($value)-1);
	}
	return $value;
}
