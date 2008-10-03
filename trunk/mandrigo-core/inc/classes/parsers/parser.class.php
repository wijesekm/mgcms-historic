<?php

/**
 * @file                parser.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2008
 * @edited              9-29-2008
 
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

class parser{
	
	const P_CODE_START 	= '<!--TPL_CODE_START-->';
	const P_CODE_END	= '<!--TPL_CODE_END-->';
	
	public function p_vparse($vars,$text){
		$keys=array_keys($vars);
		$soq=count($keys);
		for($i=0;$i<$soq;$i++){
			if(ereg('{'.$keys[$i].'}',$text)){
				$text=ereg_replace('{'.$keys[$i].'}',$vars[$keys[$i]],$text);
			}
		}
		return $text;
	}
	
	public function p_lparse($lang,$text){
		$keys=array_keys($lang);
		$soq=count($keys);
		for($i=0;$i<$soq;$i++){
			if(ereg('ACRO:',$keys[$i])&&eregi('{'.$keys[$i].'}',$text)){
				$tmp=explode(':',$keys[$i]);
				$text=eregi_replace('{ACRO:('.$tmp[1].')}',$GLOBALS['MG']['LANG']['ACRO'],$text);
				$text=ereg_replace('{AP_ACROVALUE}',$lang[$keys[$i]],$text);
			}
			else if(ereg('{LANG:'.$keys[$i].'}',$text)){
				$text=ereg_replace('{LANG:'.$keys[$i].'}',$lang[$keys[$i]],$text);
			}
		}
		return $text;
	}
	
	public function p_phpcompile($section){
		if(!eregi(parser::P_CODE_START,$section)||!eregi(parser::P_CODE_END,$section)){
			trigger_error('Nothing to compile in template',E_USER_NOTICE);
			return $section;
		}
		$section=explode(parser::P_CODE_START,$section);
		$ssoc=count($section);
		for($cco=0;$cco<$ssoc;$cco++){
			if(eregi(parser::P_CODE_END,$section[$cco])){
				$section_split=explode(parser::P_CODE_END,$section[$cco]);
				$retvar='';
				eval($section_split[0]);
				if(!$retvar){
					trigger_error('(TEMPLATE): Compile Error in template!',E_USER_WARNING);
				}
				
				$section[$cco]=$retvar.$section_split[1];
				
			}
		}
		return implode('',$section);
	}
}