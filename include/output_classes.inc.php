<?php
/**
 * NEW Output classes
 *
 *
 * PHP version >= 5.0
 *
 * @package		ARE
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		output_classes
 * @version		0.1
 */

/**
 * ARE
 *
 */
class ARE
{
  public static function render($layout_dataAr = array(), $content_dataAr = array(), $renderer=NULL, $options=array(), $menuoptions=array()) {

  	/**
  	 * @author giorgio 03/apr/2014
  	 *
  	 * If query string wants a pdf, let's obey by setting the $renderer
  	 */
  	if (isset($_GET['pdfExport']) && intval($_GET['pdfExport'])===1) {
  		$renderer = ARE_PDF_RENDER;
  	}

  	if (!isset($id_profile)) $id_profile = null;

    if (defined('MODULES_EVENTDISPATCHER') && MODULES_EVENTDISPATCHER) {
      $event = \Lynxlab\ADA\Module\EventDispatcher\ADAEventDispatcher::buildEventAndDispatch(
        [
          'eventClass' => 'CoreEvent',
          'eventName' => 'PAGEPRERENDER',
          'eventPrefix' => basename($_SERVER['SCRIPT_FILENAME']),
        ],
        basename($_SERVER['SCRIPT_FILENAME']),
        [
          'layout_dataAr' => $layout_dataAr,
          'content_dataAr' => $content_dataAr,
          'renderer' => $renderer,
          'options' => $options,
          'menu_options' => $menuoptions,
        ]
      );
      foreach($event->getArguments() as $key => $val) {
        $$key = $val;
      }
    }

    switch($renderer) {
        case ARE_PRINT_RENDER:


          $layoutObj = read_layout_from_DB($id_profile,
          isset($layout_dataAr['family']) ? $layout_dataAr['family'] : '',
          isset($layout_dataAr['node_type']) ? $layout_dataAr['node_type'] : '',
          isset($layout_dataAr['node_author_id']) ? $layout_dataAr['node_author_id'] : '',
          isset($layout_dataAr['node_course_id']) ? $layout_dataAr['node_course_id'] : '',
          isset($layout_dataAr['module_dir']) ? $layout_dataAr['module_dir'] : ''
        );

        // TODO: controlli su layoutObj
        $layout_template = $layoutObj->template;
        $layout_CSS      = $layoutObj->CSS_filename;
        if (!empty($layout_dataAr['CSS_filename']) && is_array($layout_dataAr['CSS_filename'])) {
        	$tmp = explode(';',$layoutObj->CSS_filename);
        	$tmp = array_merge($tmp,$layout_dataAr['CSS_filename']);
        	//$tmp = array_merge($layout_dataAr['JS_filename'],$tmp);
        	$layoutObj->CSS_filename = implode(';',$tmp);
        	$layout_CSS = implode(';',$tmp);
        }
        /*
         * optional arguments for HTML constructor
        */
        $user_name         = isset($options['user_name'])         ? $options['user_name'] : '';
        $course_title      = isset($options['course_title'])      ? $options['course_title'] : '';
        $node_title        = isset($options['node_title'])        ? $options['user_name'] : '';
        $meta_keywords     = isset($options['meta_keywords'])     ? $options['meta_keywords'] : '';
        $author            = isset($options['author'])            ? $options['author'] : '';
        $meta_refresh_time = isset($options['meta_refresh_time']) ? $options['meta_refresh_time'] : '';
        $meta_refresh_url  = isset($options['meta_refresh_url'])  ? $options['meta_refresh_url'] : '';
        $onload_func       = isset($options['onload_func'])       ? $options['onload_func'] : '';
        $static_dir        = isset($options['static_dir'])        ? $options['static_dir'] : ROOT_DIR.'services/media/cache/';

        $html_renderer = new HTML($layout_template, $layout_CSS, $user_name, $course_title,
                                  $node_title, $meta_keywords, $author, null,
                                  null,$onload_func, $layoutObj);


        $html_renderer->fillin_templateFN($content_dataAr);

        $imgpath = (dirname($layout_template));
        $html_renderer->resetImgSrcFN($imgpath,isset($layoutObj->family) ? $layoutObj->family : ADA_TEMPLATE_FAMILY);
        $html_renderer->apply_styleFN();

        $html_renderer->outputFN('page');

        break;

      case ARE_XML_RENDER:
        $today = today_dateFN();
        $title = $options['course_title'];
        $portal =  $options['portal'];
        $xml_renderer = new Generic_XML($portal,$today,$title);
        $xml_renderer->idNode = $options['id'];
        $xml_renderer->URL = $options['URL'];
        $xml_renderer->fillinFN($content_dataAr);
        $xml_renderer->outputFN('page');
        break;

        case ARE_FILE_RENDER:

         $layoutObj = read_layout_from_DB($id_profile,
          isset($layout_dataAr['family']) ? $layout_dataAr['family'] : null,
          isset($layout_dataAr['node_type']) ? $layout_dataAr['node_type'] : null,
          isset($layout_dataAr['node_author_id']) ? $layout_dataAr['node_author_id'] : null,
          isset($layout_dataAr['node_course_id']) ? $layout_dataAr['node_course_id'] : null,
          isset($layout_dataAr['module_dir']) ? $layout_dataAr['module_dir'] : null
        );
        // TODO: controlli su layoutObj

        $layout_template = $layoutObj->template;
        $layout_CSS      = $layoutObj->CSS_filename;

        /*
         * optional arguments for HTML constructor
         */
        $user_name         = isset($options['user_name'])         ? $options['user_name'] : '';
        $course_title      = isset($options['course_title'])      ? $options['course_title'] : '';
        $node_title        = isset($options['node_title'])        ? $options['user_name'] : '';
        $meta_keywords     = isset($options['meta_keywords'])     ? $options['meta_keywords'] : '';
        $author            = isset($options['author'])            ? $options['author'] : '';
        $meta_refresh_time = isset($options['meta_refresh_time']) ? $options['meta_refresh_time'] : '';
        $meta_refresh_url  = isset($options['meta_refresh_url'])  ? $options['meta_refresh_url'] : '';
        $onload_func       = isset($options['onload_func'])       ? $options['onload_func'] : '';
        $static_dir        = isset($options['static_dir'])         ? $options['static_dir'] : ROOT_DIR.'services/media/cache/';

        if (!file_exists($static_dir)){
                mkdir($static_dir);
        }
        $static_filename = md5($_SERVER['REQUEST_URI'].$_SERVER['QUERY_STRING']);
        $cached_file = $static_dir.$static_filename;

        $html_renderer = new HTML($layout_template, $layout_CSS, $user_name, $course_title,
                                  $node_title, $meta_keywords, $author, $meta_refresh_time,
                                  $meta_refresh_url,$onload_func, $layoutObj);

		$html_renderer->full_static_filename = $cached_file;
        $html_renderer->static_filename = $static_filename;
        $html_renderer->fillin_templateFN($content_dataAr);

        $imgpath = (dirname($layout_template));
        $html_renderer->resetImgSrcFN($imgpath,isset($layoutObj->family) ? $layoutObj->family : ADA_TEMPLATE_FAMILY);
        $html_renderer->apply_styleFN();

        $html_renderer->outputFN('file');

        break;


      case ARE_HTML_RENDER:
      case ARE_PDF_RENDER:
      default:
        $layoutObj = read_layout_from_DB($id_profile,
          isset($layout_dataAr['family']) ? $layout_dataAr['family'] : null,
          isset($layout_dataAr['node_type']) ? $layout_dataAr['node_type'] : null,
          isset($layout_dataAr['node_author_id']) ? $layout_dataAr['node_author_id'] : null,
          isset($layout_dataAr['node_course_id']) ? $layout_dataAr['node_course_id'] : null,
          isset($layout_dataAr['module_dir']) ? $layout_dataAr['module_dir'] : null
        );
        // TODO: controlli su layoutObj

        $layout_template = $layoutObj->template;
        $layout_CSS      = $layoutObj->CSS_filename;

        /**
         * @author giorgio 19/ago/2014
         *
         * fix javascript inclusion as follows:
         * - if the PhP has not included JQUERY, include it as first element
         * - if the PhP has not included SEMANTICUI_JS, include it just after JQUERY
         * - if the PhP has not included JQUERY_NO_CONFLICT include it as last element
         *
         * This way, any PhP can include what it needs and in the right order of inclusion
         */

        /**
         * @author giorgio 10/nov/2014
         *
         * If the browser is InternetExplorer 8 or less, use smartmenus instead of semantic-ui
         *
         * NOTE: $_SESSION['IE-version'] is set by module_init_functions.inc.php
         */
        $JSToUse = (isset($_SESSION['IE-version']) &&
        		    $_SESSION['IE-version']!==false && $_SESSION['IE-version']<=8) ? SMARTMENUS_JS : SEMANTICUI_JS;
        $CSSToUse = (isset($_SESSION['IE-version']) &&
        		    $_SESSION['IE-version']!==false && $_SESSION['IE-version']<=8) ? SMARTMENUS_CSS : SEMANTICUI_CSS;

		if (!empty($layout_dataAr['JS_filename']) && is_array($layout_dataAr['JS_filename'])) {

			// if jquery is not included in the script itself, add it at first position
			if (!in_array(JQUERY, $layout_dataAr['JS_filename'])) $layout_dataAr['JS_filename'] = array_merge(array(JQUERY),$layout_dataAr['JS_filename']);

			// if $JSToUse is not included in the script itself, add it just after JQUERY
			if (!in_array($JSToUse, $layout_dataAr['JS_filename'])) {
				// find the key for JQUERY
				$key = array_search(JQUERY, $layout_dataAr['JS_filename']);
				// add $JSToUse after JQUERY slicing the original array
				$layout_dataAr['JS_filename'] = array_merge(
						array_slice($layout_dataAr['JS_filename'], 0, $key+1),
						array($JSToUse),
						array_slice($layout_dataAr['JS_filename'], $key+1)
				);
			}

			// if jquery noconflict is not included in the script itself, add it at last position
			if (!in_array(JQUERY_NO_CONFLICT, $layout_dataAr['JS_filename'])) array_push($layout_dataAr['JS_filename'], JQUERY_NO_CONFLICT);

			$tmp = explode(';',$layoutObj->JS_filename);
			$tmp = array_merge($tmp,$layout_dataAr['JS_filename']);
			//$tmp = array_merge($layout_dataAr['JS_filename'],$tmp);
			$layoutObj->JS_filename = implode(';',$tmp);
		} else {
			// add jquery, semantic and jquery noconflict
			$layoutObj->JS_filename .= ';'.JQUERY.';'.$JSToUse.';'.JQUERY_NO_CONFLICT;
		}

		$tmp = explode(';',$layoutObj->CSS_filename);

		if (!empty($layout_dataAr['CSS_filename']) && is_array($layout_dataAr['CSS_filename'])) {
			$tmp = array_merge($tmp,$layout_dataAr['CSS_filename']);
		}
		/**
		 * @author giorgio 06/ago/2014
		 * add $CSSToUse last
		 */
		$tmp[] = $CSSToUse;

		/**
		 * @author giorgio 27/jul/2022
		 * add provider custom JQUERY_UI_CSS
		 */
		if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && in_array(JQUERY_UI_CSS, $tmp)) {
			$clientJQCSS = ROOT_DIR . '/clients/' . $GLOBALS['user_provider'] . '/layout/' .
			$layoutObj->family . '/css/' . basename(JQUERY_UI_CSS);
			if (is_readable($clientJQCSS)) {
				$tmp[] = $clientJQCSS;
			}
		}

		//$tmp = array_merge($layout_dataAr['JS_filename'],$tmp);
		$layoutObj->CSS_filename = implode(';',$tmp);
		$layout_CSS = implode(';',$tmp);

        /*
         * optional arguments for HTML constructor
         */
        $user_name         = isset($options['user_name'])         ? $options['user_name'] : '';
        $course_title      = isset($options['course_title'])      ? $options['course_title'] : '';
        $node_title        = isset($options['node_title'])        ? $options['user_name'] : '';
        $meta_keywords     = isset($options['meta_keywords'])     ? $options['meta_keywords'] : '';
        $author            = isset($options['author'])            ? $options['author'] : '';
        $meta_refresh_time = isset($options['meta_refresh_time']) ? $options['meta_refresh_time'] : '';
        $meta_refresh_url  = isset($options['meta_refresh_url'])  ? $options['meta_refresh_url'] : '';
        $onload_func       = isset($options['onload_func'])       ? $options['onload_func'] : '';

        /**
         * @author giorgio 19/ago/2014
         *
         * make menu here
         */
        if (0 !== strcasecmp('install.php', basename($_SERVER['SCRIPT_FILENAME']))) {
          require_once ROOT_DIR.'/include/menu_class.inc.php';
          // menu property created 'on-the-fly'
          $layoutObj->menu = new Menu($layoutObj->module_dir,
              basename(($_SERVER['SCRIPT_FILENAME'])),
              $_SESSION['sess_userObj']->getType(),
              $menuoptions);
        } else $layoutObj->menu = null;

        if ($renderer == ARE_PDF_RENDER) {

        	$orientation   = isset($options['orientation'])       ? $options['orientation'] : '';
        	$outputfile    = isset($options['outputfile'])        ? $options['outputfile'] : '';
        	$forcedownload = isset($options['forcedownload'])     ? $options['forcedownload'] : false;
        	$returnasstring= isset($options['returnasstring'])    ? $options['returnasstring'] : false;

        	// must be called $html_renderer for below code, but it's not :)
        	$html_renderer = new PDF($layout_template, $layout_CSS, $user_name, $course_title,
        			$node_title, $meta_keywords, $author, $meta_refresh_time,
        			$meta_refresh_url,$onload_func, $layoutObj, $outputfile, $orientation, $forcedownload, $returnasstring);
        } else {
        	$html_renderer = new HTML($layout_template, $layout_CSS, $user_name, $course_title,
        			$node_title, $meta_keywords, $author, $meta_refresh_time,
        			$meta_refresh_url,$onload_func, $layoutObj);
        }

        /**
         * @author giorgio 25/set/2013
         * merge the content_dataAr with the one generated by the widgets if it's needed
         */
        if (!is_null($layoutObj->WIDGET_filename))
        {
        	if (!isset($layout_dataAr['widgets'])) $layout_dataAr['widgets'] = '';
        	$widgets_dataAr = $html_renderer->fillin_widgetsFN($layoutObj->WIDGET_filename,$layout_dataAr['widgets']);
        	if (!ADA_Error::isError($widgets_dataAr))
        		$content_dataAr = array_merge ($content_dataAr, $widgets_dataAr);
        }

        /**
         * adamenu must be the first key of $content_dataAr
         * for the template_field substitution to work inside the menu
         */
        if (!is_null($layoutObj->menu)) {
          if (defined('MODULES_EVENTDISPATCHER') && MODULES_EVENTDISPATCHER) {
            \Lynxlab\ADA\Module\EventDispatcher\ADAEventDispatcher::buildEventAndDispatch(
              [
                'eventClass' => 'MenuEvent',
                'eventName' => 'PRERENDER',
              ],
              $layoutObj->menu,
              ['userType' => $_SESSION['sess_userObj']->getType() ]
            );
          }

          $content_dataAr = array ('adamenu'=>$layoutObj->menu->getHtml()) + $content_dataAr;

          if (defined('MODULES_EVENTDISPATCHER') && MODULES_EVENTDISPATCHER) {
            \Lynxlab\ADA\Module\EventDispatcher\ADAEventDispatcher::buildEventAndDispatch(
              [
                'eventClass' => 'MenuEvent',
                'eventName' => 'POSTRENDER',
              ],
              $layoutObj->menu,
              ['userType' => $_SESSION['sess_userObj']->getType() ]
            );
          }
          $content_dataAr['isVertical'] = ($layoutObj->menu->isVertical()) ? ' vertical' : '';
        }

        if (isset($_SESSION['sess_userObj'])) {
          if (!array_key_exists('user_avatar', $content_dataAr)) {
            require_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';
            $content_dataAr['user_avatar'] = CDOMElement::create('img','class,img_user_avatr,src:'.$_SESSION['sess_userObj']->getAvatar())->getHtml();
          }

          if (!array_key_exists('user_uname', $content_dataAr)) {
            $content_dataAr['user_uname'] = $_SESSION['sess_userObj']->getUserName();
          }

          if (!array_key_exists('last_visit', $content_dataAr)) {
            $tmpla = trim(AMA_DataHandler::ts_to_date($_SESSION['sess_userObj']->get_last_accessFN(null,"UT",null)));
            if (strlen($tmpla)>0) {
              $content_dataAr['last_visit'] = translateFN('ultimo accesso').': '. $tmpla;
            }
          } else {
            $content_dataAr['last_visit'] = translateFN('ultimo accesso').': '. $content_dataAr['last_visit'];
          }

          if (!array_key_exists('user_level', $content_dataAr)) {
            if (isset($_GLOBALS['user_lever']) && strlen($_GLOBALS['user_level'])>0) {
              $content_dataAr['user_level'] = translateFN('livello').':'. $_GLOBALS['user_level'];
            }
          } else {
            $content_dataAr['user_level'] = translateFN('livello').':'. $content_dataAr['user_level'];
          }

          if (defined('MODULES_IMPERSONATE') && MODULES_IMPERSONATE &&
            \Lynxlab\ADA\Module\Impersonate\ImpersonateActions::canDo(\Lynxlab\ADA\Module\Impersonate\ImpersonateActions::IMPERSONATE) &&
            !array_key_exists('impersonatelink', $content_dataAr)) {
            $content_dataAr['impersonatelink'] = \Lynxlab\ADA\Module\Impersonate\Utils::generateMenu()->getHtml();
          }
        }

        $html_renderer->fillin_templateFN($content_dataAr);

        $imgpath = (dirname($layout_template));
        // $html_renderer->resetImgSrcFN($imgpath,$template_family);
        $html_renderer->resetImgSrcFN($imgpath,$layoutObj->family);
        $html_renderer->apply_styleFN();

        if (property_exists($html_renderer, 'returnasstring') && $html_renderer->returnasstring===true) {
          return $html_renderer->outputFN(($renderer == ARE_PDF_RENDER) ? 'pdf' : 'page');
        } else {
          $html_renderer->outputFN(($renderer == ARE_PDF_RENDER) ? 'pdf' : 'page');
        }
        break;
    }
  }
}

