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

define("DB_ASSOC",MYSQLI_ASSOC);
define("DB_NUM",MYSQLI_NUM);
define("DB_BOTH",MYSQLI_BOTH);

class mysqlidb extends sql{

	/**
	* Constants
	*/
	const MYSQL_DEFAULT_PORT		= '3306';
	const MYSQL_DEFAULT_SOCKET		= '/tmp/mysql.sock';

  /**
   * mysql::__construct()
   *
   * @param bool $debug
   * @return
   */
	final public function __construct($debug=false){
		$this->print=$debug;
	}

	/**
	* General Public Functions
	*/

	/*!
	 * This function initializes a connection to the database
	 *
	 * @author Kevin Wijesekera
	 * @date 3-17-2015
	 *
	 * @param $host Connection string for database
	 * @param $user Connection user account
	 * @param $password Connection password
	 * @param $initialDB Initial database to use
	 *
	 * @return true on success, false on failure
	 */
	final public function sql_connect($host,$port_socket,$user,$password,$database,$persistent=true,$ssl=false){
		if($this->db){
			trigger_error('(mysql): Database connection already established',E_USER_NOTICE);
			return true;
		}

		$this->db = mysqli_init();
		if(!$this->db){
			trigger_error('(mysql): Init failed',E_USER_WARNING);
			return false;
		}

		//set timeout
		if(!@$this->db->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)){
			trigger_error('(mysql): Could not set options',E_USER_WARNING);
		}

		//set ssl
		if(!empty($this->cfg['sslkey'])){
			if(!@$this->db->ssl_set($this->cfg['sslkey'],$this->cfg['sslcert'],$this->cfg['sslca'],NULL,NULL)){
				trigger_error('(mysql): Could not set SSL...defaulting to OFF',E_USER_WARNING);
			}
		}

		//parse connection string
		//host:3306 or host:/tmp/stuff
		$tmp = explode(':',$host);
		$host = $tmp[0];
		$port = false;
		$socket = false;
		if(count($tmp) > 1){
			if($tmp[1][0] == '/'){
				$socket = $tmp[1];
			}
			else{
				$port = $tmp[1];
			}
		}

