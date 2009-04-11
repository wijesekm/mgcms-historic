<?php

/**
 * @file		mysql.class.php
 * @author 		Kevin Wijesekera
 * @copyright 	2009
 * @edited		2-04-2008
 
 ###################################
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program.  If not, see http://www.gnu.org/licenses/.
 ###################################
 */

if(!defined('STARTED')){
	die();
}

define("DB_ASSOC",MYSQL_ASSOC);
define("DB_NUM",MYSQL_NUM);
define("DB_BOTH",MYSQL_BOTH);

class mysql extends sql{
	
	protected $print;
	
	/**
	* Constants
	*/	
	const MYSQL_DEFAULT_PORT		= '3306';
	const MYSQL_DEFAULT_SOCKET		= '/tmp/mysql.sock';
	
	final public function __construct($debug=false){
		$this->print=$debug;
	}
	
	/**
	* General Public Functions
	*/
	
	/**
	* public function sql_connect($host,$port_socket,$user,$password,$database,$persistent=true,$ssl=false)
	*
	* Connects to a mysql server
	*
	* INPUTS:
	* $host			-	Server hostname (string)
	* $port_socket	-	Port or socket of server (string)
	* $user			-	Database user (string)
	* $password		-	Database users password (string)
	* $database		-	Database to connect to initially (string)
	* $persistent	-	Connect using a persistent connection (bool)
	* $ssl			-	Use SSL.  Note SSL not supported by mysql class. (false or array of strings)
	*
	* OUTPUTS:
	* true if connection successful, false if not.
	*/		
	final public function sql_connect($host,$port_socket,$user,$password,$database,$persistent=true,$ssl=false){
		if(!$user||!$password||!$database){
			trigger_error('(MYSQL): Not enough connect parameters given!',E_USER_ERROR);
			return false;
		}
		
		if($ssl){
			trigger_error('(MYSQL): MYSQL functions do not support ssl connections.  Please use the mysqli package for ssl support.',E_USER_NOTICE);
		}
		
		$type='s';
		if(eregi("^[0-9]+$",$port_socket)){
			$type='p';
		}
		if(!$host){
			$host='localhost';
		}
		if(!$port_socket&&$host=='localhost'){
			$port_socket=mysql::MYSQL_DEFAULT_SOCKET;
		}
		else if(!$port_socket){
			$port_socket=mysql::MYSQL_DEFAULT_PORT;
		}
		$host=$host.":".$port_socket;
		if($persistent){
			if(!$this->db=mysql_pconnect($host,$user,$password)){
				trigger_error('(MYSQL): Could not connect to server (persistent)! '.mysql_error(),E_USER_ERROR);
				return false;
			}
		}
		else{
			if(!$this->db=mysql_connect($host,$user,$password)){
				trigger_error('(MYSQL): Could not connect to server! '.mysql_error(),E_USER_ERROR);
				return false;
			}			
		}
		
		if(!$this->sql_switchDB($database)){
				$this->sql_close();
				return false;			
		}
		return true;
	}
	
	/**
	* public function sql_close()
	*
	* Closes connection to mysql server
	*
	* INPUTS:
	*
	* OUTPUTS:
	* true if successful, false if not.
	*/		
	final public function sql_close(){
		if(!$this->db){
			return false;
		}
		if(!mysql_close($this->db)){
			trigger_error('(MYSQL): Could not close connection! '.mysql_error(),E_USER_WARNING);
			return false;
		}
		return true;
	}

	/**
	* public function sql_checkConnection()
	*
	* Pings the server to check if connection is still ok
	*
	* INPUTS:
	*
	* OUTPUTS:
	* true if successful, false if not.
	*/
	final public function sql_checkConnection(){
		if(!$this->db){
			return false;
		}
		if(!mysql_ping($this->db)){
			trigger_error('(MYSQL): Ping failed! '.mysql_error(),E_USER_WARNING);
			return false;			
		}
		return true;
	}
	
	/**
	* public function sql_setDebug()
	*
	* Sets debug mode
	*
	* INPUTS:
	*
	* OUTPUTS:
	* true
	*/	
	final public function sql_setDebug(){
		if($this->print==true){
			$this->print=false;
		}
		else{
			$this->print=true;
		}
		return true;
	}

	/**
	* public function sql_info()
	*
	* Gets information about connection
	*
	* INPUTS:
	*
	* OUTPUTS:
	* Array of data
	*/	
	final public function sql_info(){
		
		/**
		* Format:
		* $dta['client-version'] - Client Version
		* $dta['server-version'] - Server Version
		* $dta['protocol-version'] - Protocol Version
		* $dta['host'] - Host Connection Information 
		* $dta['thread'] - Thread number
		* $dta['status'] - More connection information
		*/
		
		if(!$this->db){
			return false;
		}
		$dta=array();
		$dta['client-version']=mysql_get_client_info();
		$dta['server-version']=mysql_get_server_info($this->db);
		$dta['protocol-version']=mysql_get_proto_info($this->db);
		$dta['host']=mysql_get_host_info($this->db);
		$dta['thread']=mysql_thread_id($this->db);
		$dta['status']=mysql_stat($this->db);
		return $dta;
	}
	
