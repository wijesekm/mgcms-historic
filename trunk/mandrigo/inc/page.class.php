<?php
/**********************************************************
    page.class.php
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

class page{
	
	var $page_parse_vars;
	
	function page(){
        $this->page_parse_vars = array(
            "SITE_NAME",$GLOBALS["MANDRIGO"]["SITE"]["SITE_NAME"],
            "SITE_URL",$GLOBALS["MANDRIGO"]["SITE"]["SITE_URL"],
            "IMG_URL",$GLOBALS["MANDRIGO"]["SITE"]["IMG_URL"],
            "SERVER_DATE",date($GLOBALS["MANDRIGO"]["SITE"]["DATE_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"]),
            "SERVER_TIME",date($GLOBALS["MANDRIGO"]["SITE"]["TIME_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["SERVERTIME"]),
            "GMT_DATE",date($GLOBALS["MANDRIGO"]["SITE"]["DATE_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["GMT"]),
            "GMT_TIME",date($GLOBALS["MANDRIGO"]["SITE"]["TIME_FORMAT"],$GLOBALS["MANDRIGO"]["SITE"]["GMT"]),
            "WEBMASTER_NAME",$GLOBALS["MANDRIGO"]["SITE"]["WEBMASTER_NAME"],
            "WEBMASTER_EMAIL",$GLOBALS["MANDRIGO"]["SITE"]["WEBMASTER_EMAIL"],
            "SITE_LAST_UPDATED",$GLOBALS["MANDRIGO"]["SITE"]["LAST_UPDATED"],
            "MG_VER",$GLOBALS["MANDRIGO"]["SITE"]["MANDRIGO_VER"],
            "INDEX_NAME",$GLOBALS["MANDRIGO"]["SITE"]["INDEX_NAME"],
            "CUSER_ID",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["UID"],
            "CUSER_FNAME",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["FNAME"],
            "CUSER_MNAME",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["MNAME"],
            "CUSER_LNAME",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["LNAME"],
            "CUSER_LANG",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["LANGUAGE"],
            "CUSER_DATE",date($GLOBALS["MANDRIGO"]["SITE"]["DATE_FORMAT"],$GLOBALS["MANDRIGO"]["CURRENTUSER"]["TIME"]),
            "CUSER_TIME",date($GLOBALS["MANDRIGO"]["SITE"]["TIME_FORMAT"],$GLOBALS["MANDRIGO"]["CURRENTUSER"]["TIME"]),
            "CPAGE_FNAME",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["FULLNAME"],
            "CPAGE_ANAME",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],
            "CPAGE_ID",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]
        );
        if(count($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["VARS"])%2==0){
            $this->page_parse_vars=$this->pg_appendarray($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["VARS"],$this->page_parse_vars);
        }
	}
	
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	    
 	
    //
    //public function pg_display()
    //
    //generates content for the site then parses out the main site template
    //
	//returns the main site template 
	function pg_display(){
	 	//
	 	//Checks for redir
	 	//
	 	if($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["PAGE_REDIR"]){
			header("Location: ".$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["PAGE_REDIR"]);
			die();
		}
	 	//
	 	//Checks for cache
	 	//
	 	if($GLOBALS["MANDRIGO"]["SITE"]["CACHE_PAGES"]){
			$cache=new cache();
			$content=$cache->cache_rpage();
			if($content==false){
				if(!$GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
					$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(6,"display");
				}			
			}
			else if($content!=3&&$content!=2){
				$tpl_mainsite=new template();
				$tpl->tpl_load("<!--MG_TEMPLATE_START_main-->".$content."<!--MG_TEMPLATE_END_main-->","main",false);
				return $tpl->tpl_return("main");
			}
		}
	 	
	 	//
	 	//Load the main site template
	 	//
		$tpl_mainsite=new template();
		if(!$tpl_mainsite->tpl_load($GLOBALS["MANDRIGO"]["CONFIG"]["TEMPLATE_PATH"].TPL_MAINSITE,"main")){
			if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
				die();
			}
			else{
				$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(200,"core");
				die($GLOBALS["MANDRIGO"]["ELOG"]["HTMLHEAD"].$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"].$GLOBALS["MANDRIGO"]["ELOG"]["HTMLBODY"].
	           		$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport().$GLOBALS["MANDRIGO"]["ELOG"]["HTMLEND"]);

			}
		}
		
		//
		//Main IF Statment
		//
		$bypass=(string)$GLOBALS["MANDRIGO"]["VARS"]["BYPASS_CODE"]===(string)$GLOBALS["MANDRIGO"]["SITE"]["BYPASS_CODE"];
		if($GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_getstatus()!=0){
        	if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
				die();
			}
			else{
				$this->page_parse_vars=$this->pg_appendarray(array("CONTENT",$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport(),"PAGE_TITLE",$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"]),$this->page_parse_vars);			
			}
		}
		else if($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["STATUS"]===1||($bypass&&$GLOBALS["MANDRIGO"]["SITE"]["BYPASS_CODE"])){
			$content=$this->pg_gencontent();
			if(!$content&&!$GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
				$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(4,"display");	
			}
			if($GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_getstatus()!=0&&!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
				$this->page_parse_vars=$this->pg_appendarray(array("CONTENT",$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport(),"PAGE_TITLE",$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"]),$this->page_parse_vars);			
			}
			else{
               $this->page_parse_vars=$this->pg_appendarray(array("CONTENT",$content,"PAGE_TITLE",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["TITLE"]),$this->page_parse_vars);
			}
		}
		else{
        	$tpl_off=new template();
            $tpl_off->tpl_load($GLOBALS["MANDRIGO"]["CONFIG"]["TEMPLATE_PATH"].TPL_OFFPAGE,"main");
			$content=$tpl_off->tpl_return("main");
			$tpl_off="";
            $this->page_parse_vars=$this->pg_appendarray(array("CONTENT",$content,"PAGE_TITLE",$GLOBALS["MANDRIGO"]["LANGUAGE"]["OPTITLE"]),$this->page_parse_vars);		
		}
	    $tpl_mainsite->tpl_parse($this->page_parse_vars,"main",2);
	    if($GLOBALS["MANDRIGO"]["SITE"]["CACHE_PAGES"]){
			$result=$cache->cache_wpage($tpl_mainsite->tpl_return("main"));
			if($result==false){
				if(!$GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
					$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(7,"display");
					die($GLOBALS["MANDRIGO"]["ELOG"]["HTMLHEAD"].$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"].$GLOBALS["MANDRIGO"]["ELOG"]["HTMLBODY"].
		           		$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport().$GLOBALS["MANDRIGO"]["ELOG"]["HTMLEND"]);
	
				}				
			}
	    }
	    return $tpl_mainsite->tpl_return("main");
	}
	
	
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################	  
	  
    //
    //private function pg_gencontent()
    //
    //generates the page content
    //
    //returns the content
    function pg_gencontent(){
     	$soq=count($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"]);
     	$content="";
     	for($i=0;$i<$soq;$i++){
			if(!$tmp=$this->pg_regesterhook($i)){
				return false;
			}
			$content.=$tmp;
		}
  		return $content;
	}
 
    //
    //private function pg_regesterhook($i)
    //
    //generates a block of content for the given hook
    //
    //returns the content
    function pg_regesterhook($i){
        $content="";
        $string="$"."hookc=new ".$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"][$i][1].HOOK_CLASS;
        eval($string);
        $string="$"."content=$"."hookc->".$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"][$i][1].HOOK_DISPLAY;
        eval($string);
        $string="$"."vars=$"."hookc->".$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"][$i][1].HOOK_VARS;
        eval($string);
        //echo count($vars);
        if(count($vars)%2==0){
            $this->page_parse_vars=$this->pg_appendarray($vars,$this->page_parse_vars);
        }
        return $content;
	}
    
    //
    //private function pg_mergearrays($a1,$a2)
    //
    //appends $a2 onto the end of $a1
    //
    //INPUTS:
    //$a1		-	array
    //$a2		-	array
    //
	//returns the combined array	
    function pg_appendarray($a1,$a2){
		$size1=count($a1);
		$size2=count($a2);
		$soq=$size1+$size2;
		for($i=$size1;$i<$soq;$i++){
			$a1[$i]=$a2[$i-($size1)];
		}
		return $a1;
	}
}