/**
 *
 * Generic output class
 */
class  Output
{
  //vars:
  var $interface;
  var $content;
  var $static_name;
  var $error;
  var $errorCode;

  public function __construct() {
  }

  /**
   * manda effettivamente al browser la pagina, oppure solo i dati (dimensioni, testo, ...)
   *
   * @param string $type
   * @return void
   */
  public function outputFN ($type){

    switch ($type){

      case 'dimension':
        $data = $this->content;
        $dim_data = strlen($data);
        print $dim_data;
        break;

      case 'text':
        $data = $this->content;
        $text_data = strip_tags($data);
        print $text_data;
        break;

      case 'source': // debugging purpose only
        $data = $this->content;
        $source_data = htmlentities($data, ENT_COMPAT | ENT_HTML401, ADA_CHARSET);
        print $source_data;
        break;

      case 'error': // debugging purpose only
        $data = $this->error.$this->errorCode;
        print $data;
        break;

      case 'file': // useful for caching pages
        $data = $this->content;
        $fp = fopen ($this->static_name, "w");
        $result = fwrite($fp,$data);
        fclose($fp);
        break;

      case 'page':  // standard
      default:
        $data = $this->content;
        print $data;
        break;
    }
  }
}

/**
 * Classe generica di stampa su browser
 */