	/**
	* public function sql_listTables()
	*
	* Gets all the tables in the current database
	*
	* OUTPUTS:
	* Array of table names (string)
	*/		
	final public function sql_listTables(){
		$r=$this->sql_query('SHOW TABLES;');
		$tables=array();
		while($row=mysql_fetch_row($r)){
			$tables[]=$row[0];
		}
		$this->sql_freeResult($r);
		return $tables;
	}

	/**
	* public function sql_switchDB($new_database)
	*
	* Connects to a different database
	*
	* INPUTS:
	* $new_database - name of new database (string)
	*
	* OUTPUTS:
	* true if sucessful, false if not
	*/		
	final public function sql_switchDB($new_database){
		if(!$this->db){
			return false;
		}
		if(!mysql_select_db($new_database,$this->db)){
			trigger_error('(MYSQL): Could not connect to database! '.mysql_error(),E_USER_ERROR);
			return false;			
		}
		return true;
	}
	
	/**
	* Database Public Functions
	*/
	
	final public function sql_fetchResult($table,$fields,$params,$row=0){
		
		/**
		* Format:
		* $table = array('table_one','table_two',...);
		* $field = array(array(Field Name, Field Function,Group by field?),...);
		* $params = array()
		*						
		*/
		
		if(!$this->db){
			return false;
		}
		if(!$result=$this->sql_query($this->sql_formatFields($fields).' '.$this->sql_formatTable($table).' '.$this->sql_formatConds($params).';')){
			return false;
		}
		$value=mysql_result($result,$row);
		$this->sql_freeResult($result);
		return $value;
	}
	
	final public function sql_fetchArray($table,$fields,$params,$type=DB_ASSOC,$rows=DB_ALL_ROWS,$additParams=false){
		$distinct=false;
		$orderby=false;
		$limit=false;
		$having=false;
		if(isset($additParams['distinct'])){
			$distinct=$additParams['distinct'];	
		}
		if(isset($additParams['orderby'])){
			$orderby=$additParams['orderby'];
		}		
		if(isset($additParams['limit'])){
			$limit=$additParams['limit'];
		}
		if(isset($additParams['having'])){
			$having=$additParams['having'];
		}
		
		if($distinct){
			$query=$this->sql_formatFields($fields,'SELECT DISTINCT').' ';
		}
		else{
			$query=$this->sql_formatFields($fields).' ';
		}
		$query.=$this->sql_formatTable($table).$this->sql_formatConds($params).' ';
		if($orderby){
			$query.=$this->sql_formatOrderBy($orderby[0],$orderby[1]).' ';
		}
		if($this->groupBy['allow']){
			$query.=$this->sql_formatGroupBy($this->groupBy['field']).' ';
			$query.=$this->sql_formatHaving($having[0],$having[1],$having[2],$having[3]).' ';
		}
		if($limit){
			$query.=$this->sql_formatLimit($limit[0],$limit[1]).' ';
		}
		$query.=';';
		if(!$result=$this->sql_query($query)){
			return false;
		}
		if($rows==DB_ALL_ROWS){
			$rows=$this->sql_numRows(false,false,$result);
		}
		$tmp=array();
		if(!$rows){
			$tmp['count']=1;
			$tmp[]=mysql_fetch_array($result,$type);
		}
		else{
			$tmp['count']=$rows;
			for($i=0;$i<$rows;$i++){
				$tmp[]=mysql_fetch_array($result,$type);
				if($i+1<$rows){
					mysql_data_seek($result,$i+1);					
				}

			}
		}
		$this->sql_freeResult($result);
		return $tmp;
	}
	
	final public function sql_numRows($table,$params,$result=false,$distinct=true){
		if(!$result){
			if($distinct){
				$query=$this->sql_formatFields(false,'SELECT DISTINCT').' ';
			}
			else{
				$query=$this->sql_formatFields(false).' ';
			}
			$query.=$this->sql_formatTable($table).$this->sql_formatConds($params).';';
			$result=$this->sql_query($query);
			$num=mysql_num_rows($result);
			$this->sql_freeResult($result);
		}
		else{
			$num=mysql_num_rows($result);
		}
		return $num;
	}
	
