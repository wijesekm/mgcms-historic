<?php
/**********************************************************
    clean_functions.ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 11/05/05

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

function clean_num($num){
    return (eregi("^[0-9]+$",$num))?$num:BAD_DATA;
}
function clean_SESID($id){
    return (eregi("^[0-9a-z]+$",$id))?$id:BAD_DATA;
}
function clean_UID($id){
    return (eregi("^[0-9]+$",$id))?$id:BAD_DATA;
}
function clean_email($string){
    return (eregi("^[a-z0-9._-]+@[a-z0-9-]+\.[a-z.]{2,5}$",$string))?$string : BAD_DATA;
}
function clean_id($string){
    return (eregi("^[a-z]{0,1}[0-9]+$",$string))?$string:BAD_DATA;
}
function clean_page($string){
    return (eregi("^[a-z0-9._-]+$",$string))?$string:BAD_DATA;
}
function clean_action($string){
    return (eregi("^[a-z]+$",$string))?$string:BAD_DATA;
}
function clean_password($string){
    return (eregi("^[a-z0-9._-]+$",$string))?$string:BAD_DATA;
}
function clean_name($string){
    return (eregi("[[:space:]a-z.,]",$string))?$string:BAD_DATA;
}
function clean_text($string){
    return (!eregi("[<|>]",$string))?$string:BAD_DATA;
}
function clean_username($string){
    return (eregi("^[a-z0-9._-]+$",$string))?$string:BAD_DATA;
}
function clean_url($string){
	return (eregi("[<|>[|]{|}]",$string))?BAD_DATA:$string;
}
?>
