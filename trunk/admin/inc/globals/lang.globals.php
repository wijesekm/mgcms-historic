<?php
/**********************************************************
    lang.globals.php
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

if($GLOBALS["MANDRIGO"]["SITE"]["ALLOW_USERLANG"]==1){
$GLOBALS["MANDRIGO"]["CONFIG"]["LANGUAGE"]=(!empty($GLOBALS["MANDRIGO"]["CURRENTUSER"]["LANGUAGE"]))
											?$GLOBALS["MANDRIGO"]["CURRENTUSER"]["LANGUAGE"]
											:$GLOBALS["MANDRIGO"]["CONFIG"]["LANGUAGE"];
}

$lang_curset=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_LANGSETS,"",array(array("lang_name","=",$GLOBALS["MANDRIGO"]["CONFIG"]["LANGUAGE"],DB_AND),array("lang_type","=","L")));
$html_curset=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_LANGSETS,"",array(array("lang_name","=",$GLOBALS["MANDRIGO"]["CONFIG"]["HTML_VER"],DB_AND),array("lang_type","=","H")));

//
//Errors for no language stuff
//
if($lang_curset["lang_id"]<=0){
	if(!$GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
		$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(5,"sql");
	}
}
if($html_curset["lang_id"]<=0){
	if(!$GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
		$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(6,"sql");
	}
}
if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]&&$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_getstatus()==2){
	die($GLOBALS["MANDRIGO"]["ELOG"]["HTMLHEAD"].$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"].$GLOBALS["MANDRIGO"]["ELOG"]["HTMLBODY"].
        $GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport().$GLOBALS["MANDRIGO"]["ELOG"]["HTMLEND"]);
}

//
//Basic Lang Data
//
$GLOBALS["MANDRIGO"]["LANGUAGE"]["ID"]=$lang_curset["lang_id"];
$GLOBALS["MANDRIGO"]["LANGUAGE"]["NAME"]=$lang_curset["lang_name"];
$GLOBALS["MANDRIGO"]["LANGUAGE"]["CHARSET"]=$lang_curset["lang_charset"];
$GLOBALS["MANDRIGO"]["LANGUAGE"]["ENCODING"]=$lang_curset["lang_encoding"];
$GLOBALS["MANDRIGO"]["LANGUAGE"]["CONTENT_TYPE"]="text/html;";
$GLOBALS["MANDRIGO"]["LANGUAGE"]["SET_ENCODING"]=true;
$GLOBALS["MANDRIGO"]["LANGUAGE"]["REG"]=false;

$GLOBALS["MANDRIGO"]["HTML"]["ID"]=$html_curset["lang_id"];
$GLOBALS["MANDRIGO"]["HTML"]["NAME"]=$html_curset["lang_name"];

//
//Now we will form the search array which is all cores OR display core OR (package core AND (Package ID's))
//
$soq=count($GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["HOOKS"]);
$filter=array();
$count=0;
for($i=0;$i<$soq;$i++){
 	if($i+1<$soq){
 	 	$filter[$count]=array("lang_corename","=",CORE_PACKAGES,DB_AND,$i+1);
		$filter[$count+1]=array("lang_appid","=",$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["HOOKS"][$i],DB_OR,$i+1);		
	}
	else{
 	 	$filter[$count]=array("lang_corename","=",CORE_PACKAGES,DB_AND,$i+1);
		$filter[$count+1]=array("lang_appid","=",$GLOBALS["MANDRIGO"]["CURRENTAPAGE"]["HOOKS"][$i],DB_OR,$i+1);
	}
	$count+=2;
}
$filter[$count]=array("lang_corename","=",CORE_PACKAGES,DB_AND,$soq+1);
$filter[$count+1]=array("lang_appid","=","0",DB_OR,$soq+1);

$filter[$count+2]=array("lang_corename","=",CORE_NAME,DB_OR,$soq+2);
$filter[$count+3]=array("lang_corename","=","all","",$soq+3);

//
//LANGUAGE array formation
//
$tmp=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_LANG.$GLOBALS["MANDRIGO"]["LANGUAGE"]["ID"],"lang_callname,lang_value",$filter,"ASSOC",DB_ALL_ROWS);
$soq=count($tmp);
for($i=0;$i<$soq;$i++){
	$GLOBALS["MANDRIGO"]["LANGUAGE"][strtoupper($tmp[$i]["lang_callname"])]=$tmp[$i]["lang_value"];
}
//
//HTML array formation
//
$tmp=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_LANG.$GLOBALS["MANDRIGO"]["HTML"]["ID"],"lang_callname,lang_value",$filter,"ASSOC",DB_ALL_ROWS);
$soq=count($tmp);
for($i=0;$i<$soq;$i++){
	$GLOBALS["MANDRIGO"]["HTML"][strtoupper($tmp[$i]["lang_callname"])]=$tmp[$i]["lang_value"];
}
