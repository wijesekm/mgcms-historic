<?php
/**********************************************************
    hooks.class.php
    mg_news ver 0.7.0
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/02/07

	Copyright (C) 2006-2007 the MandrigoCMS Group

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

define('TABLE_NEWS','news');
define('TABLE_NEWS_COMMENTS','newscom');
define('NEWS_SINGLE','single');
define('FEED_RSS092','rss0.92');
define('FEED_RSS1','rss1.0');
define('FEED_RSS2','rss2.0');
define('FEED_ATOM','atom');
define('FEED_PATH','/mg_news/feed_templates/');
define('RSS_CONTENTTYPE','application/xml');
define('ATOM_CONTENTTYPE','application/atom+xml');
?>
