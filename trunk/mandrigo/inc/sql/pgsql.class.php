<?php
/**********************************************************
    pgsql.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 3/03/06

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

    //database varable
    var $db_id;

    //
    //Connection Commands
    //
    
    //connects to the database server and selects the initial database
    //returns true if connection and database selection successfull
    function db_connect($host,$port,$socket,$user,$password,$database,$persistant=true,$secure=false,$ssl=""){
        $con_str="";
        if($host){
            $con_str.="user=".$user;
        }
        if($password){
            $con_str.="password=".$password;
        }
        if($host){
            $con_str.="host=".$host;
        }
        if($port){
            $con_str.="port=".$port;
        }
        if($database){
            $con_str.="dbname=".$database;
        }
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            if($persistant){
                $this->db_id=pg_pconnect($con_str);
            }
            else{
                $this->db_id=pg_connect($con_str);
            }
        }
        else{
            if($persistant){
                if(!(@$this->db_id=pg_pconnect($con_str))){
                    return false;
                }
            }
            else{
                if(!(@$this->db_id=pg_connect($con_str))){
                    return false;
                }
            }
        }
        return true;
    }

    //closes the current sql connection
    function db_close(){
        if($this->db_id){
            if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                pg_close($this->db_id);
            }
            else{
                @pg_close($this->db_id)
            }
        }
    }

    //
    //Query Commands
    //
    
    //function to find a result in a query
    //returns the result value
    function db_fetchresult($table,$value,$params,$row=0){
		$tmp_value="";
		$qstring="SELECT ".pg_escape_string($value)." FROM ".pg_escape_string($table)."";
        if($params&&!$params%3){
            for($i=0;$i<count($params);$i+=3){
                $qstring.=" ".pg_escape_string($params[$i])."".$params[$i+1]."".pg_escape_string($params[$i+2])."";
            }
        }
        $qstring.=";";
        $result=$this->db_query($qstring);
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $tmp_value=pg_fetch_result($result,$row);
        }
        else{
            if(!(@$tmp_value=pg_fetch_result($result,$row))){
                return false;
            }
        }
        $this->db_freeresult($result);
        return $tmp_value;
	}
    
    //function to find an array of values from the database
    //returns the array of values
    function db_fetcharray($table,$value,$params,$type="ASSOC"){
		$tmp_value="";
		$new_value="";
		if(ereg(",",$value)){
			$value=explode($value);
			for($i=0;$i<count($value);$i++){
				$new_value.="".pg_escape_string($value[$i])."";
				if($i<count($value)){
					$new_value.=",";
				}
			}
		}
		else{
			$new_value="".pg_escape_string($value)."";
		}
		$qstring="SELECT ".$new_value." FROM ".pg_escape_string($table)."";
        if($params&&!$params%3){
            for($i=0;$i<count($params);$i+=3){
                $qstring.=" ".pg_escape_string($params[$i])."".$params[$i+1]."".pg_escape_string($params[$i+2])."";
            }
        }
        $qstring.=";";
        $result=$this->db_query($qstring);
        if($type="ASSOC"){
			$type=PGSQL_ASSOC;
		}
		else if($type="NUM"){
			$type=PGSQL_NUM;	
		}
		else{
			$type=PGSQL_BOTH;
		}       
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $tmp_value=pg_fetch_array($result,0,$type);
        }
        else{
            if(!(@$tmp_value=pg_fetch_array($result,0,$type))){
                return false;
            }
        }
        $this->db_freeresult($result);
        return $tmp_value;
	}
		
    //function to find the number of rows in a query
    //returns the number of rows
    function db_numrows($table,$params){
        $num_rows=0;
        $qstring="SELECT * FROM ".pg_escape_string($table);
        if($params&&!$params%3){
            for($i=0;$i<count($params);$i+=3){
                $qstring.=" ".pg_escape_string($params[$i]).$params[$i+1].pg_escape_string($params[$i+2]);
            }
        }
        $qstring.=";";
        $result=$this->db_query($qstring);
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $num_rows=pg_num_rows($result);
        }
        else{
            if(!(@$num_rows=pg_num_rows($result))){
                return false;
            }
        }
        $this->db_freeresult($result);
        return $num_rows;
    }
    
    //function to find the number of fields in a query
    //returns the number of fields
	function db_numfields($table,$params){
		$num_cols=0;
		$qstring="SELECT * FROM ".pg_escape_string($table);
        if($params&&!$params%3){
            for($i=0;$i<count($params);$i+=3){
                $qstring.=" ".pg_escape_string($params[$i]).$params[$i+1].pg_escape_string($params[$i+2]);
            }
        }
        $qstring.=";";
        $result=$this->db_query($qstring);
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $num_cols=pg_num_fields($result);
        }
        else{
            if(!(@$num_cols=pg_num_fields($result))){
                return false;
            }
        }
        $this->db_freeresult($result);
        return $num_cols;			
	}
	
    //internal only query function.  Should only be called by functions in the db class!!
    function db_query($string){
        $result="";
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $result=pg_query($this->db_id,$string)
        }
        else{
            if(!(@$result=pg_query($this->db_id,$string))){
                return false;
            }
        }
        return $result;
    }
    //
    //Misc Commands
    //
    
    //change the current database
    //returns true if database selection successfull
    function db_switchdatabase($new_database){
        return false;
    }
    
    //checks the current status of the connection
    //returns true if the connection is ok
    function db_checkstatus(){
        if(!pg_ping($this->db_id)){
            return false;
        }
        return true;
    }

    //frees the current result. Internal only!
    function db_freeresult($result){
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            pg_free_result($result);
        }
        else{
            @pg_free_result($result);
        }
    }
}
