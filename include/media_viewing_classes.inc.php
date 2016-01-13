<?php
/**
 * Media Viewers
 *
 * PHP version >= 5.0
 *
 * @package		view
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		media_viewing_classes
 * @version		0.1
 */

class MediaViewer {
    private $viewing_preferences;
    private $user_data;
    private $media_path;
    private $media_title;

    public function __construct($media_path, $user_data=array(), $VIEWING_PREFERENCES=array(), $title='') {
	$this->media_path = $media_path;
        $this->user_data = $user_data;
        $this->viewing_preferences = $VIEWING_PREFERENCES;
        $this->media_title = $title;
        $this->default_http_media_path = $this->media_path;
    }
    
    public function setMediaPath ($media_data=array()) {
        if (file_exists(ROOT_DIR. MEDIA_PATH_DEFAULT. $media_data['owner'] . '/'. $media_data['value'])) {
            $this->media_path = HTTP_ROOT_DIR. MEDIA_PATH_DEFAULT. $media_data['owner'].'/';
        }
        else {
            $this->media_path = $this->default_http_media_path;
        }
    }

    /**
     * function getViewer, used to call the appropriate viewer for the selected media type ($media_data['type'])
     *
     * @param array $media_data - associative array, at least with defined keys 'type' and 'path'
     * @param array $user_data  - associative array, at least with a defined key 'level'
     * @param array $node_data  - associative array, at least with a defined key 'level'
     * @param array $VIEWING_PREFERENCES
     * @return string
     */
    public function getViewer($media_data=array()) {    	
        $media_type  = isset($media_data[1]) ? $media_data[1] : null;
		if (isset($media_data['type'])) {
			$media_type = $media_data['type'];
		}

        $media_value = isset($media_data[2]) ? $media_data[2] : null;
		if (isset($media_data['value'])) {
			$media_value = $media_data['value'];
		}

		$media_title = null;
		if (isset($media_data['title']) && !is_null($media_data['title'])) {
			$media_title = $media_data['title'];
		}

		$media_width = null;
		if (isset($media_data['width']) && !is_null($media_data['width'])) {
			$media_width = $media_data['width'];
		}
		
		$media_height = null;
		if (isset($media_data['height']) && !is_null($media_data['height'])) {
			$media_height = $media_data['height'];
		}

		/**
		 * @author giorgio 23/apr/2013
		 *
		 * modified: now each 'if' body outputs to a variable instead of returning immediately.
		 * string is wrapped around a <div> element with proper class for css styling just before returning.
		 *
		 */
		
		/* @var $return string */
		$return = '';		
        if ($media_type === _IMAGE || $media_type === _MONTESSORI) {
			$return = ImageViewer::view($this->media_path,$media_value, $this->viewing_preferences[_IMAGE],$media_title,$media_width,$media_height);
		}
		else if ($media_type === _SOUND || $media_type === _PRONOUNCE) {
			$return = AudioPlayer::view($this->media_path,$media_value, $this->viewing_preferences[_SOUND],$media_title);
		}
		else if ($media_type === _VIDEO || $media_type === _LABIALE || $media_type === _FINGER_SPELLING) {
			$return = VideoPlayer::view($this->media_path,$media_value,$this->viewing_preferences[_VIDEO],$media_title,$media_width,$media_height);
		}
		else if ($media_type === _LIS && isset($_SESSION['mode']) && $_SESSION['mode'] == 'LIS') {
			$return = VideoPlayer::view($this->media_path,$media_value,$this->viewing_preferences[_VIDEO],$media_title,$media_width,$media_height);
		}
		else if ($media_type === _DOC) {
			$return = DocumentViewer::view($this->media_path,$media_value,$this->viewing_preferences[_DOC]);
		}
		else if ($media_type === _LINK) {
			$return = ExternalLinkViewer::view($this->media_path, $media_value, $this->viewing_preferences[_LINK]);
		}
		else {
			$return = '';
        }
        
        /**
         * @author giorgio 23/apr/2013
         *
         * array to hold proper css classes
         */
        
        $cssArray = array (
        		_IMAGE=>'image',
        		_MONTESSORI=>'montessori',
        		_SOUND=>'sound',
        		_PRONOUNCE=>'pronounce',
        		_VIDEO=>'video',
        		_LABIALE=>'labiale',
        		_LIS=>'lis',
        		_FINGER_SPELLING=>'finger-spelling',
        		_DOC=>'doc',
        		_LINK=>'link'
        );
        
        /**
         * @author giorgio 23/apr/2013
         *
         * wrap $return around a div and return as promised
        */
        
        if ( $return !== '' )
        {
        	$return = "<div class='media ".$cssArray[$media_type]."'>".$return."</div>";
        }
        
        return $return;
    }
	
