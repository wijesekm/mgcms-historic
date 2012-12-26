<?php

/**
 * @file		sql.abstract.php
 * @author 		Kevin Wijesekera
 * @copyright 	2008
 * @edited		5-30-2008
 
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

define("DB_LIKE","SQL_LIKE");
define("DB_NOTLIKE","SQL_NOTLIKE");
define("DB_AND","SQL_AND");
define("DB_OR","SQL_OR");
define("DB_BETWEEN","SQL_BETWEEN");
define("DB_IN","SQL_IN");
define("DB_ALL_ROWS","SQL_ALL_ROWS");
define("DB_UPDATE","SQL_UPDATE");
define("DB_RESETAUTO","SQL_RESETAUTO");
define("DB_INSERT","SQL_INSERT");
define("DB_REMOVE","SQL_REMOVE");
define("DB_DROP","SQL_DROP");
define("DB_TRUNCATE","SQL_TRUNCATE");
define("DB_ADD","SQL_ADD");
define("DB_NULL","default NULL");
define("DB_AUTOINC","NOT NULL auto_increment");
define("DB_PKEY","SQL_P");
define("DB_UKEY","SQL_U");
define("NOT_REGEXP","SQL_NOT_REGEXP");
define("REGEXP","SQL_REGEXP");

abstract class sql{
	
	protected $db;
	protected $print;
	protected $groupBy=false;
	protected $log_mode = false;
    protected $log;
    protected $admin = false;
    protected $stopCtr = 0;
    
	abstract public function sql_connect($host,$port_socket,$user,$password,$database,$persistent=true,$ssl=false);
	
	abstract public function sql_close();
	
	abstract public function sql_checkConnection();
	
    abstract public function sql_logging($log_table,$newMode=false);
    
    final public function sql_stopLogging($queryCount=1){
        $this->stopCtr = $queryCount;
    }
    
	abstract public function sql_info();
	
	abstract public function sql_listTables();
    
    abstract public function sql_getLastError();

	abstract public function sql_switchDB($new_database);
	
	abstract public function sql_fetchResult($table,$field,$params,$row=0);
	
	abstract public function sql_fetchArray($table,$field,$params,$type=DB_ASSOC,$rows=DB_ALL_ROWS,$additParams=false);
	
	abstract public function sql_numRows($table,$params,$result=false,$distinct=true,$addit=false);
	
	abstract public function sql_numFields($table,$params,$result=false,$distinct=true);
	
	abstract public function sql_dataCommands($type,$table,$params,$data=false);
	
	abstract public function sql_tableCommands($type,$alterType,$table,$params,$data=false);
	
	abstract public function sql_dbCommands($type,$db);
	
	abstract protected function sql_query($qstring);
	
	abstract protected function sql_freeResult($result);
	
	abstract protected function sql_formatTable($table,$prefix='FROM',$postfix='');

	abstract protected function sql_formatFields($fields,$prefix='SELECT',$postfix='');
	
	abstract protected function sql_formatConds($conds);
	
	abstract protected function sql_formatOrderBy($fields,$dirs);
	
	abstract protected function sql_formatLimit($start,$stop);
	
	abstract protected function sql_formatGroupBy($field);
	
	abstract protected function sql_formatHaving($funct,$field,$opr,$value);
	
	abstract protected function sql_formatKey($data);
	
	abstract protected function sql_formatTableLine($params);
	
	abstract protected function sql_escape($data,$isArray=false);
    
    final public function sql_formatUpdateInsert($key,$val,&$array1,&$array2,$add,$num=false){
        if($num){
            $val=(isset($val)&&trim($val)!='')?$val:'0';
        }
        if($add){
            $array2[]=$key;
            $array1[]=$val;	
        }
        else{
            $array1[]=array($key,$val);				
        }
    }
}
