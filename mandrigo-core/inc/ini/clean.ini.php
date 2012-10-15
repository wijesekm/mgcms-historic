<?php

/**
 * @file		clean.ini.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
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

function mginit_cleanArray($value,$clean){
	//
	//In earlier versions not all 7 slots were used so lets pad the clean array with false so it is not undefined
	//
	if(count($clean) < 7){
		$clean=array_pad($clean,7,false);
	}
    return mginit_cleanArraySub($value,$clean,$GLOBALS['MG']['CLEAN'][$clean[0]],$GLOBALS['MG']['CLEAN'][$clean[0]]['0000default']);
}

function mginit_cleanArraySub($value,$clean,$cleanPath,$default){
    $keys = array_keys($value);
    $soq = count($keys);
    for($i=0;$i<$soq;$i++){
        if(is_array($value[$keys[$i]])){
            $value[$keys[$i]]=mginit_cleanArraySub($value[$keys[$i]],$clean,$cleanPath[$keys[$i]],$default);
        }
        else{
            $tempClean = $clean;
            $tempClean[0] = $default;
            if(isset($cleanPath[$keys[$i]])){
                $tempClean[0] = $cleanPath[$keys[$i]];
            }
            else if(isset($cleanPath['0000default'])){
                $tempClean[0] = $cleanPath['0000default'];
            }
            //echo $value[$keys[$i]]
            $value[$keys[$i]]=mginit_cleanVar($value[$keys[$i]],$tempClean);
            //echo $value[$keys[$i]].'<br/>';
        }
    }
    return $value;
}

function mginit_cleanVar($value,$clean){
	
	//
	//In earlier versions not all 7 slots were used so lets pad the clean array with false so it is not undefined
	//
	if(count($clean) < 7){
		$clean=array_pad($clean,7,false);
	}
	if((boolean)$clean[5]){
		$value=base64_decode($value);
	}
	if((boolean)$clean[1]){
		$value=urldecode($value);
	}	
	if(!(boolean)$clean[2]){
		$value=strip_tags($value);
	}

	if((boolean)$clean[3]){
		$value=trim($value);
	}
	if((boolean)$clean[4]){
		$value=mginit_RLD($value);
	}
	if((boolean)$clean[6]){
		$value=stripslashes($value);
	}
	switch ($clean[0]){
		case 'boolean':
        case 'bool':
			return ( $value=="" || $value=="false" || $value == "0" )?false:true;
		break;
		case 'int':
			return (preg_match("/^[0-9]+$/",$value))?$value:false;
		break;
		case 'float':
			return (preg_match("/^[0-9]+(\.[0-9]+)?$/",$value))?$value:false;
		break;
		case 'char':
			return (preg_match("/^.{1}$$/",$value))?$value:false;
		break;
		case 'string':
			return $value;
		break;
		default:
            if(isset($GLOBALS['MG']['CLEAN'][$clean[0]])){
                return (preg_match($GLOBALS['MG']['CLEAN'][$clean[0]][1],$value) == $GLOBALS['MG']['CLEAN'][$clean[0]][0])?$value:false;
            }
            return $value;
		break;
	};
	return false;
}

function mginit_RLD($value){
	if(substr($value,strlen($value)-1,1)==$GLOBALS['MG']['SITE']['URL_DELIM']){
		$value=substr($value,0,strlen($value)-1);
	}
	return $value;
}
