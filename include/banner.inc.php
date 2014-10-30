<?php
//
// +----------------------------------------------------------------------+
// | ADA version 20                                                      |
// +----------------------------------------------------------------------+
// | Copyright (c) 2001-2012 Lynx                                              |
// +----------------------------------------------------------------------+
// |                                                                      |
// |                          BANNER CLASS                                |
// |                                                                      |
// |                                                                      |
// |                                                                      |
// |                                                                      |
// |                                                                      |
// +----------------------------------------------------------------------+
// | Author: Stefano Penge <steve@lynxlab.com>                            |
// |                                                                      |
// +----------------------------------------------------------------------+
//
//
// Banner functions.
//

/**
 * FIXME: the banner has to be managed 
 * at the moment no script use it
 * 
$bannerObj = New Banner();

if (($bannerObj->impressionControl) && ($bannerObj->moduleControl)){
		$bannerObj->updateCounter();
		return $bannerObj->html_code;
} else {
	return NULL;
}
 * 
 */

/////////////////////////////////

class Banner
{

// vars
        var $id_banner;
        var $id_client;
        var $http_address;
        var $width = "468";
        var $height="60";
        var $img;
		var $http_img;
        var $alt = "Banner pubblicitario";
        var $ok = FALSE;
        var $error_msg;
        var $html_code;
		var $img_type;


        function __construct($id_banner=0){

          # constructor

/*
          $user_prefHa = $this->get_user_pref();

          //$node_keys is a list of keywords tha can be used to select a banner
          //$node_keysAr = $this->get_node_keys();
          //$node_keys = implode(",",$node_keysAr);
*/

          $dh = $GLOBALS['dh'];
          //$layoutObj = $GLOBALS['layoutObj'];
          $out_fields_ar = array();
          $module="";
          $keywords="";
          $client="";
          $bannerListAr = $dh->find_banner_list($out_fields_ar, $module, $keywords, $client);
          $id_banner = $bannerListAr[0][0];
          $bannerAr = $dh->get_banner($id_banner);
          //$path = $layoutObj->template_dir."img/banners/";
          $path = "layout/clear/img/banners/"; // TODO: replace



          if (AMA_DataHandler::isError($bannerAr)){
                $this->error_msg = ADA_ERR_BANNER;
                $this->html_code = "<!-- BANNER NOT FOUND-->\n<!-- END BANNER -->\n";
          } else {
                $this->id_banner = $bannerAr['id_banner'];
                $this->id_client = $bannerAr['id_client'];
                $this->http_address = $bannerAr['address'];
                $this->img = ROOT_DIR.$path.$bannerAr['image'];
                $this->impressions = $bannerAr['impressions'];
                $this->acquired_impressions = $bannerAr['a_impressions'];
                $this->from = $bannerAr['date_from'];
                $this->to = $bannerAr['date_to'];
                $this->keywords = $bannerAr['keywords'];
                $this->module = $bannerAr['module'];
                $this->id_course = $bannerAr['id_course'];

                $this->html_code ="<!-- BANNER -->\n<a href=\"".$this->http_address."\" target=_blank><img src=\"".$this->img."\" alt=\"".$this->alt."\"  width=\"".$this->width."\" height=\"".$this->height."\" border=\"0\"></a>\n<!-- END BANNER -->\n";
          }

         } //constructor


         function impressionControl(){
			 return ($this->impressions <= $this->acquired_impressions);
		 }


		 function moduleControl (){
			 $self = $GLOBALS['self'];
			 return (($this->module == NULL) OR (stristr($self, $this->module)));
		 }

		 function updateCounter(){
			 $this->impressions++;
			 $id_banner = $this->id_banner;
			 $ymdhms = time();
			 $dh-> add_userclick($id_banner,"",$ymdhms);
		 }

		 function updateBanner($bannerAr){
			  //$result = $dh->update_banner($bannerAr);
		  }


} // class