	final public function sql_numFields($table,$params,$result=false,$distinct=true){
		if(!$result){
			if($distinct){
				$query=$this->sql_formatFields(false,'SELECT DISTINCT').' ';
			}
			else{
				$query=$this->sql_formatFields(false).' ';
			}
			$query.=$this->sql_formatTable($table).$this->sql_formatConds($params).';';
			$result=$this->sql_query($query);
			$num=mysql_num_fields($result);
			$this->sql_freeResult($result);
		}
		else{
			$num=mysql_num_fields($result);
		}
		return $result;
	}
	
	final public function sql_dataCommands($type,$table,$params,$data=false){
		if(!is_array($table)){
			$tmp=$table;
			$table=array($tmp);
		}
		if(count($table)>1){
			trigger_error('(MYSQL): Cannot declare more then one table in tableCommands!',E_USER_WARNING);
			return false;
		}
		switch($type){
			case DB_UPDATE:
				$query=$this->sql_formatTable($table,'UPDATE').' SET ';
				$ssize=count($data);
				for($k=0;$k<$ssize;$k++){
					$query.='`'.$this->sql_escape($data[$k][0]).'`=\''.$this->sql_escape($data[$k][1]).'\'';
					if($k+1<$ssize){
						$query.=', ';
					}
				}
				$query.=' '.$this->sql_formatConds($params).';';
			break;
			case DB_INSERT:
				$query=$this->sql_formatTable($table,'INSERT INTO');
				$query.='(`'.implode('`,`',$params).'`) VALUES (\''.implode('\',\'',$data).'\');';
			break;
			case DB_RESETAUTO:
				if(!$data){
					$data=1;
				}
				$query=$this->sql_formatTable($table,'ALTER TABLE').'AUTO_INCREMENT = '.$data;
			break;
			case DB_REMOVE:
				$query=$this->sql_formatTable($table,'DELETE FROM');
				$query.=' '.$this->sql_formatConds($params).';';
			break;
			default:
			
			break;

		};
		if($this->sql_query($query)){
			return true;	
		}
		return false;
	}
	
	final public function sql_tableCommands($type,$alterType,$table,$params,$data=false){
		switch($type){
			case DB_DROP:
				$query=$this->sql_formatTable($table,'DROP TABLE');
			break;
			case DB_TRUNCATE:
				$query=$this->sql_formatTable($table,'TRUNCATE TABLE');
			break;
			case DB_ADD:
				$query=$this->sql_formatTable($table,'CREATE TABLE').' (';
				$tsize=count($params);
				for($k=0;$k<$tsize;$k++){
					$query.=$this->sql_formatTableLine($params[$k]);
					if($k<$tsize+1){
						$query.=',';
					}
				}
				if($data){
					$query.=',';
					$sod=count($data);
					for($k=0;$k<$sod;$k++){
						$query.=$this->sql_formatKey($data[$k]);
						if($k<$sod+1){
							$query.=',';
						}
					}
				}
				$query.=')';
			break;
			default:
			
			break;			
		};
	}
	
	final public function sql_dbCommands($type,$db){
		switch($type){
			case DB_DROP:
				$query=$this->sql_formatTable($table,'DROP DATABASE');
			break;
			case DB_ADD:
				$query=$this->sql_formatTable($table,'CREATE DATABASE');
			break;
			default:
			
			break;

		};
	}
	
	/**
	* Protected Functions
	*/		
	
	final protected function sql_query($qstring){
		if($this->print){
			echo $qstring.'<br/>';
		}
		if(!$result=mysql_query($qstring,$this->db)){
			trigger_error('(MYSQL): Query failed or returned empty set! '.$qstring.': '.mysql_error(),E_USER_WARNING);
			return false;
		}
		return $result;
	}
	
	final protected function sql_freeResult($result){
		if(!mysql_free_result($result)){
			trigger_error('(MYSQL): Could not free result! '.mysql_error(),E_USER_WARNING);
			return false;		
		}
		return true;
	}
	
	/**
	* Protected Format Functions
	*/	
	final protected function sql_formatTable($table,$prefix='FROM',$postfix=''){
		$table=$this->sql_escape($table,true);
		return $prefix.' `'.implode('`,',$table).'` '.$postfix;
	}
	
	final protected function sql_formatFields($fields,$prefix='SELECT',$postfix=''){
		if(!$fields){
			return $prefix.' * '.$postfix;
		}
		$fsize=count($fields);
		$str=$prefix.' ';
		for($i=0;$i<$fsize;$i++){
			if(isset($fields[$i][1])){
				$str.=$this->sql_escape($fields[$i][1]);
				$str.='(`'.$this->sql_escape($fields[$i][0]).'`)';
				$this->groupBy['allow']=true;
			}
			else{
				$str.='`'.$this->sql_escape($fields[$i][0]).'`';				
			}
			if(!empty($fields[$i][2])){
				$this->groupBy['field']=$fields[$i][2];
			}
			
			if($i+1<$fsize){
				$str.=', ';
			}
		}
		return $str.' '.$postfix;
	}
	
