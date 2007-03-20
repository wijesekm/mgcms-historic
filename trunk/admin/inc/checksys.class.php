<?php
/**********************************************************
    checksys.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/13/07

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

class checksys{
	
	function checksys(){}
	
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	    
 	
    //
    //public cs_phpext($php_ext,$passexists=true,$fail=true)
    //
    //checks a php extension
    //$php_ext		-	name of php extension
    //$passexists	-	pass check if extension exists [true,false]
    //$fail			-	send error signal or warn signal if check fails
    //
	//returns error signal	
	function cs_phpext($php_ext,$passexists=true,$fail=true){
		$ext=extension_loaded($php_ext);
		if($passexists){
			if($ext){
				return 0;
			}
			else{
				if($fail){
					return 2;
				}
				else{
					return 1;
				}
			}
		}
		else{
			if(!$ext){
				return 0;
			}
			else{
				if($fail){
					return 2;
				}
				else{
					return 1;
				}
			}
		}
		return 0;
	}
	
    //
    //public cs_checkperms($path,$conds)
    //
    //checks the permissions of a path or file
    //$path			-	path/file location
    //$conds		-	array of conditions
    //
	//returns error signal	
	function cs_checkperms($path,$conds){
	    clearstatcache();
	    $configmod = substr(sprintf('%o', fileperms($path)), -4);
	    $soq=count($conds);
		$errors="";
	    for($i=0;$i<$soq;$i++){
			$w=substr($configmod,$conds[$i][0],1);
		    $perms=array();
		    switch($w){
				case 1:
					$perms=array("x");
				break;
				case 2:
					$perms=array("w");
				case 3:
					$perms=array("w","x");
				break;	
				case 4:
					$perms=array("r");
				break;
				case 5:
					$perms=array("r","x");
				break;
				case 6:
					$perms=array("w","r");	
				break;
				case 7:
					$perms=array("r","x","w");	
				break;
			};
			$soq=count($conds[$i][1]);
			for($j=0;$j<$soq;$j++){
			 	if($conds[$i][2]=="in"){
					if(in_array($conds[$i][1][$j],$perms)){
						$errors.=$this->cs_elvl($conds[$i][3]);
					}
				}
				else{
					if(!in_array($conds[$i][1][$j],$perms)){
						$errors.=$this->cs_elvl($conds[$i][3]);
					}				
				}
			}
		}
		if(eregi("2",$errors)){
			return 2;
		}
		else if(eregi("1",$errors)){
			return 1;
		}
		return 0;
	}
	
    //
    //public cs_checkpath($path,$returnok=true,$fail=true)
    //
    //checks to see if a file is there
    //$path			-	path/file location
    //$returnok		-	if file exists return ok (true) or err/warn (false)
    //$fail			-	if conditions not met return err (true) or warn (false)
    //
	//returns error signal	
	function cs_checkpath($path,$returnok=true,$fail=true){
	    if(is_dir($path)&&$returnok){
			return 0;
		}
		else{
			if($fail){
				return 2;
			}
			else{
				return 1;
			}
		}
	}
	
    //
    //public cs_checkwebwrite($path,$returnok=true,$fail=true)
    //
    //checks to see if a file is writable
    //$path			-	path/file location
    //$returnok		-	if file is writable return ok (true) or err/warn (false)
    //$fail			-	if conditions not met return err (true) or warn (false)
    //
	//returns error signal	
	function cs_checkwebwrite($path,$returnok=true,$fail=true){
	    if(is_writable($path)&&$returnok){
			return 0;
		}
		else{
			if($fail){
				return 2;
			}
			else{
				return 1;
			}
		}
	}	
    //
    //public cs_phpini($ini_var,$cond,$size=false)
    //
    //checks a php ini var
    //$ini_var		-	ini var
    //$conds		-	array of conditions
    //$size			-	if set to true, ini_var will be converted to a size var
    //
	//returns error signal	
	function cs_phpini($ini_var,$cond,$size=false){
		$ini_value=ini_get($ini_var);
		
		if($size){
			$ini_value=$this->cs_strtosize($ini_value);
		}
		
		return $this->cs_compare($ini_value,$cond,$size);
	}
    //
    //public cs_phpver($warn,$err)
    //
    //checks a php ini var
    //$warn			-	array of minimum php version or warning
    //$conds		-	array of minimum php versions or error
    //
	//returns error signal	
	function cs_phpver($warn,$err){
		$v_warn=false;
		$v_error=false;
		$soq=count($warn);
		for($i=0;$i<$soq;$i++){
			if(version_compare(phpversion(),$warn[$i])==-1){
				$v_warn=true;
			}
		}
		$soq=count($err);
		for($i=0;$i<$soq;$i++){
			if(version_compare(phpversion(),$err[$i])==-1){
				$v_error=true;
			}
		}
		if($v_error){
			return 2;
		}
		else if($v_warn){
			return 1;
		}
		return 0;
	}
		
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################	    
 	
    //
    //private cs_compare($value,$cond)
    //
    //compares a value to a cond array
    //$value		-	set value
    //$cond			-	conditional array
    //
	//returns error signal	
	function cs_compare($value,$cond,$size=false){
		$soq=count($cond);
		$err_level="";
		for($i=0;$i<$soq;$i++){
		 	if($size){
				$cond[$i][1]=$this->cs_strtosize($cond[$i][1]);
			}
			switch($cond[$i][0]){
				case ">=":
					if($value >= $cond[$i][1]){
						$err_level.=$this->cs_elvl($cond[$i][2]);
					}
				break;
				case ">":
					if($value > $cond[$i][1]){
						$err_level.=$this->cs_elvl($cond[$i][2]);
					}
				break;
				case "<":
					if($value < $cond[$i][1]){
						$err_level.=$this->cs_elvl($cond[$i][2]);
					}
				break;
				case "<=":
					if($value <= $cond[$i][1]){
						$err_level.=$this->cs_elvl($cond[$i][2]);
					}
				break;
				case "!=":
					if($value!=$cond[$i][1]){
						$err_level.=$this->cs_elvl($cond[$i][2]);
					}				
				break;				
				case "=":
					if($value==$cond[$i][1]){
						$err_level.=$this->cs_elvl($cond[$i][2]);
					}
				break;
				case "notset":
					if(!$value){
						$err_level.=$this->cs_elvl($cond[$i][2]);
					}						
				break;
				case "in":
					if(!eregi($cond[$i][1],$value)){
						$err_level.=$this->cs_elvl($cond[$i][2]);
					}
				break;
				case "notin":
					if(eregi($cond[$i][1],$value)){
						$err_level.=$this->cs_elvl($cond[$i][2]);
					}
				break;					
				case "set":
				default:
					if($value){
						$err_level.=$this->cs_elvl($cond[$i][2]);
					}
				break;		
			}
		}
		if(eregi("2",$err_level)){
			return 2;
		}
		else if(eregi("1",$err_level)){
			return 1;
		}
		return 0;
	}
	
    //
    //private cs_elvl($cond)
    //
    //returns the error level given a cond
    //$cond			-	error level [ok,warn,err]
    //
	//returns error signal
	function cs_elvl($cond){
		if($cond=="ok"){
			return 0;
		}
		else if($cond=="warn"){
			return 1;
		}
		else if($cond=="err"){
			return 2;
		}
		return 0;
	}
	
    //
    //private cs_strtosize($value)
    //
    //converts from a string to a size
    //$value		-	value to convert
    //
	//returns size
	function cs_strtosize($value){
		if(!preg_match('/^([0-9]+)([mk]+)$/i',$value,$matches)){
			return $value;
		}
		return (strtolower($matches[2]) == 'm' ? 1024*1024 : 1024) * (int) $matches[1];
	}	
	
}
