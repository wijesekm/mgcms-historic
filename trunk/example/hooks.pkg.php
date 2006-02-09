<?php
/**********************************************************
    hooks.pkg.php
    {package_name} ver {version}
	Last Edited By: {yourname}
	Date Last Edited: {date}

	Copyright (C) {year} {yourname}

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
//this is the only class in which formatting MATTERS
//you MUST format your class name as {packagename}_hook
//you MUST have the following hook functions:
//{packagename}_display_hook(&$sql,&$error_log,$i) - this is called by the page class when it
//                                                   is given your hook as an item to display on the page.
//{packagename}_vars_hook(&$sql,&$error_log,$i) - anything returned by this will be added to the
//                                                 page_parse_vars when the page class displays the page.
//{packagename}_admin_hook(&$sql,&$error_log,$i) - this will be called by the admin class when it is administering
//                                                 a page with your hook on it
//ALL OTHER FUNCTIONS WILL BE IGNORED
//for the passing in vars $sql is the sql database object, $error_log is the error logging object
//$i is the hooks position in the stack of hooks for this page.
//For information on using the $sql or $error_log please see its respective documentation.
//
class example_hook{
    function example_display_hook(&$sql,&$error_log,$i){
        $tmp = new example_display();
        return $tmp->display();
    }
    function example_vars_hook(&$sql,&$error_log,$i){
        $tmp = new example_display();
        return $tmp->return_vars();
    }
    function example_admin_hook(&$sql,&$error_log,$i){
        $tmp = new example_admin();
        return $tmp->admin();
    }
}

?>
