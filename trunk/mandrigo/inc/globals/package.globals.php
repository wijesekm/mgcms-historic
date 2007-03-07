<?php
/**********************************************************
    package.globals.php
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
$filter=array();
$filter=array(array("var_corename","=",CORE_PACKAGES,DB_AND,1));
$soq=count($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"]);
for($i=0;$i<$soq;$i++){
	$filter[$i+1]=array("var_appid","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"][$i][0],DB_OR,2);
}

$filter[$soq+1]=array("var_appid","=",0,"",2);
$vars=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_SERVER_GLOBALS,"",$filter,"ASSOC",DB_ALL_ROWS);

$soa=count($vars);

//parses all vars out.  When multiple var names, protocols are specified the first one which has a value wins
//and is made the var value.
for($i=0;$i<$soa;$i++){
 	$multi=false;
 	//if multiple protocols are specified we will run this
	if(eregi(";",$vars[$i]["var_protocols"])){
		$multi=true;
		$vars[$i]["var_protocols"]=explode(";",$vars[$i]["var_protocols"]);
		$soq2=count($vars[$i]["var_protocols"]);
		for($j=0;$j<$soq2;$j++){
			sg_setglobal($vars[$i],$GLOBALS["TMPURL"],$vars[$i]["var_protocols"][$j]);
		}
	}
	else{
		sg_setglobal($vars[$i],$GLOBALS["TMPURL"],$vars[$i]["var_protocols"]);
	}
}
//cleanup
$vars="";
$GLOBALS["TMPURL"]="";