<?php
/**********************************************************
    mysql.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 09/23/06

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

@include_once($GLOBALS["MANDRIGO_CONFIG"]["ROOT_PATH"]."sql/db.class.".$php_ex);

class db extends _db{

	//#################################
	//
	// PUBLIC CONNECTION FUNCTIONS
	//
	//#################################
	
    //
    //public function db_connect($host,$port,$socket,$user,$password,$database,$persistant=true,$secure=false,$ssl="");
    //
    //Connects to a mysql db server using the information supplied.  Does not work with ssl.
    //
    function db_connect($host,$port,$socket,$user,$password,$database,$persistant=true,$secure=false,$ssl=""){
        if($port){
            $host=$host.":".$port;
        }
        else if($socket){
			$host=$host.":".$socket;
		}
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){      
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
    //public function db_close();
    //
    //Closes the current connection
    //
    function db_close(){
        if($this->db_id){
            if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
                mysql_close($this->db_id);
            }
            else{
                @mysql_close($this->db_id);
            }
        }
    }
    
    //
    //public function db_switchdatabase($new_database);
    //
    //switches to a different database
    //
    function db_switchdatabase($new_database){
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            mysql_select_db($new_database,$this->db_id);
        }
        else{
            if(!(@mysql_select_db($new_database,$this->db_id))){
                return false;
            }
        }
        return true;
    }
    
    //
    //public function db_switchdatabase($new_database);
    //
    //returns true if the current connection is ok
    //
    function db_checkstatus(){
       	if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
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

	//#################################
	//
	// PUBLIC QUERY FUNCTIONS
	//
	//#################################
    
	//
    //public db_fetchresult($table,$fields,$params,$row=0);
    //
    //returns the result of a SELECT db query given by the params
    //
    function db_fetchresult($table,$fields,$params,$row=0){
		$tmp_value="";
        $result=$this->db_query($this->db_formfetchquery($table,$fields,$params));
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
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
    //public db_fetcharray($table,$fields,$params="",$type="ASSOC",$rows="");
    //
    //returns an array of values given from a SELECT query if no rows are specified.
    //if rows are specified returns a matrix to the nth row
    //
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
		if(!$rows){
	    	if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
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
		        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
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
    //public db_update($q_type,$table,$set,$params="");
    //
    //returns the result from a db UPDATE, INSERT, or DELETE query
    //
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
    //public db_dbcommands($c_type,$q_type,$db,$table="",$fields="",$params="");
    //
    //returns the result from a db DROP, CREATE, ALTER, or TRUNCATE query
    //
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
    //public db_dbcommands($c_type,$q_type,$db,$table="",$fields="",$params="");
    //
    //returns the number of rows from a SELECT query
    //
    function db_numrows($table,$params){
        $num_rows=0;
        $result=$this->db_query($this->db_formfetchquery($table,"",$params));
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
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
    //public db_dbcommands($c_type,$q_type,$db,$table="",$fields="",$params="");
    //
    //returns the number of fields from a SELECT query
    //
	function db_numfields($table,$params){
		$num_cols=0;
        $result=$this->db_query($this->db_formfetchquery($table,"",$params));
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
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
    //private db_query($string);
    //
    //returns the query id gathered from a query
    //	
    function db_query($string){
        $result="";
        if($GLOBALS["MANDRIGO_CONFIG"]["SQL_PRINT_MODE"]){
			echo $string;
		}
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
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
    //private db_freeresult($result);
    //
    //frees the query id given
    //	
    function db_freeresult($result){
        if($GLOBALS["MANDRIGO_CONFIG"]["DEBUG_MODE"]){
            mysql_free_result($result);
        }
        else{
            @mysql_free_result($result);
        }
    }
    
	//
    //private db_formfetchquery($table,$fields,$params;
    //
    //forms a query string based on the params passed in
    //	
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
