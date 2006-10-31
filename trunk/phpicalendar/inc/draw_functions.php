<?php
/**********************************************************
    globals.pkg.php
    ical ver 2.22
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 10-30-06

	PHP iCalendar is copyright the PHP iCalendar team (http://phpicalendar.net/)
	and is published under the GNU General Public License
	
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

//
//successfully converted to mandrigo version 0.6.0 as of 10-31-06 - kmw
//
function drawEventTimes ($start, $end,$gridLength) {
	
	preg_match ('/([0-9]{2})([0-9]{2})/', $start, $time);
	$sta_h = $time[1];
	$sta_min = $time[2];
	$sta_min = sprintf("%02d", floor($sta_min / $gridLength) * $gridLength);
	if ($sta_min == 60) {
		$sta_h = sprintf("%02d", ($sta_h + 1));
		$sta_min = "00";
	}
	
	preg_match ('/([0-9]{2})([0-9]{2})/', $end, $time);
	$end_h = $time[1];
	$end_min = $time[2];
	$end_min = sprintf("%02d", floor($end_min / $gridLength) * $gridLength);
	if ($end_min == 60) {
		$end_h = sprintf("%02d", ($end_h + 1));
		$end_min = "00";
	}
	
	if (($sta_h . $sta_min) == ($end_h . $end_min))  {
		$end_min += $gridLength;
		if ($end_min == 60) {
			$end_h = sprintf("%02d", ($end_h + 1));
			$end_min = "00";
		}
	}
	
	$draw_len = ($end_h * 60 + $end_min) - ($sta_h * 60 + $sta_min);
	
	return array ("draw_start" => ($sta_h . $sta_min), "draw_end" => ($end_h . $end_min), "draw_length" => $draw_len);
}

// word wrap function that returns specified number of lines
// when lines is 0, it returns the entire string as wordwrap() does it
function word_wrap($str, $length, $lines=0) {
	if ($lines > 0) {
		$len = $length * $lines;
		if ($len < strlen($str)) {
			$str = substr($str,0,$len).'...';
		}
	}
	return $str;
}
?>