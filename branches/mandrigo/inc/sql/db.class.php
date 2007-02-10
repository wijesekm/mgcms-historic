<?php
/**********************************************************
    mysql.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 3/08/06

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

class _db{

    //database varable
    var $db_id;

	function _db(){}

    //
    //Connection Commands
    //
    
    //connects to the database server and selects the initial database
    //returns true if connection and database selection successfull
    function db_connect($host,$port,$socket,$user,$password,$database,$persistant=true,$secure=false,$ssl=""){}   
    //closes the current sql connection
    function db_close(){}

    //
    //Query Commands
    //
    
    //function to find a result in a query
    //returns the result value
    function db_fetchresult($table,$fields,$params,$row=0){}
    
    //function to find an array of values from the database
    //returns the array of values
    function db_fetcharray($table,$fields,$params="",$type="ASSOC"){}
	
	//function to execute non-get db querys
	//returns true if the query was successful
	function db_update($q_type,$table,$set,$params=""){}
	
	//function to preform DROP, CREATE, ALTER, and TRUNCATE sql statements
	//returns true if query is a success
	function db_dbcommands($c_type,$q_type,$db,$table="",$fields="",$params=""){}

   //function to find the number of rows in a query
    //returns the number of rows
    function db_numrows($table,$params){}
    
    //function to find the number of fields in a query
    //returns the number of fields
	function db_numfields($table,$params){}
	
    //internal only query function.  Should only be called by functions in the db class!!
    function db_query($string){}
    //
    //Misc Commands
    //

    //change the current database
    //returns true if database selection successfull
    function db_switchdatabase($new_database){}
    
    //checks the current status of the connection
    //returns true if the connection is ok
    function db_checkstatus(){}

    //frees the current result. Internal only!
    function db_freeresult($result){}
    
    //Internal function to form querys for select statements
    //returns the query string
    function db_formfetchquery($table,$fields,$params){}
}