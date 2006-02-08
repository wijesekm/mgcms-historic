<?php
/**********************************************************
    display.pkg.php
    p_content ver 1.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 12/24/05

	Copyright (C) 2005  Kevin Wijesekera

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

class title{

    var $db;
    var $config;

    function title(&$sql){
        $this->db=$sql;
    }
    function display($i){
        $data="";
        switch($this->config["t_type"]){
            case 0:
                $h=ereg_replace("{SIZE}",$this->config["h_size"],$GLOBALS["HTML"]["H"]);
                $h=ereg_replace("{ATTRIB}",$this->config["h_attrib"],$h);
                $endh=ereg_replace("{SIZE}",$this->config["h_size"],$GLOBALS["HTML"]["H!"]);
                $data=$h.$this->config["title"].$endh."\n";
            break;
            case 1:
                $h=ereg_replace("{SIZE}",$this->config["h_size"],$GLOBALS["HTML"]["H"]);
                $h=ereg_replace("{ATTRIB}",$this->config["h_attrib"],$h);
                $endh=ereg_replace("{SIZE}",$this->config["h_size"],$GLOBALS["HTML"]["H!"]);
                $hr=ereg_replace("{ATTRIB}",$this->config["hr_attrib"],$GLOBALS["HTML"]["HR"]);
                $data=$h.$this->config["title"].$endh.$hr."\n";
            break;
            case 2:
                $attrib="src=\"".$this->config["i_url"]."\" alt=\"".$this->config["title"]."\"";
                $img=ereg_replace("{ATTRIB}",$attrib,$GLOBALS["HTML"]["IMG"]);
                $data=$img."\n";
            break;
            case 3:
                $attrib="src=\"".$this->config["i_url"]."\" alt=\"".$this->config["title"]."\"";
                $img=ereg_replace("{ATTRIB}",$attrib,$GLOBALS["HTML"]["IMG"]);
                $hr=ereg_replace("{ATTRIB}",$this->config["hr_attrib"],$GLOBALS["HTML"]["HR"]);
                $data=$img.$hr."\n";
            break;
        }
        return $data;
    }
    function load($i){
        if(!$sql_result=$this->db->fetch_array("SELECT * FROM `".TABLE_PREFIX.TABLE_TITLE_DATA."` WHERE `page_id`='".$GLOBALS["PAGE_DATA"]["ID"]."' AND `part_id`='$i';")){
            return false;
        }
        $this->config["title"]=isset($sql_result{"title"})?$sql_result{"title"}:$GLOBALS["PAGE_DATA"]["RNAME"];
        $this->config["t_type"]=$sql_result["title_type"];
        $this->config["h_size"]=$sql_result["h_size"];
        $this->config["h_attrib"]=$sql_result["h_attrib"];
        $this->config["i_url"]=$sql_result["img_url"];
        $this->config["i_attrib"]=$sql_result["img_attrib"];
        $this->config["hr_attrib"]=$sql_result["hr_attrib"];
        return true;
    }
}
?>