class  Generic_Html extends Output
{
  //vars:

  var $template;
  var $CSS_filename;
  var $family;
  var $htmlheader;
  var $htmlbody;
  var $htmlfooter;
  var $error;
  var $errorCode;
  var $replace_field_code;
  var $full_static_filename;
  var $static_filename;
  var $external_module = false;

  public function  __construct($template,$title,$meta_keywords=""){
    $keywords  = "ADA, Lynx, e-learning, Elearning, ";
    $keywords .= ADA_METAKEYWORDS;
    $description = ADA_METADESCRIPTION;
    $this->template = $template;
    $template_name = basename($template);
    $this->htmlheader = "
                 <!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
                 <html>
                 <head>
                 <meta http-equiv=\"Content-Type\" content=\"text/html; charset=".ADA_CHARSET."\">
                 <meta name=\"powered_by\" content=\"ADA v.".ADA_VERSION."\">
        <meta name=\"templates\" content=\"$template_name\">
        <meta name=\"class\" content=\"generic HTML\">
        <meta name=\"description\" content=\"$description\">
        <meta name=\"keywords\" content=\"$keywords, $meta_keywords\">
        <meta name=\"cachefile\" content=\"$this->static_filename\">
        <!-- Stile -->\n";
    $this->htmlheader.= "<title>\n$title\n</title>\n";
    $this->htmlheader.= "</head>\n";
    $this->htmlbody= "<body>\n";
    $this->htmlfooter= "\n</body>\n</html>";
    $this->replace_field_code = $GLOBALS['replace_field_code'];
    $this->replace_microtemplate_field_code = $GLOBALS['replace_microtemplate_field_code'];
  }

