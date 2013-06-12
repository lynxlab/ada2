<?php
/**
 * Functions used by index.php
 * 
 * 
 * 
 * PHP version >= 5.0
 * 
 * @package		view
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		index			
 * @version		0.1
 */

/**
 * Template Family
 */
require_once ROOT_DIR.'/include/HtmlLibrary/UserModuleHtmlLib.inc.php';

$template_family = ADA_TEMPLATE_FAMILY; // default template famliy
$_SESSION['sess_template_family'] = $template_family;

/**
 * LAYOUT
 */
$layout_dataAr = array(
  'node_type'      => NULL,
  'family'         => $template_family,
  'node_author_id' => NULL,
  'node_course_id' => NULL,
  'module_dir'     => NULL
);
?>