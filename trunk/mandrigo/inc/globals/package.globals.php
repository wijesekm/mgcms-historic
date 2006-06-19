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
$soa=0;
for($i=0;$i<count($GLOBALS["PAGE_DATA"]["HOOKS"]);$i++){
	$soa+=$sql_db->db_numrows(TABLE_PREFIX.TABLE_SERVER_GLOBALS,array(array("var_core_name","=",CORE_PACKAGES,DB_AND),array("var_app_name","=",$GLOBALS["PAGE_DATA"]["HOOKS"][$i]))); 
}
$j=0;
for($i=0;$j<$soa;$i++){
	if($parse=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_SERVER_GLOBALS,"",array(array("var_core_name","=",CORE_PACKAGES,DB_AND),array("var_id","=",$i,DB_AND),array("var_app_name","<>","mandrigo")))){
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
unset($get_names);
unset($clean_functs);
unset($defaults);
unset($url);

?>