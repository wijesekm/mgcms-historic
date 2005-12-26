<?php
/**********************************************************
    pgsql.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 11/04/05

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

class db{
    var $cur_db;

    function connect($host,$user_name,$password,$database,$port=""){
        $con="host=$host ";
        if($port){
            $con.="port=$port ";
        }
        $con.="dbname=$database user=$user_name password=$password";
		$this->cur_db=pg_connect($con);
        if(!$this->cur_db) {
            return false;
        }
        return true;
    }
    function close(){
        pg_close($this->cur_db);
    }

    //executes a pgSQL query
   	function query($query_string){
        if(!$this->result= pg_query($this->cur_db,$query_string)){
            return false;
        }
        return $this->result;
	}

	//fetches the number of rows given a query
	function num_rows($query_string){
        $query_id = $this->query($query_string);
		$data = ($query_id) ? pg_num_rows($query_id):0;
        $this->free_result($query_id);
        return $data;
	}

	//fetches the number of fields given a query
	function num_fields($query_string){
        $query_id = $this->query($query_string);
        $data = ($query_id) ?  pg_num_fields($query_id):0;
        $this->free_result($query_id);
        return $data;
	}

	//fetches an array of the row given a query
    function fetch_array($query_string,$type=ASSOC){//PGSQL_ASSOC,PGSQL_NUM
        $type.="PGSQL_".$type;
        $query_id = $this->query($query_string);
        $data =  pg_fetch_array($query_id,0,$type);
        $this->free_result($query_id);
        return $data;
    }

    //clears the pgSQL query
    function free_result($query_id){
        return pg_freeresult($query_id);
    }

    //fetches one result given a query
    function fetch_result($query_string,$row=0){
        $query_id = $this->query($query_string);
        $data =  pg_fetch_result($query_id,$row,0);
        $this->free_result($query_id);
        return $data;
    }
}
