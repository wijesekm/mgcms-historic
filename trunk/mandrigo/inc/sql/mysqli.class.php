<?php
/**********************************************************
    mysqli.class.php
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
        $this->db_id=mysqli_init();
        
        mysqli_options($this->db_id, MYSQLI_INIT_COMMAND, "SET AUTOCOMMIT=0");
        mysqli_options($this->db_id, MYSQLI_OPT_CONNECT_TIMEOUT, 5);
        if($secure){
            mysqli_ssl_set($this->db_id,$ssl["KEY"],$ssl["CERT"],$ssl["CA"],$ssl["CAPATH"],$ssl["CIPHER"]);
        }
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            mysqli_real_connect($this->db_id,$host,$user,$password,$database,$port,$socket);
        }
        else{
            if(!(@mysqli_real_connect($this->db_id,$host,$user,$password,$database,$port,$socket))){
                return false;
            }
        }
        return true;
    }

    //closes the current sql connection
    function db_close(){
        if($this->db_id){
            if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                if(!mysqli_close($this->db_id)){
                    $thread_id = mysqli_thread_id($this->db_id);
                    mysqli_kill($this->db_id, $thread_id);
                    mysqli_close($this->db_id)
                }

            }
            else{
                if(!(@mysqli_close($this->db_id))){
                	$thread_id = mysqli_thread_id($this->db_id);
                    @mysqli_kill($this->db_id, $thread_id);
					@mysqli_close($this->db_id)					
				}
            }
        }
    }

    //
    //Query Commands
    //
    
    //function to find a result in a query
    //returns the result value
    function db_fetchresult($table,$fields,$params,$row=0){
		$tmp_value="";
        $result=$this->db_query($this->db_formfetchquery($table,$fields,$params));
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $tmp_value=mysqli_result($result,$row);
        }
        else{
            if(!(@$tmp_value=mysqli_result($result,$row))){
                return false;
            }
        }
        $this->db_freeresult($result);
        return $tmp_value;
	}
    
    //function to find an array of values from the database
    //returns the array of values
    function db_fetcharray($table,$fields,$params,$type="ASSOC"){
		$tmp_value="";
		
        $result=$this->db_query($this->db_formfetchquery($table,$fields,$params));
        if($type="ASSOC"){
			$type=MYSQLI_ASSOC;
		}
		else if($type="NUM"){
			$type=MYSQLI_NUM;	
		}
		else{
			$type=MYSQLI_BOTH;
		}       
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $tmp_value=mysqli_fetch_array($result,$type);
        }
        else{
            if(!(@$tmp_value=mysqli_fetch_array($result,$type))){
                return false;
            }
        }
        $this->db_freeresult($result);
        return $tmp_value;
	}
	
	//function to execute non-get db querys
	//returns true if the query was successful
	function db_update($q_type,$table,$set,$params=""){
		$qstring="";
		switch($q_type){
			case DB_UPDATE:
				$qstring.="UPDATE `".mysqli_real_escape_string($table)."` SET";
				for($i=0;$i<count($set);$i++){
					$qstring.=" `".mysqli_real_escape_string($set[$i][0])."`='".mysqli_real_escape_string($set[$i][1])."'";
					if($i<count($set)-1){
						$qstring.=",";	
					}
				}
				$qstring.=" WHERE";
				for($i=0;$i<count($params);$i++){
					$qstring.=" `".mysqli_real_escape_string($params[$i][0])."`".$params[$i][1]."'".mysqli_real_escape_string($params[$i][2])."'"; 	 
					if($i<count($params)-1){
						$qstring.=" AND";	
					}					
				}
			break;
			case DB_INSERT:
				$qstring.="INSERT INTO `".mysqli_real_escape_string($table)."` VALUES (";
				for($i=0;$i<count($set);$i++){
					$qstring.="'".mysqli_real_escape_string($set[$i])."'";	
					if($i<count($set)-1){
						$qstring.=",";	
					}
				}
				$qstring.=")";	
			break;	
			case DB_REMOVE:
				$qstring.="DELETE FROM `".mysqli_real_escape_string($table)."` WHERE";
	            for($i=0;$i<count($params);$i++){
	              	$qstring.=" `".mysqli_real_escape_string($params[$i][0])."`".$params[$i][1]."'".mysqli_real_escape_string($params[$i][2])."'";
	            	switch($params[$i][3]){
						case DB_AND;
							$qstring.=" AND";
						break;
						case DB_OR:
							$qstring.=" OR";					
						break;
						default:
							$qstring.="";
						break;
					};
				}
			break;
		};
		$qstring.=";";
		if($this->db_query($qstring)){
			$this->db_freeresult($result);
			return true;	
		}
		return false;
	}
	
	//function to preform DROP, CREATE, ALTER, and TRUNCATE sql statements
	//returns true if query is a success
	function db_dbcommands($c_type,$q_type,$db,$table="",$fields="",$params=""){
	  	$qstring.="";
		switch($c_type){
			case DB_DROP:
				if($q_type==DB_DATABASE){
					$qstring.="DROP DATABASE `".mysqli_real_escape_string($db)."`";
				}
				else if($q_type==DB_TABLE){
					$qstring.="DROP TABLE `".mysqli_real_escape_string($table)."`";
				}				
			break;
			case DB_CREATE:
				if($q_type==DB_DATABASE){
					$qstring.="CREATE DATABASE `".mysqli_real_escape_string($db)."`";
				}
				else if($q_type==DB_TABLE){
					$qstring.="CREATE TABLE `".mysqli_real_escape_string($table)."` (";
					for($i=0;$i<count($fields);$i++){
						$qstring.="`".mysqli_real_escape_string($fields[$i][0])."` ";
						$qstring.=mysqli_real_escape_string($fields[$i][1])."(".mysqli_real_escape_string($fields[$i][2]).") ";
						if($fields[$i][3]==DB_NULL){
							$qstring.="default NULL";	  
						}
						else if($fields[$i][3]==DB_AUTO_INC){
							$qstring.="NOT NULL  auto_increment";	
						}
						else{
							$qstring.="NOT NULL default '".mysqli_real_escape_string($fields[$i][4])."'";
						}
						if($i<count($fields)-1){
							$qstring.=",";
						}
					}
					if($params){
						$qstring.=",";
					}
					if($params){
						for($i=0;$i<count($params);$i++){
							if($params[$i][0]==DB_PRIMARY){
								$qstring.="PRIMARY KEY (`".mysqli_real_escape_string($params[$i][1])."`)"; 
							}
							else if($params[$i][0]==DB_UINDEX){
								$qstring.="UNIQUE KEY `".mysqli_real_escape_string($params[$i][1])."` (`".mysqli_real_escape_string($params[$i][2])."`)";   
							}
							else{
								$qstring.="KEY `".mysqli_real_escape_string($params[$i][1])."` (`".mysqli_real_escape_string($params[$i][2])."`)"; 					
							}
							if($i<count($fields)-1){
								$qstring.=",";
							}
						}
					}
					$qstring.=")"; 
				}
			break;
			case DB_ALTER:
				$qstring.="ALTER TABLE `".mysqli_real_escape_string($table)."`";
				if($q_type==DB_DROP){
					for($i=0;$i<count($fields);$i++){
					  	if($fields[$i][0]==DB_TABLE){
							$qstring.=" DROP `".mysqli_real_escape_string($fields[$i][1])."`";	
						}
						else if($fields[$i][0]==DB_PRIMARY){
							$qstring.=" DROP PRIMARY KEY";
						}
						else{
							$qstring.=" DROP INDEX `".mysqli_real_escape_string($fields[$i][1])."`";
						}
						if($i<count($fields)-1){
							$qstring.=",";	
						}
					} 
				}
				if($q_type==DB_ADD){
					for($i=0;$i<count($fields);$i++){
					  	if($fields[$i][0]==DB_PRIMARY){
							$qstring.=" ADD PRIMARY KEY(`".mysqli_real_escape_string($fields[$i][1])."`)";
						}
						else if($fields[$i][0]==DB_KEY){
							$qstring.=" ADD INDEX(`".mysqli_real_escape_string($fields[$i][1])."`)";
						}
						else if($fields[$i][0]==DB_UINDEX){
							$qstring.=" ADD UNIQUE(`".mysqli_real_escape_string($fields[$i][1])."`)";	
						}
					  	else{
							$qstring.=" ADD `".mysqli_real_escape_string($fields[$i][0])."` ";
							$qstring.=mysqli_real_escape_string($fields[$i][1])."(".mysqli_real_escape_string($fields[$i][2]).") ";
							if($fields[$i][3]==DB_NULL){
								$qstring.="default NULL";	  
							}
							else if($fields[$i][3]==DB_AUTO_INC){
								$qstring.="NOT NULL  auto_increment";	
							}
							else{
								$qstring.="NOT NULL default '".mysqli_real_escape_string($fields[$i][4])."'";
							}
						}	
						if($i<count($fields)-1){
							$qstring.=",";	
						}
					} 				  
				}
			break;
			case DB_TRUNCATE:
				$qstring.="TRUNCATE TABLE `".mysqli_real_escape_string($table)."`";
			break;
		};
		$qstring.=";";
		if($this->db_query($qstring)){
			$this->db_freeresult($result);
			return true;	
		}
		return false;
	}

   //function to find the number of rows in a query
    //returns the number of rows
    function db_numrows($table,$params){
        $num_rows=0;
        result=$this->db_query($this->db_formfetchquery($table,"",$params));
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $num_rows=mysqli_num_rows($result);
        }
        else{
            if(!(@$num_rows=mysqli_num_rows($result))){
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
        result=$this->db_query($this->db_formfetchquery($table,"",$params));
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $num_cols=mysqli_num_fields($result);
        }
        else{
            if(!(@$num_cols=mysqli_num_fields($result))){
                return false;
            }
        }
        $this->db_freeresult($result);
        return $num_cols;			
	}
	
    //Basic query function.  This should be NOT used for external (outside this class) operations unless absolutly necessary.
    function db_query($string){
        $result="";
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            $result=mysqli_query($this->db_id,$string)
        }
        else{
            if(!(@$result=mysqli_query($this->db_id,$string))){
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
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            mysqli_select_db($this->db_id,$new_database);
        }
        else{
            if(!(@mysqli_select_db($this->db_id,$new_database))){
                $thread_id = mysqli_thread_id($this->db_id);
                if(!(@mysqli_kill($this->db_id, $thread_id))){
                    return false;
                }
            }
        }
        return true;
    }
    
    //checks the current status of the connection
    //returns true if the connection is ok
    function db_checkstatus(){
        if(!mysqli_ping($this->db_id)){
            return false;
        }
        return true;
    }

    //frees the current result. Internal only!
    function db_freeresult($result){
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            mysqli_free_result($result);
        }
        else{
            @mysqli_free_result($result);
        }
    }
    
    //Internal function to form querys for select statements
    //returns the query string
    function db_formfetchquery($table,$fields,$params){
		$new_field="";
		$new_table="";
		if(ereg(",",$fields)){
			$fields=explode($fields);
			for($i=0;$i<count($fields);$i++){
				$new_field.="`".mysqli_real_escape_string($fields[$i])."`";
				if($i<count($fields)){
					$new_field.=",";
				}
			}
		}
		else{
		  	if($fields){
				$new_field="`".mysqli_real_escape_string($fields)."`";
			}
			else{
				$new_field.="*";
			}
		}
		if(ereg(",",$table)){
			$table=explode($table);
			for($i=0;$i<count($table);$i++){
				$new_table.="`".mysqli_real_escape_string($table[$i])."`";
				if($i<count($table)){
					$new_table.=",";
				}
			}
		}
		else{
			$new_table="`".mysqli_real_escape_string($table)."`";
		}
		$qstring="SELECT ".$new_field." FROM ".$new_table;
        if($params){
          	$qstring.=" WHERE";
            for($i=0;$i<count($params);$i++){
              	switch($params[$i][0]){
              	  	case DB_IN:
              	  		$qstring.=" `".mysqli_real_escape_string($params[$i][1])."` IN (";
              	  		for($j=0;$j<count($params[$i][2]);$j++){
							$qstring.="'".mysqli_real_escape_string($params[$i][2][$j])."'";
							if($j<count($params[$i][2])-1){
								$qstring.=",";
							}
						}
						$qstring.=")";
              	  	break;
              	  	case DB_BETWEEN:
              	  		$qstring.=" `".mysqli_real_escape_string($params[$i][1])."` BETWEEN '";
						$qstring.=mysqli_real_escape_string($params[$i][2][0])."' AND '".mysqli_real_escape_string($params[$i][2][1])."'";
								
              	  	break;
				 	default:  
						$qstring.=" `".mysqli_real_escape_string($params[$i][0])."`".$params[$i][1]."'".mysqli_real_escape_string($params[$i][2])."'";
				    break;
				};
            	switch($params[$i][3]){
					case DB_AND;
						$qstring.=" AND";
					break;
					case DB_OR:
						$qstring.=" OR";					
					break;
					default:
						$qstring.="";
					break;
				};
			}
        }
        else{
			$qstring.=" WHERE 1";
		}
        return $qstring.";";
	}
}
}
