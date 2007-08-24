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

$pkg["name"]="mg_fmail";
$pkg["id"]=7;
$pkg["version"]="0.7.0";
$pkg["maintainer"]="Kevin Wijesekera";
$pkg["email"]="k_wijesekera@yahoo.com";
$pkg["website"]="http://kevinwijesekera.net";

$pkg["enabled"]=true;
$pkg["no_load_error"]="506";

$pkg["errors"]=array("sql"=>array(),
					 "access"=>array(array("430","The mg_fmail package could not load the gallery table.  This usually means the table is blank or missing.")),
					 "core"=>array(),
					 "display"=>array(array("150","The mg_fmail package could not load the template for the current page."),
					 				  array("151","The mg_fmail package could not send the message due to an internal error."),
					 				  array("506","The mg_fmail package could not be loaded.")
									 ),
					 "ldap"=>array());

$pkg["languages"]=array("en-US");

$pkg_language_install["en-US"]=array(array("MG_FMAIL_IERROR","The message could not be sent.  Please fix the dotted fields below and try again."),
									 array("MG_FMAIL_SENT","Message Sent!"),
									 array("MG_FMAIL_NOEMAIL","No E-Mail Address or Invalid E-Mail.")
									);

$pkg["tables"]=array("fmail_data","fmail_list");

$pkg_table_install["fmail_data"]["struct"]=array(array("page_id","int","11","","0"),
												 array("part_id","int","11","","0"),
										         array("fmail_subjprefix","varchar","20","","[MG]"),
										         array("fmail_dsubject","varchar","100","","Message from {SITE_NAME}"),
										         array("fmail_emailtpl","longtext","","",""),
										         array("fmail_html","tinyint","1","","0"),
										         array("fmail_bbcode","tinyint","1","","1"),
										         array("fmail_usecaptcha","tinyint","1","","1"),
										         array("fmail_elevel","tinyint","4","","4")
											    );	   
$pkg_table_install["fmail_data"]["keys"]=array();
$pkg_table_install["fmail_data"]["records"]=array();

$pkg_table_install["fmail_list"]["struct"]=array(array("fmail_id","int","11",DB_AUTO_INC,""),
												 array("fmail_uid","int","11",DB_NULL,""),
												 array("fmail_name","varchar","40",DB_NULL,""),
												 array("fmail_addr","varchar","80",DB_NULL,"")
											    );	   
$pkg_table_install["fmail_list"]["keys"]=array(array(DB_PRIMARY,"fmail_id"));
$pkg_table_install["fmail_list"]["records"]=array();

//Do Not Edit Below This Line
if($pkg["enabled"]){
	$pkg["enabled"]="E";	
}
else{
	$pkg["enabled"]="D";	
}
