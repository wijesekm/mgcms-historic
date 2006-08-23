<?php
/**********************************************************
    admin.pkg.php
    menu ver 0.6.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 08/23/06

	Copyright (C) 2006 Kevin Wijesekera
	
	MandrigoCMS is Copyright (C) 2005-2006 the MandrigoCMS Group
	
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
if(!defined('START_MANDRIGO')){
    die('<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width="300" align="left"/><p>You do not have permission to access this file directly.</p>
        </html></body>');
}

//this file will contain admin functionality which will be called by the
//{packagename}_admin_hook function which you will write.  Basically do what ever you want with it.

class example_admin{

    function example_admin{
        return true;
    }
    function admin(){
        return "hi";
    }
}
?>
