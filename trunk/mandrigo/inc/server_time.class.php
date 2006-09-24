<?php
/**********************************************************
    server_time.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 09/23/06

	Copyright (C) 2006  Kevin Wijesekera

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


class server_time{
 
    var $gmt;
    var $server_time;
    var $client_time;

    
    function server_time($client_zone,$client_dst){
    	$this->server_time=time();
    	$this->st_mkgmt();
    	$this->st_mkct($client_zone,$client_dst);
    }
    
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################   
	 
	//
	//public function st_returngmt();
	//
	//returns the gmt time
	//
	function st_returngmt(){
		return $this->gmt;
	}
	
	//
	//public function st_returnst();
	//
	//returns the server time
	//
	function st_returnst(){
		return $this->server_time;
	}
	
	//
	//public function st_returnct();
	//
	//returns the client time
	//
	function st_returnct(){
		return $this->client_time;
	}
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################
	
	//
	//private function st_mkgmt();
	//
	//sets the gmt time stamp
	//
	function st_mkgmt(){
		$this->gmt=$server_time + date("Z",$server_time);
	}
	
	//
	//private function st_mkct($c_zone,$c_dst);
	//
	//sets the client time stamp
	//
	function st_mkct($c_zone,$c_dst){
		$this->client_time=$this->gmt + $this->st_dst($c_zone,$c_dst)
	}
	
	//
	//private function st_dst($zone,$dst);
	//
	//returns the time offset from GMT adjusted for DST
	//
    function st_dst($zone,$dst){
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
            return ($zone+1)*3600;
        }
        return ($zone)*3600;
    }
}

?>