    public function displayLink ($media_data=array()) {
        $media_value = isset($media_data[2]) ? $media_data[2] : null;
		if (isset($media_data['value'])) {
			$media_value = $media_data['value'];
		}
        return InternalLinkViewer::view($this->media_path, $media_value, $this->viewing_preferences[INTERNAL_LINK], $this->user_data['level'], $this->user_data['id_course']);
    }

    /**
     * function getMediaLink
     *
     * @param  $media_data -
     * @return string      - the html string containing the appropriate link for the given media
     */
    public function getMediaLink( $media_data=array() ) {
		$media_type  = $media_data[1];
		if (isset($media_data['type'])) {
			$media_type  = $media_data['type'];
		}

		$media_value = $media_data[2];
		if (isset($media_data['value'])) {
			$media_value  = $media_data['value'];
		}
                $media_real_file_name = $media_data[3];
                $path_to_media = $media_data[4];
		$media_title = isset($media_data[5]) ? $media_data[5] : null;
		if (isset($media_data['title'])) {
			$media_title  = $media_data['title'];
		}
				
		
        if ($media_type === _IMAGE || $media_type === _MONTESSORI) {
        	$viewing_prefs = isset($this->viewing_preferences[_IMAGE]) ? $this->viewing_preferences[_IMAGE] : null;
			return ImageViewer::link($this->media_path,$media_value, $media_real_file_name,$path_to_media, $viewing_prefs, $media_title, $media_type);
		}
		else if ($media_type === _SOUND || $media_type === _PRONOUNCE) {
			$viewing_prefs = isset($this->viewing_preferences[_SOUND]) ? $this->viewing_preferences[_SOUND] : null;
			return AudioPlayer::link($this->media_path,$media_value, $media_real_file_name, $path_to_media,$viewing_prefs, $media_title, $media_type);
		}
		else if ($media_type === _VIDEO || $media_type === _LABIALE || $media_type === _LIS || $media_type === _FINGER_SPELLING) {
			$viewing_prefs = isset($this->viewing_preferences[_VIDEO]) ? $this->viewing_preferences[_VIDEO] : null;
			return VideoPlayer::link($this->media_path,$media_value, $media_real_file_name, $path_to_media,$viewing_prefs, $media_title, $media_type);
		}
		else if ($media_type === _DOC) {
			$viewing_prefs = isset($this->viewing_preferences[_DOC]) ? $this->viewing_preferences[_DOC] : null;
			return DocumentViewer::link($this->media_path,$media_value, $media_real_file_name, $path_to_media,$viewing_prefs, $media_title);
		}
		else if ($media_type === _LINK) {
			$viewing_prefs = isset($this->viewing_preferences[_LINK]) ? $this->viewing_preferences[_LINK] : null;
			return ExternalLinkViewer::view($this->media_path, $media_value, $viewing_prefs);
		}
		else {
			return '';
        }

    }

    private function checkExtension($filename, $extension)
    {
        $path_to_file = str_replace(HTTP_ROOT_DIR, ROOT_DIR, $this->media_path);
        return (pathinfo($path_to_file . $filename, PATHINFO_EXTENSION) == $extension);
    }
}

