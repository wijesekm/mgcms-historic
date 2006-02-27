<?php
/**********************************************************
    server.globals.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 12/13/05

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

//
//HTTP_GET Global Vars
//
//
//0 = php style paths: index.php?p=hi&u=tmp
//1 = http style paths: index.php/s/p=hi/u=tmp
//
$GLOBALS["HTTP_GET"]["MAIL_ADDR_SYS"]=false;
if($GLOBALS["SITE_DATA"]["URL_FORMAT"] == 0){
    $GLOBALS["HTTP_GET"]["PAGE"] = (isset($HTTP_GET_VARS["p"]))?clean_page($HTTP_GET_VARS["p"]): $GLOBALS["SITE_DATA"]["MAIN_PAGE"];
    $GLOBALS["HTTP_GET"]["ACTION"] = (isset($HTTP_GET_VARS["a"]))?clean_action($HTTP_GET_VARS["a"]):DEFAULT_ACTION;
    $GLOBALS["HTTP_GET"]["KEY"]  = (isset($HTTP_GET_VARS["k"]))?clean_password($HTTP_GET_VARS["k"]):"";
    $GLOBALS["HTTP_GET"]["MAIL_ADDR"] = (isset($HTTP_GET_VARS["mail"]))?clean_email($HTTP_GET_VARS["mail"]):$GLOBALS["SITE_DATA"]["WEBMASTER_EMAIL"];
    $GLOBALS["HTTP_GET"]["ID"] = (isset($HTTP_GET_VARS["id"]))?clean_id($HTTP_GET_VARS["id"]):DEFAULT_ID;
	$GLOBALS["HTTP_GET"]["PAGE_NUMBER"] = (isset($HTTP_GET_VARS["n"]))?clean_num($HTTP_GET_VARS["n"]):DEFAULT_PN;
    if(eregi(BAD_DATA,$GLOBALS["HTTP_GET"]["MAIL_ADDR"])){
        $GLOBALS["HTTP_GET"]["MAIL_ADDR"] = clean_num($HTTP_GET_VARS["mail"]);
    }
    //{HTTP_GET_0_ADD_IN}
}
else{
    $url = array(null=>null);
    if(ereg($GLOBALS["MANDRIGO_CONFIG"]["INDEX"]."/",$HTTP_SERVER_VARS["PHP_SELF"])){
        $raw_url = eregi_replace("^.*\.$php_ex/p","p",$HTTP_SERVER_VARS["PHP_SELF"]);
        $raw_url = explode("/",$raw_url);
        $array_url = array("null","null");
        for($i =0; $i < count($raw_url); $i++){
            $tmp = explode("=",$raw_url[$i]);
            $array_url = array_merge_recursive($array_url,$tmp);
        }
        for($i=2; $i< count($array_url); $i=$i+2){
              $tmp = array($array_url[$i]=>$array_url[$i+1]);
              $url = array_merge_recursive($url, $tmp);
        }
        unset($array_url);
    }
    $GLOBALS["HTTP_GET"]["PAGE"] = (isset($url["p"]))?clean_page($url["p"]): $GLOBALS["SITE_DATA"]["MAIN_PAGE"];
    $GLOBALS["HTTP_GET"]["ACTION"] = (isset($url["a"]))?clean_action($url["a"]):DEFAULT_ACTION;
    $GLOBALS["HTTP_GET"]["KEY"] = (isset($url["k"]))?clean_password($url["k"]):"";
    $GLOBALS["HTTP_GET"]["MAIL_ADDR"] = (isset($url["mail"]))?clean_email($url["mail"]):$GLOBALS["SITE_DATA"]["WEBMASTER_EMAIL"];
    $GLOBALS["HTTP_GET"]["ID"] = (isset($url["id"]))?clean_id($url["id"]):DEFAULT_ID;
    $GLOBALS["HTTP_GET"]["PAGE_NUMBER"] = (isset($url["n"]))?clean_num($url["n"]):DEFAULT_PN;
    if(eregi(BAD_DATA,$GLOBALS["HTTP_GET"]["MAIL_ADDR"])){
        $GLOBALS["HTTP_GET"]["MAIL_ADDR"] = clean_num($url["mail"]);
    }
    
    //{HTTP_GET_1_ADD_IN}
}
if(clean_num($GLOBALS["HTTP_GET"]["MAIL_ADDR"])!=BAD_DATA){
    $GLOBALS["HTTP_GET"]["MAIL_ADDR_SYS"]=true;
}

//
//URI
//
$GLOBALS["HTTP_SERVER"]["URI"]=substr($HTTP_SERVER_VARS["REQUEST_URI"],1);

//
//if any illegal inputs were used will will add an error to prevent page generation
//this is done to help prevent users from 'fishing' for script weaknesses
//
$fail = false;
while( list($k, $v) = each($GLOBALS["HTTP_GET"])){
    if($GLOBALS["HTTP_GET"][$k]===BAD_DATA){
        $GLOBALS["HTTP_GET"][$k] = "";
        $fail = true;
    }
}
reset($GLOBALS["HTTP_GET"]);
if($fail){
    $error_log->add_error(3,"display");
}

//
//HTTP_COOKIE Vars
//
$sesid = (isset($HTTP_COOKIE_VARS["SMX_SESID"]))?explode(":",clean_SESID($HTTP_COOKIE_VARS["SMX_SESID"])):array(0,"");
$userid = (isset($HTTP_COOKIE_VARS["SMX_UID"]))?explode(":",clean_UID($HTTP_COOKIE_VARS["SMX_UID"])):array(0,1);
$GLOBALS["HTTP_COOKIE"]["U_SESID"] = $sesid[0];
$GLOBALS["HTTP_COOKIE"]["SESID"] = $sesid[1];
$GLOBALS["HTTP_COOKIE"]["U_UID"] = $userid[0];
$GLOBALS["HTTP_COOKIE"]["UID"] = $userid[1];

//{HTTP_COOKIE_ADD_IN}

//
//HTTP_POST Vars
//
$GLOBALS["HTTP_POST"]["M_USER_NAME"]=(!empty($HTTP_POST_VARS["u_name"]))?clean_name($HTTP_POST_VARS["u_name"]):"";
$GLOBALS["HTTP_POST"]["M_USER_EMAIL"]=(!empty($HTTP_POST_VARS["u_addr"]))?clean_email($HTTP_POST_VARS["u_addr"]):"";
$GLOBALS["HTTP_POST"]["M_SUBJECT"]=(!empty($HTTP_POST_VARS["u_subj"]))?clean_text($HTTP_POST_VARS["u_subj"]):"";
$GLOBALS["HTTP_POST"]["M_MESSAGE"]=(!empty($HTTP_POST_VARS["u_message"]))?clean_text($HTTP_POST_VARS["u_message"]):"";
$GLOBALS["HTTP_POST"]["S_CODE"]=(!empty($HTTP_POST_VARS["s_code"]))?clean_text($HTTP_POST_VARS["s_code"]):"";
//{HTTP_POST_ADD_IN}

?>
