<?php
/**********************************************************
    setup.ini.php
    mg_menu ver 0.7.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 04/11/07

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

$pkg["name"]="mg_menu";
$pkg["version"]="0.7.0";
$pkg["maintainer"]="Kevin Wijesekera";
$pkg["email"]="k_wijesekera@yahoo.com";
$pkg["website"]="http://kevinwijesekera.net";

$pkg["enabled"]=true;
$pkg["no_load_error"]="503";

$pkg["errors"]=array("sql"=>array(array("410","Could not access the menu overview table.  This usually means the table is blank or missing.")),
					 "access"=>array(),
					 "core"=>array(),
					 "display"=>array(array("130","The mg_menu package could not load the template for the current page hook."),array("503","The mg_menu package could not be loaded.")),
					 "ldap"=>array());

$pkg["languages"]=array();

$pkg["tables"]=array("menu");

$pkg_table_install["menu"]["struct"]=array(array("menu_id","int","11",DB_AUTO_INC),
										   array("page_id","int","11","","0"),
									       array("part_id","tinyint","4","","0"),
										   array("menu_items","varchar","250",DB_NULL));
										   
$pkg_table_install["menu"]["keys"]=array(array(DB_PRIMARY,"menu_id"));
$pkg_table_install["menu"]["records"]=array();

//Do Not Edit Below This Line
if($pkg["enabled"]){
	$pkg["enabled"]="E";	
}
else{
	$pkg["enabled"]="D";	
}