/**
 * class ImageViewer, returns the correct representation for the image based on ImageViewingPreferences
 *
 */
class ImageViewer {
    /**
     * function view
     *
     * @param  string $http_file_path
     * @param  string $file_name
     * @param  mixed  $ImageViewingPreferences
     * @return string
     */
    public static function view( $http_file_path, $file_name, $ImageViewingPreferences = IMG_VIEWING_MODE, $imageTitle = null,$width = null,$height = null) {
		if (!is_null($width)) {
			$width = ' width="'.$width.'"';
		}
		if (!is_null($height)) {
			$height = ' height="'.$height.'"';
		}
        switch ( $ImageViewingPreferences ) {
            case 0:
            case 1:
            default:
                $exploded_img = '<a href="'.$http_file_path.$file_name.'"><img src="img/_img.png" border="0" alt="'.$file_name.'"'.$width.$height.' />'.$imageTitle.'</a>';
			break;
		
            case 2:
                $exploded_img = '<img src="'.$http_file_path.$file_name.'" alt="'.$file_name.'"'.$width.$height.' />';
			break;
        }
        return $exploded_img;
    }

    public static function link( $http_file_path, $file_name, $real_file_name, $path_to_file, $ImageViewingPreferences = IMG_VIEWING_MODE, $imageTitle = null) {

		$size = getimagesize($path_to_file);
		$x = $size[0];
		$y = $size[1];

        $file_name_http = $http_file_path.$real_file_name;

        if ($imageTitle == NULL || !isset($imageTitle)) {
                $imageTitle = $file_name;
        }

        switch (IMG_VIEWING_MODE) {   // it would be better to use a property instead
            case 2: // full img in page, only icon here
                $link_media = '<img src="img/_img.png"><a href="#" onclick="newWindow(\''.$file_name_http.'\','.$x.','.$y.');">'.$imageTitle.'</a>';
			break;

            case 1: // icon in page, a reduced size preview  here
                $link_media = '<img src="'.HTTP_ROOT_DIR.'/include/resize.php?img='.$file_name.'&ratio='.$r.'"><a href="#" onclick="newWindow(\''.$file_name_http.'\','.$x.','.$y.');">'.$file_name.'</a>';
			break;

            case 0: // icon in page,  icon here
            default:
                $link_media = '<img src="img/_img.png"><a href="#" onclick="newWindow(\''.$file_name_http.'\','.$x.','.$y.');">'.$imageTitle.'</a>';
			break;
        }

        return $link_media;
    }
}

/**
 * class AudioPlayer, returns the correct player for this audio file based on AudioPlayingPreferences
 *
 */
class AudioPlayer {
    /**
     * function view
     *
     * @param  string $http_file_path
     * @param  string $file_name
     * @param  mixed  $AudioPlayingPreferences
     * @return string
     */
    public static function view( $http_file_path, $file_name, $AudioPlayingPreferences = AUDIO_PLAYING_MODE, $audioTitle = null ) {
    	$http_root_dir = $GLOBALS['http_root_dir'];    	
    	
    	require_once ROOT_DIR.'/include/getid3/getid3.php';
    	$getID3 = new getID3();
    	$toAnalyze = ( !empty($http_file_path) ? $http_file_path : ROOT_DIR).$file_name;
    	$fileInfo = $getID3->analyze(urldecode(str_replace (HTTP_ROOT_DIR,ROOT_DIR,$toAnalyze)));

        if ($audioTitle == NULL || !isset($audioTitle)) {
            $audioTitle = $file_name;
        }
        switch ( $AudioPlayingPreferences ) {
            case 0:
                $exploded_audio = '<a href="'.$http_file_path.$file_name.'" target="_blank"><img src="img/_audio.png" border="0" alt="'.$audioTitle.'">'.$audioTitle.'</a>';
			break;

            case 1:
            case 2:
            default:
            	
            	if ($fileInfo['fileformat']=='mp3') // use jplayer if mp3
            	{
            		require_once ROOT_DIR . '/include/HtmlLibrary/MediaViewingHtmlLib.inc.php';
            		$exploded_audio = MediaViewingHtmlLib::jplayerMp3Viewer($http_file_path.$file_name, $audioTitle);
            	} else {
					$url = $http_root_dir. "/external/mediaplayer/1pixelout/1pixelout.swf";
					$exploded_audio = '
					<object type="application/x-shockwave-flash" data="'.$url.'" width="290" height="24" >
						<param name="movie" value="'.$url.'" />
						<param name="wmode" value="transparent" />
						<param name="menu" value="false" />
						<param name="quality" value="high" />
						<param name="FlashVars" value="soundFile='.$http_file_path.$file_name.'" />
						<embed src="'.$url.'" flashvars="soundFile='.$http_file_path.$file_name.'" width="290" height="24" />
					</object>';
            	}
			break;
        }
        return $exploded_audio;
    }