  public function fillin_templateFN($dataHa){
    /* Riempie i campi del template

    Il template e' HTML standard con campi,
    Per default i campi sono commenti in stile dreamWeaver 4)
    <!-- #BeginEditable "doctitle" -->
    <!-- #EndEditable -->
    ma il formato puo' essere cambiato dal file di configurazione
    ed e' contenuto nella variabile globale $replace_field_code

    I dati passati sono in forma di array associativo field=>data
    */

    $root_dir = $GLOBALS['root_dir'];
    $tpl_fileextension =  $GLOBALS['tpl_fileextension'];
    if (!isset($this->replace_field_code) OR  empty($this->replace_field_code)) {
      $this->replace_field_code = "<!-- #BeginEditable \"%field_name%\" -->([a-zA-Z0-9_\t;&\n ])*<!-- #EndEditable -->"; // default value
    }
    $template = $this->template;

    if (!strstr($template,$tpl_fileextension)) {
      $template.=$tpl_fileextension;
    }
    if(!file_exists($template)) {
        $template = $root_dir."/layout/".ADA_TEMPLATE_FAMILY."/templates/default".$tpl_fileextension;
    }
    $tpl = '';
    $fid = fopen($template,'r');

    while ($row = fread($fid,4096)){
      $tpl.=$row;
    }



    $bodytpl = strstr ($tpl,'<body');
    $n = strpos($bodytpl, "</body>");
    $tpl = substr ($bodytpl,strpos($bodytpl, ">",5)+1,$n-strpos($bodytpl, ">",5)-1);
    $this->htmlbody .= $tpl;
    if (USE_MICROTEMPLATES) {
      $tpl = $this->include_microtemplates();
    }
    // $tpl = $this->include_microtemplates_tree();
    /**
     * @author giorgio 08/mag/2015
     * added HTTP_ROOT_DIR as template_field 'constant'
     */
	$dataHa['HTTP_ROOT_DIR'] = HTTP_ROOT_DIR;

    foreach ($dataHa as $field=>$data){

      $ereg = str_replace('%field_name%',$field,$this->replace_field_code);
      $preg = str_replace('%field_name%',$field,preg_quote($this->replace_field_code,'/'));
      //$replace_string = "<!-- #BeginEditable \"$field\" -->([a-zA-Z0-9_\t;&\n ])*<!-- #EndEditable -->";

      if (gettype($data)=='array'){
        $tObj = new Table();
        $tObj->setTable($data);
        $tabled_data = $tObj->getTable();
        if (ADA_STATIC_TEMPLATE_FIELD) {
          $tpl = str_replace($ereg,$tabled_data,$tpl); //faster !!!
        } else {
          $tpl = preg_replace('/'.$preg.'/i',$tabled_data,$tpl);
//        $tpl = eregi_replace($ereg,$tabled_data,$tpl);
        }
      } else {
        // simple data type
        if (ADA_STATIC_TEMPLATE_FIELD) {
          $tpl = str_replace($ereg,$data,$tpl); //faster !!!
        } else {
          $tpl = preg_replace('/'.$preg.'/i',$tabled_data,$tpl);
//        $tpl = eregi_replace($ereg,$data,$tpl);
        }
      }
    }

    // removing extra template fields that don't match
    $ereg = str_replace('%field_name%',"([a-zA-Z0-9_]+)",$this->replace_field_code);
    $preg = str_replace('%field_name%',"([a-zA-Z0-9_]+)",preg_quote($this->replace_field_code,'/'));
    $tpl = preg_replace('/'.$preg.'/i',"<!-- template_field_removed -->",$tpl);
//  $tpl = eregi_replace($ereg,"<!-- template_field_removed -->",$tpl);

    /*
     * traduzione dei template
     * vito, 15 ottobre 2008: parse del template per tradurre il testo contenuto nella lingua dell'utente
     */
    // ottiene tutto il testo marcato per la traduzione
    $matches=array();
    preg_match_all('/<i18n>(.*)<\/i18n>/', $tpl, $matches);
    // costruisce l'array contenente il testo tradotto
    $pattern = array();
    $translated_text = array();
    foreach( $matches[1] as $match => $text )
    {
    	$quoted_text = preg_quote($text,'/');
    	$pattern[$match] = "/<i18n>$quoted_text<\/i18n>/";
    	$translated_text[$match] = translateFN($text);
    }
    // sostituisce nel template il testo tradotto al testo originale
    $tpl = preg_replace( $pattern, $translated_text, $tpl);
    /*
     * fine della traduzione
     */

    $this->htmlbody = $tpl;

  }

  public function include_microtemplates(){
    // trying to include microtemplates (from files)
    // parses template row by row
    $root_dir = $GLOBALS['root_dir'];
    $tpl_fileextension =  $GLOBALS['tpl_fileextension'];
    $tpl = $this->htmlbody;
    $module_dir = $this->module_dir;
    $preg = str_replace('%field_name%',"([a-zA-Z0-9_]+)",preg_quote($this->replace_microtemplate_field_code,'/'));
    $tpl_ar = explode("\n",$tpl);
    $k=0;
    foreach($tpl_ar as $tpl_row){
      //echo $k.$tpl_row;
      $k++;
      if (preg_match("/$preg/",$tpl_row,$regs)){
        $microtpl_name = $regs[1];

		// valerio: 26/11/2012 inizio modifica microtemplate per moduli esterni
		$external_microtpl_filename = $root_dir."/$module_dir/layout/".$this->family."/templates/".$microtpl_name.$tpl_fileextension;
		if ($this->external_module && file_exists($external_microtpl_filename)) {
			$microtpl_filename = $external_microtpl_filename;
		}
		else {
			// steve 26/03/09: try to find microtemplates navigating the folders tree
			// layout/claire/templates/browsing/header.tpl ?
			$microtpl_filename = $root_dir."/layout/".$this->family."/templates/$module_dir/".$microtpl_name.$tpl_fileextension;
		}

		// giorgio: 12/ago/2013 try to load provider microtemplate if it's singleprovider environment
		if (!MULTIPROVIDER && isset($GLOBALS['user_provider']))
		{
			$provider_microtpl_filename = $root_dir."/clients/".$GLOBALS['user_provider']."/layout/".$this->family."/templates/$module_dir/".$microtpl_name.$tpl_fileextension;

			if (file_exists($provider_microtpl_filename)) {
				$microtpl_filename = $provider_microtpl_filename;
			} else {
				$clientmicrotpl_filename = $root_dir."/clients/".$GLOBALS['user_provider']."/layout/".$this->family."/templates/".$microtpl_name.$tpl_fileextension;
				if (file_exists($clientmicrotpl_filename)) $microtpl_filename = $clientmicrotpl_filename;
			}
		}
		// giorgio: 12/ago/2013 end

		// fine modifica moduli esterni
        if (file_exists($microtpl_filename)) {
            $microtpl_code = file_get_contents($microtpl_filename);
        }
        else {  // layout/claire/templates/header.tpl ?
           // $microtpl_filename = $root_dir."/templates/".$this->family."/".$microtpl_name.$tpl_fileextension;
            $microtpl_filename = $root_dir."/layout/".$this->family."/templates/".$microtpl_name.$tpl_fileextension;
            if (file_exists($microtpl_filename))
				$microtpl_code = file_get_contents($microtpl_filename);
            else {
              $microtpl_code = "<!-- not found at address: $microtpl_filename -->"; // raises an error?
            }
        }
        $preg = str_replace('%field_name%',"([a-zA-Z0-9_]+)",preg_quote($this->replace_microtemplate_field_code,'/'));
        $tpl_row = preg_replace('/'.$preg.'/',$microtpl_code,$tpl_row);
//         $tpl_row = ereg_replace($ereg,$microtpl_code,$tpl_row);

      }
      $tpl_new_ar[]=$tpl_row;
    }
    $tpl = implode("\n", $tpl_new_ar);
    return $tpl;
  }
  public function include_microtemplates_tree(){
    // mod  steve 26/03/09:
    // this version tries to find microtemplates navigating the folders tree
    //  allowing to have a single header, footer etc placed in /main/$family or main/default
    // trying to include microtemplates (from files)
    // parses template row by row
    $root_dir = $GLOBALS['root_dir'];
    $tpl_fileextension =  $GLOBALS['tpl_fileextension'];
    $tpl = $this->htmlbody;
    $module_dir = $this->module_dir;
    $preg = str_replace('%field_name%',"([a-zA-Z0-9_]+)",preg_quote($this->replace_microtemplate_field_code,'/'));
    $tpl_ar = explode("\n",$tpl);
    $k=0;
    foreach($tpl_ar as $tpl_row){
      //echo $k.$tpl_row;
      $k++;
      if (preg_match("/$preg/",$tpl_row,$regs)){
        $microtpl_name = $regs[1];

		// valerio: 26/11/2012 inizio modifica microtemplate per moduli esterni
		$external_microtpl_filename = $root_dir."/$module_dir/layout/".$this->family."/templates/".$microtpl_name.$tpl_fileextension;
		if ($this->external_module && file_exists($external_microtpl_filename)) {
			$microtpl_filename = $external_microtpl_filename;
		}
		else {
			$microtpl_filename = $root_dir."/templates/$module_dir/".$this->family."/".$microtpl_name.$tpl_fileextension;
		}
		// fine modifica moduli esterni
        // layout/claire/browsing/header.tpl ?
        $microtpl_filename = $root_dir."/templates/$module_dir/".$this->family."/".$microtpl_name.$tpl_fileextension;
        if (file_exists($microtpl_filename))
        $microtpl_code = file_get_contents($microtpl_filename);
        else {
          // main/claire/header.tpl ?
          $microtpl_filename = $root_dir."/templates/main/".$this->family."/".$microtpl_name.$tpl_fileextension;
          if (file_exists($microtpl_filename))
          $microtpl_code = file_get_contents($microtpl_filename);
          else {
            // main/default/header.tpl ?
            $microtpl_filename = $root_dir."/templates/main/default/".$microtpl_name.$tpl_fileextension;
            if (file_exists($microtpl_filename))
            $microtpl_code = file_get_contents($microtpl_filename);
            else {
              $microtpl_code = ""; // raises an error?
            }
          }
        }
        $preg = str_replace('%field_name%',"([a-zA-Z0-9_]+)",preg_quote($this->replace_microtemplate_field_code,'/'));
        $tpl_row = preg_replace('/'.$preg.'/',$microtpl_code,$tpl_row);
//         $tpl_row = ereg_replace($ereg,$microtpl_code,$tpl_row);
      }
      $tpl_new_ar[]=$tpl_row;
    }
    $tpl = implode("\n", $tpl_new_ar);
    return $tpl;
  }

