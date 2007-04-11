<?php
/**********************************************************
    hooks.pkg.php
    {package_name} ver {version}
	Last Edited By: {yourname}
	Date Last Edited: {date}

	Copyright (C) {year} {yourname}
	
	MandrigoCMS is Copyright (C) 2005-2007 the MandrigoCMS Group
	
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


//
//this is the only class in which formatting MATTERS
//you MUST format your class name as {packagename}_hook
//you MUST have the following hook functions:
//{packagename}_display_hook($i) - this is called by the page class when it
//                                                   is given your hook as an item to display on the page.
//{packagename}_vars_hook($i) - anything returned by this will be added to the
//                                                 page_parse_vars when the page class displays the page.
//{packagename}_admin_hook($i) - this will be called by the admin class when it is administering
//                                                 a page with your hook on it
//ALL OTHER FUNCTIONS WILL BE IGNORED
//$i is the hooks position in the stack of hooks for this page.
//
class example_hook{
    function example_display_hook($i){
        $tmp = new example_display();
        return $tmp->ex_display();
    }
    function example_vars_hook($i){
        $tmp = new example_display();
        return $tmp->ex_retvars();
    }
    function example_admin_hook($i){
        $tmp = new example_admin();
        return $tmp->admin();
    }
}

?>