/*

    function get_user_pref(){

      $http_root_dir = $GLOBALS['http_root_dir'];
	  $root_dir = $GLOBALS['root_dir'];
	  $sess_id_user = $_SESSION['sess_id_user'];
	  $dh = $GLOBALS['dh'];
	  $user_family = $GLOBALS['user_family'];
	  $id_profile = $GLOBALS['id_profile'];
	  $layoutObj =  $GLOBALS['layoutObj'];
	  $userObj = $GLOBALS['userObj'];


  //        we could else build user profile on history basis...
  //        $historyObj = new History($sess_id_user);
  //        foreach ($visit)
  //             $node_keys[] = $this->get_node_keys($id_node);
  //      etc etc


		$id_banner = 0;  // 0: random; 1: first; ...
		$this->img_type = 'gif'; // jpg, png



		$layout_template = $layoutObj->template;
        $banner_dir = $layoutObj->template_dir."img/banners";

        $user_pref['id_banner'] = $id_banner;
        $user_pref['banner_dir'] = $banner_dir;
        return $user_pref;
        }


    function get_node_keys(){

	 $sess_id_node = $_SESSION['sess_id_node'];
	 $dh = $GLOBALS['dh'];

         $res_ha = $dh->get_node_info($sess_id_node);
         $node_keysAr = array();
         $node_name = $res_ha['name'];
         $node_keywords = $res_ha['title'];
         $node_name_keys = explode(" ",strtolower($node_name));
         $node_keywords_keys = explode(" ",strtolower($node_keywords));
         $node_keysAr = array_merge ($node_name_keys,$node_keywords_keys);
         $unique_node_keysAr = array_filter ($node_keysAr, 'is_stopwordFN');
         return $unique_node_keysAr;
         }


    function click_through($id_banner,$id_user){
         // chiamata quando un utente segue il link
			 $dh = $GLOBALS['dh'];
			 $ymdhms = today_dateFN();
			 $dh->add_userclick($id_banner,$id_user,$ymdhms);


         }

	function get_banner_info($id_banner,$dir,$static=0){

		 $http_root_dir = $GLOBALS['http_root_dir'];
		 $root_dir = $GLOBALS['root_dir'];
		 $dh = $GLOBALS['dh'];


         if ($static) {// static: only one banner

         	$this->img = "$root_dir/img/banners/lynxlab.".$this->img_type;
         	$this->http_address = "http://www.lynxlab.com";
         	$this->alt = "LynxLab";
         	$this->width="468";
         	$this->height="60";
         	$this->ok = 1;
         } else {
               // now reads from file system (templates/<module>/<family>/img/banner/)

               $banner = $this->get_banner($id_banner,$dir);

              if ($banner){

	     	$infofile = $banner.".txt";
	     	if (file_exists($infofile)){
			$infoAr = file($infofile);
			$url_imgAR = explode(',',$infoAr[0]);
			$url = $url_imgAR[0];
			$alt = $url_imgAR[1];

			$this->http_address = $http_root_dir."/include/visit.php?id=$id_banner&url=$url";
			$this->alt = $alt;
			$this->longdesc = $alt;
			//$this->width="468";
			//$this->height="60";
			$this->img = $banner;
			$rel_path_bannerAr = split("/templates",$banner);
			$rel_path_banner = $rel_path_bannerAr[1];
			$this->http_img = $http_root_dir."/templates".$rel_path_banner;
			$this->ok = 1;
		} else {
                	$this->ok = 0;
             	}
             } else {
                $this->ok = 0;
             }
         }
         }





     function get_banner($id_banner,$dir){

         if ($id_banner){
             $elencofile = $this->leggidir($dir,1,$this->img_type); // 0:random mode; 1: ordered mode
             $img = $elencofile[$id_banner]['file'];
         } else {
             $id_banner = 0;
             $elencofile = $this->leggidir($dir,0,$this->img_type); // 0:random mode; 1: ordered mode
             $img = $elencofile[0]['file'];
         }

         $this->id_cliente = $id_banner;

         return "$dir/$img";
    }


    function leggidir($dir,$mode,$type){
         // read a directory and returns an Array with all files of specified type
         // mode can be 0 (random order) or 1 (regular order))

         	$dirid = @opendir($dir);

         	if (!empty($dirid)){
			$i = 0;
			while (($file = readdir($dirid))!=false){
				$fileAndExt = explode('.',$dir.$file);
				$stop = count($fileAndExt)-1;
				if ($fileAndExt[$stop]==$type){
					$elencofile[$i]['file'] = $file;
					//print $file;
					$i++;
				}
			}
			closedir ($dirid);
			if (is_array($elencofile)){
				switch ($mode){
				case 1: // ordinato:
					sort($elencofile);
					break;
				case 0: // random
					default:
					shuffle($elencofile);
				}
				reset($elencofile);
			} else {
			$elencofile = array();
		}
		} else {
			$elencofile = array();
		}
         return $elencofile;

         }




// UTILITIES
function is_stopwordFN(&$val){
// used by get_node_keys

// Attention: this is language dependent !!!
         $stopwords = array(
                            'il',
                            'lo',
                            'la',
                            'i',
                            'gli',
                            'le',
                            'a',
                            'e',
                            'ma',
                            'che',
                            'per',
                            'su',
                            'con',
                            'tra'
                               );
         return (!in_array($val,$stopwords));
}

// not used
function filter_array(&$val,$key,$stopwords){
                  if (in_array($val,$stopwords))
                      $val = "";
}
* */
?>