    public static function link( $http_file_path, $file_name, $real_file_name, $path_to_file, $AudioPlayingPreferences = AUDIO_PLAYING_MODE, $audioTitle = null ) {
        if ($audioTitle == NULL || !isset($audioTitle)) {
                $imageTitle = $file_name;
        }
        return '<img src="img/_audio.png"><a href="'.$http_file_path.$real_file_name.'" target="_blank">'.$audioTitle.'</a>';
    }
}

/**
 * class VideoPlayer, returns the correct player for this video file, based on VideoPlayingPreferences
 *
 */
class VideoPlayer {
	const DEFAULT_WIDTH = DEFAULT_VIDEO_WIDTH;
	const DEFAULT_HEIGHT = DEFAULT_VIDEO_HEIGHT;
	
	/**
	 * function heightCalc
	 * 
	 */
	public static function heightCalc($width = DEFAULT_WIDTH, $mediaWidth = DEFAULT_WIDTH, $mediaHeight = DEFAULT_HEIGHT) {
		$height_dest = floor($mediaHeight*($width/$mediaWidth));
		return $height_dest;
		
	}
	
	
    /**
     * function view
     *
     * @param  string $http_file_path
     * @param  string $file_name
     * @param  mixed  $VideoPlayingPreferences
     * @return string
     */
    public static function view( $http_file_path, $file_name, $VideoPlayingPreferences = VIDEO_PLAYING_MODE, $videoTitle = null, $width = null,$height = null) {
    	
    	require_once ROOT_DIR.'/include/getid3/getid3.php';

    	$getID3 = new getID3();
    	$toAnalyze = ( !empty($http_file_path) ? $http_file_path : ROOT_DIR).$file_name;
    	$fileInfo = $getID3->analyze(urldecode(str_replace (HTTP_ROOT_DIR,ROOT_DIR,$toAnalyze)));
		
    	if (empty($width)) {
    		$width = self::DEFAULT_WIDTH;
    	}
//     	if (empty($height)) {
//     		$height = self::DEFAULT_HEIGHT;
//     	}
		$mediaWidth = (intval ($fileInfo['video']['resolution_x'])>0) ? intval($fileInfo['video']['resolution_x']) : null;
		$mediaHeight = (intval ($fileInfo['video']['resolution_y'])>0) ? intval($fileInfo['video']['resolution_y']) : null;
		$height = VideoPlayer::heightCalc($width,$mediaWidth, $mediaHeight);
		
    	if ( (empty($width) || empty($height) ) && isset ($fileInfo['video']) && !empty($fileInfo['video']))
    	{
    		$width = (intval ($fileInfo['video']['resolution_x'])>0) ? intval($fileInfo['video']['resolution_x']) : null;
    		$height = (intval ($fileInfo['video']['resolution_y'])>0) ? intval($fileInfo['video']['resolution_y']) : null;
    	}
    	

        if ($videoTitle == NULL || !isset($videoTitle)) {
            $videoTitle = $file_name;
        }
		
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);		

