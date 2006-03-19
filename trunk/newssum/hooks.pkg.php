<?php
/**********************************************************
    hooks.pkg.php
    newssum ver 1.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/16/05

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

class newssum_hook{
  	var $pparse_vars;
    
	function newssum_display_hook(&$sql,&$error_log,$i){
	  	$cur_newssum=new newssum_display($sql);
	  	$string=$cur_newssum->display($i);
	  	$this->pparse_vars=$cur_newssum->return_vars();
		return $string;
    }
    function newssum_vars_hook(&$sql,&$error_log,$i){
		return $this->pparse_vars;
    }
    function newssum_admin_hook(&$sql,&$error_log,$i){

    }
}

?>