  public function verify_templateFN($dataHa){
    /* verify if template exists and if there is a  match among number and names of fields
     case 0: ok
     case 1: file doesn't exist   (very bad!)
     *case 2: more field in template than in data array
     (some field are left empty, we want to filter data from code side)
     case 3: more field in data array than in template
     (some data get lost, we want to filter data  from interface side)


     */

    //$replace_field_code = $GLOBALS['replace_field_code'];
    $replace_field_code= $this->replace_field_code;
    //$template_family = $GLOBALS['template_family'];
    $tpl_fileextension = $GLOBALS['tpl_fileextension'];

    $template_family = $this->family;
    if (!$template_family){
      if (defined('ADA_TEMPLATE_FAMILY')){
        $template_family = ADA_TEMPLATE_FAMILY;
      } else {
        $template_family = "default";
      }
    } else {
      $template_family = "default";
    }

    if (!isset($replace_field_code) OR  empty($replace_field_code)) {
      $replace_field_code = "<!-- #BeginEditable \"%field_name%\" -->([a-zA-Z0-9_\t;&\n ])*<!-- #EndEditable -->";
    }


    $template = $this->template;
    if (!strstr($template,$tpl_fileextension))
    $template.=$tpl_fileextension;   // add extension
    if (!file_exists($template))
    $template = $root_dir."/templates/main/".$template_family."/default".$tpl_fileextension;
    if (file_exists($template)){
      $tpl = '';
      $fid = fopen($template,'r');
      while ($row = fread($fid,4096)){
        $tpl.=$row;
      }

      $tplOk = array();
      foreach ($dataHa as $field=>$data){
        $ereg = str_replace('%field_name%',$field,$replace_field_code);
        $preg = str_replace('%field_name%',$field,preg_quote($replace_field_code,'/'));
        //$ereg = "<!-- #BeginEditable \"$field\" -->([a-zA-Z0-9_\t;&\n ])*<!-- #EndEditable -->";
        if (ADA_STATIC_TEMPLATE_FIELD) {
          $tplOk[$field] = strpos($ereg,$tpl); //faster !!!
        } else {
          $tplOk[$field] = preg_match("/$preg/",$tpl);
        }

      }


    } else {
      $this->error = translateFN("Il template non esiste.");
      $this->errorCode = '1';
    }

    $this->tplfield = $tplOk;
    $totalTplFields = count($tplOk);
    $totalDataFields = count($dataHa);
    $matching = ($totalDataFields-$totalTplFields);
    if ($matching>0){
      $this->error = translateFN("I campi del template non sono sufficienti.");
      $this->errorCode = '3';
    } elseif ($matching<0){
      $this->error = translateFN("Non tutti i campi del template sono stati riempiti.");
      $this->errorCode = '2';
    } else {
      $this->error = '';
      $this->errorCode = 0;
    }

  }

  public function ignore_templateFN($dataHa){
    /*
     ignora il template e restituisce solo il contenuto dei campi
     i dati passati sono in forma di array associativo field=>data

     */

    $start_separator = "<br>";  // or else <p>
    $end_separator = "";    // </p>

    foreach ($dataHa as $field=>$data){
      if (gettype($data)=='array'){
        $tObj = new Table();
        $tObj->setTable($data);
        $tabled_data = $tObj->getTable();
        $tpl .= $start_separator.$tabled_data.$end_separator;
      } else {
        $tpl .= $start_separator.$data.$end_separator;
      }

    }

    $this->htmlbody .= $tpl;
  }

  public function apply_styleFN($stylesheetpath='') {
    $this->_apply_CSSFN($stylesheetpath);
    $this->_apply_JSFN($stylesheetpath);
  }

  /**
   * @deprecated use apply_styleFN instead
   * @param $stylesheetpath
   * @return unknown_type
   */
  public function apply_CSSFN($stylesheetpath=""){
    // wrapper for applyCSS and apply_JS
    $this->_apply_CSSFN($stylesheetpath);
    $this->_apply_JSFN($stylesheetpath);
  }


  private function _apply_JSFN($jspath=""){
    // inserting js link
    $http_root_dir = $GLOBALS['http_root_dir'];
    $root_dir = $GLOBALS['root_dir'];
/*
    $template_family = $this->family;
    if (!$template_family){
      if (defined('ADA_TEMPLATE_FAMILY')){
        $template_family = ADA_TEMPLATE_FAMILY;
      }
      else {
        $template_family = "default";
      }
    }
    else {
      $template_family = "default";
    }
*/
    $template_family = 'standard';

    if (empty($jspath)){
      if (!isset($this->module_dir)){
        //$jspath = "js/main/$template_family/";
        $jspath = 'js/main/';
      }
      else {
        $module_dir = $this->module_dir;
        //$jspath = "../js/$module_dir/$template_family/";
        $jspath = "../js/$module_dir/";
      }
    }

    $jsAr = array_unique(explode(";",$this->JS_filename));
    $html_js_code = "";
    /*
     * vito, 6 ottobre 2008: import PHP defines from ada_config.php as javascript variables.
     */
    if (false === stristr($this->JS_filename,'install.js')) {
      $html_js_code .= "<script type=\"text/javascript\" src=\"$http_root_dir/include/PHPjavascript.php\"></script>";
    }

    foreach ($jsAr as $javascript){
      if (!empty($javascript)){
		if (!file_exists($javascript)) {
			if (!strstr($javascript,'.js')) {
			  $javascript =  $javascript.'.js'; // if there is no extension, we add it
			}
			if (!stristr($javascript,'js/')) {
			  $javascript =  $jspath.$javascript; // if there is no path, we add it
			}
		}

        if (file_exists($javascript)){
          // giorgio: 28/dic/2020 try to load provider js if it's singleprovider environment
          if (!MULTIPROVIDER && isset($GLOBALS['user_provider'])) {
            $clientJavascript = str_replace($root_dir, $root_dir . DIRECTORY_SEPARATOR . 'clients' . DIRECTORY_SEPARATOR . $GLOBALS['user_provider'], $javascript);
            if (is_readable($clientJavascript)) {
                $javascript = $clientJavascript;
            }
        }
        $jsFileTS = filemtime($javascript);
        $javascript = str_replace($root_dir,$http_root_dir,$javascript);
        $html_js_code .= "<script type=\"text/javascript\" src=\"$javascript?ts=$jsFileTS\"></script>\n<noscript>".translateFN("Questo browser non supporta Javascript")."</noscript>\n";
        }
      }
    }

    $this->htmlheader = str_replace('<!-- Javascript -->',$html_js_code,$this->htmlheader);
  }

