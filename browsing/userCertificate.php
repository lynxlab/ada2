<?php

/**
 * Outputs user's credits as PDF (HTML, JSON etc)
 *
 * @package		view
 * @author		Sara Capotosti <sara@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		view
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
 */
$neededObjAr = array(
   AMA_TYPE_SWITCHER =>array('layout', 'course','course_instance')
);

/**
 * Performs basic controls before entering this module
 */

require_once ROOT_DIR . '/include/module_init.inc.php';

require_once ROOT_DIR .'/include/Course.inc.php';
require_once ROOT_DIR .'/include/CourseInstance.inc.php';

require_once 'include/browsing_functions.inc.php';


// simple generating PDF module
//include_once ROOT_DIR .'/include/Cezpdf/Cezpdf.php';

//include_once ROOT_DIR .'/include/utilities.php';

// va chiamato cosi'
// usercredits.php?id_course_instance=31
$self = whoami();
$message='questo è il mio certificato';
$logo='<img src="'.HTTP_ROOT_DIR.'/layout/ada_blu/img/header-logo.png"  />';
$content_dataAr   = array(
 'logo'=> $logo,  
 'logoProvider'=>$logoProvider, 
 'message'=>$message
    );
ARE::render($layout_dataAr, $content_dataAr,ARE_PDF_RENDER); 

