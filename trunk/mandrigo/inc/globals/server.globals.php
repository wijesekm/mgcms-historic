<?php
/**********************************************************
    server.globals.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/12/07

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

$url = array();
$vars= array();

//makes the URL array for type 1 urls only
if($GLOBALS["MANDRIGO"]["SITE"]["URL_FORMAT"]==1){
 
 	//detects php_self.  I have seen servers regester this as index.php/vars or just /vars
 	//which is why the check is there.
    if(!ereg($GLOBALS["MANDRIGO"]["SITE"]["INDEX_NAME"]."/",$_SERVER["PHP_SELF"])){
		$php_self=$GLOBALS["MANDRIGO"]["SITE"]["INDEX_NAME"].$_SERVER["PHP_SELF"];
    }
	else{
		$php_self=$_SERVER["PHP_SELF"];
    }
    //exploes the array into its basic chunks
    $raw_url = eregi_replace("^.*".$GLOBALS["MANDRIGO"]["SITE"]["INDEX_NAME"]."/p","p",$php_self);
    $raw_url = explode("/",$raw_url);
    $array_url = array();
    //just in case we want to do /var=value/var2=value2/ we will explode all ='s too
    for($i =0; $i < count($raw_url); $i++){
        $array_url = array_merge_recursive($array_url,explode("=",$raw_url[$i]));
    }
    //forms the url array
    for($i=0; $i< count($array_url); $i=$i+2){
        $url = array_merge_recursive($url, array($array_url[$i]=>$array_url[$i+1]));
    }
    
    //cleanup
    $array_url="";
    $raw_url="";
    $php_self="";
}

//gets vars array
$vars=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_SERVER_GLOBALS,"",array(array("var_corename","=",CORE_NAME,DB_OR),array("var_corename","=","all")),"ASSOC",DB_ALL_ROWS);

$soa=count($vars);

if($soa<=1){
	$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(4,"sql");
}

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
			sg_setglobal($vars[$i],$url,$vars[$i]["var_protocols"][$j]);
		}
	}
	else{
		sg_setglobal($vars[$i],$url,$vars[$i]["var_protocols"]);
	}
}
//cleanup
$vars="";
$GLOBALS["TMPURL"]=$url;
$url="";
//function sg_setglobal($vars,$url,$protocol="http_get")
//
//regesters a var based on the database
//
//INPUTS:
//$vars		-	array of data for the whole row from the database
//$url		-	url array
//$protocol -	protocol we will run this for
function sg_setglobal($vars,$url,$protocol="http_get"){
	$get_names=explode(";",$vars["var_getnames"]);
	$clean_functs=explode(";",$vars["var_cleanfuncts"]);
	$defaults=explode(";",$vars["var_defaults"]);
	$soc=count($get_names);
	for($k=0;$k<$soc;$k++){
		if(!$GLOBALS["MANDRIGO"]["VARS"][strtoupper($vars["var_name"])]||$GLOBALS["MANDRIGO"]["VARS"][strtoupper($vars["var_name"])]==BAD_DATA){
			$tmp="";
			switch($protocol){
				case METHOD_GET:
					if($GLOBALS["MANDRIGO"]["SITE"]["URL_FORMAT"]==1){
						$tmp=$url[$get_names[$k]];
						if(!$tmp){
							$tmp=$_GET[$get_names[$k]];	
						}
					}
					else{
						$tmp=$_GET[$get_names[$k]];	
					}
				break;
				case METHOD_POST:
					$tmp=$_POST[$get_names[$k]];	
				break;
				case METHOD_COOKIE:
					$tmp=$_COOKIE[$get_names[$k]];	
				break;
				case METHOD_SERVER:
					$tmp=$_SERVER[$get_names[$k]];	
				break;
			};

			if(isset($tmp)){
				$tmp=clean_var($tmp,$clean_functs[$k]);
			}
			else{
			 	if(!$defaults[$k]){
					$defaults[$k]="''";
				}
				eval("$"."tmp=".$defaults[$k].";");
			}
			$GLOBALS["MANDRIGO"]["VARS"][strtoupper($vars["var_name"])]=$tmp;
		}
	}
}