  private function _apply_CSSFN($stylesheetpath=""){

    // inserting style sheet link
    $http_root_dir =   $GLOBALS['http_root_dir'];
    $root_dir =   $GLOBALS['root_dir'];

    $template_family = $this->family;
    if (!$template_family){
      if (defined('ADA_TEMPLATE_FAMILY')){
        $template_family = ADA_TEMPLATE_FAMILY;
      } else {
        $template_family = "default";
      }
    }/* else {
    	$template_family = "default";
	}*/

    /**
     * @author giorgio 04/apr/2014
     *
     * removed the above else to have $template_family
     * not pointing to 'default' that does not exists
     * anymore, and it will point to ADA_TEMPLATE_FAMILY
     */

    if (empty($stylesheetpath)){
      if (!isset($this->module_dir)){
        $stylesheetpath = ROOT_DIR. "/layout/$template_family/css/main/";
      } else {
        $module_dir = $this->module_dir;
        if ($module_dir == "main") {
            $stylesheetpath = ROOT_DIR. "/layout/../$template_family/css/main/";
        }
        else {
            $stylesheetpath = ROOT_DIR . "/layout/$template_family/css/$module_dir/";
        }
      }
    }

    $stylesheetAr = explode(";",$this->CSS_filename);
    $html_css_code = "";
    foreach ($stylesheetAr as $stylesheet){
      if (!empty($stylesheet)){

		if (!file_exists($stylesheet)) {
			if (!strstr($stylesheet,'.css'))
			$stylesheet =  $stylesheet.'.css'; // if there is no extension, we add it

			if (!stristr($stylesheet,'css/'))
			$stylesheet =  $stylesheetpath.$stylesheet; // if there is no path, we add it
		}

        if (file_exists($stylesheet)){
          // this is for standard browsers
          $stylesheet = str_replace($root_dir,$http_root_dir,$stylesheet);
          $html_css_code .= "<link rel=\"stylesheet\" href=\"$stylesheet\" type=\"text/css\" media=\"screen,print\">\n";
        }


        /* steve 31/03/09
         *
         * add alternate CSS for non standard browsers, namely IE 6 to 9 and...*/

        for($ie_version=6; $ie_version<=9; $ie_version++) {

	        $cond_com_begin = "\n<!--[if IE ".$ie_version."]>\n";
	        $cond_com_end = "<![endif]-->\n";

	        //  if there is the extension we strip it off
	        if (strstr($stylesheet,'.css')){
	          $stylesheet_name = substr($stylesheet,0,-4);
	          $ie_stylesheet =  $stylesheet_name."_ie".$ie_version;
	        } else {
	          $ie_stylesheet =  $stylesheet."_ie".$ie_version;
	        }
	        $ie_stylesheet =  $ie_stylesheet.".css";
	        // path
	        if (!stristr($ie_stylesheet,'css/'))
	        $ie_stylesheet =  $stylesheetpath.$ie_stylesheet; // if there is no path, we add it

	        if (file_exists($ie_stylesheet)){
	            $ie_stylesheet = str_replace($root_dir,$http_root_dir,$ie_stylesheet);
	          	$html_css_code .= $cond_com_begin."<link rel=\"stylesheet\" href=\"$ie_stylesheet\" type=\"text/css\" media=\"screen,print\">\n".$cond_com_end;
	        }
        }
        /* end mod	*/

      }
    }

    /**
     * @author giorgio 03/apr/2014
     * Look for a print.css that will be used for print media
     * and will be one for each module_dir (i.e. browsing/print.css, switcher/print.css)
     * plus a global print.css that must be put at css root level
     */
    $lookFor = 'print.css';
    /**
     * Look for the print.css file in :
     *
     * 	$stylesheetpath . '../' that is css root
     *  $stylesheetpath . ''  that is module's own dir
     *
     * This way module's own print.css will be the
     * last loaded one and can overwrite properly
     */
    foreach (array ('../','') as $subdir) {
    	$fileName = $stylesheetpath . $subdir . $lookFor;
    	if (file_exists($fileName)) {
        $fileTS = filemtime($fileName);
    		$fileName = str_replace($root_dir,$http_root_dir,$fileName);
    		$html_css_code .= "<link rel=\"stylesheet\" href=\"$fileName?ts=$fileTS\" type=\"text/css\" media=\"print\">\n";
    	}
    }

     /*
     * sara 24/nov/2014
     * Look for the print.css file in external modules (newsletter, test ecc..)
     */
      if($this->external_module){
        $stylesheetpath = ROOT_DIR.'/'.$this->module_dir.'/layout/';
        foreach (array ('',$template_family.'/css/') as $subdir){
            $fileName = $stylesheetpath .$subdir.$lookFor;
            if (file_exists($fileName)) {
                $fileTS = filemtime($fileName);
                $fileName = str_replace($root_dir,$http_root_dir,$fileName);
                $html_css_code .= "<link rel=\"stylesheet\" href=\"$fileName?ts=$fileTS\" type=\"text/css\" media=\"print\">\n";
            }
        }
    }

    /**
     * end @author giorgio 03/apr/2014
     */

    $this->htmlheader = str_replace('<!-- Stile -->',$html_css_code,$this->htmlheader);
  }


  public function resetImgSrcFN($path,$family=""){
    // we have to substitute  src="img/pippo.png" with src="templates/browsing/default/img/pippo.png"
    $http_root_dir =   $GLOBALS['http_root_dir'];
    $root_dir =   $GLOBALS['root_dir'];


    $module_dir = $this->module_dir;

    if (empty($module_dir)) {
        $module_dir = "main";
    }

    if ($module_dir == "main") {

        $rel_path = "";
    } else {

        $rel_path = "../";
    }



    //$rel_path = $root_dir."/";
    if (!isset($family) or $family == ""){
      if (isset($this->family) and ($this->family<>"")) {
      $family = $this->family;
      }
      else {
          if (defined('ADA_TEMPLATE_FAMILY')){
            $family = ADA_TEMPLATE_FAMILY;
          } else {
            $family = "default";
          }
      }
    }

//valerio 17/10/2012 10:00
    $newpath = $http_root_dir.'/layout/'.$family;

    $this->htmlbody = str_replace('src="img/','src="'.$newpath.'/img/', $this->htmlbody);
    $this->htmlbody = str_replace("src='img/","src='".$newpath."/img/", $this->htmlbody);
    $this->htmlbody = str_replace('background="img/','background="'.$newpath.'/img/', $this->htmlbody);
    $this->htmlbody = str_replace("background='img/","background='".$newpath."/img/", $this->htmlbody);
    //$this->htmlbody .= '<!-- PATH TO IMAGES: '.$newpath.'/img/-->';
  }


