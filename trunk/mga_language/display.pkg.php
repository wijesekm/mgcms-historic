<?php
/**********************************************************
    display.pkg.php
    mga_language ver 0.7.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 04/13/07

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

class language_admin{
	
	var $tpl;
	
    //for xml parsing
   	var $document;
   	var $curr_tag;
   	var $tag_stack;
	   	
	function language_admin($i){
	    $file=$GLOBALS['MANDRIGO']['CONFIG']['TEMPLATE_PATH'].$GLOBALS['MANDRIGO']['CURRENTAPAGE']['DATAPATH'].$GLOBALS['MANDRIGO']['CURRENTAPAGE']['NAME'].'_'.$i.'.'.TPL_EXT;
		switch($GLOBALS["MANDRIGO"]["VARS"]["SECONDARYACTION"]){
			default:
				$this->tpl=new template();
				if(!$this->tpl->tpl_load($file,"index")||!$this->tpl->tpl_load($file,"index_item")||!$this->tpl->tpl_load($file,"install_item")){
					$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(2001,"display");
					return false;					
				}
			break;
		};
	}
	
	
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################
    
	
	//
	//public function la_display();
	//
	//Displays the page
	//
	function la_display(){
	 	$id="L";
	 	if($GLOBALS["MANDRIGO"]["VARS"]["SECONDARYACTION"]=="html"){
	 	 	$id="H";
		}
		$path=LANG_PATH;
		if($id=="H"){
			$path=HTML_PATH;
		}
		switch($GLOBALS["MANDRIGO"]["VARS"]["ACTION"]){
			case 'add':
				include($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$path.$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"].strtolower($GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"]).".inc.".PHP_EXT);
				if(!$this->la_alterlang($language_init,$language_vals,true)){
					return $this->la_overview($GLOBALS["MANDRIGO"]["LANGUAGE"]["LANG_ADDERR"],$id);	
				}
				return $this->la_overview($GLOBALS["MANDRIGO"]["LANGUAGE"]["LANG_ADDED"],$id);				 		
			break;
			case 'remove':
				$lid=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_LANGSETS,"lang_id",array(array("lang_name","=",$GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"])));
				if($lid<3){
					return $this->la_overview($GLOBALS["MANDRIGO"]["LANGUAGE"]["LANG_NOREMOVE"],$id);	
				}
				include($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$path.$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"].strtolower($GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"]).".inc.".PHP_EXT);
				if(!$this->la_alterlang($language_init,$language_vals,false)){
					return $this->la_overview($GLOBALS["MANDRIGO"]["LANGUAGE"]["LANG_DELERR"],$id);	
				}
				return $this->la_overview($GLOBALS["MANDRIGO"]["LANGUAGE"]["LANG_REMOVED"],$id);
			break;
			default:
				return $this->la_overview("",$id);					
			break;
		};

	}
	
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################
    	
	//
	//private function la_overview($msg="",$id="L");
	//
	//Displays the overview page
	//
	//INPUTS
	//$msg	-	status message to display
	//$id	-	[L,H] L is language H is html
	//
	//returns the parsed template
	function la_overview($msg="",$id="L"){
	 	$langs=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_LANGSETS,"",array(array("lang_type","=",$id),array(DB_ORDERBY,"lang_name","ASC")),"ASSOC",DB_ALL_ROWS);
		$html=array("","");
		if($id=="H"){
			$html=array("asub","html");
		}
		$this->la_xmlparse(LANGUAGE_VER_PREFIX.$GLOBALS["MANDRIGO"]["SITE"]["UPDATE_SERVER"].LANGUAGE_FILE);
		$this->document=$this->document["MANDRIGO_LANGUAGES"][0];
		$names=array();
		$soq=count($langs);
		$content="";
		for($i=0;$i<$soq;$i++){
			$names[$i]=strtolower($langs[$i]["lang_name"]);
			if($names[$i]){
			 
				//
				//Parsing
				//
				$status="";
				$keys=array_keys($this->document);
				if(in_array(mb_strtoupper($langs[$i]["lang_name"]),$keys)){
					if($this->la_versioncomp($this->document[mb_strtoupper($langs[$i]["lang_name"])][0]["VERSION"][0]["data"],$langs[$i]["lang_ver"])){
						$status=ereg_replace("{VALUE}","OK",$GLOBALS["MANDRIGO"]["HTML"]["ACRONYM"]);
						$status=ereg_replace("{ATTRIB}","title=\"".$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_UP_TO_DATE"]."\"",$status);
					}
					else{
						$status=ereg_replace("{VALUE}","NU",$GLOBALS["MANDRIGO"]["HTML"]["ACRONYM"]);
						$status=ereg_replace("{ATTRIB}","title=\"".$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_NEEDS_UPDATING"]."\"",$status);
					}
				}
				else{
						$status=ereg_replace("{VALUE}","U",$GLOBALS["MANDRIGO"]["HTML"]["ACRONYM"]);
						$status=ereg_replace("{ATTRIB}","title=\"".$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_UNKNOWN"]."\"",$status);
				}
				
				$new_tpl=new template();
				$new_tpl->tpl_load($this->tpl->tpl_return("index_item"),"item",false);

				$rurl=$this->la_genlink(array("pa",$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["NAME"],$html[0],$html[1],"pkg",$langs[$i]["lang_name"],"a","remove"),"X",true,$GLOBALS["MANDRIGO"]["LANGUAGE"]["ADMIN_REMOVE"]);
				if($langs[$i]["lang_id"]<3){
					$rurl="";
				}
				$parse=array("ID",$langs[$i]["lang_id"],
							 "NAME",$langs[$i]["lang_name"],
							 "STATUS",$status,
							 "REMOVE_URL",$rurl);
				$new_tpl->tpl_parse($parse,"item",1,false);
				$content.=$new_tpl->tpl_return("item");			
			}
		}
		$path=LANG_PATH;
		if($id=="H"){
			$path=HTML_PATH;
		}
		$upackages=scandir($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$path.$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]);
		$soq=count($upackages);
		$string2="";
		for($i=0;$i<$soq;$i++){
		 	$upackages[$i]=ereg_replace(".inc.".PHP_EXT,"",$upackages[$i]);
			if(!in_array($upackages[$i],$names)&&$upackages[$i]!="."&&$upackages[$i]!=".."){
				$tpl_item=new template();
				$tpl_item->tpl_load($this->tpl->tpl_return("install_item"),"itemi",false);
				$parse=array("NAME",$upackages[$i],"INSTALL",$this->la_genlink(array("pa",$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["NAME"],$html[0],$html[1],"pkg",$upackages[$i],"a","add"),"X"));
				$tpl_item->tpl_parse($parse,"itemi",1,false);
				$string2.=$tpl_item->tpl_return("itemi");
			}
		}		
		
		$this->tpl->tpl_parse(array("LANGUAGES",$content,"MSG",$msg,"ILANGUAGES",$string2),"index",1,false);
		return $this->tpl->tpl_return("index");
	}
	
	//
	//private la_versioncomp($reference,$local);
	//
	//compares the $local version to the $reference
	//
	//INPUTS:
	//$reference	-	reference version
	//$local		-	local version
	//
	//returns true if the local >= the reference or false otherwise
	function la_versioncomp($reference,$local){
		$reference=explode(".",$reference);
		$local=explode(".",$local);
		if($local[0]<$reference[0]){
			return false;
		}
		else{
			if($local[1]<$reference[1]){
				return false;
			}
			else{
				if($local[2]<$reference[2]){
					return false;
				}
				else{
					if($local[3]<$reference[3]){
						return false;
					}
				}
			}
		}
		return true;
	}
	
	
	//
	//private la_alterlang($init_array,$lang_array,$add=false);
	//
	//adds a language to the db
	//
	//INPUTS:
	//$init_array	-	array of language init data
	//$lang			-	array of language values
	//$add			-	if set to true we will add, otherwise we will remove
	//
	//returns true on success or false on fail	
	function la_alterlang($init_array,$lang_array,$add=false){
	 	if($add){
			if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.TABLE_LANGSETS,array($init_array["lang_name"],$init_array["lang_type"],$init_array["lang_charset"],$init_array["lang_encoding"],$init_array["lang_maintainer"],$init_array["lang_email"],$init_array["lang_web"],$init_array["lang_ver"]),array("lang_name","lang_type","lang_charset","lang_encoding","lang_maintainer","lang_email","lang_web","lang_ver"))){
				return false;
			}
			$langid=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_LANGSETS,"lang_id",array(array("lang_name","=",$init_array["lang_name"])));
			if(!$langid){
				return false;
			}
			if(!$GLOBALS["MANDRIGO"]["DB"]->db_dbcommands(DB_CREATE,DB_TABLE,"",TABLE_PREFIX.TABLE_LANG.$langid,$GLOBALS["MANDRIGO"]["LANG_INIT"]["STRUCTURE"],$GLOBALS["MANDRIGO"]["LANG_INIT"]["KEYS"])){
				return false;
			}
			$solang=count($lang_array);
			for($k=0;$k<$solang;$k++){
				if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.TABLE_LANG.$langid,array($lang_array[$k][0],$lang_array[$k][1],$lang_array[$k][2],$lang_array[$k][3]),array("lang_callname","lang_value","lang_corename","lang_appid"))){
					return false;
				}				
			}
		}
		else{
			$langid=$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_LANGSETS,"lang_id",array(array("lang_name","=",$init_array["lang_name"])));
			if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_REMOVE,TABLE_PREFIX.TABLE_LANGSETS,"",array(array("lang_id","=",$langid)))){
				return false;
			}
			if(!$GLOBALS["MANDRIGO"]["DB"]->db_dbcommands(DB_DROP,DB_TABLE,"",TABLE_PREFIX.TABLE_LANG.$langid)){
				return false;
			}				
		}
		return true;
	}
	
	//
	//private la_genlink($url_data,$name,$conf=false,$conf_msg="");
	//
	//generates a link
	//
	//INPUTS:
	//$url_data		-	array of url data [format: array("var","value")]
	//$name			-	link name
	//$conf			-	use javascript conformation box
	//$conf_msg		-	conformation message
	//
	//returns the link
    function la_genlink($url_data,$name,$conf=false,$conf_msg=""){
      	$link='';
 		if($GLOBALS['MANDRIGO']['SITE']['URL_FORMAT']==1){
			$url=$GLOBALS['MANDRIGO']['SITE']['ADMIN_URL'].$GLOBALS['MANDRIGO']['SITE']['ADMIN_NAME']."/";
		}
		else{
		  	$url=$GLOBALS['MANDRIGO']['SITE']['ADMIN_URL'].$GLOBALS['MANDRIGO']['SITE']['ADMIN_NAME']."?";
		}  
		$soq=count($url_data);
		$i=0;
		while($i<$soq){
		 	if($url_data[$i]){
				if($GLOBALS['MANDRIGO']['SITE']['URL_FORMAT']==1){
					$url.=$url_data[$i]."/".$url_data[$i+1];
					if($i<$soq){
						$url.="/";
					}
				}
				else{
					$url.=$url_data[$i]."=".$url_data[$i+1];
					if($i<$soq){
						$url.="&amp;";
					}
				}				
			}
			$i+=2;
		}
		if($conf){
			$url="javascript:if (confirm('$conf_msg')) window.location='$url';";
		}
		$link=ereg_replace("{ATTRIB}","href=\"".$url."\"",$GLOBALS["MANDRIGO"]["HTML"]["A"]);
		return ereg_replace("{VALUE}",$name,$link);
	}	
	
    //
    //private function la_xmlparse($path)
    //
    //Loads and parses an xml file
    //
    //INPUTS:
    //$path	-	path to the xml file (default: )   
    //
    //returns true on success or false on fail
	function la_xmlparse($path){
		$parser=xml_parser_create();
	 	$this->document = array();
		$this->currTag =& $this->document;
		$this->tagStack = array();
	    xml_set_object($parser, $this);
	    xml_set_character_data_handler($parser, 'la_datahandler');
	    xml_set_element_handler($parser, 'la_starthandler', 'la_endhandler');
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
    //private function la_starthandler($parser, $name, $attribs)
    //
    //Required funtion to parse the beginning of each xml tag
    //
    //INPUTS:
    //$parser	-	xml parser(default: )  
	//$name		-	xml tag name (default: ) 
    //$attribs	-	xml tag attributes (default: )
	function la_starthandler($parser, $name, $attribs){
	    if(!isset($this->currTag[$name])){
			$this->currTag[$name] = array();
		}
	      
	    $newTag = array();
	    if(!empty($attribs)){
			$newTag['attr'] = $attribs;	
		}
	    array_push($this->currTag[$name], $newTag);
	      
		$t =& $this->currTag[$name];
		$this->currTag =& $t[count($t)-1];
		array_push($this->tagStack, $name);
	}

    //
    //private function la_datahandler($parser, $data)
    //
    //Required funtion to parse the middle of each xml tag
    //
    //INPUTS:
    //$parser	-	xml parser(default: )  
	//$data		-	xml tag data (default: ) 	  
	function la_datahandler($parser, $data){
	    $data = trim($data); 
	    if(!empty($data)){
	        if(isset($this->currTag['data'])){
				$this->currTag['data'] .= $data;	
			}
	        else{
				$this->currTag['data'] = $data;
			}
	    }
	}
	
    //
    //private function la_endhandler($parser, $name)
    //
    //Required funtion to parse the end of each xml tag
    //
    //INPUTS:
    //$parser	-	xml parser(default: )  
	//$name		-	xml tag name (default: )  
	function la_endhandler($parser, $name){
	    $this->currTag =& $this->document;
	    array_pop($this->tagStack);
	      
	    for($i = 0; $i < count($this->tagStack); $i++){
	        $t =& $this->currTag[$this->tagStack[$i]];
	        $this->currTag =& $t[count($t)-1];
	    }
	}	
}