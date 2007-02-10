<?php
/**********************************************************
    db.class.php
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

//
//DB Constants
//
define("DB_UPDATE","UPDATE");
define("DB_INSERT","INSERT");
define("DB_DELETE","DELETE");
define("DB_REMOVE","DELETE");
define("DB_DROP","DROP");
define("DB_ADD","ADD");
define("DB_CREATE","CREATE");
define("DB_ALTER","ALTER");
define("DB_TRUNCATE","TRUNCATE");
define("DB_DATABASE","DATABASE");
define("DB_PRIMARY","PRIMARY");
define("DB_KEY","KEY");
define("DB_TABLE","TABLE");
define("DB_UINDEX","UINDEX");
define("DB_AND","AND");
define("DB_OR","OR");
define("DB_IN","IN");
define("DB_BETWEEN","BETWEEN");
define("DB_NULL","NULL");
define("DB_AUTO_INC","AUTO");
define("DB_ALL_ROWS","ALL");

class _db{

    //database varable
    var $db_id;

	function _db(){}

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
	//$socket 		- path to socket, only on localhost connections (default: /tmp/sql_type.sock)
	//$user 		- username with access to database (default: )
	//$password 	- password for $user (default: )
	//$database		- database we will connect to (default: )
	//$persistant	- use persistant connections (default: true)
	//$secure		- use a ssl connection (default: false)
	//$ssl			- ssl data string (default: )
	//
	//returns true on success or false on fail
    function db_connect($host,$port,$socket,$user,$password,$database,$persistant=true,$secure=false,$ssl=""){}   
    
	//
	//public db_close()
	//
	//closes current connection
	//
	//returns true on success or false on fail
    function db_close(){}
    
	//
	//public db_checkstatus()
	//
	//checks the status of the current connection
	//
	//returns true if database connection ok or false if its not
    function db_checkstatus(){}
    
	//
	//public db_switchdatabase($new_database)
	//
	//changes the database 
	//INPUTS:
	//$new_database	- database we will switch to (default: )
	//
	//returns true if switch successful or false on fail
    function db_switchdatabase($new_database){}    
    
    
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
    function db_fetchresult($table,$fields,$params,$row=0){}
    
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
    function db_fetcharray($table,$fields,$params="",$type="ASSOC",$rows=DB_ALL_ROWS){}
	
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
	function db_update($q_type,$table,$set,$params=""){}
	
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
	function db_dbcommands($c_type,$q_type,$db,$table="",$fields="",$params=""){}

	//
	//public db_numrows($table,$params)
	//
	//number of rows in the current query
	//INPUTS:
	//$table 	- table we will read from (default: )
	//$params	- the search parameters (default: )
	//
	//returns number of rows or false on fail
    function db_numrows($table,$params){}
    
	//
	//public db_numfields($table,$params)
	//
	//number of fields in the current query
	//INPUTS:
	//$table 	- table we will read from (default: )
	//$params	- the search parameters (default: )
	//
	//returns number of fields or false on fail
	function db_numfields($table,$params){}

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
    function db_query($string){}

	//
	//private db_freeresult($result)
	//
	//frees the current result from memory
	//INPUTS:
	//$result 	- result object (default: )
	//
	//returns true on sucess or false on fail
    function db_freeresult($result){}
    
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
    function db_formfetchquery($table,$fields,$params){}
}