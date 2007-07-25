<?php
/**********************************************************
    captcha.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 07/25/07

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

@include_once($GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"]."img.class.".PHP_EXT);

class captcha extends img{

	var $config;
	var $app;
	
    //
    //constructor captcha($id)
    //
    //Initializes the captcha script
    //
    //INPUTS:
    //$id	-	page part id [int]
    //
    //returns object on sucess or false on fail		
	function captcha($id){
		if(!$this->config=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_CAPTCHA_DATA,"",array(array("page_id","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"],DB_AND),array("part_id","=",$id)))){
			$this->config=array("chars"=>5,
							"websafecolors"=>false,
							"nb_noise"=>4,
							"maxtry"=>3,
							"ttf_range"=>array(),
							"minsize"=>20,
							"maxsize"=>30,
							"maxrotation"=>20,
							"jpgquality"=>80,
							"ttf_range"=>array("antelope.ttf","epilog.ttf","arialbd.ttf","britannica.ttf"));
		}
		else{
			$this->config["ttf_range"]=explode(";",$this->config["ttf_range"]);
		}
		$this->config["x"]=($this->confg["chars"]+1)*(int)(($this->config["maxsize"]+$this->config["minsize"])/ 1.5);
		$this->config["y"]=(int)(2.4*$this->config["maxsize"]);
		$this->config["x"]=($this->config["chars"]+1)*(int)(($this->config["maxsize"]+$this->config["minsize"])/1.5);
		$this->config["y"]=(int)($this->config["maxsize"]*2.4);
		$this->app=(empty($this->config["name"]))?"default":$this->config["name"];
	}
	
	
	//#################################
	//
	// PUBLIC FUNCTIONS
	//
	//#################################	    
 	
    //
    //public function ca_genca()
    //
    //Generates the captcha
    //
	//returns captcha id on sucess or false on fail
	function ca_genca(){
		$id=$this->ca_genid();
		$ctype="";
		if($this->config["websafecolors"]){
			$ctype="close";
		}

		$this->img_create($this->config["x"],$this->config["y"]);

		// Set Backgroundcolor
		$c=$this->ca_randcolor(224, 255);
		$this->img_fillbackground($c);

		// allocates the 216 websafe color palette to the image
		if($this->config["websafecolors"]){
			$image=$this->ca_websafecolors($image);
		}

		// fill with noise or grid
		if($this->config["nb_noise"]>0){
			// random characters in background with random position, angle, color
			for($i=0;$i<$this->config["nb_noise"];$i++){
				srand((double)microtime()*1000000);
				$size=intval(rand((int)($this->config["minsize"]/2.3),(int)($this->config["maxsize"]/1.7)));
				srand((double)microtime()*1000000);
				$angle=intval(rand(0, 360));
				srand((double)microtime()*1000000);
				$x=intval(rand(0,$this->config["x"]));
				srand((double)microtime()*1000000);
				$y=intval(rand(0,(int)($this->config["y"]-($size/5))));
				$c=$this->ca_randcolor(160, 224);
				srand((double)microtime()*1000000);
				$text=chr(intval(rand(45,250)));
				$this->img_ttftext($text,$size,$angle,$x,$y,$c,this->ca_randttf(),$ctype);
			}
		}
		else{
			// generate grid
			for($i=0;$i<$this->config["x"];$i+=(int)($this->config["minsize"]/1.5)){
				$c=$this->ca_randcolor(160, 224);
				$this->img_line(array($i,$i),array(0,$this->config["y"]),$c,$ctype)
			}
			for($i=0;$i<$this->config["y"];$i+=(int)($this->config["minsize"]/1.8)){
				$c=$this->ca_randcolor(160, 224);
				$this->img_line(array(0,$this->config["x"]),array($i,$i),$c,$ctype)
			}
		}

		// generate Text
		$rnd_text="";
		for($i=0,$x=intval(rand($this->config["minsize"],$this->config["maxsize"]));$i<$this->config["chars"];$i++){
			$text=chr(rand(65,90));
			$rnd_text.=$text;
			srand((double)microtime()*1000000);
			$angle=intval(rand(($this->config["maxrotation"]*-1),$this->config["maxrotation"]));
			srand((double)microtime()*1000000);
			$size=intval(rand($this->config["minsize"],$this->config["maxsize"]));
			srand((double)microtime()*1000000);
			$y=intval(rand((int)($size*1.5),(int)($this->config["y"]-($size/7))));
			$c=$this->ca_randcolor(0, 127);
			$color=$c;
			$c=$this->ca_randcolor(0, 127);
			$shadow=$c
			$ttf_file=$this->ca_randttf();
			$this->img_ttftext($text,$size,$angle,$x+(int)($size/15),$y,$shadow,$ttf_file,$ctype);
			$this->img_ttftext($text,$size,$angle,$x,$y-(int)($size/15),$color,$ttf_file,$ctype);
			$x+=(int)($size+($this->config["minsize"]/5));
		}
		if(!$this->img_write(array("quality"=>$this->config["jpgquality"]),$GLOBALS["MANDRIGO"]["CONFIG"]["IMG_PATH"].TMP_IMG.$this->app."_".$id.".jpg")){
			return false;
		}
		if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.TABLE_CAPTCHA,array($this->app."_".$id,$rnd_text),array("ca_id","ca_string"))){
			return false;
		}
		return $this->app."_".$id;
	}

    //
    //public function  ca_checkca()
    //
    //Checks to see if the user correclty id'd the captcha
    //
	//returns true if user input matches database or false if not
	function ca_checkca(){
		if(!$ca_string=(string)$GLOBALS["MANDRIGO"]["DB"]->db_fetchresult(TABLE_PREFIX.TABLE_CAPTCHA,"ca_string",array(array("ca_id","=",$GLOBALS["MANDRIGO"]["VARS"]["MG_NEWS_CAID"])))){
			return false;
		}

		//cleanup
		@unlink($GLOBALS["MANDRIGO"]["CONFIG"]["IMG_PATH"].TMP_IMG.$GLOBALS["MANDRIGO"]["VARS"]["CA_ID"].".jpg");
		$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_REMOVE,TABLE_PREFIX.TABLE_CAPTCHA,"",array(array("ca_id","=",$GLOBALS["MANDRIGO"]["VARS"]["CA_ID"])));	

		if($ca_string===(string)$GLOBALS["MANDRIGO"]["VARS"]["CA_STRING"]){
			return true;
		}
		return false;
	}
	
	//#################################
	//
	// PRIVATE FUNCTIONS
	//
	//#################################
	
	//
	//private function ca_randttf()
	//
    //returns a random ttf file
    //
	//returns the ttf file path
	function ca_randttf(){
	  	$ttf_file="";
		if(is_array($this->config["ttf_range"])){
			srand((float)microtime() * 10000000);
			$key = array_rand($this->config["ttf_range"]);
			$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"].TTF_FOLDER.$this->config["ttf_range"][$key];
			$ttf_file=$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"].TTF_FOLDER.$this->config["ttf_range"][$key];
		}
		else{
			$ttf_file=$GLOBALS["MANDRIGO"]["CONFIG"]["ROOT_PATH"].TTF_FOLDER.$this->config["ttf_range"];
		}
		return $ttf_file;
	}
	
	//
	//private function ca_websafecolors()
	//
    //colors using only web safe colors
	function ca_websafecolors(){
		for($r = 0; $r <= 255; $r += 51){
			for($g = 0; $g <= 255; $g += 51){
				for($b = 0; $b <= 255; $b += 51){
					$this->img_getcolor(array("r"=>$r,"g"=>$g,"b"=>$b));
				}
			}
		}
	}
	
	//
	//private function ca_randcolor($min,$max)
	//
    //generates a random color 
    //INPUTS:
    //$min		-	minimum color value [int]
    //$max		-	maximum color value [int]
    //
	//returns the color
	function ca_randcolor($min,$max){
	  	$colors=array();
		srand((double)microtime() * 1000000);
		$colors['r'] = intval(rand($min,$max));
		srand((double)microtime() * 1000000);
		$colors['g'] = intval(rand($min,$max));
		srand((double)microtime() * 1000000);
		$colors['b'] = intval(rand($min,$max));
		return $colors;
	}

	//
	//private function ca_genid()
	//
    //Makes a captcha id to identify the captcha
    //
	//returns the id	
	function ca_genid(){
	  	$raw_key=rand(0,999999);
		$enc_key = substr(md5($raw_key),16-($this->config["chars"])/2,15);
		return $enc_key;
	}
}