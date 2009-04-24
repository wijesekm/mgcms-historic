<?php

class mgXML {

    // XML parser variables
    private $parser;
    private $data  = array();
    private $cfg;
    
    public function __construct($pkg){
		//$c=array(array(false,array(DB_AND,1),'pkg_name','=',$pkg),array(false,array(DB_OR,2),'page_path','=',$GLOBALS['MG']['PAGE']['PATH']),array(false,array(false,2),'page_path','=','*'));
		//$dta=$GLOBALS['MG']['SQL']->sql_fetchArray(array(TABLE_PREFIX.'packageconf'),false,$c);
		//for($i=0;$i<$dta['count'];$i++){
		//	$this->cfg[(string)$dta[$i]['var_name']]=(string)$dta[$i]['var_value'];
		//}
		$this->cfg['xml_encoding']='ISO-8859-1';
		$this->cfg['xml_version']='1.0';
	}
	
	public function mxml_getCurrent(){
		print_r($this->data);
	}
	
	public function mxml_write($file=false){
		$string.='<?xml version="'.$this->cfg['xml_version'].'" encoding="'.$this->cfg['xml_encoding'].'"?>'."\n";
		$string.=$this->mxml_writeRecursive($this->data[0],0);
		if(!$file){
			return $string;
		}
		else{
			if(!$fp=fopen($file,'w')){
				trigger_error('(MGXML): Could not open file or url for writing: '.$file,E_USER_ERROR);
			}
			@fwrite($fp);
			fclose($fp);
		}
		return $string;
	}
	
	public function mxml_addTag($tag,$attrs,$data,$parent_indexes){
		$base='$this->data';
		foreach($parent_indexes as $i){
			$base.="[$i]";
		}
		eval("\$c=count($base);");
		eval("\$d=($base"."['data'])?true:false;");
		if($d){
			$c-=3;
		}
		else{
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
		$string.=$t.'<'.$array['tag'].' '.$this->mxml_formatAttribs($array['attrs']).'>';
		$sub=2;
		if($array['data']){
			$string.=$array['data'];
			$sub=3;
		}
		$size=count($array)-$sub;
		if($size == 0){
			$t='';
		}
		for($i=0;$i<$size;$i++){
			$string.="\n";
			$string.=$this->mxml_writeRecursive($array[$i],$level+1);
		}
		$string.=$t.'</'.$array['tag'].">\n";
		return $string;
	}
	
	private function mxml_formatAttribs($attribs){
		$string='';
		if(count($attribs)==0){
			return false;
		}
		foreach($attribs as $key => $value){
			$string.=$key.'="'.$value.'"';
		}
		return $string;
	}

}