		if(!@$this->db->real_connect($host,$user,$password,$database,$port,$socket)){
            $this->db = false;
			trigger_error('(mysql): Could not connect to database!'.$this->db->connect_error,E_USER_WARNING);
			return false;
		}
		return true;
	}

  /**
   * mysql::sql_close()
   *
   * @return
   */
	final public function sql_close(){
		if(!$this->db){
			return true;
		}
		if(!$this->db->close()){
			trigger_error('(mysql): Could not close connection.'.$this->db_getLastError(),E_USER_WARNING);
            $this->db = false;
			return false;
		}
        $this->db = false;
		return true;
	}

  /**
   * mysql::sql_checkConnection()
   *
   * @return
   */
	final public function sql_checkConnection(){
		if(!$this->db){
			return false;
		}
		return $this->db->ping();
	}

	/*!
	 * This function returns information on the database
	 *
	 * Types:
	 * DB_INFO_CLIENT_VER - Client version
	 * DB_INFO_SERVER_VER - Server version
	 * DB_INFO_PROTOCOL_VER - Protocol version
	 * DB_INFO_THREAD - Thread ID
	 *
	 * @author Kevin Wijesekera
	 * @date 3-17-2015
	 *
	 * @param $type Information type to get
	 *
	 * @return database information
	 */
	final public function sql_info(){
		return false;
	}

	final public function sql_freeResult($result){

	}

	/**
	* public function sql_listTables()
	*
	* Gets all the tables in the current database
	*
	* OUTPUTS:
	* Array of table names (string)
	*/
  /**
   * mysql::sql_listTables()
   *
   * @return
   */
	final public function sql_listTables(){
		$r=$this->sql_query('SHOW TABLES;');
        $res = $r->fetch_all($res_type);
		return $res[0];
	}

    final public function sql_getLastError(){
		if(!$this->db){
			return false;
		}

		$ret = '';

		foreach($this->db->error_list as $key=>$val){
			$ret.='#'.$val['errno'].': '.$val['error'].' -- ';
		}
		return $ret;
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
  /**
   * mysql::sql_switchDB()
   *
   * @param mixed $new_database
   * @return
   */
	final public function sql_switchDB($new_database){
		if(!$this->db){
			return false;
		}
		if(!$this->db->select_db($new_database)){
			trigger_error('(mysql): Could not change to database '.$new_database.' '.$this->sql_getLastError(),E_USER_WARNING);
			return false;
		}
		return true;
	}

	/**
	* Database Public Functions
	*/

  /**
   * mysql::sql_fetchResult()
   *
   * @param mixed $table
   * @param mixed $fields
   * @param mixed $params
   * @param integer $row
   * @return
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
		$q='';
		if(isset($fields['funct'])){
			$q=$this->sql_formatFunctions($fields);
		}
		else{
			$q=$this->sql_formatFields($fields);
		}
		if(!$result=$this->sql_query($q.' '.$this->sql_formatTable($table).' '.$this->sql_formatConds($params).';')){
			return false;
		}
        if($row > $result->num_rows || $result->num_rows == 0){
            return "";
        }
		$value=$result->fetch_all(MYSQLI_NUM);
		$value = $value[$row][0];
        $result->free();
		return $value;
	}

  /**
   * mysql::sql_fetchArray()
   *
   * @param mixed $table
   * @param mixed $fields
   * @param mixed $params
   * @param mixed $type
   * @param mixed $rows
   * @param bool $additParams
   * @return
   */
	final public function sql_fetchArray($table,$fields,$params,$type=DB_ASSOC,$rows=DB_ALL_ROWS,$additParams=false){
		$distinct=false;
		$orderby=false;
		$limit=false;
		$having=false;
		$join=false;
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
		if(isset($additParams['join'])){
            $join=$this->sql_escape($additParams['join'][0],false,0).' '.$this->sql_escape($additParams['join'][1],false,1).' ON ';
            $join.=$this->sql_escape($additParams['join'][2],false,1).'='.$this->sql_escape($additParams['join'][3],false,1);
		}

		if($distinct){
			$query=$this->sql_formatFields($fields,'SELECT DISTINCT').' ';
		}
		else{
			$query=$this->sql_formatFields($fields).' ';
		}
		$query.=$this->sql_formatTable($table);
		if($join){
			$query.=$join.' ';
		}
		$query.=$this->sql_formatConds($params).' ';
		if($orderby){
			$query.=$this->sql_formatOrderBy($orderby[0],$orderby[1]).' ';
		}
		if($this->groupBy && $this->groupBy['allow']){
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
			$rows=$result->num_rows;
		}
		$tmp=array();
        $items = $result->fetch_all($type);
        $items['count']=$rows;
		$result->free();
		return $items;
	}

	final public function sql_fetchJSON($table,$fields,$params,$type=DB_ASSOC,$rows=DB_ALL_ROWS,$additParams=false){
	    $distinct=false;
	    $orderby=false;
	    $limit=false;
	    $having=false;
	    $join=false;
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
	    if(isset($additParams['join'])){
	        $join=$this->sql_escape($additParams['join'][0],false,0).' '.$this->sql_escape($additParams['join'][1],false,1).' ON ';
	        $join.=$this->sql_escape($additParams['join'][2],false,1).'='.$this->sql_escape($additParams['join'][3],false,1);
	    }
	    if($distinct){
	        $query=$this->sql_formatFields($fields,'SELECT DISTINCT').' ';
	    }
	    else{
	        $query=$this->sql_formatFields($fields).' ';
	    }
	    $query.=$this->sql_formatTable($table);
	    if($join){
	        $query.=$join.' ';
	    }
	    $query.=$this->sql_formatConds($params).' ';
	    if($orderby){
	        $query.=$this->sql_formatOrderBy($orderby[0],$orderby[1]).' ';
	    }
	    if($this->groupBy && $this->groupBy['allow']){
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
	    $ret = '[';
	    while($row = $result->fetch_array($type)){
            $ret .= mg_jsonEncode($row).',';
	    }
	    if($ret[strlen($ret)-1] != '['){
	        $ret[strlen($ret)-1] = ']';
	    }
	    else{
	        $ret .= ']';
	    }
	    return $ret;
	}

  /**
   * mysql::sql_numRows()
   *
   * @param mixed $table
   * @param mixed $params
   * @param bool $result
   * @param bool $distinct
   * @return
   */
	final public function sql_numRows($table,$params,$result=false,$distinct=true,$addit=false){
		if(!$result){
			$join='';
			if(isset($addit['join'])){
				$join=$this->sql_escape($addit['join'][0],false,0).' '.$this->sql_escape($addit['join'][1],false,1).' ON ';
				$join.=$this->sql_escape($addit['join'][2],false,1).'='.$this->sql_escape($addit['join'][3],false,1);
			}
			if(is_array($distinct)){
				$query=$this->sql_formatFields($distinct,'SELECT DISTINCT',' ');
			}
			else{
				$query=$this->sql_formatFields(false).' ';
			}
			$query.=$this->sql_formatTable($table).$join.' '.$this->sql_formatConds($params).';';
			$result=$this->sql_query($query);
			if($result){
			    $num=$result->num_rows;
			    $result->free();
			}
			else{
			    $num = 0;
			}
		}
		else{
			$num=$result->num_rows;
		}
		return $num;
	}

  /**
   * mysql::sql_numFields()
   *
   * @param mixed $table
   * @param mixed $params
   * @param bool $result
   * @param bool $distinct
   * @return
   */
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
			$num=$result->field_count;
			$result->free();
		}
		else{
			$num=$result->field_count;
		}
		return $$num;
	}

  /**
   * mysql::sql_dataCommands()
   *
   * @param mixed $type
   * @param mixed $table
   * @param mixed $params
   * @param bool $data
   * @return
   */
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
				    if($data[$k][0] == DB_APPEND){
                        if(is_int($data[$k][2])){
                            $query.=$this->sql_escape($data[$k][1],false,1).'= IFNULL('.$this->sql_escape($data[$k][1],false,1).',0) + '.$this->sql_escape($data[$k][2],false,2);
                        }
                        else{
                            $query.=$this->sql_escape($data[$k][1],false,1).'= CONCAT('.$this->sql_escape($data[$k][1],false,1).','.$this->sql_escape($data[$k][2],false,2).')';
                        }
                    }
					else if(isset($data[$k][2])){
						$query.=$this->sql_escape($data[$k][0],false,1).'='.$this->sql_escape($data[$k][2]).'('.$this->sql_escape($data[$k][3],false,1).','.$this->sql_escape($data[$k][1],false,2).')';
					}
                    else if($data[$k][1]==='++'){
                        $query.=$this->sql_escape($data[$k][0],false,1).'='.$this->sql_escape($data[$k][0],false,1).'+1';
                    }
                    else if($data[$k][1] == null){
                        $query.=$this->sql_escape($data[$k][0],false,1).'=NULL';

                    }
					else{
						$query.=$this->sql_escape($data[$k][0],false,1).'='.$this->sql_escape($data[$k][1],false,2);
					}

					if($k+1<$ssize){
						$query.=', ';
					}
				}
				$query.=' '.$this->sql_formatConds($params).';';
			break;
			case DB_INSERT_IGNORE:
			    $query=$this->sql_formatTable($table,'INSERT IGNORE INTO');
			    $query.='(`'.implode('`,`',$this->sql_escape($params,true)).'`) VALUES';
			    if(is_array($data[0])){
			        $soq=count($data);
			        for($i=0;$i<$soq;$i++){
			            $query.=' (\''.implode('\',\'',$this->sql_escape($data[$i],true)).'\')';
			            if($i+1<$soq){
			                $query.=',';
			            }
			        }
			        $query.=';';
			    }
			    else{
			        $query.=' (\''.implode('\',\'',$this->sql_escape($data,true)).'\');';
			    }
			break;
			case DB_INSERT:
				$query=$this->sql_formatTable($table,'INSERT INTO');
				$query.='(`'.implode('`,`',$this->sql_escape($params,true)).'`) VALUES';
				if(is_array($data[0])){
					$soq=count($data);
					for($i=0;$i<$soq;$i++){
						$query.=' (\''.implode('\',\'',$this->sql_escape($data[$i],true)).'\')';
						if($i+1<$soq){
							$query.=',';
						}
					}
					$query.=';';
				}
				else{
					$query.=' (\''.implode('\',\'',$this->sql_escape($data,true)).'\');';
				}
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

  /**
   * mysql::sql_tableCommands()
   *
   * @param mixed $type
   * @param mixed $alterType
   * @param mixed $table
   * @param mixed $params
   * @param bool $data
   * @return
   */
	final public function sql_tableCommands($type,$alterType,$table,$params,$data=false){
		switch($type){
			case DB_DROP:
				$query=$this->sql_formatTable($table,'DROP TABLE');
			break;
			case DB_TRUNCATE:
				$query=$this->sql_formatTable($table,'TRUNCATE TABLE').';';
			break;
			case DB_LOCK:
				$query= $this->sql_formatTable($table,'LOCK TABLE');
				$query.=' '.(($params == 'READ')?'READ;':'WRITE;');
			break;
			case DB_UNLOCK:
				$query = 'UNLOCK TABLES;';
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
		$this->sql_query($query);
	}

  /**
   * mysql::sql_dbCommands()
   *
   * @param mixed $type
   * @param mixed $db
   * @return
   */
	final public function sql_dbCommands($type,$db){
		switch($type){
			case DB_DROP:
				$query=$this->sql_formatTable(array($db),'DROP DATABASE');
			break;
			case DB_ADD:
				$query=$this->sql_formatTable(array($db),'CREATE DATABASE');
			break;
			default:

			break;
		};
		$this->sql_query($query);
	}

	/**
	* Protected Functions
	*/

  /**
   * mysql::sql_query()
   *
   * @param mixed $qstring
   * @return
   */
	final protected function sql_query($qstring){
		if($this->print){
			echo $qstring.'<br/>';
		}

		if(!$res = $this->db->query($qstring)){
			trigger_error('(MYSQL): Query failed or returned empty set! '.$qstring.': '.$this->sql_getLastError(),E_USER_WARNING);
			return false;
		}
		if($this->log){
		    $this->sql_log($qstring);
		}
		return $res;
	}

	/**
	* Protected Format Functions
	*/
  /**
   * mysql::sql_formatTable()
   *
   * @param mixed $table
   * @param string $prefix
   * @param string $postfix
   * @return
   */
	final protected function sql_formatTable($table,$prefix='FROM',$postfix=''){
		$table=$this->sql_escape($table,true);
		return $prefix.' `'.implode('`,',$table).'` '.$postfix;
	}

  /**
   * mysql::sql_formatFields()
   *
   * @param mixed $fields
   * @param string $prefix
   * @param string $postfix
   * @return
   */
	final protected function sql_formatFields($fields,$prefix='SELECT',$postfix=''){
		if(!$fields){
			return $prefix.' * '.$postfix;
		}
		$fsize=count($fields);
		$str=$prefix.' ';
		for($i=0;$i<$fsize;$i++){
			if(isset($fields[$i][1])){
				$str.=$this->sql_escape($fields[$i][1]);
				$str.=$this->sql_escape($fields[$i][0],false,1);
				$this->groupBy['allow']=true;
			}
			else{
				$str.=$this->sql_escape($fields[$i][0],false,1);
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

  /**
   * mysql::sql_formatFunctions()
   *
   * @param mixed $fields
   * @param string $prefix
   * @param string $postfix
   * @return
   */
	final protected function sql_formatFunctions($fields,$prefix='SELECT',$postfix=''){
		if(!$fields){
			return $prefix.' * '.$postfix;
		}
		$str=$prefix;
		switch($fields['funct'][0]){
			case 'MAX':
				$str.=' MAX(`'.$this->sql_escape($fields['funct'][1]).'`)';
			break;
			case 'SUM':
				$str.=' SUM(`'.$this->sql_escape($fields['funct'][1][0]).'`)';
				if($fields[0][1]){
					$str.=' AS \''.$fields['funct'][1][1].'\'';
				}
			break;
		}
		$str.=$postfix;
		return $str;
	}

  /**
   * mysql::sql_formatConds()
   *
   * @param mixed $conds
   * @return
   */
	final protected function sql_formatConds($conds){
		$str='WHERE ';
		if(!$conds){
			return $str.'1';
		}
		$last=array(false,false);
		$ended=array(true,true);
		$csize=count($conds);
		for($i=0;$i<$csize;$i++){
			if(isset($conds[$i][1][1])){
				if($last[0]!=$conds[$i][1][1]){
					$str.='( ';
					$ended[0]=false;
					$last[0]=$conds[$i][1][1];
				}
			}
			if(isset($conds[$i][1][2])){
				if($last[1]!=$conds[$i][1][2]){
					$str.='( ';
					$ended[1]=false;
					$last[1]=$conds[$i][1][2];
				}
			}
			if(!isset($conds[$i][0]) || count($conds[$i]) < 3){
				trigger_error('(SQL): Incorrect Format Conds: '.var_export($conds,true),E_USER_WARNING);
				break;
			}
			switch($conds[$i][0]){
				case DB_LIKE:
				case DB_NOTLIKE:
					$str.=$this->sql_escape($conds[$i][2],false,1);
					if($conds[$i][0]==DB_NOTLIKE){
						$str.=' NOT';
					}
					$str.=' LIKE \''.$this->sql_escape($conds[$i][3]).'\'';
				break;
				case NOT_REGEXP:
				case REGEXP:
					$str.=$this->sql_escape($conds[$i][2],false,1);
					if($conds[$i][0]==NOT_REGEXP){
						$str.=' NOT';
					}
					$str.=' REGEXP '.$this->sql_escape($conds[$i][3],false,2);
				break;
				case DB_BETWEEN:
					$str.=$this->sql_escape($conds[$i][2],false,1).' BETWEEN ';
					$str.='\''.$this->sql_escape($conds[$i][3]).'\' AND \''.$this->sql_escape($conds[$i][4]).'\'';
				break;
				case DB_IN:
					$str.=$this->sql_escape($conds[$i][2],false,1).' IN (\''.inplode('\',\'',$$conds[$i][3]).'\')';
				break;
				default:
					if($conds[$i][4]===null){
						$conds[$i][4]='';
						if($conds[$i][3]=='='){
							$str.=$this->sql_escape($conds[$i][2],false,1).' IS NULL';

						}
						else{
							$str.=$this->sql_escape($conds[$i][2],false,1).' IS NOT NULL';
						}
					}
					else{
						$str.=$this->sql_escape($conds[$i][2],false,1).' '.$this->sql_escape($conds[$i][3]).' '.$this->sql_escape($conds[$i][4],false,2);
					}
				break;
			}
			if(isset($conds[$i+1][1][2])){
				if($last[1]!=$conds[$i+1][1][2]){
					$str.=' )';
					$ended[1]=true;
				}
			}
			if(isset($conds[$i+1][1][1])){
				if($last[0]!=$conds[$i+1][1][1]){
				    if(!$ended[1]){
				        $str.=' )';
                        $ended[1] = true;
				    }
					$str.=' )';
					$ended[0]=true;
				}
			}
			if(!empty($conds[$i][1])){
			    switch($conds[$i][1][0]){
			        case DB_AND:
			            $str.=' AND ';
			            break;
			        case DB_OR:
			            $str.=' OR ';
			            break;
			        default:

			            break 2;
			    }
			}
		}
		if(!$ended[1]){
			$str.=' )';
		}
		if(!$ended[0]){
			$str.=' )';
		}
		return $str;
	}

  /**
   * mysql::sql_formatOrderBy()
   *
   * @param mixed $fields
   * @param mixed $dirs
   * @return
   */
	final protected function sql_formatOrderBy($fields,$dirs){
		$soq=count($fields);
		if(!$soq){
			return false;
		}
		$str='ORDER BY ';
		$fields=$this->sql_escape($fields,true,1);
		$dirs=$this->sql_escape($dirs,true);
		for($i=0;$i<$soq;$i++){
			$str.=$fields[$i].' '.$dirs[$i];
			if($i+1<$soq){
				$str.=',';
			}
			$str.=' ';
		}
		return $str;
	}

  /**
   * mysql::sql_formatLimit()
   *
   * @param mixed $start
   * @param mixed $stop
   * @return
   */
	final protected function sql_formatLimit($start,$num){
		if($start!==false){
		    return 'LIMIT '.$this->sql_escape($num).' OFFSET '.$this->sql_escape($start);
		}
		return '';
	}

  /**
   * mysql::sql_formatGroupBy()
   *
   * @param mixed $field
   * @return
   */
	final protected function sql_formatGroupBy($field){
		if($field){
			return 'GROUP BY '.$this->sql_escape($field,false,1);
		}
		return '';

	}

  /**
   * mysql::sql_formatHaving()
   *
   * @param mixed $funct
   * @param mixed $field
   * @param mixed $opr
   * @param mixed $value
   * @return
   */
	final protected function sql_formatHaving($funct,$field,$opr,$value){
		if($field){
			return 'HAVING '.$this->sql_escape($funct).'('.$this->sql_escape($field,false,1).') '.$this->sql_escape($opr).' '.$this->sql_escape($value);
		}
		return '';
	}

  /**
   * mysql::sql_formatKey()
   *
   * @param mixed $data
   * @return
   */
	final protected function sql_formatKey($data){
		switch($data[0]){
			case DB_PKEY:
				return 'PRIMARY KEY ('.$this->sql_escape($data[1],false,1).')';
			break;
			case DB_UKEY:
				return 'UNIQUE '.$this->sql_escape($data[1]).' '.$this->sql_escape($data[2],false,1).' ('.$this->sql_escape($data[3],false,1).')';
			break;
			default:
				return $this->sql_escape($data[1]).' '.$this->sql_escape($data[2],false,1).' ('.$this->sql_escape($data[3],false,1).')';
			break;
		};
	}
  /**
   * mysql::sql_formatTableLine()
   *
   * @param mixed $params
   * @return
   */
	final protected function sql_formatTableLine($params){
		$query=$this->sql_escape($params[0],false,1).' ';
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
				$query.='NOT NULL default '.$this->sql_escape($params[3],false,2);
			break;
		};
		return $query;
	}

  /**
   * mysql::sql_escape()
   *
   * @param mixed $data
   * @param bool $isArray
   * @return
   */
	final protected function sql_escape($data,$isArray=false,$type=0){
	   $append='';
		if($isArray){
			$soq=count($data);
			for($i=0;$i<$soq;$i++){
    			$append='';
    			switch($type){
    				default:
    					$append='';
    				break;
    				case '1':
    						$append='`';
    					if(preg_match('/\./',$data[$i])){
   						   $data[$i] =preg_replace('/\./','`.`',$data[$i]);
    					}
    				break;
    				case '2':
    					$append='\'';
    				break;
    			}
				$data[$i]=$append.$this->db->escape_string($data[$i]).$append;
			}
		}
		else{
			switch($type){
				default:
					$append='';
				break;
				case '1':
				    $data = explode('.',$data);

				    $data[0] = '`'.$data[0].'`';
				    if(!empty($data[1])){
				        if($data[1] != '*'){
				            $data[1] = '`'.$data[1].'`';
				        }
				        $data =  $data[0].'.'.$data[1];
				    }
				    else{
				        $data = $data[0];
				    }
				break;
				case '2':
				    $data='\''.$this->db->escape_string($data).'\'';

				break;
			}
		}
		return $data;
	}

	final protected function sql_log($query){
        $GLOBALS['MG']['ERROR']['LOGGER']->el_addError(E_SQL, $query, '', '', '');
	}
}