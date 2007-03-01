<?php
/**********************************************************
    page.globals.php
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

//
//Sets what the page-input is
//
$pageinput_type = "pg_name";
if($GLOBALS["MANDRIGO"]["SITE"]["PAGE_TYPE"]==1){
    $pageinput_type = "pg_id";
}

if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
    $page_data=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_PAGES,"",array(array($pageinput_type,"=",$GLOBALS["MANDRIGO"]["VARS"]["PAGE"])));
}
else{
    if(!$page_data=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_PAGES,"",array(array($pageinput_type,"=",$GLOBALS["MANDRIGO"]["VARS"]["PAGE"])))){
        $GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(2,"display");
    }
}

$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]=(int)trim($page_data["pg_id"]);

//
//If page has ID of 0 it is a bad page
//

if($GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"]===0){
	if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
		die();
	}
	else{
		$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_adderror(9,"sql");
	   	die($GLOBALS["MANDRIGO"]["ELOG"]["HTMLHEAD"].$GLOBALS["MANDRIGO"]["ELOG"]["TITLE"].$GLOBALS["MANDRIGO"]["ELOG"]["HTMLBODY"].
           	$GLOBALS["MANDRIGO"]["ERROR_LOGGER"]->el_generatereport().$GLOBALS["MANDRIGO"]["ELOG"]["HTMLEND"]);		
	}
}


//
//Now we will get the page data
//
$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"]=(string)trim($page_data["pg_name"]);
$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["FULLNAME"]=(string)trim($page_data["pg_fullname"]);
$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["TITLE"]=(string)trim($page_data["pg_title"]);
$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["VARS"]=$page_data["pg_vars"];
$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["HOOKS"]=explode(";",(string)trim($page_data["pg_hooks"]));
$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["SUBPAGES"]=explode(";",(string)trim($page_data["pg_subpages"]));
$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["PARENT"]=(int)trim($page_data["pg_parent"]);
$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ISROOT"]=(int)trim($page_data["pg_root"]);
$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["DATAPATH"]=(string)trim($page_data["pg_datapath"]);
$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["STATUS"]=(int)trim($page_data["pg_status"]);
$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["READLEVEL"]=(int)trim($page_data["pg_readlevel"]);
