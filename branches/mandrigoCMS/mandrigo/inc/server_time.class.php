<?php
/**********************************************************
    server_time.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/11/07

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

class server_time{

	//current gmt 
    var $gmt;
    //current time of the server
    var $server_time;
    //current time of the client
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
	function st_returngmt(){
		return $this->gmt;
	}
	
	//
	//public function st_returnst();
	//
	//returns the server time
	function st_returnst(){
		return $this->server_time;
	}
	
	//
	//public function st_returnct();
	//
	//returns the client time
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
	function st_mkgmt(){
		$this->gmt= $this->server_time + date("Z",$server_time);
	}
	
	//
	//private function st_mkct($c_zone,$c_dst);
	//
	//sets the client time stamp
	//
	//INPUTS:
	//$c_zone	-	timezone of the client [in format +/- 5] (default: )
	//$c_dst	-	dst of the client [0,1] (default: )
	function st_mkct($c_zone,$c_dst){
	 	if($c_zone=="serv"){
			$this->client_time=$this->server_time;
		}
		else{
			$this->client_time=$this->gmt + $this->st_dst($c_zone,$c_dst);		
		}
	}
	
	//
	//private function st_dst($zone,$dst);
	//
	//returns the time offset from GMT adjusted for DST
	//
	//$zone	-	timezone [in format +/- 5] (default: )
	//$dst	-	dst [0,1] (default: 0)
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