        switch ($VideoPlayingPreferences) {
            case 2:

                // tag are replaced by fullsize img
                switch ($extension) {
                    case 'dcr': //shockwave
                        $exploded_video = '
							<object classid="clsid:166B1BCA-3F9C-11CF-8075-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/director/sw.cab#version=8,0,0,0" width="'.$width.'" height="'.$height.'">
								<param name="movie" value="'.$http_file_path.$file_name.'">
								<embed src="'.$http_file_path.$file_name.'" quality="high" pluginspage="http://www.macromedia.com/shockwave/download/" width="'.$width.'" height="'.$height.'"></embed>
							</object>';
					break;
				
                    case 'swf': // flash
						$exploded_video = '
							<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=5,0,0,0" width="'.$width.'" height="'.$height.'">
								<param name="movie" value="'.$http_file_path.$file_name.'">
								<param name="quality" value="high">
								<embed src="'.$http_file_path.$file_name.'" quality="high" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="'.$width.'" height="'.$height.'"></embed>
							</object>';
					break;

                    case 'flv':
                    case 'avi':
                    case 'mp4':                    	
                        if(defined('USE_MEDIA_CLASS') && class_exists(USE_MEDIA_CLASS, false)) {
                            $className = USE_MEDIA_CLASS;
                            $file_name = $className::getPathForFile($file_name);
                        } else {
                            $file_name = Media::getPathForFile($file_name);
                        }
                        
                        /**
                         * old code to be used for flowplayer
                         */
                        // if (!$_SESSION['mobile-detect']->isMobile()) $playerAttr = ' data-engine="flash" ';
                        // else $playerAttr = '';
                        
                        if ($fileInfo['fileformat']=='mp4')
                        {
                        	/**
                        	 * old code to be used for flowplayer
                        	 */
                        	// $exploded_video = '<div class="ADAflowplayer color-light no-background" style="width:'.$width.'px; height:'.$height.'px;"'.
                        	// 		$playerAttr.'data-swf="'.HTTP_ROOT_DIR.'/external/mediaplayer/flowplayer-5.4.3/flowplayer.swf" data-embed="false">
                        	//		<video>
                        	//			<source src="'.$http_file_path.$file_name.'" type="video/mp4" />
                        	//		</video></div>';
                        	require_once ROOT_DIR . '/include/HtmlLibrary/MediaViewingHtmlLib.inc.php';
                        	$exploded_video = MediaViewingHtmlLib::jplayerMp4Viewer($http_file_path. $file_name, $file_name, $width, $height);                       
                        }
                        else {                        
						$exploded_video = '
							<object id="flowplayer" width="'.$width.'" height="'.$height.'" data="'.HTTP_ROOT_DIR.'/external/mediaplayer/flowplayer/flowplayer.swf"	type="application/x-shockwave-flash">
							<param name="movie" value="'.HTTP_ROOT_DIR.'/external/mediaplayer/flowplayer/flowplayer.swf" />
							<param name="allowfullscreen" value="true" />
							<param name="flashvars" value="config={\'clip\':{\'url\':\''.$http_file_path.$file_name.'\', \'autoPlay\':false, \'autoBuffering\':true}}" />
						</object>';
                        }						
					break;

                    case 'mpg':                    
                    default:
						$exploded_video = '<embed src="'.$http_file_path.$file_name.'" controls="smallconsole" width="'.$width.'" height="'.$height.'" loop="false" autostart="false">';
					break;
                }
			break;
		
            case 1:
            case 0:
            default:
            // tag are replaced by icons
                $desc = translateFN('guarda il filmato');
                $exploded_video = '<a href="#" onclick="openMessenger(\'loader.php?lObject=\\1&ext=\\2&sAuthorId=9&sWidth='.$width.'&sHeight='.$height.'\','.$width.','.$height.');"><img src="img/_video.png" border="0" alt="'.$file_name.'">'.$desc.'</a>';
			break;
        }
        return $exploded_video;
    }

    public static function link( $http_file_path, $file_name, $real_file_name, $path_to_file, $VideoPlayingPreferences=VIDEO_PLAYING_MODE, $videoTitle = null, $media_type ) {
        switch ($media_type) {
            case _VIDEO:
                $label = translateFN('video');
                break;
            case _LABIALE:
                $label = translateFN('video del labiale');
                break;
            case _LIS:
                $label = translateFN('video LIS');
                break;
            case _FINGER_SPELLING:
                $label = translateFN('video dello Spelling');
                break;
        }
        $root_dir_path = str_replace(HTTP_ROOT_DIR, ROOT_DIR, $http_file_path);
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
        if ($videoTitle == NULL || !isset($videoTitle)) {
            $videoTitle = $file_name;
        }
        
        $templateFamily = (isset($_SESSION['sess_template_family']) && strlen($_SESSION['sess_template_family'])>0) ? $_SESSION['sess_template_family'] : ADA_TEMPLATE_FAMILY;
        return '<a href="#" onClick="openInRightPanel(\''.$http_file_path.$file_name.'\',\''.$extension.'\');"><img src="../layout/'.$templateFamily.'/img/flv_icon.png" alt="video">'.$label.' '.$videoTitle.'</a>';
        //return '<img src="img/_video.png"><a href="'.$http_file_path.$real_file_name.'" target="_blank">'.$file_name.'</a>';
    }
}

