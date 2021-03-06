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
	const TPL_ALL			= 'ALL';
	/**
	* Variables
	*/	
	private $tpl;
	private $filter;
	private $keys;
	private $size;
	private $parser;
	private $adminTpl;
    private $tmp_str;
	
	/**
	* Construct and Destruction functions
	*/
	
	public function __construct(){
		$this->tpl=array();
		$this->parser=new parser();
		
	}
	public function __destruct(){
		$this->tpl=false;
		$this->filter=false;
		$this->keys=false;
		$this->size=false;
		$this->parser=false;
		$this->adminTpl=false;
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
	* $fatal    -   Fatal error on failure?
    * 
	* OUTPUTS:
	* true on success, false on fail (bool)
	*/
	public function tpl_load($input,$s_name,$file=true,$fatal=true){	
		$str='';
		if($file){
			if(!$f=fopen($input,'r')){

				trigger_error('(TEMPLATE): Cannot open template: '.$input, ($fatal)?E_USER_ERROR:E_USER_WARNING);
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
		if(is_array($s_name)){
			foreach($s_name as $tmp){
				if(!$this->tpl_checkSectionIsValid($tmp,$str)){
					trigger_error('(TEMPLATE): Could not find section '.$tmp.' in template: '.$input, ($fatal)?E_USER_ERROR:E_USER_WARNING);
					return false;
				}
				$tmp_str=explode(template::TPL_START.$tmp.template::TPL_E,$str);
				$this->tpl[(string)$tmp]=explode(template::TPL_END.$tmp.template::TPL_E,$tmp_str[1]);		
			}
		}
		else{
			if(!$this->tpl_checkSectionIsValid($s_name,$str)){
				trigger_error('(TEMPLATE): Could not find section '.$s_name.' in template: '.$input, ($fatal)?E_USER_ERROR:E_USER_WARNING);
				return false;
			}
			$str=explode(template::TPL_START.$s_name.template::TPL_E,$str);
			$this->tpl[(string)$s_name]=explode(template::TPL_END.$s_name.template::TPL_E,$str[1]);	
		}
		$this->keys=array_keys($this->tpl);
		$this->size=count($this->keys);
		return true;
	}

    private function tpl_checkSectionIsValid($section,$data){
        $str = '/'.preg_quote(template::TPL_START.$section.template::TPL_E,'/');
        $str.='(.*?)'.preg_quote(template::TPL_END.$section.template::TPL_E,'/').'/mis';
        return preg_match($str,$data);
    }
	
	private function tpl_checkWrite($filename,$s_name){
		$str='';
		if(!$f=fopen($filename,'r')){
			trigger_error('(TEMPLATE): Cannot open template: '.$filename, E_USER_ERROR);
			return false;
		}
		while(!feof($f)){
			$str.=fgets($f);
		}
		fclose($f);
		$this->adminTpl=$str;
		if(!$this->tpl_checkSectionIsValid($s_name,$str)){
			return false;
		}
		return true;
	}
	
	public function tpl_write($content,$s_name,$filename){
        $str = '/'.preg_quote(template::TPL_START.$s_name.template::TPL_E,'/');
        $str.='(.*?)'.preg_quote(template::TPL_END.$s_name.template::TPL_E,'/').'/mis';
		$write='';
		$content=template::TPL_START.$s_name.template::TPL_E."\n".$content."\n".template::TPL_END.$s_name.template::TPL_E;
		if(!$this->tpl_checkWrite($filename,$s_name)){
			$write=$this->adminTpl."\n\n".$content;
		}
		else{
			$write=preg_replace($str,$content,$this->adminTpl);
		}
		if(!$f=fopen($filename,'w')){
			trigger_error('(TEMPLATE): Cannot open template for writing: '.$filename,E_USER_ERROR);
			return false;
		}
		fwrite($f,$write);
		fclose($f);
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
		else if(is_array($s_name)){
			$ret='';
			foreach($s_name as $val){
				$ret.=$this->tpl[(string)$val][0];
			}
			return $ret;
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

        /*
        * Format Vars to prevent issues
        */
		if($s_name==template::TPL_ALL){
			for($i=0;$i<$this->size;$i++){
				$t=$this->tpl_parseSection($vars,$this->tpl[$this->keys[$i]][0],$level,$rempty);
				if(!$t){
					return false;
				}
				$this->tpl[$this->keys[$i]][0]=$t;
			}
		}
		else if(is_array($s_name)){
			foreach($s_name as $val){
				$t=$this->tpl_parseSection($vars,$this->tpl[(string)$val][0],$level,$rempty);
				if(!$t){
					return false;
				}
				$this->tpl[(string)$val][0]=$t;	
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
	
	public function tpl_parseCustom($hooks,$s_name=template::TPL_ALL){
		if($s_name==template::TPL_ALL){
			for($i=0;$i<$this->size;$i++){
				$t=$this->parser->p_runCustomParsers($this->tpl[$this->keys[$i]][0],$hooks);
				if(!$t){
					return false;
				}
				$this->tpl[$this->keys[$i]][0]=$t;
			}
		}
		else if(is_array($s_name)){
			foreach($s_name as $val){
				$t=$this->parser->p_runCustomParsers($this->tpl[(string)$val][0],$hooks);
				if(!$t){
					return false;
				}
				$this->tpl[(string)$val][0]=$t;
			}
		}
		else{
			$t=$this->parser->p_runCustomParsers($this->tpl[(string)$s_name][0],$hooks);
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
	* $level	-	Level of parsing: 0 - Do Nothing, 1 - Parse Vars only, 2 - 1 + compile
	* $rempty	-	Remove variables with no value? (bool)
	*
	* OUTPUTS:
	* The section or false on fail
	*/	
	private function tpl_parseSection($vars,$section,$level,$rempty){
		switch($level){
			case 2:
				$section=$this->parser->p_vparse($vars,$section);	
				$section=$this->parser->p_lparse($GLOBALS['MG']['LANG'],$section);
				$section=$this->parser->p_vparse($vars,$section);
				$section=$this->parser->p_phpcompile($section);
				$section=$this->parser->p_vparse($vars,$section);
				$section=$this->parser->p_lparse($GLOBALS['MG']['LANG'],$section);
			break;
			case 1:	
				$section=$this->parser->p_vparse($vars,$section);
				$section=$this->parser->p_lparse($GLOBALS['MG']['LANG'],$section);
			break;
			default:
				
			break;
		};
		if(!$section){
			return false;
		}
		if($rempty){
			$section=preg_replace("/{[a-z0-9_-]+}/i","",$section);
		}
		return $section;
		
	}

}