<?php
/**********************************************************
    package.ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/28/07

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

$soq=count($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"]);

$filter=array();
$count=0;
for($i=0;$i<$soq;$i++){
 	if($i+1<$soq){
		$filter[$count]=array("pkg_id","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"][$i],DB_AND,$i+1);
		$filter[$count+1]=array("pkg_status","=","E",DB_OR,$i+1);
	}
	else{
		$filter[$count]=array("pkg_id","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"][$i],DB_AND,$i+1);
		$filter[$count+1]=array("pkg_status","=","E","",$i+1);
	}
	$count+=2;
}

$packages=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_PACKAGES,"pkg_name,pkg_nlerror",$filter,"ASSOC",DB_ALL_ROWS);
$soq=count($packages);

if(!$GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
	if($packages===false){
		$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(7,"sql");
	}
	if($soq==0){
		$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(3,"display");
	}	
}

for($i=0;$i<$soq;$i++){
	if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
		include_once($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$packages[$i]["pkg_name"]."/hooks.pkg.".PHP_EXT);
		include_once($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$packages[$i]["pkg_name"]."/globals.pkg.".PHP_EXT);
		include_once($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$packages[$i]["pkg_name"]."/display.pkg.".PHP_EXT);
	}
	else{
	 	$fail=false;
		if(!(@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$packages[$i]["pkg_name"]."/hooks.pkg.".PHP_EXT))){
			$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror((int)$packages[$i]["pkg_nlerror"],"display");
			$fail=true;	
		}
		if(!(@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$packages[$i]["pkg_name"]."/globals.pkg.".PHP_EXT))){
			if(!$fail){
				$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror((int)$packages[$i]["pkg_nlerror"],"display");
				$fail=true;
			}
		}
		if(!(@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$packages[$i]["pkg_name"]."/display.pkg.".PHP_EXT))){
			if(!$fail){
				$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror((int)$packages[$i]["pkg_nlerror"],"display");
			}
		}
	}
}
