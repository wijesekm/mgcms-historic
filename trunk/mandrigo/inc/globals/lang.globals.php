<?php
/**********************************************************
    lang.globals.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 06/21/06

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
//
if(!defined("START_MANDRIGO")){
    die("<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width=\"300\" align=\"left\"/>\n<p>You do not have permission to access this file directly.</p>
        </html></body>");
}
$GLOBALS["LANGUAGE"]="";

//to set the content/type charset header
if(!$lang=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_LANG_MAIN,"",array(array("lang_name","=",$langname)))){
	if(!$lang=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_LANG_MAIN,"",array(array("lang_name","=",$this->sys_lang)))){
		if(!$lang=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_LANG_MAIN,"",array(array("lang_name","=",0)){
			if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
        		$error_log->add_error(30,"sql");		  
			}
		}
	}			
}
$GLOBALS["LANGUAGE"]["CHARSET"]=$lang["lang_charset"];
$GLOBALS["LANGUAGE"]["ENCODING"]=$lang["lang_encoding"];
header("Content-type: text/html; charset=".$GLOBALS["LANG"]["CHARSET"]);

//to get the name of the lang table
$langname=(isset($GLOBALS["USER_DATA"]["LANGUAGE"]))?$GLOBALS["USER_DATA"]["LANGUAGE"]:$default_lang["LANGUAGE"];
if(!$lang_id=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_LANG_MAIN,"lang_id",array(array("lang_name","=",$langname)))){
	if(!$lang_id=$$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_LANG_MAIN,"lang_id",array(array("lang_name","=",$default_lang["LANGUAGE"])))){
		if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
        	$error_log->add_error(30,"sql");		  
		}
	}			
}

//populates the language array
$soa=$sql_db->db_numrows(TABLE_PREFIX.TABLE_LANG.$lang_id);
$j=0;
for($i=0;$j<$soa;$i++){
	if($result=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_LANG.$lang_id,"",array(array("lang_id","=",$i)))){
		$j++;
		$GLOBALS["LANGUAGE"][$result["lang_callname"]]=$result["lang_value"];
	}
}

?>