	final protected function sql_formatConds($conds){
		$str='WHERE ';
		if(!$conds){
			return $str.'1';
		}
		$last=false;
		$ended=true;
		$csize=count($conds);
		for($i=0;$i<$csize;$i++){
			if(isset($conds[$i][1][1])){
				if($last!=$conds[$i][1][1]){
					$str.='( ';
					$ended=false;
					$last=$conds[$i][1][1];
				}				
			}

			switch($conds[$i][0]){
				case DB_LIKE:
					$str.='`'.$this->sql_escape($conds[$i][2]).'` LIKE \''.$this->sql_escape($conds[$i][3]).'\'';
				break;
				case DB_BETWEEN:
					$str.='`'.$this->sql_escape($conds[$i][2]).'` BETWEEN ';
					$str.='\''.$this->sql_escape($conds[$i][3]).'\' AND \''.$this->sql_escape($conds[$i][4]).'\'';
				break;
				case DB_IN:
					$str.='`'.$this->sql_escape($conds[$i][2]).'` IN (\''.inplode('\',\'',$$conds[$i][3]).'\')';
				break;
				default:
					$str.='`'.$this->sql_escape($conds[$i][2]).'` '.$this->sql_escape($conds[$i][3]).' \''.$this->sql_escape($conds[$i][4]).'\'';
				break;
			}
			if(isset($conds[$i+1][1][1])){
				if($last!=$conds[$i+1][1][1]){
					$str.=' )';
					$ended=true;
				}				
			}
			switch($conds[$i][1][0]){
				case DB_AND:
					$str.=' AND';
				break;
				case DB_OR:
					$str.=' OR';
				break;
				default:
				
				break 2;
			}
		}
		if(!$ended){
			$str.=' )';
		}
		return $str;
	}
	
	final protected function sql_formatOrderBy($fields,$dirs){
		$soq=count($fields);
		if(!$soq){
			return false;
		}
		$str='ORDER BY ';
		$fields=$this->sql_escape($fields,true);
		$dirs=$this->sql_escape($dirs,true);
		for($i=0;$i<$soq;$i++){
			$str.='`'.$fields[$i].'` '.$dirs[$i];
			if($i+1<$soq){
				$str.=',';
			}
			$str.=' ';
		}
		return $str;
	}
	
	final protected function sql_formatLimit($start,$stop){
		if($start!==false){
			return 'LIMIT '.$this->sql_escape($start).', '.$this->sql_escape($stop);
		}
		return '';
	}
	
	final protected function sql_formatGroupBy($field){
		if($field){
			return 'GROUP BY `'.$this->sql_escape($field).'`';	
		}
		return '';
		
	}
	
	final protected function sql_formatHaving($funct,$field,$opr,$value){
		if($field){
			return 'HAVING '.$this->sql_escape($funct).'(`'.$this->sql_escape($field).'`) '.$this->sql_escape($opr).' '.$this->sql_escape($value);	
		}
		return '';
	}
	
	final protected function sql_formatKey($data){
		switch($data[0]){
			case DB_PKEY:
				return 'PRIMARY KEY (`'.$this->sql_escape($data[1]).'`)';
			break;
			case DB_UKEY:
				return 'UNIQUE '.$this->sql_escape($data[1]).' `'.$this->sql_escape($data[2]).'` (`'.$this->sql_escape($data[3]).'`)';
			break;
			default:
				return $this->sql_escape($data[1]).' `'.$this->sql_escape($data[2]).'` (`'.$this->sql_escape($data[3]).'`)';
			break;
		};
	}
	final protected function sql_formatTableLine($params){
		$query='`'.$this->sql_escape($params[0]).'` ';
		$query.=$this->sql_escape($params[1][0]);
		if($params[1][1]){
			$query.='('.$this->sql_escape($params[1][1]).') ';
		}
		switch($params[2]){
			case DB_NULL:
				$query.=DB_NULL;
			break;
			case DB_AUTO_INC:
				$query.=DB_AUTO_INC;
			break;
			default:
				$query.='NOT NULL default \''.$this->sql_escape($params[3]).'\'';
			break;
		};
		return $query;
	}
	
	final protected function sql_escape($data,$isArray=false){
		if($isArray){
			$soq=count($data);
			for($i=0;$i<$soq;$i++){
				$data[$i]=mysql_real_escape_string($data[$i]);
			}			
		}
		else{
			$data=mysql_real_escape_string($data);
		}
		return $data;
	}
}