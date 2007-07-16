<?php
/**********************************************************
    setup.ini.php
    mg_news ver 0.7.0
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

$pkg["name"]="mg_gallery";
$pkg["id"]=6;
$pkg["version"]="0.7.0";
$pkg["maintainer"]="Kevin Wijesekera";
$pkg["email"]="k_wijesekera@yahoo.com";
$pkg["website"]="http://kevinwijesekera.net";

$pkg["enabled"]=true;
$pkg["no_load_error"]="505";

$pkg["errors"]=array("sql"=>array(),
					 "access"=>array(array("420","The mg_gallery package could not load the gallery table.  This usually means the table is blank or missing.")),
					 "core"=>array(),
					 "display"=>array(array("140","The mg_gallery package could not load the template for the current page."),
					 				  array("505","The mg_gallery package could not be loaded.")
									 ),
					 "ldap"=>array());

$pkg["languages"]=array("en-US");

$pkg_language_install["en-US"]=array(array("GA_NOIMG","Mandrigo Gallery cannot display the requested image."));

$pkg["tables"]=array("gallery");

$pkg_table_install["gallery"]["struct"]=array(array("page_id","int","11","","0"),
										      array("thumb_size","varchar","10","","80x80"),
										      array("display_size","varchar","10","","400x400"),
											  );
);
										   
$pkg_table_install["news"]["keys"]=array(array(DB_PRIMARY,"page_id"));
$pkg_table_install["news"]["records"]=array();

//Do Not Edit Below This Line
if($pkg["enabled"]){
	$pkg["enabled"]="E";	
}
else{
	$pkg["enabled"]="D";	
}
