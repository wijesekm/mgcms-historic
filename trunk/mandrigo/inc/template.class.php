<?php
/**********************************************************
    template.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/29/07

	Copyright (C) 2006-2007 the MandrigoCMS Group

    ##########################################################
	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

	###########################################################

**********************************************************/

//
//To prevent direct script access
//
if(!defined("START_MANDRIGO")){
    die($GLOBALS["MANDRIGO"]["CONFIG"]["DIE_STRING"]);
}

define("TPL_START","<!--MG_TEMPLATE_START_");
define("TPL_END","<!--MG_TEMPLATE_END_");
define("TPL_CODE_START","<!--MG_CODE_START-->");
define("TPL_CODE_END","<!--MG_CODE_END-->");
define("TPL_E","-->");
define("TPL_ALL","ALL");


class template{
	
	var $tpl;
	
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	    
 	
    //
    //public function tpl_load($data,$section,$file)
    //
    //sets the template for a given section
    //INPUTS:
    //$data		-	path or template contents
    //$section	-	name of section to grab
    //$file		-	if set to true data will be treated as a file instead of a string (default: true)
    //
	//returns true or false 
	function tpl_load($data,$section,$file=true){
	 	$string="";
		if($file){
            if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
                $f=fopen($data,"r");
                return false;
            }
            else{
                if(!(@$f=fopen($data,"r"))){
                    return false;
                }
            }
            while(!feof($f)){
                $string.=fgets($f);
            }
            fclose($f);		
		}
		else{
			$string=$data;
		}
		if(!eregi(TPL_START.$section.TPL_E,$string)||!eregi(TPL_END.$section.TPL_E,$string)){
			return false;
		}
		$tmp=explode(TPL_START.$section.TPL_E,$string);
		$tmp=explode(TPL_END.$section.TPL_E,$tmp[1]);
		$this->tpl[(string)$section]=$tmp[0];
		return true;		
	}
	
    //
    //public function tpl_parse($vars=(),$section=TPL_ALL,$level=1)
    //
    //sets the template for a given section
    //INPUTS:
    //$vars		-	vars to parse (default: none)
    //$section	-	section to parse (default: TPL_ALL)
    //$level	-	parse level to use [0 - none, 1 - vars, 2 - compile] (default: 1)
    //$rempty	-	remove any set vars in the template that are not defined (default: true)
    //
	//returns true or false 
	function tpl_parse($vars=array(),$section=TPL_ALL,$level=1,$rempty=true){
		if($section==TPL_ALL){
			$soq=count($this->tpl);
			$keys=array_keys($this->tpl);
			for($i=0;$i<$soq;$i++){
				if($level===1){
					$this->tpl[$keys[$i]]=$this->tpl_vparse($vars,$this->tpl[$keys[$i]]);
				}
				else if($level===2){
					$this->tpl[$keys[$i]]=$this->tpl_compile($vars,$this->tpl[$keys[$i]]);
					$this->tpl[$keys[$i]]=$this->tpl_vparse($vars,$this->tpl[$keys[$i]]);
				}
				if($rempty){
					$this->tpl[$keys[$i]]=eregi_replace("[{]+[a-z0-9_-]+[}]","",$this->tpl[$keys[$i]]);	
				}
			}
		}
		else{
			if($level===1){
				$this->tpl[(string)$section]=$this->tpl_vparse($vars,$this->tpl[(string)$section]);	
			}
			else if($level===2){
				$this->tpl[(string)$section]=$this->tpl_compile($vars,$this->tpl[(string)$section]);
				$this->tpl[(string)$section]=$this->tpl_vparse($vars,$this->tpl[(string)$section]);
			}
			if($rempty){
				$this->tpl[(string)$section]=eregi_replace("[{]+[a-z0-9_-]+[}]","",$this->tpl[(string)$section]);
			}
		}
		$this->tpl_regester();
		return true;
	}
	
	//
    //public function tpl_return($section)
    //
    //returns the template for the given section if set to TPL_ALL returns the entire tpl array
    //INPUTS:
    //$section	-	name of section to grab (default: TPL_ALL)
    //
	//returns template
	function tpl_return($section=TPL_ALL){
		if($section==TPL_ALL){
		 	$str='';
		 	$soq=count($this->tpl);
		 	$keys=array_keys($this->tpl);
		 	for($i=0;$i<$soq;$i++){
				$string.=$this->tpl[$keys[$i]];
			}
			return $string;
		}
		else{
			return $this->tpl[(string)$section];
		}
		return false;
	}

	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################	 
	
	//
    //public function tpl_vparse($vars,$string)
    //
    //parses out variables in a given string
    //INPUTS:
    //$vars		-	array of vars
    //$string	-	string to parse
    //
	//returns parsed string	
    function tpl_vparse($vars,$string){
        $sov=count($vars);
        if(!$sov%2||!$vars){
            return $string;
        }
        for($i=0;$i<$sov-1;$i+=2){
            $string=ereg_replace("{".$vars[$i]."}",$vars[$i+1],$string);
        }
        return $string;
    }
	
	//
    //public function tpl_compile($vars,$string)
    //
    //compiles a string using the following vars
    //INPUTS:
    //$vars		-	array of vars
    //$string	-	string to compile
    //
	//returns parsed string	
    function tpl_compile($vars,$string){
        if(!eregi(TPL_CODE_START,$string)){
			return $string;
		}
		$mg_return="";
		$string=$this->tpl_vparse($vars,$string);
		$tmp=explode(TPL_CODE_START,$string);
		$soq=count($tmp);
		$compiled="";
		
		for($i=0;$i<$soq;$i++){
			if(eregi(TPL_CODE_END,$tmp[$i])){
				$cur=explode(TPL_CODE_END,$tmp[$i]);
				$compile_string=$cur[0];
				$mg_return="";
				if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
					eval($compile_string);	
				}
				else{
					@eval($compile_string);
				}
				$compiled.=$mg_return.$cur[1];
			}
			else{
				$compiled.=$tmp[$i];
			}
		}
		return $compiled;
    }
    
	//
    //public function  tpl_regester()
    //
    //regesters the current charset, encoding wit the browser
    function tpl_regester(){
		if(!$GLOBALS["MANDRIGO"]["LANGUAGE"]["REG"]){
		  	if($GLOBALS["MANDRIGO"]["LANGUAGE"]["SET_ENCODING"]){
				header("Content-type: ".$GLOBALS["MANDRIGO"]["LANGUAGE"]["CONTENT_TYPE"]." charset=".$GLOBALS["MANDRIGO"]["LANGUAGE"]["CHARSET"]);
			}
			else{
				header("Content-type: ".$GLOBALS["MANDRIGO"]["LANGUAGE"]["CONTENT_TYPE"]);
			}
			$GLOBALS["MANDRIGO"]["LANGUAGE"]["REG"]=true;
		}
	}
}