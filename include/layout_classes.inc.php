<?php
/**
 * Layout, Template, CSS, JS classes
 *
 *
 * @package		view
 * @author		Stefano Penge <steve@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

class Layout {
    // vars
    var $template;
    var $template_dir;
    var $CSS_filename;
    var $CSS_dir;
    var $JS_filename;
    var $JS_dir;
    var $family;
    var $module_dir;
    var $error_msg;
    var $full;
	var $external_module = false;
	// @author giorgio 25/set/2013
	// widgets configuration file name and dir
	var $WIDGET_filename;
	var $WIDGET_dir;

    //constructor
    function Layout($user_type,$node_type,$family="",$node_author_id="",$node_course_id="",$module_dir="") {

	$http_root_dir = HTTP_ROOT_DIR;
	$root_dir      = ROOT_DIR;
	$modules_dir   = MODULES_DIR;

	$this->error = "";
	if (empty($module_dir)) {
		$modules_dir = str_replace($root_dir,'',$modules_dir);
		$actual_dir = str_replace($root_dir,'',dirname($_SERVER['SCRIPT_FILENAME']));

		if (empty($actual_dir)) {
			$module_dir = 'main';
		}
		else {
			if (strpos($actual_dir,$modules_dir) !== false) {
				$this->external_module = true;
			}
			$module_dir = substr($actual_dir,1);
		}
	}

	if (!$family) {
		$family = ADA_TEMPLATE_FAMILY; //default
	}
	$this->family = $family;
	$this->module_dir = $module_dir;
	
	/**
	 * @author giorgio 20/ott/2014
	 * 
	 * $basedir_ada var was already here but it looks like
	 * it's never used. I set it to null and keep it here
	 * for compatibilty reason, it's probably safe to remove it
	 */
	if (!isset($basedir_ada)) $basedir_ada = null;

	// Template
	$TplObj = new Template($user_type,$node_type,$family,$node_author_id,$node_course_id,$basedir_ada,$module_dir,$this->external_module);
	$this->template = $TplObj->template;
	$this->template_dir = $TplObj->template_dir;

	// Cascading Style Sheet(s)
	$CSSObj = new CSS($user_type,$node_type,$family,$node_author_id,$node_course_id,$basedir_ada,$module_dir,$this->external_module);
	$this->CSS_filename = $CSSObj->CSS_filename;
	$this->CSS_dir = $CSSObj->CSS_dir;

	// Javascript
	$JSObj = new JS($user_type,$node_type,$family,$node_author_id,$node_course_id,$basedir_ada,$module_dir,$this->external_module);
	$this->JS_filename = $JSObj->JS_filename;
	$this->JS_dir = $JSObj->JS_dir;
	//$this->debug();
	
	// Widgets
	$pageWidgetObj = new PageWidget($this->template);
	$this->WIDGET_dir = $pageWidgetObj->pageWidgetsDir;
	$this->WIDGET_filename = $pageWidgetObj->pageWidgets;

    }//end function Layout

    function debug () {
        // forces debug
        //var_dump($this);
        $GLOBALS['debug'] = 1;
         mydebug(__LINE__,__FILE__,"FDIR: ".$this->family);
        mydebug(__LINE__,__FILE__,"CSS: ".$this->CSS_filename);
        mydebug(__LINE__,__FILE__,"CSSDIR: ".$this->CSS_dir);

        mydebug(__LINE__,__FILE__,"TPL: ".$this->template);
        mydebug(__LINE__,__FILE__,"TPLDIR: ".$this->template_dir);

        mydebug(__LINE__,__FILE__,"JS: ".$this->JS_filename);
        mydebug(__LINE__,__FILE__,"JSDIR: ".$this->JS_dir);

        mydebug(__LINE__,__FILE__,"MDIR: ".$this->module_dir);


        $GLOBALS['debug'] = 0;
    }

    /**
     * Returns an associative array of the layouts family installed in ADA
     *
     * @return array $layouts
     */
    public static function getLayouts() {
        /*
     * path to the directory containing all the layouts families
        */
        $path_to_dir = ROOT_DIR.'/layout/';
        /*
     * initialize the layouts array so that it contains at least the 'none' option
        */
        $layouts = array('none' => translateFN('seleziona un layout'));
        if(is_readable($path_to_dir)) {
            /*
       * do not consider as layout names '.', '..', and 'CVS'
            */
            $files = array_diff(scandir($path_to_dir), array('.','..','.svn'));
            /*
       * check if any of the resulting filenames is a directory and if it so
       * consider the filename as a layout name
            */
            foreach ($files as $filename) {
                if(is_dir($path_to_dir.$filename)) {
                    $layouts[$filename] = $filename;
                }
            }
        }
        return $layouts;
    }
}   //end class Layout

