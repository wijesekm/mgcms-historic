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


$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["READ"]=0;
$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["POST"]=0;
$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["EDIT"]=0;
$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["CONFIG"]=0;

$soq=count($GLOBALS["MANDRIGO"]["CURRENTUSER"]["GROUPS"]);
$filter=array(array("acl_pageid","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"],DB_OR,1),array("acl_pageid","=",0,DB_AND,1));
for($k=0;$k<$soq;$k++){
 	if($k+1<$soq){
		$filter[$k+2]=array("acl_groupid","=",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["GROUPS"][$k],DB_OR,2);	
	}
	else{
		$filter[$k+2]=array("acl_groupid","=",$GLOBALS["MANDRIGO"]["CURRENTUSER"]["GROUPS"][$k],"",2);	
	}
}

$acl=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_ACL,"",$filter,"ASSOC",DB_ALL_ROWS);
$soq=count($acl);
for($k=0;$k<$soq;$k++){
	if((int)$acl[$k]["read_level"]>$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["READ"]){
		$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["READ"]=(int)$acl[$k]["read_level"];
	}
	if((int)$acl[$k]["post_level"]>$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["POST"]){
		$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["POST"]=(int)$acl[$k]["post_level"];
	}
	if((int)$acl[$k]["edit_level"]>$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["EDIT"]){
		$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["EDIT"]=(int)$acl[$k]["edit_level"];
	}
	if((int)$acl[$k]["config_level"]>$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["CONFIG"]){
		$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["CONFIG"]=(int)$acl[$k]["config_level"];
	}
	if((int)$acl[$k]["full_control"]===1){
		$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["READ"]=4;
		$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["POST"]=4;
		$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["EDIT"]=4;
		$GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["CONFIG"]=4;	
		break;	
	}
}
$filter="";
$acl="";