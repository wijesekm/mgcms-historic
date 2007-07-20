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
$newpkg=array();
$packages=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_PACKAGES,"pkg_id,pkg_name,pkg_nlerror,pkg_classhooks",$filter,"ASSOC",DB_ALL_ROWS);
$soq=count($packages);
if(!$GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
	if(!$packages&&$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["EXISTS"]){
		$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(3,"display");
	}
}


$count=0;
$hooks=array();
for($i=0;$i<$soq;$i++){
	if($packages[$i]["pkg_name"]){
		if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
			include_once($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$packages[$i]["pkg_name"]."/hooks.pkg.".PHP_EXT);
			include_once($GLOBALS["MANDRIGO"]["CONFIG"]["PLUGIN_PATH"].$packages[$i]["pkg_name"]."/globals.pkg.".PHP_EXT);
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
		}
		//loads classes
		$hooks_temp=explode(";",$packages[$i]["pkg_classhooks"]);
		$soh=count($hooks_temp);
		for($j=0;$j<$soh;$j++){
			if($hooks_temp[$j]&&!in_array($hooks_temp[$j],$hooks)){
				$hooks[$count]=$hooks_temp[$j];
				$count++;
			}
		}

	}
}
$soh=count($hooks);
$count=0;
$filter=array();
for($j=0;$j<$soh;$j++){
	if($hooks[$j]){
		if($i+1<$soq){
			$filter[$count]=array("class_id","=",(string)$hooks[$j],DB_AND,$j+1);
			$filter[$count+1]=array("class_status","=","E",DB_OR,$j+1);
		}
		else{
			$filter[$count]=array("class_id","=",(string)$hooks[$j],DB_AND,$j+1);
			$filter[$count+1]=array("class_status","=","E",DB_OR,$j+1);
		}
		$count+=2;
	}	
}

$classes=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_CLASSES,"class_id,class_files,class_error",$filter,"ASSOC",DB_ALL_ROWS);
$soc=count($classes);
for($j=0;$j<$soc;$j++){
	$files=explode(";",$classes["class_files"]);
	$sof=count($files);
	for($i=0;$i<$sof;$i++){
		$file=$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"].$files[$i];
		if(file_exists($file)&&!is_dir($file)){
			if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
				include_once($file);
			}
			else{
				if(!(@include_once($file))){
					$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror((int)$classes[$j]["class_error"],"core");
				}
			}
		}	
	}
}

$soq2=count($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"]);
for($i=0;$i<$soq2;$i++){
	for($j=0;$j<$soq;$j++){
		if($packages[$j]["pkg_id"]==$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"][$i]){
			$newpkg[$i]=array($packages[$j]["pkg_id"],$packages[$j]["pkg_name"]);
		}
	}
}
$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"]=$newpkg;

$packages="";
$newpkg="";