class Template {
    var $template;
    var $template_dir;
    var $family;
    var $error_msg;
    var $error;
    var $full;

    function Template ($user_type,$node_type,$family="",$node_author_id="",$node_course_id="",$basedir_ada,$function_group,$is_external_module = false) {

        $root_dir = $GLOBALS['root_dir'];
        $http_root_dir = $GLOBALS['http_root_dir'];
        $duplicate_dir_structure =  $GLOBALS['duplicate_dir_structure'];
        // 0 or 1

        $tpl_fileextension =  $GLOBALS['tpl_fileextension'];

        /**
         * giorgio 12/ago/2013
         * sets user select provider
         */
        if (!MULTIPROVIDER) $user_provider = $GLOBALS['user_provider'];

        // templates file extensions could be .tpl or .dwt or .HTML etc
        // default: .tpl
        if (!isset($tpl_fileextension)) {
            $tpl_fileextension = ".tpl";
        }
        if (!isset($duplicate_dir_structure))
            $duplicate_dir_structure = 1; //default

        if (!$family) {

            $family = ADA_TEMPLATE_FAMILY; //default
            //                 } else {
            //                       $family = $GLOBALS['template_family'];
            //                 }
            //             } else {
            //                $GLOBALS['template_family'] = $family;
        }

        // mydebug(__LINE__,__FILE__,"BA $basedir_ada FG $function_group");

        //___________TPL ____________
        // reads templates from filesystem
        //
        if (($function_group == "main") || (strtoupper($function_group) == strToUpper($basedir_ada))) {
            $module_dir = 'main';
        } else {
            $module_dir = $function_group;
        }

	if ($is_external_module) {
		
		if (!MULTIPROVIDER) {
			$tpl_dir = $root_dir."/clients/".$user_provider."/layout/$family/templates/$module_dir/";
			$tpl_filename = $tpl_dir.$node_type.$tpl_fileextension;
			
			if (!isset($tpl_filename) || !file_exists($tpl_filename)) {
				$tpl_filename = $tpl_dir."default".$tpl_fileextension;
				if (!isset($tpl_filename) || !file_exists($tpl_filename)) {
					unset($tpl_filename);
				}
			}
		} 
		
		if(!isset($tpl_filename)) {
			$tpl_dir = $root_dir."/$module_dir/layout/$family/templates/";
			$tpl_filename = $tpl_dir.$node_type.$tpl_fileextension;
		}
	}
	else {
		/**
		 * giorgio 11/ago/2013
		 * if it's not multiprovider, let's firstly check for a template
		 * in the clients/provider dir with only one possibility in $module_dir
		 */
		if (!MULTIPROVIDER)
		{
			if (stristr($module_dir,$user_provider)) $module_dir='main';
			$tpl_dir = $root_dir."/clients/".$user_provider."/layout/$family/templates/$module_dir/";
			$tpl_filename = $tpl_dir.$node_type.$tpl_fileextension;
			/**
			 * giorgio 12/ago/2013
			 *
			 * checking for default template in user selected provider may not be
			 * a good idea because it's not known where and when ada shall use this
			 * template, and it's unpleasant that all of a sudden the user finds
			 * him/her self in the provider template while he/she is browsing....
			 *
			 *  Should you disable it, check carefully all 'anonymous' pages
			 *  at least info.php should use the default template
			 */
			if (!isset($tpl_filename) || !file_exists($tpl_filename))
			{
				$tpl_filename = $tpl_dir."default".$tpl_fileextension;
			}
		}
	}
	/**
	 * giorgio 11/ago/2013
	 * if $tpl_filename is not found inside client dir, resume normal operation
	 */
	if (!isset($tpl_filename) || !file_exists($tpl_filename))
	{
		$tpl_dir = $root_dir."/layout/$family/templates/$module_dir/";
		$tpl_filename = $tpl_dir.$node_type.$tpl_fileextension;
	}

        // es. layout/clear/templates/browsing/default/view.tpl
        if (!file_exists($tpl_filename)) {
            //$tpl_dir = $root_dir."/templates/$module_dir/$family/";
            $tpl_filename = $tpl_dir."default".$tpl_fileextension;
            // mydebug(__LINE__,__FILE__, " trying $tpl_filename...<br>");
            if (!file_exists($tpl_filename)) {
                $module_dir = "main";
                $tpl_dir = $root_dir."/layout/$family/templates/$module_dir/";
                $tpl_filename = $tpl_dir."default".$tpl_fileextension;
                if (!file_exists($tpl_filename)) {
                    $this->error = "$tpl_filename not found";
                }
                //mydebug(__LINE__,__FILE__, "  $tpl_filename...<br>");
            }
        }
        $this->template = $tpl_filename;
        $this->template_dir = $tpl_dir;
        $this->family = $family;
    } // end function Template
}

