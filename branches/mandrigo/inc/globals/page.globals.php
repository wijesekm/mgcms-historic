<?php
/**********************************************************
    site.globals.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 12/14/05

	Copyright (C) 2005  Kevin Wijesekera

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

$page_input_type = "page_name";
if($GLOBALS["SITE_DATA"]["PAGE_INPUT_TYPE"]==1){
    $page_input_type = "page_id";
}

if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
    $page_data=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_PAGE_DATA,"",array(array($page_input_type,"=",$GLOBALS["HTTP_GET"]["PAGE"])));
}
else{
    if(!$page_data=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_PAGE_DATA,"",array(array($page_input_type,"=",$GLOBALS["HTTP_GET"]["PAGE"])))){
        $GLOBALS["error_log"]->add_error(1,"display");
    }
}

$GLOBALS["PAGE_DATA"]["ID"] = $page_data["page_id"];
if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
	if($GLOBALS["PAGE_DATA"]["ID"]==0&&!$GLOBALS["error_log"]->get_status()){
	    $GLOBALS["error_log"]->add_error(2,"access");
	}
}
$GLOBALS["PAGE_DATA"]["NAME"] = $page_data["page_name"];

$GLOBALS["USER_DATA"]["PERMISSIONS"]["READ"]=false;
$GLOBALS["USER_DATA"]["PERMISSIONS"]["READ_RESTRICTED"]=false;
$GLOBALS["USER_DATA"]["PERMISSIONS"]["POST_TO"]=false;
$GLOBALS["USER_DATA"]["PERMISSIONS"]["EDIT"]=false;
$GLOBALS["USER_DATA"]["PERMISSIONS"]["CHANGE_DATA"]=false;
$GLOBALS["USER_DATA"]["PERMISSIONS"]["FULL_CONTROL"]=false;

$default_permissions="";
$page_permissions="";
if(!$GLOBALS["error_log"]->get_status()){
    for($i=0;$i<count($GLOBALS["USER_DATA"]["GROUPS"]);$i++){
        if(!$page_permissions=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_GROUP_PERMISSIONS,"",array(array("group_id","=",$GLOBALS["USER_DATA"]["GROUPS"]["$i"],DB_AND),array("page_id","=",$GLOBALS["PAGE_DATA"]["ID"])))){
			$default_permissions=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_GROUP_PERMISSIONS,"",array(array("group_id","=",$GLOBALS["USER_DATA"]["GROUPS"]["$i"],DB_AND),array("page_id","=",0)));
			if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]&&!$default_permissions){
                $GLOBALS["error_log"]->add_error(1,"access");
                break;
            }
			else{
	            if($default_permissions["read"]){
	                $GLOBALS["USER_DATA"]["PERMISSIONS"]["READ"]=true;
	            }
	            if($default_permissions["read_restricted"]){
	                $GLOBALS["USER_DATA"]["PERMISSIONS"]["READ_RESTRICTED"]=true;
	            }
	            if($default_permissions["post_to"]){
	                $GLOBALS["USER_DATA"]["PERMISSIONS"]["POST_TO"]=true;
	            }
	            if($default_permissions["edit"]){
	                $GLOBALS["USER_DATA"]["PERMISSIONS"]["EDIT"]=true;
	            }
	            if($default_permissions["change_data"]){
	                $GLOBALS["USER_DATA"]["PERMISSIONS"]["CHANGE_DATA"]=true;
	            }
	            if($default_permissions["full_control"]){
	                $GLOBALS["USER_DATA"]["PERMISSIONS"]["FULL_CONTROL"]=true;
	            }
			}
        }
        else{
            if($page_permissions["read"]){
                $GLOBALS["USER_DATA"]["PERMISSIONS"]["READ"]=true;
            }
            if($page_permissions["read_restricted"]){
                $GLOBALS["USER_DATA"]["PERMISSIONS"]["READ_RESTRICTED"]=true;
            }
            if($page_permissions["post_to"]){
                $GLOBALS["USER_DATA"]["PERMISSIONS"]["POST_TO"]=true;
            }
            if($page_permissions["edit"]){
                $GLOBALS["USER_DATA"]["PERMISSIONS"]["EDIT"]=true;
            }
            if($page_permissions["change_data"]){
                $GLOBALS["USER_DATA"]["PERMISSIONS"]["CHANGE_DATA"]=true;
            }
            if($page_permissions["full_control"]){
                $GLOBALS["USER_DATA"]["PERMISSIONS"]["FULL_CONTROL"]=true;
            }
        }
    }
}
$page_permissions=array();
$default_permissions=array();
if(!$GLOBALS["error_log"]->get_status()){
    if(!($r_page_data=$sql_db->db_fetcharray(TABLE_PREFIX.TABLE_RESTRICTED_PAGE_DATA,"",array(array("page_id","=",$GLOBALS["PAGE_DATA"]["ID"]))))||!$GLOBALS["USER_DATA"]["PERMISSIONS"]["READ_RESTRICTED"]){
        $GLOBALS["PAGE_DATA"]["AUTH_PAGE"]=false;
        $GLOBALS["PAGE_DATA"]["RNAME"]=$page_data["page_rname"];
        $GLOBALS["PAGE_DATA"]["TITLE"]=$page_data["page_title"];
        $GLOBALS["PAGE_DATA"]["VARS"]=explode(";",$page_data["page_vars"]);
        $GLOBALS["PAGE_DATA"]["HOOKS"]=explode(";",$page_data["page_hooks"]);
        $GLOBALS["PAGE_DATA"]["SUBPAGES"]=explode(";",$page_data["page_subpages"]);
        $GLOBALS["PAGE_DATA"]["PARENT"]=$page_data["page_parent"];
        $GLOBALS["PAGE_DATA"]["IS_ROOT"]=$page_data["page_root"];
        $GLOBALS["PAGE_DATA"]["DATAPATH"]=$page_data["page_datapath"];
        $GLOBALS["PAGE_DATA"]["PAGE_STATUS"]=$page_data["page_status"];
    }
    else if($GLOBALS["USER_DATA"]["PERMISSIONS"]["READ"]){
        $GLOBALS["PAGE_DATA"]["AUTH_PAGE"]=true;
        $GLOBALS["PAGE_DATA"]["RNAME"]=$r_page_data["page_rname"];
        $GLOBALS["PAGE_DATA"]["TITLE"]=$r_page_data["page_title"];
        $GLOBALS["PAGE_DATA"]["VARS"]=explode(";",$r_page_data["page_vars"]);
        $GLOBALS["PAGE_DATA"]["HOOKS"]=explode(";",$r_page_data["page_hooks"]);
        $GLOBALS["PAGE_DATA"]["SUBPAGES"]=$r_page_data["page_subpages"];
        $GLOBALS["PAGE_DATA"]["PARENT"]=$r_page_data["page_parent"];
        $GLOBALS["PAGE_DATA"]["IS_ROOT"]=$r_page_data["page_root"];
        $GLOBALS["PAGE_DATA"]["DATAPATH"]=$r_page_data["page_datapath"];
        $GLOBALS["PAGE_DATA"]["PAGE_STATUS"]=$r_page_data["page_status"];
    }
}
$r_page_data=array();
$page_data=array();
?>