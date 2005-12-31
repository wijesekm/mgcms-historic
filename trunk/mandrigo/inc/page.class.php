<?php
/**********************************************************
    page.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 12/24/05

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

class page{

    var $page_error_logger;
    var $page_db;
    var $page_parse_vars;

    function page(&$error_logger,&$db){
        $this->page_error_logger=$error_logger;
        $this->page_db=$db;
                $this->page_parse_vars = array(
                    "SITE_NAME",$GLOBALS["SITE_DATA"]["SITE_NAME"]
                    ,"SITE_URL",$GLOBALS["SITE_DATA"]["SITE_URL"]
                    ,"IMG_URL",$GLOBALS["SITE_DATA"]["IMG_URL"]
                    ,"WEBMASTER_NAME",$GLOBALS["SITE_DATA"]["WEBMASTER_NAME"]
                    ,"LAST_UPDATED",$GLOBALS["SITE_DATA"]["LAST_UPDATED"]
                    ,"MANDRIGO_VER",$GLOBALS["SITE_DATA"]["MANDRIGO_VER"]
                    ,"USER_NAME",$GLOBALS["USER_DATA"]["NAME"]
                    ,"CURRENT_PAGE",$GLOBALS["PAGE_DATA"]["RNAME"]
                );
        if(count($GLOBALS["PAGE_DATA"]["VARS"])%2==0){
            $this->page_parse_vars=array_merge_recursive($GLOBALS["PAGE_DATA"]["VARS"],$this->page_parse_vars);
        }
    }
    function display(){
        $tpl=new template();
        if($GLOBALS["USER_DATA"]["AUTHENTICATED"]&&$GLOBALS["USER_DATA"]["PERMISSIONS"]["READ_RESTRICTED"]){
            if(!$tpl->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].TPL_AUTH_SITE)){
                if(!$tpl->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].TPL_MAIN_SITE)){
                    if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                        $this->page_error_logger->add_error(30,"script");
                        die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                            $this->page_error_logger->generate_report().$GLOBALS["HTML"]["EEND"]);
                    }
                }
            }
        }
        else{
            if(!$tpl->load($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].TPL_MAIN_SITE)){
                if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                    $this->page_error_logger->add_error(30,"script");
                    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                        $this->page_error_logger->generate_report().$GLOBALS["HTML"]["EEND"]);
                }
            }
        }
        if($this->page_error_logger->get_status()!=0){
                    $this->page_parse_vars=array_merge_recursive(array("CONTENT",$this->page_error_logger->generate_report(),"PAGE_TITLE",$GLOBALS["LANGUAGE"]["ETITLE2"]),$this->page_parse_vars);
        }
        else if($GLOBALS["PAGE_DATA"]["PAGE_STATUS"]||$GLOBALS["HTTP_GET"]["KEY"]==$GLOBALS["SITE_DATA"]["BYPASS_CODE"]){
                if($GLOBALS["PAGE_DATA"]["AUTH_PAGE"]&&!($GLOBALS["USER_DATA"]["PERMISSIONS"]["READ_RESTRICTED"]||$GLOBALS["USER_DATA"]["PERMISSIONS"]["FULL_CONTROL"])){
                    $this->page_error_logger->add_error(1,"access");
                    if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                        die($GLOBALS["LANGUAGE"]["PERMISSION"]);
                    }
                }
                if(!$GLOBALS["PAGE_DATA"]["AUTH_PAGE"]&&!($GLOBALS["USER_DATA"]["PERMISSIONS"]["READ"]||$GLOBALS["USER_DATA"]["PERMISSIONS"]["FULL_CONTROL"])){
                    $this->page_error_logger->add_error(1,"access");
                    if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                        die($GLOBALS["LANGUAGE"]["PERMISSION"]);
                    }
                }
            $content=$this->gen_content();
            if(!$content){
                if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                    $this->page_error_logger->add_error(2,"display");
                }
            }
            if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                if($this->page_error_logger->get_status()!=0){
                    $this->page_parse_vars=array_merge_recursive(array("CONTENT",$this->page_error_logger->generate_report(),"PAGE_TITLE",$GLOBALS["LANGUAGE"]["ETITLE2"]),$this->page_parse_vars);
                }
                else{
                    $this->page_parse_vars=array_merge_recursive(array("CONTENT",$content,"PAGE_TITLE",$GLOBALS["PAGE_DATA"]["TITLE"]),$this->page_parse_vars);
                }
            }
            else{
                $content=$this->gen_content();
                $this->page_parse_vars=array_merge_recursive(array("CONTENT",$content,"PAGE_TITLE",$GLOBALS["PAGE_DATA"]["TITLE"]),$this->page_parse_vars);
            }

        }
        else{
            if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                if($this->page_error_logger->get_status()==2){
                    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                        $this->page_error_logger->generate_report().$GLOBALS["HTML"]["EEND"]);
                    return false;
                }
                else{
                    $tmp_tpl=new template($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].TPL_OFF_PAGE);
                    $content=$tmp_tpl->return_template();
                    $this->page_parse_vars=array_merge_recursive(array("CONTENT",$content,"PAGE_TITLE",$GLOBALS["LANGUAGE"]["OPTITLE"]),$this->page_parse_vars);
                }
            }
            else{
                $tmp_tpl=new template($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].TPL_OFF_PAGE);
                $content=$tmp_tpl->return_template();
                $this->page_parse_vars=array_merge_recursive(array("CONTENT",$content,"PAGE_TITLE",$GLOBALS["LANGUAGE"]["OPTITLE"]),$this->page_parse_vars);
            }
        }
        $tpl->pparse($this->page_parse_vars);
        return $tpl->return_template();
    }
    function gen_content(){
        $content="";
        $soq=count($GLOBALS["PAGE_DATA"]["HOOKS"]);
        for($i=0;$i<$soq;$i++){
            if(!($sql_result=$this->page_db->fetch_array("SELECT * FROM `".TABLE_PREFIX.TABLE_PACKAGE_DATA."` WHERE `package_id`='".$GLOBALS["PAGE_DATA"]["HOOKS"][$i]."';"))){
                if(!$GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                    $this->page_error_logger->add_error(14,"sql");
                    die($GLOBALS["HTML"]["EHEAD"].$GLOBALS["LANGUAGE"]["ETITLE"].$GLOBALS["HTML"]["EBODY"].
                        $this->page_error_logger->generate_report().$GLOBALS["HTML"]["EEND"]);
                }
            }
            if(!$tmp=$this->regester_hook($i,$sql_result["package_name"])){
                return false;
            }
            $content.=$tmp;
        }
        return $content;
    }
    function regester_hook($i,$hook){
        $content="";
        $string="$"."hookc=new ".$hook.HOOK_CLASS;
        eval($string);
        $string="$"."content=$"."hookc->".$hook.HOOK_DISPLAY;
        eval($string);
        $string="$"."vars=$"."hookc->".$hook.HOOK_VARS;
        if(count($vars)%2==0){
            $this->page_parse_vars=array_merge_recursive($vars,$this->page_parse_vars);
        }
        return $content;
    }
}

?>