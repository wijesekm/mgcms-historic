<?php
/**********************************************************
    display.pkg.php
    mga_package ver 0.7.0
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

class package_admin{
	
	var $tpl;
	
    //for xml parsing
   	var $document;
   	var $curr_tag;
   	var $tag_stack;
	   	
	function package_admin($i){
	    $file=$GLOBALS['MANDRIGO']['CONFIG']['TEMPLATE_PATH'].$GLOBALS['MANDRIGO']['CURRENTAPAGE']['DATAPATH'].$GLOBALS['MANDRIGO']['CURRENTAPAGE']['NAME'].'_'.$i.'.'.TPL_EXT;

		switch($GLOBALS["MANDRIGO"]["VARS"]["ACTION"]){
			default:
				$this->tpl=new template();
				if(!$this->tpl->tpl_load($file,"index")||!$this->tpl->tpl_load($file,"index_item")||!$this->tpl->tpl_load($file,"install_item")){
					$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(2000,"display");
					return false;					
				}
			break;
		};
		return true;
	}
	
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################
    
	
	//
	//public function pa_display();
	//
	//Displays the page
	//
	function pa_display(){
	 	$string="";
	 	$msg="";
		switch($GLOBALS["MANDRIGO"]["VARS"]["ACTION"]){
		 	case "add":
		 		include($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"].SETUP_FOLDER.SETUP_NAME);
		 		if($pkg["name"]){
					if(!$this->pa_changelog($pkg["errors"],true)){
						$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_LOGERROR"];	
					}
					else if(!$this->pa_updatelang($pkg["languages"],$pkg_language_install,$pkg["id"],true)){
						$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["PKG_LANGERROR"];	
					}
					else if(!$this->pa_updatedb($pkg["tables"],$pkg_table_install,true)){
						$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_DBERROR"];	
					}
					else{
						$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.TABLE_PACKAGES,array($pkg["id"],$pkg["name"],$pkg["no_load_error"],$pkg["version"],$pkg["maintainer"],$pkg["email"],$pkg["website"],($pkg["enabled"]==true)?"E":"D"),array("pkg_id","pkg_name","pkg_nlerror","pkg_ver","pkg_maintainer","pkg_email","pkg_web","pkg_status"));
						$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_PACKAGEINSTALLED"];
					}
				}
				$string=$this->pa_genoverview($msg);
		 	break;
		 	case "remove":
		 		if($GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"]&&($GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"]!=MANDRIGO_PKGID&&$GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"]!=PCONTENT_ID&&$GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"]!=SELF_ID)){
		 		 	$package=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_PACKAGES,"",array(array("pkg_id","=",$GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"])));
		 			include($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$package["pkg_name"].SETUP_FOLDER.SETUP_NAME);
		 			if($pkg["name"]){
						if(!$this->pa_changelog($pkg["errors"])){
							$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_LOGERROR"];	
						}
						else if(!$this->pa_updatelang($pkg["languages"],$pkg_language_install,0,false)){
							$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["PKG_LANGERROR"];		
						}
						else if(!$this->pa_updatedb($pkg["tables"],$pkg_table_install,false)){
							$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_DBREMOVEERROR"];
						}
						else{
		 					$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_REMOVE,TABLE_PREFIX.TABLE_PACKAGES,"",array(array("pkg_id","=",$GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"])));
							$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_REMOVED"];
						}
					}
				}
		 		$string=$this->pa_genoverview($msg);
		 	break;//PK_PACKAGE
		 	case "disable":
		 		if($GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"]&&$GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"]>0){
					$package=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_PACKAGES,"",array(array("pkg_id","=",$GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"])));
					if($package["pkg_status"]=="E"){
						$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_PACKAGES,array(array("pkg_status","D")),array(array("pkg_id","=",$GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"])));
						$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_PACKAGEDISABLED"];
					}
					else if($package["pkg_status"]=="D"){
						$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_UPDATE,TABLE_PREFIX.TABLE_PACKAGES,array(array("pkg_status","E")),array(array("pkg_id","=",$GLOBALS["MANDRIGO"]["VARS"]["PACKAGE"])));						
						$msg=$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_PACKAGEENABELED"];
					}
				}
				$string=$this->pa_genoverview($msg);
		 	break;
			default:
				$string=$this->pa_genoverview();
			break;
		};
		return $string;
	}
	
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################
    
	
	//
	//private function pa_genoverview();
	//
	//Generates the package overview
	//
	function pa_genoverview($msg=""){
		$string="";
		$this->pa_xmlparse(PACKAGE_VER_PREFIX.$GLOBALS["MANDRIGO"]["SITE"]["UPDATE_SERVER"].PACKAGE_FILE);
		$this->document=$this->document["MANDRIGO_PACKAGES"][0];
		$packages=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_PACKAGES,"",array(array(DB_ORDERBY,"pkg_name","ASC")),"ASSOC",DB_ALL_ROWS);
		$soq=count($packages);
		$names=array();
		for($i=0;$i<$soq;$i++){
			$names[$i]=$packages[$i]["pkg_name"];				 
				 	
			//
			//Parsing
			//
			$status="";
			$keys=array_keys($this->document);
			if(in_array(mb_strtoupper($packages[$i]["pkg_name"]),$keys)){
				if($this->pa_versioncomp($this->document[mb_strtoupper($packages[$i]["pkg_name"])][0]["VERSION"][0]["data"],$packages[$i]["pkg_ver"])){
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
			if($packages[$i]["pkg_status"]=="E"){
					$dname=ereg_replace("{VALUE}","D",$GLOBALS["MANDRIGO"]["HTML"]["ACRONYM"]);
					$dname=ereg_replace("{ATTRIB}","title=\"".$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_DISABLE"]."\"",$dname);
			}
			else{
					$dname=ereg_replace("{VALUE}","E",$GLOBALS["MANDRIGO"]["HTML"]["ACRONYM"]);
					$dname=ereg_replace("{ATTRIB}","title=\"".$GLOBALS["MANDRIGO"]["LANGUAGE"]["PK_ENABLE"]."\"",$dname);
			}
			//
			//Display
			//

			$tpl_item=new template();
			$tpl_item->tpl_load($this->tpl->tpl_return("index_item"),"item",false);
			if($packages[$i]["pkg_id"]<1){
				$parse=array("ID",$packages[$i]["pkg_id"],"NAME",$packages[$i]["pkg_name"],"STATUS",$status
						,"DISABLE_URL",""
						,"REMOVE_URL","");
			}
			else{
				$parse=array("ID",$packages[$i]["pkg_id"],"NAME",$packages[$i]["pkg_name"],"STATUS",$status
						,"DISABLE_URL",$this->pa_genlink(array("pa",$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["NAME"],"pkg",$packages[$i]["pkg_id"],"a","disable"),$dname)
						,"REMOVE_URL",$this->pa_genlink(array("pa",$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["NAME"],"pkg",$packages[$i]["pkg_id"],"a","remove"),"X",true,$GLOBALS["MANDRIGO"]["LANGUAGE"]["ADMIN_REMOVE"]));	
			}
			$tpl_item->tpl_parse($parse,"item",1,false);
			$string.=$tpl_item->tpl_return("item");
		}
		$upackages=scandir($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"]);
		$soq=count($upackages);
		$string2="";
		for($i=0;$i<$soq;$i++){
		 	//echo !in_array($upackages[$i],$names);
			if(!in_array($upackages[$i],$names)&&$upackages[$i]!="."&&$upackages[$i]!=".."&&$upackages[$i]!=LANG_LOCATION&&$upackages[$i]!=HTML_LOCATION){
				$tpl_item=new template();
				$tpl_item->tpl_load($this->tpl->tpl_return("install_item"),"itemi",false);
				$parse=array("NAME",$upackages[$i],"INSTALL",$this->pa_genlink(array("pa",$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["NAME"],"pkg",$upackages[$i],"a","add"),"X"));
				$tpl_item->tpl_parse($parse,"itemi",1,false);
				$string2.=$tpl_item->tpl_return("itemi");
			}
		}
		$this->tpl->tpl_parse(array("PACKAGES",$string,"IPACKAGES",$string2,"MSG",$msg),"index",1,false);
		return $this->tpl->tpl_return("index"); 
	}
	
	//
	//private pa_versioncomp($reference,$local);
	//
	//compares the $local version to the $reference
	//
	//INPUTS:
	//$reference	-	reference version
	//$local		-	local version
	//
	//returns true if the local >= the reference or false otherwise
	function pa_versioncomp($reference,$local){
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
			}
		}
		return true;
	}
	
	//
	//private pa_genlink($url_data,$name,$conf=false,$conf_msg="");
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
    function pa_genlink($url_data,$name,$conf=false,$conf_msg=""){
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
			if($GLOBALS['MANDRIGO']['SITE']['URL_FORMAT']==1){
				$url.=$url_data[$i]."/".$url_data[$i+1];
				$i+=2;
				if($i<$soq){
					$url.="/";
				}
			}
			else{
				$url.=$url_data[$i]."=".$url_data[$i+1];
				$i+=2;
				if($i<$soq){
					$url.="&amp;";
				}
			}
		}
		if($conf){
			$url="javascript:if (confirm('$conf_msg')) window.location='$url';";
		}
		$link=ereg_replace("{ATTRIB}","href=\"".$url."\"",$GLOBALS["MANDRIGO"]["HTML"]["A"]);
		return ereg_replace("{VALUE}",$name,$link);
	}
	
	//
	//private pa_updatelang($keys,$lang,$pid,$add=false);
	//
	//updates the lang table
	//
	//INPUTS:
	//$keys			-	lang tables to update
	//$lang			-	array of lang data
	//$pid			-	package id
	//$add			-	if set to true we will add, otherwise we will remove
	//
	//returns true on success or false on fail	
	function pa_updatelang($keys,$lang,$pid,$add=false){
		$solang=count($keys);
		for($i=0;$i<$solang;$i++){
			$soj=count($lang[$keys[$i]]);
			$lang_data=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_LANGSETS,"",array(array("lang_name","=",$keys[$i])));
			if(!$lang_data){
				return false;
			}
			for($j=0;$j<$soj;$j++){
				if($add){
					if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.TABLE_LANG.$lang_data["lang_id"],array($lang[$keys[$i]][$j][0],$lang[$keys[$i]][$j][1],"mg_packages",$pid),array("lang_callname","lang_value","lang_corename","lang_appid"))){
						return false;
					}		
				}
				else{
					if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_REMOVE,TABLE_PREFIX.TABLE_LANG.$lang_data["lang_id"],"",array(array("lang_callname","=",$lang[$keys[$i]][$j][0])))){
						return false;
					}
				}
			}
		}
		return true;
	}
	
	//
	//private pa_updatedb($keys,$table,$add=false);
	//
	//updates the lang table
	//
	//INPUTS:
	//$keys			-	database tables to add
	//$lang			-	array of database table data
	//$add			-	if set to true we will add, otherwise we will remove
	//
	//returns true on success or false on fail		
	function pa_updatedb($keys,$table,$add=false){
		$sodb=count($keys);
		for($i=0;$i<$sodb;$i++){
		 	if($add){
				if(!$GLOBALS["MANDRIGO"]["DB"]->db_dbcommands(DB_CREATE,DB_TABLE,"",TABLE_PREFIX.$keys[$i],$table[$keys[$i]]["struct"],$table[$keys[$i]]["keys"])){
					return false;
				}
				$soj=count($table[$keys[$i]]["records"]);
				for($j=0;$j<$soj;$j++){
				 	$tmp=$table[$keys[$i]]["records"][$j];
					if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.$keys[$i],$tmp[1],$tmp[0])){
						return false;
					}
				}
			}
			else{
				if(!$GLOBALS["MANDRIGO"]["DB"]->db_dbcommands(DB_DROP,DB_TABLE,"",TABLE_PREFIX.$keys[$i])){
					return false;
				}
			}
		}
		return true;
	}
	
	//
	//private pa_changelog($array,$add=false);
	//
	//updates the log xml files
	//
	//INPUTS:
	//$array		-	array of log data
	//$add			-	if set to true we will add, otherwise we will remove
	//
	//returns true on success or false on fail		
	function pa_changelog($array,$add=false){
		$keys=array_keys($array);
		$soi=count($keys);
		for($i=0;$i<$soi;$i++){
			$soj=count($array[$keys[$i]]);
			$this->pa_xmlparse($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"].LOG_SETUP.$keys[$i].".".XML_EXT);
			if(!$this->document["ERROR_LOG"][0]["MSG"]){
				return false;
			}
			$this->document=$this->document["ERROR_LOG"][0]["MSG"];
			if($array[$keys[$i]][0][0]){
				for($j=0;$j<$soj;$j++){
					$this->pa_editlogconf($array[$keys[$i]][$j][0],$add,$array[$keys[$i]][$j][1]);		
				}
				if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
					$f=fopen($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"].LOG_SETUP.$keys[$i].".".XML_EXT,"w");
					if(!$f){
						return false;
					}
				}
				else{
					$f=@fopen($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"].LOG_SETUP.$keys[$i].".".XML_EXT,"w");
					if(!$f){
						$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(2001,"display");
						return false;
					}
				}
				
				$xml_doc=
				"<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>
				<error_log>		
				";
		
				$som=count($this->document);
				for($m=0;$m<$som;$m++){
				 	$tmp=$this->document[$m];
				 	$tmp["VALUE"][0]["data"]=ereg_replace(">","&gt;",$tmp["VALUE"][0]["data"]);
				 	$tmp["VALUE"][0]["data"]=ereg_replace("<","&lt;",$tmp["VALUE"][0]["data"]);
					$xml_doc.="
						<msg>
							<id>{$tmp["ID"][0]["data"]}</id>
							<value>
								{$tmp["VALUE"][0]["data"]}
							</value>
						</msg>
					";
				}
		
				$xml_doc.="</error_log>";
				if(!fwrite($f,$xml_doc)){
					return false;
				}
				fclose($f);
			}
		}
		return true;
	}
	
	//
	//private pa_changelog($num,$add=false,$error="");
	//
	//updates the xml array
	//
	//INPUTS:
	//$num			-	error number
	//$add			-	if set to true we will add, otherwise we will remove
	//$error		-	error string
	function pa_editlogconf($num,$add=false,$error=""){
	 	$string="";	

		$tmp=$this->document;
		if($add){
			$sotemp=count($tmp);
			$new_array=array();
			$part=true;
			for($k=0;$k<$sotemp;$k++){
				if((int)$tmp[$k+1]["ID"][0]["data"]>(int)$num&&$part){
				 	$new_array[$k]=$tmp[$k];
					$new_array[$k+1]["ID"][0]["data"]=$num;
					$new_array[$k+1]["VALUE"][0]["data"]=$error;
					$part=false;
				}
				else if($part){
					$new_array[$k]=$tmp[$k];
					if($k+1>=$sotemp){
						$new_array[$k+1]["ID"][0]["data"]=$num;
						$new_array[$k+1]["VALUE"][0]["data"]=$error;						
					}
				}
				else{
					$new_array[$k+1]=$tmp[$k];	
				}
			}
			$this->document=$new_array;		
		}
		else{
			$sotemp=count($tmp);
			$new_array=array();
			$part=true;
			for($k=0;$k<$sotemp;$k++){
				if($tmp[$k]["ID"][0]["data"]==(string)$num){
					$part=false;
				}
				else if($part){
					$new_array[$k]=$tmp[$k];
				}
				else{
					$new_array[$k-1]=$tmp[$k];	
				}
			}
			$this->document=$new_array;
		}		
	}

    //
    //private function pa_xmlparse($path)
    //
    //Loads and parses an xml file
    //
    //INPUTS:
    //$path	-	path to the xml file (default: )   
    //
    //returns true on success or false on fail
	function pa_xmlparse($path){
		$parser=xml_parser_create();
	 	$this->document = array();
		$this->currTag =& $this->document;
		$this->tagStack = array();
	    xml_set_object($parser, $this);
	    xml_set_character_data_handler($parser, 'pa_datahandler');
	    xml_set_element_handler($parser, 'pa_starthandler', 'pa_endhandler');
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
    //private function pa_starthandler($parser, $name, $attribs)
    //
    //Required funtion to parse the beginning of each xml tag
    //
    //INPUTS:
    //$parser	-	xml parser(default: )  
	//$name		-	xml tag name (default: ) 
    //$attribs	-	xml tag attributes (default: )
	function pa_starthandler($parser, $name, $attribs){
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
    //private function pa_datahandler($parser, $data)
    //
    //Required funtion to parse the middle of each xml tag
    //
    //INPUTS:
    //$parser	-	xml parser(default: )  
	//$data		-	xml tag data (default: ) 	  
	function pa_datahandler($parser, $data){
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
    //private function pa_endhandler($parser, $name)
    //
    //Required funtion to parse the end of each xml tag
    //
    //INPUTS:
    //$parser	-	xml parser(default: )  
	//$name		-	xml tag name (default: )  
	function pa_endhandler($parser, $name){
	    $this->currTag =& $this->document;
	    array_pop($this->tagStack);
	      
	    for($i = 0; $i < count($this->tagStack); $i++){
	        $t =& $this->currTag[$this->tagStack[$i]];
	        $this->currTag =& $t[count($t)-1];
	    }
	}
}