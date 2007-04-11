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

class admin_page{
	
	var $page_parse_vars;
	
	function admin_page(){
        $this->page_parse_vars = array(
            "SITE_NAME",$GLOBALS["MANDRIGO"]["SITE"]["SITE_NAME"],
            "SITE_URL",$GLOBALS["MANDRIGO"]["SITE"]["SITE_URL"],
            "ADMIN_URL",$GLOBALS["MANDRIGO"]["SITE"]["ADMIN_URL"],
            "ADMIN_NAME",$GLOBALS["MANDRIGO"]["SITE"]["ADMIN_NAME"],
            "LOGIN_URL",$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_URL"],
            "LOGIN_NAME",$GLOBALS["MANDRIGO"]["SITE"]["LOGIN_NAME"],
            "IMG_URL",$GLOBALS["MANDRIGO"]["SITE"]["IMG_URL"],
            "APAGE_FNAME",$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["FULLNAME"],
            "APAGE_NAME",$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["NAME"],
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
			);
        if(count($GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["VARS"])%2==0){
            $this->page_parse_vars=$this->ap_appendarray($GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["VARS"],$this->page_parse_vars);
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
	function ap_display(){
	 
	 	//
	 	//Load the main site template
	 	//
		$tpl_mainsite=new template();
		if(!$tpl_mainsite->tpl_load($GLOBALS["MANDRIGO"]["CONFIG"]["TEMPLATE_PATH"].TPL_ADMINPATH.TPL_ADMIN,"main")){
			if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
				die();
			}
			else{
				$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(201,"core");
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
				$this->page_parse_vars=$this->ap_appendarray(array("CONTENT",$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport(),"PAGE_TITLE",$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"]),$this->page_parse_vars);			
			}
		}
		else if($GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["STATUS"]===1||($bypass&&$GLOBALS["MANDRIGO"]["SITE"]["BYPASS_CODE"])){
			$content=$this->ap_gencontent();
			if(!$content&&!$GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
				$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(4,"display");	
			}
			if($GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_getstatus()!=0&&!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
				$this->page_parse_vars=$this->ap_appendarray(array("CONTENT",$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport(),"PAGE_TITLE",$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"]),$this->page_parse_vars);			
			}
			else{
               $this->page_parse_vars=$this->ap_appendarray(array("CONTENT",$content,"PAGE_TITLE",$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["TITLE"]),$this->page_parse_vars);
			}
		}
	    $tpl_mainsite->tpl_parse($this->page_parse_vars,"main",2);
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
    function ap_gencontent(){
     	$soq=count($GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["HOOKS"]);
     	$content="";
     	for($i=0;$i<$soq;$i++){
			if(!$tmp=$this->ap_regesterhook($i)){
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
    function ap_regesterhook($i){
        $content="";
        $string="$"."hookc=new ".$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["HOOKS"][$i][1].HOOK_CLASS;
        eval($string);
        $string="$"."content=$"."hookc->".$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["HOOKS"][$i][1].HOOK_DISPLAY;
        eval($string);
        $string="$"."vars=$"."hookc->".$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["HOOKS"][$i][1].HOOK_VARS;
        eval($string);
        //echo count($vars);
        if(count($vars)%2==0){
            $this->page_parse_vars=$this->ap_appendarray($vars,$this->page_parse_vars);
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
    function ap_appendarray($a1,$a2){
		$size1=count($a1);
		$size2=count($a2);
		$soq=$size1+$size2;
		for($i=$size1;$i<$soq;$i++){
			$a1[$i]=$a2[$i-($size1)];
		}
		return $a1;
	}
}