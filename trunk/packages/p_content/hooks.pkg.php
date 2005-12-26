<?php
/**********************************************************
    hooks.pkg.php
    p_content package
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
class p_content_hook{
    function p_content_display_hook(&$sql,&$error_log,$i){
        $tpl=new template($GLOBALS["MANDRIGO_CONFIG"]["TEMPLATE_PATH"].$GLOBALS["PAGE_DATA"]["DATAPATH"].$i.$GLOBALS["PAGE_DATA"]["ID"].TPL_EXT);
        return $tpl->return_template();
    }
    function p_content_vars_hook(&$sql,&$error_log,$i){
        return array();
    }
    function p_content_admin_hook(&$sql,&$error_log,$i){

    }
}

?>
