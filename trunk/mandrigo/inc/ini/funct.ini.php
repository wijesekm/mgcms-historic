<?php
/**********************************************************
    funct.ini.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 11/14/05

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

//
//Function checks the sessionid saved on the server with the one provided
//by the user.  Also checks username validity.
//
    function user_is_logged_in($cookie_sesid,$cookie_uid,&$sql_db){
        if(!$cookie_sesid||!$cookie_uid){
            return false;
        }
        if($cookie_uid===1){
            return false;
        }
        $sql_sesid=$sql_db->db_fetchresult(TABLE_PREFIX.TABLE_USER_DATA,"user_session",array(array("user_id","=",$cookie_uid)));
        if($sql_sesid!=$cookie_sesid){
            return false;
        }
        return true;
    }
    //forms an associative array based on the error log file
    //ex array("error_number","error_message")
    function open_array_assoc($file_name,$deliminator,$text=""){
        $file="";
        if(!$text){
            if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                $f=fopen($file_name,"r");
            }
            else{
                if(!(@$f=fopen($file_name,"r"))){
                return false;
                }
            }
    		while(!feof($f)){
    			$file.=fgets($f);
            }
    		fclose($f);
        }
        else{
            $file=$text;
        }
        $raw_data = explode($deliminator,$file);
        $data = array("nan"=>"nan");
        $raw_data_length=count($raw_data);
        if($raw_data_length%2){
            return false;
        }
        for($i=0; $i< $raw_data_length; $i=$i+2){
            $data = add_array($data,$raw_data[$i],$raw_data[$i+1]);
        }
        return $data;
    }
    //simple function to a value to an array
    function add_array($array, $key, $val){
        $tmp = array("$key"=>"$val");
        $array = array_merge_recursive($array, $tmp);
        return $array;
    }

?>
