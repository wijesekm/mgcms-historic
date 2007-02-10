<?php
/**********************************************************
    mysql.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 02/10/07

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

@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."db{$GLOBALS["MANDRIGO"]["CONFIG"]["PATH"]}db.class.".$php_ex);

class db extends _db{


	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	
    
    //#############
    //Connection Commands
    //#############
    
	//
	//public function db_connect($host,$port,$socket,$user,$password,$database,$persistant=true,$secure=false,$ssl="")
	//
	//connects to a database
	//
	//INPUTS:
	//$host 		- hostname of server (default: localhost)
	//$port 		- port of sever (default: 3306)
	//$socket 		- path to socket, only on localhost connections (default: /tmp/mysql.sock)
	//$user 		- username with access to database (default: )
	//$password 	- password for $user (default: )
	//$database		- database we will connect to (default: )
	//$persistant	- use persistant connections (default: true)
	//$secure		- use a ssl connection (default: false) !!Not Supported!!
	//$ssl			- ssl data string (default: ) !!Not Supported!!
    function db_connect($host,$port,$socket,$user,$password,$database,$persistant=true,$secure=false,$ssl=""){
     
     	//we need these to be set
     	if(!$user||!$password||!$database){
			return false;
		}
		
		//if host not set we will default to localhost
        if(!$host){
			$host="localhost";
		}
		
		//sets default socket if host is localhost and no port or socket is set
		if($host=="localhost"&&!$port&&!socket){
			$socket="/tmp/mysql.sock";
		}
		
		//adds port/socket to host string
		if($port){
            $host=$host.":".$port;
        }
        else if($socket){
			$host=$host.":".$socket;
		}
	
		//connects to the database
        if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){      
            if($persistant){
                $this->db_id=mysql_pconnect($host,$user,$password);
            }
            else{
                $this->db_id=mysql_connect($host,$user,$password);
            }
        }
        else{
            if($persistant){
                if(!(@$this->db_id=mysql_pconnect($host,$user,$password))){
                    return false;
                }
            }
            else{
                if(!(@$this->db_id=mysql_connect($host,$user,$password))){
                    return false;
                }
            }
        }
        if(!$this->db_switchdatabase($database)){
            $this->db_close();
            return false;
        }
        return true;
    }
    
	//
	//public db_close()
	//
	//closes current connection
	//
	//returns true on success or false on fail
    function db_close(){
        if($this->db_id){
            if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
                mysql_close($this->db_id);
            }
            else{
                if(!(@mysql_close($this->db_id))){
					return false;
				}
            }
        }
        return true;
    }
    
	//
	//public db_checkstatus()
	//
	//checks the status of the current connection
	//
	//returns true if database connection ok or false if its not
    function db_checkstatus(){
       	if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
	        if(!mysql_ping($this->db_id)){
	            return false;
	        }
        }
        else{
			if(!@mysql_ping($this->db_id)){
	            return false;
	        }	
		}
		return true;
    }    
    
	//
	//public db_switchdatabase($new_database)
	//
	//changes the database 
	//INPUTS:
	//$new_database	- database we will switch to (default: )
	//
	//returns true if switch successful or false on fail
    function db_switchdatabase($new_database){
        if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
            mysql_select_db($new_database,$this->db_id);
        }
        else{
            if(!(@mysql_select_db($new_database,$this->db_id))){
                return false;
            }
        }
        return true;
    }
    

    
    //#############
    //Query Commands
    //#############
    
	//
	//public db_fetchresult($table,$fields,$params,$row=0)
	//
	//fetches one value from the database
	//INPUTS:
	//$table 	- table we will read from (default: )
	//$fields 	- field we will recover (default: )
	//$params	- the search parameters (default: )
	//$row		- if more then one result come up which one will we use (default: 0)
	//
	//returns result or false on fail
    function db_fetchresult($table,$fields,$params,$row=0){
		$tmp_value="";
        $result=$this->db_query($this->db_formfetchquery($table,$fields,$params));
        if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
            $tmp_value=mysql_result($result,$row);
        }
        else{
            if(!(@$tmp_value=mysql_result($result,$row))){
                return false;
            }
        }
        $this->db_freeresult($result);
        return $tmp_value;
	}
    
	//
	//public db_fetcharray($table,$fields,$params="",$type="ASSOC",$rows="")
	//
	//fetches values from the database 
	//INPUTS:
	//$table 	- table we will read from (default: )
	//$fields 	- fields we will recover (default: )
	//$params	- the search parameters (default: )
	//$type		- the format the data will be returned in [ASSOC, NUM, BOTH] (default: ASSOC)
	//$row		- do we want to fetch the first row or a matrix of all values we get more then 
	//				one result (default: DB_ALL_ROWS)
	//
	//returns result or false on fail
    function db_fetcharray($table,$fields,$params="",$type="ASSOC",$rows=""){
		$tmp_value=array();	
        $result=$this->db_query($this->db_formfetchquery($table,$fields,$params));
        if($type="ASSOC"){
			$type=MYSQL_ASSOC;
		}
		else if($type="NUM"){
			$type=MYSQL_NUM;	
		}
		else{
			$type=MYSQL_BOTH;
		}     
		if($rows==DB_ALL_ROWS){
			$rows=$this->db_numrows($table,$params)+1;
		}
		if(!$rows){
	    	if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
	        	$tmp_value=mysql_fetch_array($result,$type);
	    	}
	    	else{
	            if(!(@$tmp_value=mysql_fetch_array($result,$type))){
	                return false;
	            }
	        }		
		}
		else{
			for($i=1;$i<$rows;$i++){
		        if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
		            $tmp_value[$i-1]=mysql_fetch_array($result,$type);
		        }
		        else{
		            if(!(@$tmp_value[$i-1]=mysql_fetch_array($result,$type))){
		                return false;
		            }
		        }
				@mysql_data_seek($result,$i);
			}		
		}
        $this->db_freeresult($result);
        return $tmp_value;
	}
    
	//
	//public db_update($q_type,$table,$set,$params="")
	//
	//updates values in the current database
	//INPUTS:
	//$q_type	- action we will perform [DB_UPDATE, DB_INSERT, DB_REMOVE] default(: )
	//$table 	- table we will read from (default: )
	//$set 		- set of data we will use (default: )
	//$params	- the search parameters (default: )
	//
	//returns true on success or false on fail
	function db_update($q_type,$table,$set,$params=""){
		$qstring="";
		switch($q_type){
			case DB_UPDATE:
				$qstring.="UPDATE `".mysql_real_escape_string($table)."` SET";
				for($i=0;$i<count($set);$i++){
					$qstring.=" `".mysql_real_escape_string($set[$i][0])."`='".mysql_real_escape_string($set[$i][1])."'";
					if($i<count($set)-1){
						$qstring.=",";	
					}
				}
				$qstring.=" WHERE";
				for($i=0;$i<count($params);$i++){
					$qstring.=" `".mysql_real_escape_string($params[$i][0])."`".$params[$i][1]."'".mysql_real_escape_string($params[$i][2])."'"; 	 
					if($i<count($params)-1){
						$qstring.=" AND";	
					}					
				}
			break;
			case DB_INSERT:
				$qstring.="INSERT INTO `".mysql_real_escape_string($table)."` (";
				for($i=0;$i<count($params);$i++){
					$qstring.=mysql_real_escape_string($params[$i]);
					if($i<count($set)-1){
						$qstring.=",";	
					}					
				}		
				$qstring.=") VALUES (";
				for($i=0;$i<count($set);$i++){
					$qstring.="'".mysql_real_escape_string($set[$i])."'";	
					if($i<count($set)-1){
						$qstring.=",";	
					}
				}
				$qstring.=")";	
			break;	
			case DB_REMOVE:
				$qstring.="DELETE FROM `".mysql_real_escape_string($table)."` WHERE";
	            for($i=0;$i<count($params);$i++){
	              	$qstring.=" `".mysql_real_escape_string($params[$i][0])."`".$params[$i][1]."'".mysql_real_escape_string($params[$i][2])."'";
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
			return true;	
		}
		return false;
	}
	    
	//
	//public db_dbcommands($c_type,$q_type,$db,$table="",$fields="",$params="")
	//
	//updates the current database/table
	//INPUTS:
	//$c_type	- action we will perform [DB_DROP, DB_CREATE, DB_ALTER, DB_TRUNCATE]
	//$q_type	- what will we perform the action to? [DB_TABLE, DB_DATABASE] default(: )
	//$db		- database we will perform the action on (default: )
	//$table 	- table we will read from (default: )
	//$set 		- set of data we will use (default: )
	//$fields	- fields we will use (default: )
	//$params	- the search parameters (default: )
	//
	//returns true on success or false on fail
	function db_dbcommands($c_type,$q_type,$db,$table="",$fields="",$params=""){
	  	$qstring.="";
		switch($c_type){
			case DB_DROP:
				if($q_type==DB_DATABASE){
					$qstring.="DROP DATABASE `".mysql_real_escape_string($db)."`";
				}
				else if($q_type==DB_TABLE){
					$qstring.="DROP TABLE `".mysql_real_escape_string($table)."`";
				}				
			break;
			case DB_CREATE:
				if($q_type==DB_DATABASE){
					$qstring.="CREATE DATABASE `".mysql_real_escape_string($db)."`";
				}
				else if($q_type==DB_TABLE){
					$qstring.="CREATE TABLE `".mysql_real_escape_string($table)."` (";
					for($i=0;$i<count($fields);$i++){
						$qstring.="`".mysql_real_escape_string($fields[$i][0])."` ";
						$qstring.=mysql_real_escape_string($fields[$i][1])."(".mysql_real_escape_string($fields[$i][2]).") ";
						if($fields[$i][3]==DB_NULL){
							$qstring.="default NULL";	  
						}
						else if($fields[$i][3]==DB_AUTO_INC){
							$qstring.="NOT NULL  auto_increment";	
						}
						else{
							$qstring.="NOT NULL default '".mysql_real_escape_string($fields[$i][4])."'";
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
								$qstring.="PRIMARY KEY (`".mysql_real_escape_string($params[$i][1])."`)"; 
							}
							else if($params[$i][0]==DB_UINDEX){
								$qstring.="UNIQUE KEY `".mysql_real_escape_string($params[$i][1])."` (`".mysql_real_escape_string($params[$i][2])."`)";   
							}
							else{
								$qstring.="KEY `".mysql_real_escape_string($params[$i][1])."` (`".mysql_real_escape_string($params[$i][2])."`)"; 					
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
				$qstring.="ALTER TABLE `".mysql_real_escape_string($table)."`";
				if($q_type==DB_DROP){
					for($i=0;$i<count($fields);$i++){
					  	if($fields[$i][0]==DB_TABLE){
							$qstring.=" DROP `".mysql_real_escape_string($fields[$i][1])."`";	
						}
						else if($fields[$i][0]==DB_PRIMARY){
							$qstring.=" DROP PRIMARY KEY";
						}
						else{
							$qstring.=" DROP INDEX `".mysql_real_escape_string($fields[$i][1])."`";
						}
						if($i<count($fields)-1){
							$qstring.=",";	
						}
					} 
				}
				if($q_type==DB_ADD){
					for($i=0;$i<count($fields);$i++){
					  	if($fields[$i][0]==DB_PRIMARY){
							$qstring.=" ADD PRIMARY KEY(`".mysql_real_escape_string($fields[$i][1])."`)";
						}
						else if($fields[$i][0]==DB_KEY){
							$qstring.=" ADD INDEX(`".mysql_real_escape_string($fields[$i][1])."`)";
						}
						else if($fields[$i][0]==DB_UINDEX){
							$qstring.=" ADD UNIQUE(`".mysql_real_escape_string($fields[$i][1])."`)";	
						}
					  	else{
							$qstring.=" ADD `".mysql_real_escape_string($fields[$i][0])."` ";
							$qstring.=mysql_real_escape_string($fields[$i][1])."(".mysql_real_escape_string($fields[$i][2]).") ";
							if($fields[$i][3]==DB_NULL){
								$qstring.="default NULL";	  
							}
							else if($fields[$i][3]==DB_AUTO_INC){
								$qstring.="NOT NULL  auto_increment";	
							}
							else{
								$qstring.="NOT NULL default '".mysql_real_escape_string($fields[$i][4])."'";
							}
						}	
						if($i<count($fields)-1){
							$qstring.=",";	
						}
					} 				  
				}
			break;
			case DB_TRUNCATE:
				$qstring.="TRUNCATE TABLE `".mysql_real_escape_string($table)."`";
			break;
		};
		$qstring.=";";
		if($this->db_query($qstring)){
			return true;	
		}
		return false;
	}

	//
	//public db_numrows($table,$params)
	//
	//number of rows in the current query
	//INPUTS:
	//$table 	- table we will read from (default: )
	//$params	- the search parameters (default: )
	//
	//returns number of rows or false on fail
    function db_numrows($table,$params){
        $num_rows=0;
        $result=$this->db_query($this->db_formfetchquery($table,"",$params));
        if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
            $num_rows=mysql_num_rows($result);
        }
        else{
            if(!(@$num_rows=mysql_num_rows($result))){
                return false;
            }
        }
        $this->db_freeresult($result);
        return $num_rows;
    }
    
	//
	//public db_numfields($table,$params)
	//
	//number of fields in the current query
	//INPUTS:
	//$table 	- table we will read from (default: )
	//$params	- the search parameters (default: )
	//
	//returns number of fields or false on fail
	function db_numfields($table,$params){
		$num_cols=0;
        $result=$this->db_query($this->db_formfetchquery($table,"",$params));
        if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
            $num_cols=mysql_num_fields($result);
        }
        else{
            if(!(@$num_cols=mysql_num_fields($result))){
                return false;
            }
        }
        $this->db_freeresult($result);
        return $num_cols;			
	}
	

	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################	
	
	//
	//private db_query($string)
	//
	//performs a query on the current database
	//INPUTS:
	//$string 	- query string (default: )
	//
	//returns query object or false on fail
    function db_query($string){
        $result="";
        if($GLOBALS["MANDRIGO"]["CONFIG"]["SQL_PRINT_MODE"]){
			echo $string;
		}
        if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
            $result=mysql_query($string,$this->db_id);
        }
        else{
            if(!(@$result=mysql_query($string,$this->db_id))){
                return false;
            }
        }
        return $result;
    } 

	//
	//private db_freeresult($result)
	//
	//frees the current result from memory
	//INPUTS:
	//$result 	- result object (default: )
	//
	//returns true on sucess or false on fail
    function db_freeresult($result){
        if($GLOBALS["MANDRIGO"]["CONFIG"]["DEBUG_MODE"]){
            mysql_free_result($result);
        }
        else{
            if(!@mysql_free_result($result)){
				return false;
			}
        }
        return true;
    }
    
	//
	//private db_formfetchquery($table,$fields,$params)
	//
	//forms a fetch query
	//INPUTS:
	//$table 	- table we will read from (default: )
	//$fields	- fields we will search for (default: )
	//$params	- the search parameters (default: )
	//
	//returns query string
    function db_formfetchquery($table,$fields,$params){
		$new_field="";
		$new_table="";
		if(ereg(",",$fields)){
			$fields=explode($fields);
			for($i=0;$i<count($fields);$i++){
				$new_field.="`".mysql_real_escape_string($fields[$i])."`";
				if($i<count($fields)){
					$new_field.=",";
				}
			}
		}
		else{
		  	if($fields){
				$new_field="`".mysql_real_escape_string($fields)."`";
			}
			else{
				$new_field.="*";
			}
		}
		if(ereg(",",$table)){
			$table=explode($table);
			for($i=0;$i<count($table);$i++){
				$new_table.="`".mysql_real_escape_string($table[$i])."`";
				if($i<count($table)){
					$new_table.=",";
				}
			}
		}
		else{
			$new_table="`".mysql_real_escape_string($table)."`";
		}
		$qstring="SELECT ".$new_field." FROM ".$new_table;
		$last_group=0;
		$set=false;
        if($params){
          	$qstring.=" WHERE";
            for($i=0;$i<count($params);$i++){
              	switch($params[$i][0]){
              	  	case DB_IN:
              	  		$qstring.=" `".mysql_real_escape_string($params[$i][1])."` IN (";
              	  		for($j=0;$j<count($params[$i][2]);$j++){
							$qstring.="'".mysql_real_escape_string($params[$i][2][$j])."'";
							if($j<count($params[$i][2])-1){
								$qstring.=",";
							}
						}
						$qstring.=")";
              	  	break;
              	  	case DB_BETWEEN:
              	  		$qstring.=" `".mysql_real_escape_string($params[$i][1])."` BETWEEN '";
						$qstring.=mysql_real_escape_string($params[$i][2][0])."' AND '".mysql_real_escape_string($params[$i][2][1])."'";
								
              	  	break;
				 	default:  
				 		if($params[$i][4]&&$params[$i][4]!=$last_group){
							$qstring.=" (";
							$set=true;
							$last_group=$params[$i][4];	
						}
						$qstring.=" `".mysql_real_escape_string($params[$i][0])."`".$params[$i][1]."'".mysql_real_escape_string($params[$i][2])."'";
				 		if(($params[$i+1][4]&&$params[$i+1][4]!=$last_group)||($set&&($i+1)==count($params))){
							$qstring.=" )";
							$set=false;
						}
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
