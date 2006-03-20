<?php
/**********************************************************
    server.globals.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/20/06

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

//
//HTTP_GET Global Vars
//
//
//0 = php style paths: index.php?p=hi&u=tmp
//1 = http style paths: index.php/s/p=hi/u=tmp
//
$GLOBALS["HTTP_GET"]["MAIL_ADDR_SYS"]=false;
if($GLOBALS["SITE_DATA"]["URL_FORMAT"] == 0){
    $GLOBALS["HTTP_GET"]["ACTION"] = (isset($HTTP_GET_VARS["a"]))?clean_action($HTTP_GET_VARS["a"]):DEFAULT_ACTION;

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
    $GLOBALS["HTTP_GET"]["ACTION"] = (isset($url["a"]))?clean_action($url["a"]):DEFAULT_ACTION;
    
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

?>
