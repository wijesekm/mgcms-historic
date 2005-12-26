<?php
/**********************************************************
    server_time.class.php
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

class server_time{
    var $gmt;

    //sets the current GMT
    function server_time($server_zone,$server_dst){
        //gmmktime wasnt working for some reason
        $this->gmt=time()-$this->dst($server_zone,$server_dst)*3600;
    }
    //returns the current GMT
    function gmt(){
        return $this->gmt;
    }
    //
    //changes the zone offset for Daylight Savings Time
    //
    function dst($zone,$dst){
        $time_stamp_dst_april = 0;
        $time_stamp_dst_october = 0;
        for($i=1; $i < 31; $i++){
            $tmp_timestamp = mktime(0,0,0,4,$i);
            if(eregi(date("l",$tmp_timestamp),"Sunday")){
                $time_stamp_dst_april = $tmp_timestamp;
            }
        }
        for($i=31; $i > 1; $i--){
            $tmp_timestamp = mktime(0,0,0,10,$i);
            if(eregi(date("l",$tmp_timestamp),"Sunday")){
                $time_stamp_dst_october = $tmp_timestamp;
            }
        }
        if(time()>=$time_stamp_dst_april&&time()<=$time_stamp_dst_october&&$dst==1){
            return $zone+1;
        }
        return $zone;
    }
    //Gets the users local time
    function local_time($local_zone,$local_dst){
        return $this->gmt + $this->dst($local_zone,$local_dst)*3600;
    }
}

?>