/**
 * class InternalLinkViewer, returns the correct representation for this internal link based on
 * InternalLinkViewingPreferences and on user level
 *
 */
class InternalLinkViewer {
    /**
     * function view
     *
     * @param  string $http_file_path
     * @param  string $media_value
     * @param  mixed  $InternalLinkViewingPreferences
     * @param  int    $user_level
     * @return string
     */
    public static function view( $http_file_path, $media_value, $InternalLinkViewingPreferences = 0, $user_level, $id_course ) {
        $id_node = $id_course .'_'.$media_value;

        $nodeObj = new Node($id_node, 0);
        // controllo errore

        if ( $nodeObj->full == 1 ) {
            $linked_node_level = $nodeObj->level;
            $name = $nodeObj->name;

            if ($_SESSION['sess_id_user_type'] == AMA_TYPE_STUDENT && $linked_node_level > $user_level ) {
                $exploded_link = '<img src="img/_linkdis.png" border="0" alt="'.$name.'" /><span class="link_unreachable">'.$name.'</span>';
            }
            else {
				$exploded_link = '<a href="'.HTTP_ROOT_DIR.'/browsing/view.php?id_node='.$id_node.'"><img src="img/_linka.png" border="0" alt="'.$name.'">'.$name.'</a>';
            }
        }
        else {
            $exploded_link = '<img src="img/_linkdis.png" border="0" alt="'.$id_node.'" />';
        }
        return $exploded_link;
    }

    public static function link( $http_file_path, $file_name, $real_file_name, $path_to_file, $InternalLinkViewingPreferences ) {
        //return '<a href="'.$http_file_path.$real_file_name.'">'.$file_name.'</a>';
        return '';
    }
}

/**
 * class ExternalLinkViewer
 *
 */
