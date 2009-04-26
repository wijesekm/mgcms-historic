<?php

/**
 * @file                mgXML.class.php
 * @author              Kevin Wijesekera
 * @copyright   		2009
 * @edited              4-25-2009
 
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

class mgXML {

    // XML parser variables
    private $parser;
    private $data  = array();
    private $cfg =  array();
    
    public function __construct($pkg){
		$c=array(array(false,array(DB_AND,1),'pkg_name','=',$pkg),array(false,array(DB_OR,2),'page_path','=',$GLOBALS['MG']['PAGE']['PATH']),array(false,array(false,2),'page_path','=','*'));
		$dta=$GLOBALS['MG']['SQL']->sql_fetchArray(array(TABLE_PREFIX.'packageconf'),false,$c);
		for($i=0;$i<$dta['count'];$i++){
			$this->cfg[(string)$dta[$i]['var_name']]=(string)$dta[$i]['var_value'];
		}
	}
	
	public function mxml_getCurrent(){
		print_r($this->data);
	}
	
	public function mxml_write($file=false){
		$output='<?xml version="'.$this->cfg['xml_version'].'" encoding="'.$this->cfg['xml_encoding'].'"?>'."\n";
		$soq=count($this->data);
		for($i=0;$i<$soq;$i++){
			$output.=$this->mxml_writeRecursive($this->data[$i],0);	
		}
		if(!$file){
			$GLOBALS['MG']['LANG']['CONTENT_TYPE']=$this->cfg['xml_contenttype'];
			$GLOBALS['MG']['PAGE']['NOSITETPL']=true;
			return $output;
		}
		else{
			if(!$fp=fopen($file,'w')){
				trigger_error('(MGXML): Could not open file or url for writing: '.$file,E_USER_ERROR);
			}
			@fwrite($fp,$output);
			fclose($fp);
		}
		return true;
	}
	
	public function mxml_addTag($tag,$attrs,$data,$parent_indexes){
		$base='$this->data';
		if($parent_indexes){
			foreach($parent_indexes as $i){
				$base.="[$i]";
			}
		}
		eval("\$c=count($base);");
		eval("\$d=($base"."['data'])?true:false;");
		eval("\$u=($base"."['tag'])?true:false;");
		if($d){
			$c-=3;
		}
		else if($u){
			$c-=2;
		}
		$base.="[$c]";
		eval("$base=array();");
		eval($base."['tag']=\$tag;");
		eval($base."['attrs']=\$attrs;");
		if($data){
			eval($base."['data']=\$data;");
		}
		return true;
	}
	
	public function mxml_reset(){
		$this->data=array();
	}
	
    public function mxml_load($input,$file=true){
    	
        $this->parser = xml_parser_create ($this->cfg['xml_encoding']);
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'mxml_start', 'mxml_end');
        xml_set_character_data_handler($this->parser, 'mxml_char');
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
        
        if($file){
            if(!($fp = @fopen($input, 'r'))){
            	trigger_error('(MGXML): Could not open File or URL: '.$input,E_USER_ERROR);
            	return false;
            }
            while($data = fread($fp,8192)){
                if(!xml_parse($this->parser, $data, feof($fp))){
                	trigger_error(sprintf('(MGXML): XML error at line %d column %d',xml_get_current_line_number($this->parser),xml_get_current_column_number($this->parser)),E_USER_WARNING);
				}
            }
            fclose($fp);
		} 
		else{
			$data = '';
            $lines = explode("\n",$input);
            foreach ($lines as $val) {
                if(trim($val) == ''){
					continue;
				}
                $data = $val . "\n";
                if(!xml_parse($this->parser, $data)){
                	trigger_error(sprintf('(MGXML): XML error at line %d column %d',xml_get_current_line_number($this->parser),xml_get_current_column_number($this->parser)),E_USER_WARNING);
				}
            }
        }
        return true;
    }

    private function mxml_start($parser, $name, $attrs){
       $tag=array("tag"=>$name,"attrs"=>$attrs);
       array_push($this->data,$tag);
    }

    private function mxml_end($parser, $name){
       $this->data[count($this->data)-2][] = $this->data[count($this->data)-1];
       array_pop($this->data);
    }

    private function mxml_char($parser, $tagData){
       if(trim($tagData)){
           if(isset($this->data[count($this->data)-1]['data'])){
               $this->data[count($this->data)-1]['data'] .= htmlentities($tagData);
           }
           else{
               $this->data[count($this->data)-1]['data'] = htmlentities($tagData);
           }
       }    	
    }
	
	private function mxml_writeRecursive($array,$level){
		$t='';
		for($i=0;$i<$level;$i++){
			$t.="\t";
		}
		$string.=$t.'<'.$array['tag'].$this->mxml_formatAttribs($array['attrs']);
		$sub=2;
		if($array['data']){
			$sub=3;
			$t='';
		}
		$size=count($array)-$sub;
		
		if($size == 0&&!$array['data']){
			return $string.="/>\n";
		}
		$string.='>';
		if($array['data']){
			$string.=$array['data'];
		}
		for($i=0;$i<$size;$i++){
			$string.="\n";
			$string.=$this->mxml_writeRecursive($array[$i],$level+1);
		}
		$string.=$t.'</'.$array['tag'].">\n";
		return $string;
	}
	
	private function mxml_formatAttribs($attribs){
		if(!$attribs){
			return false;
		}
		$string=' ';
		foreach($attribs as $key => $value){
			$string.=$key.'="'.$value.'" ';
		}
		return $string;
	}

}
