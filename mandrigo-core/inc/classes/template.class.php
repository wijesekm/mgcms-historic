<?php

/**
 * @file		template.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		5-28-2008
 
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

class template{
	
	/**
	* Constants
	*/
	
	const TPL_START 		= '<!--TPL_START_';
	const TPL_END			= '<!--TPL_END_';
	const TPL_E				= '-->';
	const TPL_CODE_START 	= '<!--TPL_CODE_START-->';
	const TPL_CODE_END		= '<!--TPL_CODE_END-->';
	const TPL_ALL			= 'ALL';
	
	/**
	* Variables
	*/
	
	private $tpl;
	private $filter;
	private $keys;
	private $size;
	
	/**
	* Construct and Destruction functions
	*/
	
	public function __construct(){
		$this->tpl=array();
	}
	public function __destruct(){
		$this->tpl=false;
		$this->filter=false;
		$this->keys=false;
		$this->size=false;
	}
	
	/**
	* Public Functions
	*/
	
	/**
	* tpl_load($input,$s_name,$file)
	*
	* Function to load a section into the template 
	*
	* INPUTS:
	* $input	-	Text or filename of section (string)
	* $s_name	-	Section Name (string)
	* $file		-	Is File? (bool)
	*
	* OUTPUTS:
	* true on success, false on fail (bool)
	*/	
	public function tpl_load($input,$s_name,$file=true){	
		$str='';
		if($file){
			if(!$f=fopen($input,'r')){
				trigger_error('(TEMPLATE): Cannot open template: '.$input, E_USER_ERROR);
				return false;
			}
			while(!feof($f)){
				$str.=fgets($f);
			}
			fclose($f);
		}
		else{
			$str=$input;
		}
		if(!eregi(template::TPL_START.$s_name.template::TPL_E,$str)||!eregi(template::TPL_END.$s_name.template::TPL_E,$str)){
			trigger_error('(TEMPLATE): Could not find section '.$s_name.' in template: '.$input, E_USER_ERROR);
			return false;
		}
		$str=explode(template::TPL_START.$s_name.template::TPL_E,$str);
		$this->tpl[(string)$s_name]=explode(template::TPL_END.$s_name.template::TPL_E,$str[1]);
		$this->keys=array_keys($this->tpl);
		$this->size=count($this->keys);
		return true;
	}

	/**
	* tpl_return($s_name=template::TPL_ALL)
	*
	* Function to return the template
	*
	* INPUTS:
	* $s_name	-	Section Name (string)
	*
	* OUTPUTS:
	* the template section (string)
	*/	
	public function tpl_return($s_name=template::TPL_ALL){
		if($s_name==template::TPL_ALL){
			$str='';
			for($i=0;$i<$this->size;$i++){
				$str.=$this->tpl[$this->keys[$i]][0];
			}
			return $str;
		}
		else{
			return $this->tpl[(string)$s_name][0];
		}
	}

	/**
	* tpl_parse($vars=array(),$s_name=template::TPL_ALL,$level=1,$rempty=false)
	*
	* Parses the template
	*
	* INPUTS:
	* $vars		-	Array of variable names and values (array of strings)
	* $s_name	-	Section Name (string)
	* $level	-	Level of parsing: 0 - Do Nothing, 1 - Parse Vars only, 2 - Compile and Parse Vars (int)
	* $rempty	-	Remove variables with no value? (bool)
	*
	* OUTPUTS:
	* true on success, false on fail
	*/	
	public function tpl_parse($vars=array(),$s_name=template::TPL_ALL,$level=2,$rempty=false){

		if($s_name==template::TPL_ALL){
			for($i=0;$i<$this->size;$i++){
				$t=$this->tpl_parseSection($vars,$this->tpl[$this->keys[$i]][0],$level,$rempty);
				if(!$t){
					return false;
				}
				$this->tpl[$this->keys[$i]][0]=$t;
			}
		}
		else{
			$t=$this->tpl_parseSection($vars,$this->tpl[(string)$s_name][0],$level,$rempty);
			if(!$t){
				return false;
			}
			$this->tpl[(string)$s_name][0]=$t;
		}
		return true;
	}

	/**
	* Private Functions
	*/

	/**
	* tpl_parseSection($vars,$section,$level,$rempty)
	*
	* Parses a section of the template
	*
	* INPUTS:
	* $vars		-	Array of variable names and values (array of strings)
	* $section	-	Text to parse (string)
	* $level	-	Level of parsing: 0 - Do Nothing, 1 - Parse Vars only, 2 - Compile and Parse Vars (int)
	* $rempty	-	Remove variables with no value? (bool)
	*
	* OUTPUTS:
	* The section or false on fail
	*/	
	private function tpl_parseSection($vars,$section,$level,$rempty){
		switch($level){
			case 2:
				$section=$this->tpl_vparse($vars,$section);
				$section=$this->tpl_compile($vars,$section);
				$section=$this->tpl_vparse($vars,$section);
			break;
			case 1:
				$section=$this->tpl_vparse($vars,$section);
			break;
			default:
				
			break;
		};
		if(!$section){
			return false;
		}
		if($rempty){
			$section=eregi_replace("[{]+[a-z0-9_-]+[}]","",$section);
		}
		return $section;
		
	}

	/**
	* tpl_vparse($vars,$section)
	*
	* Parses the variables in a section
	*
	* INPUTS:
	* $vars		-	Array of variable names and values (array of strings)
	* $section	-	Text to parse (string)
	*
	* OUTPUTS:
	* The section or false on fail
	*/	
	private function tpl_vparse($vars,$section){
		$keys=array_keys($vars);
		$sov=count($keys);
        for($i=0;$i<$sov;$i++){
        	if(eregi($keys[$i],$section)){
	        	if(eregi('LANG::',$keys[$i])){
	        		$title=ereg_replace('LANG::','',$keys[$i]);
	        		$section=ereg_replace("\{".$keys[$i]."\}",$GLOBALS['MG']['LANG'][$title],$section);
				}
				else{
					$section=ereg_replace("\{".$keys[$i]."\}",$vars[$keys[$i]],$section);
				}				
			}
        }
        return $section;
	}

	/**
	* tpl_compile($vars,$section)
	*
	* Compiles embedded PHP code in a section
	*
	* INPUTS:
	* $vars		-	Array of variable names and values (array of strings)
	* $section	-	Text to parse (string)
	*
	* OUTPUTS:
	* The section or false on fail
	*/	
	private function tpl_compile($vars,$section){
		if(!eregi(template::TPL_CODE_START,$section)||!eregi(template::TPL_CODE_END,$section)){
			trigger_error('Nothing to compile in template',E_USER_NOTICE);
			return $section;
		}
		$section=explode(template::TPL_CODE_START,$section);
		$ssoc=count($section);
		for($cco=0;$cco<$ssoc;$cco++){
			if(eregi(template::TPL_CODE_END,$section[$cco])){
				$section_split=explode(template::TPL_CODE_END,$section[$cco]);
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