  public function outputFN ($type){
    // manda effettivamente al browser la pagina   oppure solo i dati (dimensioni, testo, ...))

    switch ($type){
      case 'page':  // standard
      default:
        $data = $this->htmlheader;
        $data.= $this->htmlbody;
        $data.= $this->htmlfooter;
        print $data;
        break;
      case 'dimension':
        $data = $this->htmlheader;
        $data.= $this->htmlbody;
        $data.= $this->htmlfooter;
        $dim_data = strlen($data);
        print $dim_data;
        break;
      case 'text':
        $data = $this->htmlheader;
        $data.= $this->htmlbody;
        $data.= $this->htmlfooter;
        $text_data = strip_tags($data);
        print $text_data;
        break;
      case 'source': // debugging purpose only
        $data = $this->htmlheader;
        $data.= $this->htmlbody;
        $data.= $this->htmlfooter;
        $source_data = htmlentities($data, ENT_COMPAT | ENT_HTML401, ADA_CHARSET);
        print $source_data;
        break;
      case 'error': // debugging purpose only
        $data = $this->error;
        print $data;
        break;
      case 'file': // useful for caching pages
        $data = $this->htmlheader;
        $data.= $this->htmlbody;
        $data.= $this->htmlfooter;
        $fp = fopen ($this->full_static_filename, "w");
        $result = fwrite($fp,$data);
        fclose($fp);
        break;
      case 'pdf':
      	$data = $this->htmlheader;
      	$data.= $this->htmlbody;
      	$data.= $this->htmlfooter;
      	// make dompf tmp font dir if needed
      	if (!is_dir(ADA_UPLOAD_PATH.'tmp-dompdf')) {
				$oldmask = umask(0);
				mkdir (ADA_UPLOAD_PATH.'tmp-dompdf', 0775, true);
				umask($oldmask);
      	}
      	// include dompdf autoloader
		require_once 'dompdf/autoload.inc.php';
      	$dompdf_options = array(
      			// Rendering
      			"default_media_type"       => 'print',
      			"default_paper_size"       => 'A4',
      			"font_dir"				   => ADA_UPLOAD_PATH.'tmp-dompdf',
      			"font_cache"			   => ADA_UPLOAD_PATH.'tmp-dompdf',
      			"temp_dir"				   => ADA_UPLOAD_PATH.'tmp-dompdf',
      			// Features
      			"enable_unicode"           => true,
      			"enable_php"               => true,
      			"enable_remote"            => true,
      			"enable_css_float"         => true,
      			"enable_javascript"        => true,
      			"enable_html5_parser"      => false,
      			"enable_font_subsetting"   => false
      	);
      	$dompdf = new \Dompdf\Dompdf($dompdf_options);
      	$dompdf->setPaper('A4',$this->orientation);
      	$dompdf->loadHtml($data);
      	$dompdf->render();

        if ($this->returnasstring) {
          return $dompdf->output();
        } else {
          $dompdf->stream($this->outputfile.'.pdf', array('Attachment'=>$this->forcedownload));
          die();
        }
        break;
    }
  }

  public function print_pageFN($node_data,$template,$imgpath,$stylesheetpath,$use_template=1){
    if ($use_template){
      $this->template =  $template;
      $this->verify_templateFN($node_data);
      if (!empty($this->error)){
        // echo $this->errorCode;
        switch ($this->errorCode) {
          case 1: //template doesn't exist !
            $this->ignore_templateFN($node_data);
            break;
          case 2: // some template's fields are empty: ok
            $this->fillin_templateFN($node_data);
            break;
          case 3: //template's fields don't suffice: ok
            $this->fillin_templateFN($node_data);
            break;
        }
        $this->apply_CSSFN($stylesheetpath);
      } else {
        $this->fillin_templateFN($node_data);
      }
    } else {
      $this->ignore_templateFN($node_data);
    }
    $this->resetImgSrcFN($imgpath);
    $this->outputFN('page');
  }
} //end class Generic_HTML


/**
 *
 *
 */
class Html extends Generic_HTML
{
  //vars:
  var $template;
  var $CSS_filename;
  var $JS_filename;
  var $htmlheader;
  var $htmlbody;
  var $htmlfooter;
  var $replace_field_code;
  var $replace_microtemplate_field_code;
  var $module_dir;
  var $family;
  var $static_filename;
  var $full_static_filename;
  //functions:

  public function new__construct($layoutObj, $optionsAr = array()) {
      $this->template = $layoutObj->template;
      $this->CSS_filename = $layoutObj->CSS_filename;
      $this->family = $layoutObj->family;
      $this->JS_filename = $layoutObj->JS_filename;
      $this->module_dir = $layoutObj->module_dir;
	  $this->external_module = $layoutObj->external_module;


      $charset = ADA_CHARSET;
      $http_root_dir = HTTP_ROOT_DIR;
      $author;
      $description = ADA_METADESCRIPTION;
      $keywords;
      $meta_keywords;

      $this->htmlheader = <<< EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=$charset">
    <meta name="powered_by" content="ADA v.".ADA_VERSION."">
    <meta name="address" content="$http_root_dir">
    <meta name="author" content="$author">
    <meta name="template" content="$this->template">
    <meta name="family" content="$this->family">
    <meta name="ADA-module" content="$this->module_dir">
    <meta name="class" content="HTML">
    <meta name="outputClasses" content="NEW">
    <meta name="description" content="$description">
    <meta name="keywords" content="$keywords,$meta_keywords">
    <meta name=\"cachefile\" content=\"$static_filename\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <!-- Stile -->
    <!-- Javascript -->
    <title>$title</title>
</head>
EOT;

      $this->htmlbody = <<< EOT
<body>
EOT;
      $this->htmlfooter = <<< EOT
</body>
</html>
EOT;
  }


  public function __construct($template,$CSS_filename,$user_name,$course_title,$node_title="",$meta_keywords="",$author="",$meta_refresh_time="",$meta_refresh_url="",$onload_func="",$layoutObj=NULL){

    $HTTP_USER_AGENT =   $_SERVER['HTTP_USER_AGENT'];
    $root_dir =   $GLOBALS['root_dir'];
    $http_root_dir =   $GLOBALS['http_root_dir'];
    $keywords = "ADA, Lynx, ";
    $keywords.= ADA_METAKEYWORDS; // from config file
    $description = ADA_METADESCRIPTION; // from config file
    //$layoutObj = $GLOBALS['layoutObj'];
    if (!is_Object($layoutObj)){ // we use function parameters
      $this->template = $template;
      $this->CSS_filename = $CSS_filename;
      $this->JS_filename = $JS_filename;
      $this->family = "";
      $this->module_dir = "";

    } else {// we use data from LayOut object
      $this->template = $layoutObj->template;
      $this->CSS_filename = $layoutObj->CSS_filename;
      $this->family = $layoutObj->family;
      $this->JS_filename = $layoutObj->JS_filename;
      $this->module_dir = $layoutObj->module_dir;
	  $this->external_module = $layoutObj->external_module;
    }
    $template_name = basename($template);
    $widget_filename = (!is_null($layoutObj)) ? basename ($layoutObj->WIDGET_filename) : '';
    $family_name = $this->family;
    $module_dir =  $this->module_dir;
    $static_filename = $this->static_filename;
    $this->htmlheader ="
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".ADA_CHARSET."\">";

    //this is useful for all those html pages that need a refresh time
    // if the refresh time & url are set, this tag is added into the header part of the html page
    if (!empty($meta_refresh_time)) {
      $this->htmlheader .="
            <meta http-equiv=\"refresh\" content=\"$meta_refresh_time; url=$meta_refresh_url\">";
    }

    $this->htmlheader .="
<meta name=\"powered_by\" content=\"ADA v.".ADA_VERSION."\">
<meta name=\"powered_by\" content=\"PHP v.".phpversion()."\">
        <meta name=\"address\" content=\"$http_root_dir\">
        <meta name=\"author\" content=\"$author\">
        <meta name=\"template\" content=\"$template_name\">
        <meta name=\"family\" content=\"$family_name\">
        <meta name=\"ADA-module\" content=\"$module_dir\">
        <meta name=\"widgets\" content=\"$widget_filename\">";
        if (isset($layoutObj->menu)) {
        	$this->htmlheader .= "
        <meta name=\"menu\" content=\"".$layoutObj->menu->getId()."\"";
        	if (!is_null($layoutObj->menu->getLinkedFromId())) {
        		$this->htmlheader .= " linked-from=\"".$layoutObj->menu->getLinkedFromId()."\"";
        	}
        	$this->htmlheader .= ">";
        }

        $this->htmlheader .= "
        <meta name=\"class\" content=\"HTML\">
        <meta name=\"outputClasses\" content=\"NEW\">
        <meta name=\"description\" content=\"$description\">
        <meta name=\"keywords\" content=\"$keywords,$meta_keywords\">
        <meta name=\"cachefile\" content=\"$static_filename\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <!-- Stile -->
        <!-- Javascript -->\n";

    if(isset($course_title) && !empty($course_title) && isset($node_title) && !empty($node_title)) {
        $this->htmlheader.="<title>".PORTAL_NAME." > $course_title > $node_title</title>\n\n";
    }
    else {
        $this->htmlheader.="<title>".PORTAL_NAME."</title>\n\n";
    }

    $this->replace_field_code = $GLOBALS['replace_field_code'];
    $this->replace_microtemplate_field_code = $GLOBALS['replace_microtemplate_field_code'];
    $this->htmlheader.= "</head>\n";

    $this->htmlbody = '<body class=\'ada-'.
      str_replace(' ','-', strtolower(trim(ADAGenericUser::convertUserTypeFN($_SESSION['sess_userObj']->getType(), false)))).
      '\'';
    if(isset($onload_func) && !empty($onload_func)) {
      $this->htmlbody .= " onload=\"$onload_func\"";
    }
    $this->htmlbody .= ">\n";
    $this->htmlfooter= "</body>\n</html>";
  }  // end function HTML

