<?php
/**********************************************************
	xml.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 04/12/07

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

class mxml{

    //for xml parsing
   	var $document;
   	var $curr_tag;
   	var $tag_stack;	

	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################
	
	function mxml_read($data,$ispath=true){
		if($ispath){
			$this->mxml_parsefile($data);
		}
		else{
			return false;
		}
		return $this->document;
	}
	function mxml_write($xml_array){
		return $this->xml_writearray($xml_array);
	}

	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################
	
	
	function mxml_writearray($xml_array){
		$keys=array_keys($xml_array);
		$soq=count($keys);
		$xml_string="";
		for($i=0;$i<$soq;$i++){
		 	if($keys[$i]!="attr"&&$keys[$i]!="data"){
			 	$cur=$xml_array[$keys[$i]][$i];
				$xml_string.='<'.$keys[$i].' '.$this->mxml_writeattr($cur['attr']);
				$cur_keys=array_keys($cur);
				if($cur_keys[0]==""||($cur_keys[0]=="attr"&&$cur_keys[1]=="")){
					$xml_string.="/>";
				}
				else if($cur["data"]){
					$xml_string.=">".$cur["data"]."</".$keys[$i].">";
				}
				else{
					$xml_string.=$this->xml_writearray($xml_array[strtoupper($keys[$i])][$i]);		
				}	
			}
		}
		return $xml_string;
	}

	function mxml_writeattr($attr){
		$keys=array_keys($attr);
		$soa=count($keys);
		if(!$soa){
			return false;
		}
		$string='';
		for($j=0;$j<$soa;$j++){
			$string.=$keys[$i].'='.$attr[$keys[$i]].' ';
		}
		return $string;
	}
	
    //
    //private function xml_parsefile($path)
    //
    //Loads and parses an xml file
    //
    //INPUTS:
    //$path	-	path to the xml file (default: )   
    //
    //returns true on success or false on fail
	function mxml_parsefile($path){
		$parser=xml_parser_create();
	 	$this->document = array();
		$this->curr_tag =& $this->document;
		$this->tag_stack = array();
	    xml_set_object($parser, $this);
	    xml_set_character_data_handler($parser, 'mxml_datahandler');
	    xml_set_element_handler($parser, 'mxml_starthandler', 'mxml_endhandler');
	   	if(!($fp = @fopen($path, "r"))){
	           return false;
	    }
		while($data = fread($fp, 4096)){
	    	if(!xml_parse($parser, $data, feof($fp))){
	        	return false;
			}
		}
	    fclose($fp);
	   	xml_parser_free($parser);
	   	return true;
	}
	
    //
    //private function xml_starthandler($parser, $name, $attribs)
    //
    //Required funtion to parse the beginning of each xml tag
    //
    //INPUTS:
    //$parser	-	xml parser(default: )  
	//$name		-	xml tag name (default: ) 
    //$attribs	-	xml tag attributes (default: )
	function mxml_starthandler($parser, $name, $attribs){
	    if(!isset($this->curr_tag[$name])){
			$this->curr_tag[$name] = array();
		}
	      
	    $newTag = array();
	    if(!empty($attribs)){
			$newTag['attr'] = $attribs;	
		}
	    array_push($this->curr_tag[$name], $newTag);
	      
		$t =& $this->curr_tag[$name];
		$this->curr_tag =& $t[count($t)-1];
		array_push($this->tag_stack, $name);
	}

    //
    //private function xml_datahandler($parser, $data)
    //
    //Required funtion to parse the middle of each xml tag
    //
    //INPUTS:
    //$parser		-	xml parser(default: )  
	//$data		-	xml tag data (default: ) 	  
	function mxml_datahandler($parser, $data){
	    $data = trim($data); 
	    if(!empty($data)){
	        if(isset($this->curr_tag['data'])){
				$this->curr_tag['data'] .= $data;	
			}
	        else{
				$this->curr_tag['data'] = $data;
			}
	    }
	}
	
    //
    //private function xml_endhandler($parser, $name)
    //
    //Required funtion to parse the end of each xml tag
    //
    //INPUTS:
    //$parser		-	xml parser(default: )  
	//$name		-	xml tag name (default: )  
	function mxml_endhandler($parser, $name){
	    $this->curr_tag =& $this->document;
	    array_pop($this->tag_stack);
	      
	    for($i = 0; $i < count($this->tag_stack); $i++){
	        $t =& $this->curr_tag[$this->tag_stack[$i]];
	        $this->curr_tag =& $t[count($t)-1];
	    }
	}

}