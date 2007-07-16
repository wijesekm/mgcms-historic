

class mg_gallery{
	
	function mg_gallery{
		$config=array(
		"THUMB_SIZE"=>"80x80",
		"DISPLAY_SIZE"=>"400x400",
		"PATH"=>"/gallery/",
		"DISP_PATH"=>"/display/",
		"SOURCE_PATH"=>"/source/"
		);
		$config["PATH"]=$GLOBALS["MANDRIGO"]["CONFIG"]["EXTERNAL_PATH"].$config["PATH"].$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"];

	}
	function ga_display(){
		
	}
	function ga_displayimg(){
	 	if(!$album=$this->ga_load("album")){

		}
	 	if(!$image=$this->ga_load("image"){
		
		}
		$file=$config["PATH"].$album["al_path"].$config["DISP_PATH"].$image["img_file"];
		if(!is_file($file)){
			$source=$config["PATH"].$album["al_path"].$config["SOURCE_PATH"].$image["img_file"];
			$s_image=new img();
			$s_image->img_read($source);
			$size=explode($this->config["DISPLAY_SIZE"]);
			$s_image->img_presizelimit($size[0],$size[1]);
			$s_image->img_write("",$file);
		}
		if((int)$album["al_enabeled"]==0){
			return false;	
		}
		if((int)$album["al_readlevel"] < $GLOBALS["MANDRIGO"]["CURRENTUSER"]["ACL"]["READ"]){
			return false;
		}
		$parse("IMG_PATH",$file,
			   "AL_IMG_ID",$image["al_img_id"],
			   "IMG_ID",$image["img_id"],
			   "IMG_NAME",$image["img_name"],
			   "IMG_FULLNAME",$image["img_extendedname"],
			   "IMG_FILENAME",$image["img_file"],
			   "IMG_DESCRIPTION",$image["img_description"],
			   "IMG_FILETYPE",$image["img_filetype"]
			   );
	}
	function ga_fullimg(){
	 	if(!$album=$this->ga_load("album")){
			
		}
	 	if(!$image=$this->ga_load("image"){
		
		}
	 	$file=$config["PATH"].$album["al_path"].$config["SOURCE_PATH"].$image["img_file"];
	 	$cur_img=new img();
	 	$cur_img->img_read($file);
	 	if(!$cur_img->img_display()){
			
		}
	}
	function ga_convertext($ext){
		switch($ext){
			case 'jpg':
			case 'jpeg':
				return IMAGETYPE_JPEG;
			break;
			case 'png':
				return IMAGETYPE_PNG;
			break;
			case 'gif':
				return IMAGETYPE_GIF;
			break;
			default:
				return false;
			break;
		}
	}
	function ga_load($item="config"){
	 	switch($config){
			case "album":
	 			return $GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_ALBUM.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],'',array(array("al_name","=",$GLOBALS["MANDRIGO"]["VARS"]["G_ALBUM"])));	
			break;
			case "img":
	 			return $GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_IMAGES.$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["NAME"],'',array(array("img_name","=",$GLOBALS["MANDRIGO"]["VARS"]["G_IMAGE"])));				
			break;
			case "config":
			default:
	 			return $GLOBALS["MANDRIGO"]["DB"]->db_fetcharray(TABLE_PREFIX.TABLE_GALLERY,'',array(array("pg_id","=",$GLOBALS["MANDRIGO"]["CURRENTPAGE"]["ID"])));				
			break;
		}
	}
}