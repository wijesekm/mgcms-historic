<?php
/**********************************************************
    filter.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/20/07

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

define("FILTER_PATH","/filters/");

class mfilter{
	
	var $cur_filter;
	var $str;
	var $filter_size;
	
	function mfilter($name){
		$this->change_filter($name);
	}
	function fi_change($name){
	 	$file=$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"].$name.".filter.".PHP_EXT;
		if(file_exists($file)&&!is_dir($file)){
			if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
				if(!include_once($file)){
					return false;
				}
			}
			else{
				if(!(@include_once($file))){
					return false;
				}
			}
		}
		else{
			return false;
		}
		$this->cur_filter=$filter_list;
		$filter=array();
		$this->filter_size=count(this->cur_filter);
		return true;		
	}
	function fi_settext($stri){
		$this->str=$stri;
	}
	function fi_returntext(){
		return $this->str;
	}
	function fi_filter($mode,$censor=""){
	 	$found_something=false;
		for($i=0;$i<$this->filter_size;$i++){
			switch($mode){
				case 1:
					if($this->fi_search($this->cur_filter[$i][0],$this->cur_filter[$i][1])){
						$found_something=true;
					}
				break;
				case 2:
					if($this->fi_search($this->cur_filter[$i][0],$this->cur_filter[$i][1])){
						$found_something=true;
						$this->fi_destroy($this->cur_filter[$i][0],"",$this->cur_filter[$i][1]);
					}				
				break;
				default:
					return false;
				break;
			};
		}
		return $found_something
	}
	function fi_search($item,$case){
	 	if($case){
			return @mb_ereg($item,$this->str);		
		}
		return @mb_eregi($item,$this->str);
	}
	function fi_destroy($item,$censor="",$case){
		if($case){
			return @mg_ereg_replace($item,$censor,$this->str);			
		}
		return @mg_eregi_replace($item,$censor,$this->str);	
	}
}