<?php
/**********************************************************
    captcha.class.php
	Last Edited By: Kevin Wijesekera
	Date Last Edited: 03/13/07

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


class captcha{

	var $config;
	var $app;
	
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
    //generates a captcha id and image.
    //
	//returns id
	function ca_genca(){
		$id=$this->ca_genid();
		$gd_version=$this->ca_getgdver();
		if($gd_version >= 2 && !$this->config["websafecolors"]){
			$func1 = 'imagecreatetruecolor';
			$func2 = 'imagecolorallocate';
		}
		else{
			$func1 = 'imageCreate';
			$func2 = 'imagecolorclosest';
		}
		$image = $func1($this->config["x"],$this->config["y"]);

		// Set Backgroundcolor
		$c=$this->ca_randcolor(224, 255);
		$back= @imagecolorallocate($image,$c["r"],$c["g"],$c["b"]);
		imagefilledrectangle($image,0,0,$this->config["x"],$this->config["y"],$back);

		// allocates the 216 websafe color palette to the image
		if($gd_version<2||$this->config["websafecolors"]){
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
				$color=$func2($image,$c["r"],$c["g"],$c["b"]);
				srand((double)microtime()*1000000);
				$text=chr(intval(rand(45,250)));
				imagettftext($image,$size,$angle,$x,$y,$color, $this->ca_randttf(),$text);
			}
		}
		else{
			// generate grid
			for($i=0;$i<$this->config["x"];$i+=(int)($this->config["minsize"]/1.5)){
				$c=$this->ca_randcolor(160, 224);
				$color=$func2($image,$c["r"],$c["g"],$c["b"]);
				imageline($image,$i,0,$i,$this->config["y"],$color);
			}
			for($i=0;$i<$this->config["y"];$i+=(int)($this->config["minsize"]/1.8)){
				$c=$this->ca_randcolor(160, 224);
				$color=$func2($image,$c["r"],$c["g"],$c["b"]);
				imageline($image,0,$i,$this->config["x"],$i,$color);
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
			$color=$func2($image,$c["r"],$c["g"],$c["b"]);
			$c=$this->ca_randcolor(0, 127);
			$shadow=$func2($image,$c["r"]+127,$c["g"]+127,$c["b"]+127);
			$ttf_file=$this->ca_randttf();
			imagettftext($image,$size,$angle,$x+(int)($size/15),$y,$shadow,$ttf_file,$text);
			imagettftext($image,$size,$angle,$x,$y-(int)($size/15),$color,$ttf_file,$text);
			$x+=(int)($size+($this->config["minsize"]/5));
		}
		imagejpeg($image,$GLOBALS["MANDRIGO"]["CONFIG"]["IMG_PATH"].TMP_IMG.$this->app."_".$id.".jpg",$this->config["jpgquality"]);
		imagedestroy($image);
		if(!$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_INSERT,TABLE_PREFIX.TABLE_CAPTCHA,array($this->app."_".$id,$rnd_text),array("ca_id","ca_string"))){
			return false;
		}
		return $this->app."_".$id;
	}

    //
    //public function  ca_checkca()
    //
    //checks user inputted data to see if it matches
    //
	//returns true if it matches and false if not
	function ca_checkca(){
		if(!$sql_result=$GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_CAPTCHA,"",array(array("ca_id","=",$GLOBALS["MANDRIGO"]["VARS"]["CA_ID"])))){
			return false;
		}
		
		//cleanup
		@unlink($GLOBALS["MANDRIGO"]["CONFIG"]["IMG_PATH"].TMP_IMG.$GLOBALS["MANDRIGO"]["VARS"]["CA_ID"].".jpg");
		$GLOBALS["MANDRIGO"]["DB"]->db_update(DB_REMOVE,TABLE_PREFIX.TABLE_CAPTCHA,"",array(array("ca_id","=",$GLOBALS["MANDRIGO"]["VARS"]["CA_ID"])));	
		
		if($sql_result["ca_string"]===$GLOBALS["HTTP_POST"]["CA_STRING"]){
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
	//private function ca_websafecolors($image)
	//
    //colors using only web safe colors
    //
	//returns the colored image
	function ca_websafecolors($image){
	  	//$a = array();
		for($r = 0; $r <= 255; $r += 51){
			for($g = 0; $g <= 255; $g += 51){
				for($b = 0; $b <= 255; $b += 51){
					$color = imagecolorallocate($image, $r, $g, $b);
					//$a[$color] = array('r'=>$r,'g'=>$g,'b'=>$b);
				}
			}
		}
		return $image;
	}
	
	//
	//private function ca_randcolor($min,$max)
	//
    //generates a random color 
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
	//private function ca_getgdver()
	//
    //gets the gd version
    //
	//returns the gd version
	function ca_getgdver(){
		static $gd_version_number = null;
		if($gd_version_number === null){
			ob_start();
			phpinfo(8);
			$module_info = ob_get_contents();
			ob_end_clean();
			if(preg_match("/\bgd\s+version\b[^\d\n\r]+?([\d\.]+)/i", $module_info, $matches)){
				$gd_version_number = $matches[1];
			}
			else{
				$gd_version_number = 0;
			}
		}
		return $gd_version_number;
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