  /**
   * @author giorgio 25/set/2013
   *
   * renders the widgets of the page as described in the passed xml config file
   *
   * @param string $widgetsConfFilename xml configuration filename for the widgets
   * @param arrayn $optionsArray array of option to be passed to the widget loader
   *
   * @return array|AMA_Error
   */
  public function fillin_widgetsFN ($widgetsConfFilename = '', $optionsArray = array())
  {

  	require_once ROOT_DIR.'/widgets/include/widget_includes.inc.php';
  	if (is_file($widgetsConfFilename)) {
  		try {
  			$widgetAr = ArrayToXML::toArray(file_get_contents($widgetsConfFilename));
  		}
  		catch (Exception $e) {
  			/*
  			 * see config_errors.inc.php line 167 and following.
  			 * depending on the erorr phase / severity something will happen...
  			 */
  			return new ADA_Error(NULL,'Widget configuration XML is not valid',__METHOD__,ADA_ERROR_ID_XML_PARSING);
  		}
  	}

  	/**
  	 * @author giorgio 25/feb/2014
  	 * ArrayToXML::toArray does not return an array of array if there's
  	 * only one widget in the xml. Let's build an array of array even in this case.
  	 */
  	if (!is_array(reset($widgetAr['widget']))) $widgets = array ($widgetAr['widget']);
  	else $widgets = $widgetAr['widget'];
  	$retArray = array();

  	foreach ( $widgets as $widget ) {
  		// if widget is not active skip the current iteration
  		if ((isset($widget['active']) && intval($widget['active'])===0) ||
  			(isset($widget[$widget['id']]) && intval($widget[$widget['id']['isActive']])===0)) continue;
  		$wobj = new Widget ( $widget );
  		/**
		 * if there are some params passed in, tell it to the widget
  		 */
  		if (isset($optionsArray[$wobj->templateField]) && !empty($optionsArray[$wobj->templateField]))
  		{
  			foreach ($optionsArray[$wobj->templateField] as $name=>$value) $wobj->setParam($name, $value);
  		}
  		$retArray[$wobj->templateField] = $wobj->getWidget ();
  	}
  	return $retArray;
  }

} //end class HTML

/**
 * Classe generica di output PDF
 *
 * @author giorgio
 *
 */
class PDF extends HTML {
	var $outputfile;
	var $orientation;
  var $forcedownload;
  var $returnasstring;

	public function __construct($template,$CSS_filename,$user_name,$course_title,$node_title="",$meta_keywords="",$author="",$meta_refresh_time="",
			    $meta_refresh_url="",$onload_func="",$layoutObj=NULL,$outputfile="ada",$orientation="landscape", $forcedownload = false, $returnasstring = false)
	{
		$this->outputfile = $outputfile;
		$this->orientation = $orientation;
    $this->forcedownload = $forcedownload;
    $this->returnasstring = $returnasstring;

		parent::__construct($template,$CSS_filename,$user_name,$course_title,$node_title,$meta_keywords,$author,$meta_refresh_time,$meta_refresh_url,$onload_func,$layoutObj);
	}
} //end class PDF

/**
 *
 * Classe generica di output XML
 */
class Generic_XML extends Output
{
  //vars:
  var $xmlheader;
  var $xmlbody;
  var $xmlfooter;
  var $error;
  var $errorCode;
  var $static_name;
  //functions:
  public function  __construct($portal,$date,$course_title){
    $root_dir =   $GLOBALS['root_dir'];
    $http_root_dir =   $GLOBALS['http_root_dir'];

    $this->xmlheader = "<?xml version='1.0'?>
        <?xml-stylesheet type=\"text/xsl\" href=\"$http_root_dir/browsing/ada.xsl\"?>
        <!DOCTYPE MAP SYSTEM \"$http_root_dir/browsing/ada.dtd\">
        <MAP>\n";
    $this->xmlheader.= "<PORTAL>\n$portal\n</PORTAL>\n";
    $this->xmlheader.= "<DOCDATE>\n$date\n</DOCDATE>\n";
    $this->xmlheader.= "<DOCTITLE>\n$course_title\n</DOCTITLE>\n";

    $this->xmlfooter= "</MAP>\n";
  }


  public function fillinFN($dataHa){
   /*  traduzione parziale delle chiavi essenziali */
    $this->xmlbody="<NODE>\n";
    $this->xmlbody.="<NODEID>".$this->idNode."</NODEID>\n";
    $this->xmlbody.="<VERSION>".$dataHa['version']."</VERSION>\n";
    $this->xmlbody.="<NAME>".$dataHa['title']."</NAME>\n";
    $this->xmlbody.="<COPYRIGHT>".strip_tags($dataHa['author'])."</COPYRIGHT>\n";
    $this->xmlbody.="<KEYWORDS>".strip_tags($dataHa['keywords'])."</KEYWORDS>\n";



   /* traduzione completa di tutte le chiavi;
   $this->xmlbody="<NODE>\n";
   foreach ($dataHa as $field=>$data){
	   if ($field<>'text'){
			$this->xmlbody.="<".$field.">".$data."</".$field.">";
		}
	}
	*/
    $this->xmlbody.="<TEXT>\n";
    $this->xmlbody.="<PARAGRAPH><![CDATA[".$dataHa['text'];
    //     $this->xmlbody.="<PARAGRAPH>".strip_tags($dataHa['text']); ONLY TEXT
    $this->xmlbody.="]]></PARAGRAPH>\n";
    //    $this->xmlbody.="</PARAGRAPH>\n";

    $this->xmlbody.="</TEXT>\n";
    /* MEDIA e LINKS
     //if ($dataHa['media']!=translateFN("Nessuno")) {
     $this->xmlbody.="<MEDIA>".$dataHa['media']."</MEDIA>\n";
     //}
     //if ($dataHa['links']!=translateFN("Nessuno")) {
     $this->xmlbody.="<LINKS>".$dataHa['links']."</LINKS>\n";
     //}
     */
    $this->xmlbody.="</NODE>\n";

  }

  public function outputFN ($type){
    // manda effettivamente al browser i dati(dimensioni, testo, ...)

    switch ($type){
      case 'page':  // standard
      default:
        $data = $this->xmlheader;
        $data.= $this->xmlbody;
        $data.= $this->xmlfooter;
        print $data;
        break;
      case 'dimension':
        $data = $this->xmlheader;
        $data.= $this->xmlbody;
        $data.= $this->xmlfooter;
        $dim_data = strlen($data);
        print $dim_data;
        break;
      case 'source': // debugging purpose only
        $data = $this->xmlheader;
        $data.= $this->xmlbody;
        $data.= $this->xmlfooter;
        $source_data = htmlentities($data, ENT_COMPAT | ENT_HTML401, ADA_CHARSET);
        print $source_data;
        break;
      case 'error': // debugging purpose only
        $data = $this->error;
        print $data;
        break;
      case 'file': // useful for caching pages
        $data = $this->xmlheader;
        $data.= $this->xmlbody;
        $data.= $this->xmlfooter;
        $fp = fopen ($this->static_name, "w");
        $result = fwrite($fp,$data);
        fclose($fp);
        break;
    }
  }
}
?>