class ExternalLinkViewer {
    /**
     * function view
     *
     * @param  string $http_file_path
     * @param  string $media_value
     * @param  mixed  $ExternalLinkViewingPreferences
     * @return string
     */
    public static function view( $http_file_path, $media_value, $ExternalLinkViewingPreferences ) {
        switch ( $ExternalLinkViewingPreferences ) {
            case 0:
            case 1:
            case 2:
            default:
            /*
         * Remove http[s]:// from the link
            */
                $cleaned_string = preg_replace("/http[s]?:\/\//", "", $media_value);
//        $ADA_EXTERNAL_LINKS_MAX_LENGTH = 10;
//        $string_length = count($cleaned_string);
//
//        $diff = $string_length - $ADA_EXTERNAL_LINKS_MAX_LENGTH;
//        if($diff > 3) {
//          $stop = $string_lenght/2 -$diff/2;
//          $inizio = substr($cleaned_string,0, $stop);
//          $fine   = substr($tring, $string_lenght-$stop);
//          $cleaned_string = "$inizio...$fine";
//        }
//
                //$exploded_ext_link = "<a href=\"$media_value\" target=\"_blank\"><img src=\"img/_web.png\" border=\"0\" title=\"$media_value\" alt=\"\"> $cleaned_string </a>";

                /*
         * Ottiene l'id per il media con nome $media_value
         * e costruisce il link a external_link.php
                */
                $dh = $GLOBALS['dh'];
                $id = $dh->get_risorsa_esterna_id($media_value);
                if(AMA_DataHandler::isError($id)) {
                    $exploded_ext_link = $cleaned_string;
                }
                else {
                	$spanLink = CDOMElement::create('span');
                	$linkImg = CDOMElement::create('img');
                	$linkImg->setAttribute('src', 'img/_web.png');
                	$linkImg->setAttribute('border', '0');
                	$linkImg->setAttribute('title', $media_value);
                	$linkImg->setAttribute('alt', $media_value);
                	$spanLink->addChild($linkImg);
                	$spanLink->addChild(new CText($cleaned_string));
                	
                	if (stripos($media_value,'https')===0) {
                		/**
                		 * @author giorgio 09/set/2015
                		 * 
                		 * if link is https do not show it in an iframe
                		 * as it will cause security problems
                		 */
                		$href = $media_value;
                	} else {
	                	$href = HTTP_ROOT_DIR.'/browsing/external_link.php?id='.$id;                		
                	}
                	$link = BaseHtmlLib::link($href, $spanLink);
                	$link->setAttribute('target', '_blank');
                	$exploded_ext_link = $link->getHtml();
                }
                break;
        }
        return $exploded_ext_link;
    }

    public static function link( $http_file_path, $file_name, $real_file_name,$path_to_file, $ExternalLinkViewingPreferences ) {
        //return '<a href="'.$http_file_path.$real_file_name.'">'.$file_name.'</a>';
        return '';
    }
}

/**
 * class DocumentViewer
 *
 */
class DocumentViewer {
    /**
     * function view
     *
     * @param  string $http_file_path
     * @param  string $media_value
     * @param  mixed  $DocumentViewingPreferences
     * @return string
     */
    public static function view( $http_file_path, $media_value, $DocumentViewingPreferences = DOC_VIEWING_MODE) {
        switch ( $DocumentViewingPreferences ) {
            case 0:
            case 1:
            case 2:
            default:
                $exploded_document = '<a href="'.$http_file_path.$media_value.'" target="_blank"><img src="img/_doc.png" border="0" alt="'.$media_value.'"></a>';
                break;
        }
        return $exploded_document;
    }

    public static function link( $http_file_path, $file_name, $real_file_name, $path_to_file,$DocumentViewingPreferences ) {
        $complete_file_name = $file_name;
        if (strlen($file_name) > 15) {
            preg_match('/\.[^.]*$/', $complete_file_name, $ext);
            preg_replace('/\.[^.]*$/', '', $file_name);
            $file_name = substr($file_name, 0, 12). '...'.$ext[0];
        }
        $link = array (
            '<img src="img/_doc.png">',
            '<a href="'.$http_file_path.$real_file_name.'" target="_blank" title="'.$complete_file_name.'">'.$file_name.'</a>'                
        );
//        return $link;
        return '<img src="img/_doc.png"><a href="'.$http_file_path.$real_file_name.'" target="_blank" title="'.$complete_file_name.'">'.$file_name.'</a>';
    }
}
