<?php
/**********************************************************
    display.pkg.php
    menu ver 1.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 1/25/2006

	Copyright (C) 2006 Kevin Wijesekera

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
    die("<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width=\"300\" align=\"left\"/>\n<p>You do not have permission to access this file directly.</p>
        </html></body>");
}


class menu_display{

	var $sql_db;
	var $config;
	var $tpl;

    function menu_display($sql){
        $this->sql_db=$sql;
    }
    function display($i){
		$link_string="";
		$soa=count($this->config["menu_items"]);
		for($i=0;$i<$soa;$i++){
			$cur_subpage=$GLOBALS["PAGE_DATA"]["SUBPAGES"][$this->config["menu_items"][$i]];
			$subpage_sql=$this->sql_db->fetch_array("SELECT * FROM `".TABLE_PREFIX.TABLE_PAGE_DATA."` WHERE `page_id`='".$cur_subpage."';");
			$name=$subpage_sql["page_name"];
			if(eregi("==>",$name)){
				$attr="href=\"".ereg_replace("==>","",$name)."\" ".$this->config["menu_attrib"];
				$link=ereg_replace("{ATTRIB}",$attr,$GLOBALS["HTML"]["A"]).$subpage_sql["page_rname"].$GLOBALS["HTML"]["A!"];
				$link_string.=ereg_replace("{LINK}",$link,$this->tpl->return_template(1));
			}
			else{
			  	$page="";
				if($GLOBALS["SITE_DATA"]["PAGE_INPUT_TYPE"]){
					$page=$subpage_sql["page_id"];
				}
				else{
					$page=$name;
				}
				$link_string.=ereg_replace("{LINK}",$this->gen_url($page,$subpage_sql["page_rname"]),$this->tpl->return_template(1));	
			}
		}
		$this->tpl->pparse(array("MENU_STR",$link_string));
		return $this->tpl->return_template();	  
	}
	function gen_url($page,$page_name){
		if($GLOBALS["SITE_DATA"]["URL_FORMAT"]){
			$attr="href=\"".$GLOBALS["SITE_DATA"]["SITE_URL"].$GLOBALS["MANDRIGO_CONFIG"]["INDEX"]."/p/$page\" ".$this->config["link_attrib"]; 
		}
		else{
	  		$attr="href=\"".$GLOBALS["SITE_DATA"]["SITE_URL"].$GLOBALS["MANDRIGO_CONFIG"]["INDEX"]."?p=$page\" ".$this->config["link_attrib"]; 
		}
		return ereg_replace("{ATTRIB}",$attr,$GLOBALS["HTML"]["A"]).$page_name.$GLOBALS["HTML"]["A!"];
	}
	function load($i){
		if(!$sql_result=$this->sql_db->fetch_array("SELECT * FROM `".TABLE_PREFIX.TABLE_MENU_DATA."` WHERE `page_id`='".$GLOBALS["PAGE_DATA"]["ID"]."' AND `part_id`='$i';")){
            return false;
        }
        $this->config["menu_items"]=explode(";",$sql_result["menu_items"]);
        $this->config["link_attrib"]=$sql_result["link_attrib"];
        $this->tpl=new template();
        if(!$this->tpl->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].$GLOBALS["PAGE_DATA"]["DATAPATH"].$GLOBALS["PAGE_DATA"]["ID"]."_".$i."_menu.".TPL_EXT,"","<!--LINK_DELIM-->")){
			return false;
		}
        return true;
	}
}
?>