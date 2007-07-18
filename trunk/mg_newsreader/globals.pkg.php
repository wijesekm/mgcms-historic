<?php
/**********************************************************
    globals.pkg.php
    newssum ver 0.6.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 7-17-06

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
if(!defined('START_MANDRIGO')){
    die('<html><head>
            <title>Forbidden</title>
        </head><body>
            <h1>Forbidden</h1><hr width="300" align="left"/>\n<p>You do not have permission to access this file directly.</p>
        </html></body>');
}

define('TABLE_NEWS_SUM_DATA','newssum_data');
define('TABLE_NEWSSUM','newssum');
define('TABLE_NEWS','news');
define('TPL_NEWSSUM','newssum');
define('TPL_NEWSSUM_MINI','_newssum_mini');
define('SKIN_MINI','mini');

?>