class CSS {

    var $CSS_filename;
    var $CSS_dir;
    var $family;
    var $error_msg;
    var $full;

    function CSS ($user_type,$node_type,$family="",$node_author_id="",$node_course_id="",$basedir_ada,$function_group,$is_external_module = false) {

        $root_dir = $GLOBALS['root_dir'];
        $http_root_dir = $GLOBALS['http_root_dir'];

        /**
         * giorgio 12/ago/2013
         * sets user select provider
         */
        if (!MULTIPROVIDER) $user_provider = $GLOBALS['user_provider'];

        $CSS_files = array();
        // reads CSS from filesystem
        //  la struttura dei CSS ricopia quella di ADA (default)

        $rel_pref = $root_dir.'/';
        if (($function_group == "main") || (strtoupper($function_group) == strToUpper($basedir_ada))) {
            $module_dir = "main";
            // es. index.php -> layout/clear/css/main/default/index.css
        }  else {
            $module_dir = $function_group;
            // es. browsing/view.php -> layout/clear/css/browsing/default/view.css
        }
        if(!$family) {
            $family = ADA_TEMPLATE_FAMILY;
        }

	if ($is_external_module) {
		$CSS_module_dir = $rel_pref.$module_dir."/layout/$family/css/";
		// as an extreme fallback, use css/main
		$CSS_dir = $rel_pref."layout/$family/css/main/";
	}
	else {
		/**
		 * giorgio 11/ago/2013
		 * module_dir comes as 'clients/PROVIDERNAME'
		 * let's put it back in place
		 */
		if (!MULTIPROVIDER && stristr ($module_dir,$user_provider))
		{
			$module_dir = 'main';
		}

		if (!isset($CSS_module_dir)) $CSS_module_dir = '';
		$CSS_dir = $rel_pref."layout/$family/css/$module_dir/";
	}


		if (is_file($CSS_module_dir."default.css"))
			$CSS_files[] = $CSS_module_dir."default.css";
        else
        	$CSS_files[] = $CSS_dir."default.css"; //adding default file

        if (is_file($CSS_module_dir.$node_type.".css"))
        	$CSS_files[] = $CSS_module_dir.$node_type.".css";
        else if (!in_array($CSS_dir.$node_type.".css",$CSS_files))
        	$CSS_files[] = $CSS_dir.$node_type.".css"; //adding specific node type file

        if (!empty($node_author_id)) {
            if (!empty($node_course_id)) {
                $CSS_files[] = $http_root_dir."/courses/media/$node_author_id/css/$node_course_id.css";
            }
        }

        /**
         * giorgio 11/ago/2013
         * if it's not multiprovider add node_type css and default css
         * (same structure as in 'main' css sudir)
         */
        if (!MULTIPROVIDER)
        {
        	$CSS_provider_dir = $rel_pref."clients/".$user_provider."/layout/$family/css/"; 
        	
        	if (is_file($CSS_provider_dir.$module_dir."/default.css"))
        		$CSS_files[] = $CSS_provider_dir."default.css";
        	
        	if (is_file($CSS_provider_dir."main/default.css"))
        		$CSS_files[] = $CSS_provider_dir."main/default.css";
        	
        	if (is_file($CSS_provider_dir.$module_dir."/".$node_type.".css"))
        		$CSS_files[] = $CSS_provider_dir.$module_dir."/".$node_type.".css";
        }
        
        /**
         * @author giorgio 10/nov/2014
         * 
         * add adamenu.css
         */
        $adamenuCSS = (isset($_SESSION['IE-version']) && 
        		    $_SESSION['IE-version']!==false && $_SESSION['IE-version']<=8) ? "adamenu-ie8.css" : "adamenu.css";
        
        $adamenuCSSDir = $rel_pref."layout/$family/css/";
        if (is_file($adamenuCSSDir.$adamenuCSS)) $CSS_files[] = $adamenuCSSDir.$adamenuCSS;
        
        if (!MULTIPROVIDER) {
        	/**
        	 * if not multiprovider, include client's adamenu.css also
        	 */
        	$adamenuCSSDir = $CSS_provider_dir . '../';
        	if (is_file($adamenuCSSDir.$adamenuCSS)) $CSS_files[] = $adamenuCSSDir.$adamenuCSS;
        }
        
        $this->CSS_filename = implode(';',$CSS_files);
        $this->CSS_dir = $CSS_dir;
        $this->family = $family;

        //  mydebug(__LINE__,__FILE__,"CSS DDS: $duplicate_dir_structure fgroup:$function_group mdir:$module_dir bdir:$basedir_ada". $this->CSS_filename."<br>");

    } //end function CSS

}

