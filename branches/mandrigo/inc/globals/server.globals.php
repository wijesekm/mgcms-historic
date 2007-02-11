<?php
/**********************************************************
    server.globals.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 06/14/06

	Copyright (C) 2006  Kevin Wijesekera

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
//$GLOBALS["SITE_DATA"]["URL_FORMAT"]
if(!defined("START_MANDRIGO")){
    die("<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width=\"300\" align=\"left\"/>\n<p>You do not have permission to access this file directly.</p>
        </html></body>");
}

//array defs
$GLOBALS["HTTP_COOKIE"]=array();
$GLOBALS["HTTP_GET"]=array();
$GLOBALS["HTTP_SERVER"]=array();
$GLOBALS["HTTP_POST"]=array();

//makes the URL array for type 1 urls only
if($GLOBALS["SITE_DATA"]["URL_FORMAT"]==1){
	$url = array(null=>null);
    if(!ereg($GLOBALS["MANDRIGO_CONFIG"]["INDEX"]."/",$HTTP_SERVER_VARS["PHP_SELF"])){
		$php_self=$GLOBALS["MANDRIGO_CONFIG"]["INDEX"].$HTTP_SERVER_VARS["PHP_SELF"];
    }
	else{
		$php_self=$HTTP_SERVER_VARS["PHP_SELF"];
    }
    $raw_url = eregi_replace("^.*".$GLOBALS["MANDRIGO_CONFIG"]["INDEX"]."/p","p",$php_self);
    $raw_url = explode("/",$raw_url);
    $array_url = array("","");
    for($i =0; $i < count($raw_url); $i++){
        $tmp = explode("=",$raw_url[$i]);
        $array_url = array_merge_recursive($array_url,$tmp);
    }
    for($i=2; $i< count($array_url); $i=$i+2){
    	$tmp = array($array_url[$i]=>$array_url[$i+1]);
        $url = array_merge_recursive($url, $tmp);
    }
    $array_url=array();
    $tmp=array();
    $raw_url=array();
}
$soa=$sql_db->db_numrows(TABLE_PREFIX.TABLE_SERVER_GLOBALS,array(array("var_core_name","=",CORE_NAME,DB_OR),array("var_core_name","=","all")));
$j=0;
for($i=0;$j<$soa;$i++){
	if($parse=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_SERVER_GLOBALS,"",array(array("var_core_name","=",CORE_NAME,DB_OR,1),array("var_core_name","=","all",DB_AND,1),array("var_id","=",$i,"",5)))){
		$j++;
		switch($parse["var_protocol"]){
			case METHOD_GET:
				$get_names=explode(";",$parse["var_get_names"]);
				$clean_functs=explode(";",$parse["var_clean_functs"]);
				$defaults=explode("&split;",$parse["var_defaults"]);
				$soc=count($get_names);
				for($k=0;$k<$soc;$k++){
					if(!$GLOBALS["HTTP_GET"][$parse["var_store_name"]]||$GLOBALS["HTTP_GET"][$parse["var_store_name"]]==BAD_DATA){
						if($GLOBALS["SITE_DATA"]["URL_FORMAT"]==0){
							$tmp_=$HTTP_GET_VARS[$get_names[$k]];	
						}
						else if($GLOBALS["SITE_DATA"]["URL_FORMAT"]==1){
							$tmp_=$url[$get_names[$k]];	
						}
						if(isset($tmp_)){
							$tmp=clean_var($tmp_,$clean_functs[$k]);
						}
						else{
							eval($defaults[$k]);
						}
						$GLOBALS["HTTP_GET"][$parse["var_store_name"]]=$tmp;
					}
				}
			break;
			case METHOD_POST:
				$get_names=explode(";",$parse["var_get_names"]);
				$clean_functs=explode(";",$parse["var_clean_functs"]);
				$defaults=explode("&split;",$parse["var_defaults"]);
				$soc=count($get_names);
				for($k=0;$k<$soc;$k++){
					if(!$GLOBALS["HTTP_POST"][$parse["var_store_name"]]||$GLOBALS["HTTP_POST"][$parse["var_store_name"]]==BAD_DATA){
						if(isset($HTTP_POST_VARS[$get_names[$k]])){
							$tmp=clean_var($HTTP_POST_VARS[$get_names[$k]],$clean_functs[$k]);
						}
						else{
							eval($defaults[$k]);
						}
						$GLOBALS["HTTP_POST"][$parse["var_store_name"]]=$tmp;
					}
				}			
			break;
			case METHOD_COOKIE:
				$get_names=explode(";",$parse["var_get_names"]);
				$clean_functs=explode(";",$parse["var_clean_functs"]);
				$defaults=explode("&split;",$parse["var_defaults"]);
				$soc=count($get_names);
				for($k=0;$k<$soc;$k++){
					if(!$GLOBALS["HTTP_COOKIE"][$parse["var_store_name"]]||$GLOBALS["HTTP_POST"][$parse["var_store_name"]]==BAD_DATA){
						if(isset($HTTP_COOKIE_VARS[$get_names[$k]])){
							$tmp=clean_var($HTTP_COOKIE_VARS[$get_names[$k]],$clean_functs[$k]);
						}
						else{
							eval($defaults[$k]);
						}
						$GLOBALS["HTTP_COOKIE"][$parse["var_store_name"]]=$tmp;
					}
				}			
			break;
			case METHOD_SERVER;
				$get_names=explode(";",$parse["var_get_names"]);
				$clean_functs=explode(";",$parse["var_clean_functs"]);
				$defaults=explode("&split;",$parse["var_defaults"]);
				$soc=count($get_names);
				for($k=0;$k<$soc;$k++){
					if(!$GLOBALS["HTTP_SERVER"][$parse["var_store_name"]]||$GLOBALS["HTTP_SERVER"][$parse["var_store_name"]]==BAD_DATA){
						if(isset($HTTP_SERVER_VARS[$get_names[$k]])){
							$tmp=clean_var($HTTP_SERVER_VARS[$get_names[$k]],$clean_functs[$k]);
						}
						else{
							eval($defaults[$k]);
						}
						$GLOBALS["HTTP_SERVER"][$parse["var_store_name"]]=$tmp;
					}
				}
			break;
			default:
			break;
		};
	}
}
$parse=array();
$get_names=array();
$clean_functs=array();
$defaults=array();

?>