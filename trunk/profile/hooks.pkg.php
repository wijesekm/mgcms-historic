<?php
/**********************************************************
    hooks.pkg.php
    profile ver 1.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 2-17-06

	Copyright (C) 2006 Kevin Wijesekera

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

class profile_hook{
    function profile_display_hook(&$sql,&$error_log,$i){
      	$cur_profile=new profile_display($sql);
      	$string="";
      	if(ereg("g",$GLOBALS["HTTP_GET"]["ID"])){
			$GLOBALS["HTTP_GET"]["ID"]=substr($GLOBALS["HTTP_GET"]["ID"],1);	
			$string=$cur_profile->display_group($i);
		}
		else{
			$GLOBALS["HTTP_GET"]["ID"]=substr($GLOBALS["HTTP_GET"]["ID"],1);
			$string=$cur_profile->display_user($i);
		}
		return $string;
    }
    function profile_vars_hook(&$sql,&$error_log,$i){
        return array();
    }
    function profile_admin_hook(&$sql,&$error_log,$i){

    }
}

?>