class JS {
    var $JS_filename;
    var $JS_dir;
    var $error_msg;
    var $full;
    function JS ($user_type,$node_type,$family="",$node_author_id="",$node_course_id="",$basedir_ada,$function_group,$is_external_module = false) {
        $root_dir = $GLOBALS['root_dir'];
        $http_root_dir = $GLOBALS['http_root_dir'];
        if (($function_group == "main") || (strtoupper($function_group) == strToUpper($basedir_ada))) {
            $module_dir = "main";
            $rel_pref = "";
            // es. index.php -> js/main/default/index.js
        }
        else {
            $rel_pref = "../";
            $module_dir = $function_group;
            // es. browsing/view.php -> ../js/browsing/default/view.js
        }

        $rel_pref = $root_dir.'/';
	if ($is_external_module) {
		$JS_dir = $rel_pref.$module_dir."/js/";
	}
	else {
		$JS_dir = $rel_pref."js/$module_dir/";
	}

        $JS_files[]= $rel_pref."external/lib/js/prototype-1.6.0.1.js";
        $JS_files[]= $rel_pref."external/lib/js/scriptaculous/scriptaculous.js";
        $JS_files[]= $JS_dir."default.js";
        if (!in_array($JS_dir.$node_type.".js",$JS_files)) $JS_files[]= $JS_dir.$node_type.".js";
        if (!empty($node_author_id)) {
            if (!empty($node_course_id)) {
                $JS_author_file = $rel_pref."courses/media/$node_author_id/js/$node_course_id.js";
            }
        }
        // javascript fissi
        $JS_files[]= $rel_pref."js/include/chkfrm.js";
        //  $this->JS_filename = $default_JS_file.";".$JS_file.";".$JS_author_file.";".$JS_ajax.";".$check_JS_file.";";
        $this->JS_filename = implode(';',$JS_files);
        $this->JS_dir = $JS_dir;
    } //end function JS
}


/**
 * class for setting the needed XML for the page widget, if any.
 *
 * @author giorgio 25/set/2013
 */
class PageWidget
{
	/**
	 * holds widgets configuration file full pathname or null on error
	 * @var string
	 */
	var $pageWidgets;
	
	/**
	 * holds widgets configuration file full dirname or null on error
	 * @var string
	 */	
	var $pageWidgetsDir;
	
	/**
	 * hold error string if any
	 * @var string
	 */
	var $error;
	
	/**
	 * default widget configuration file extension
	 * @var string
	 */
	private static $widgetConfFileExtension = '.xml';
	
	/**
	 * where to start looking for dirname.
	 * e.g. assuming template is in ROOT_DIR .'layout/ada_blu/templates/main/default.tpl'
	 * it'll extract the dir starting AND NOT INCLUDING the value of the variable.
	 * e.g. 'main/'
	 * 
	 * @var string
	 */	
	private static $extractPathStartingFrom = 'templates/';
	
    /**
     * PageWidget constructor, the XML filename is the same as the template, but with xml
     * extension. If one with same name is found inside the currently active provider, that
     * one is preferred over the standard one.
     * 
     * @param string $filename template file name used to build widget xml file name
     */
    public function __construct($filename)
    {    	
    	$this->pageWidgets = null;
    	$this->pageWidgetsDir = null;
    	$this->error = '';
    	
    	$extractStringFrom = strpos($filename, self::$extractPathStartingFrom) + strlen (self::$extractPathStartingFrom);
    	$extractLength  = strrpos($filename, '/') - $extractStringFrom + 1 ; 
    	
    	$dirname = substr ($filename, $extractStringFrom, $extractLength);
    	$filename = preg_replace('/\..*$/', self::$widgetConfFileExtension, basename($filename));

    	$widgets_filename = '';
    	
    	if (!MULTIPROVIDER)
    	{
    		$widgets_dir = ROOT_DIR."/clients/".$GLOBALS['user_provider']."/widgets/$dirname";
    		$widgets_filename = $widgets_dir.$filename;
    	}
    	
    	if (!file_exists($widgets_filename))
    	{
    		$widgets_dir = ROOT_DIR . "/widgets/$dirname";
    		$widgets_filename = $widgets_dir.$filename;
    		if (!file_exists($widgets_filename)) {
    			$widgets_dir = $widgets_filename = null;
    			$this->error = "$widgets_filename not found";    		
    		}
    	}    	
    	$this->pageWidgets = $widgets_filename;
    	$this->pageWidgetsDir = $widgets_dir;    	
    }
}
?>
