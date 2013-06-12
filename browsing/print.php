<?php

/**
 * VIEW.
 *
 * @package		view
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @author 		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		view
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT, AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_VISITOR => array('node', 'layout', 'course'),
    AMA_TYPE_STUDENT => array('node', 'layout', 'tutor', 'course', 'course_instance'),
    AMA_TYPE_TUTOR => array('node', 'layout', 'course', 'course_instance'),
    AMA_TYPE_AUTHOR => array('node', 'layout', 'course')
);

//FIXME: course_instance is needed by videochat BUT not for guest user


/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR . '/include/module_init.inc.php';

include_once 'include/browsing_functions.inc.php';
include_once 'include/cache_manager.inc.php';

/* Static mode */

$cacheObj = New CacheManager($id_profile);
$cacheObj->checkCache($id_profile);
if ($cacheObj->getCachedData){
	exit();
}


/** DYNAMIC mode
 *
 */

if ($userObj instanceof ADAGuest) {
    $self = 'guest_view';
} else {
    $self = whoami();
}

/**
 * Backurl: if user bookmarked an address and tried to get it directly...
 *
  if (isset($_SESSION['sess_backurl'])) {
  unset($_SESSION['sess_backurl']);
  }

  if (!isset($_SESSION['sess_id_user'])) {
  $_SESSION['sess_backurl'] = $_SERVER['REQUEST_URI'];
  header("Location: $http_root_dir"); // to login page
  exit();
  }
 */
/**
 * ANONYM Browsing
 *
 * if status of course_instance is ADA_STATUS_PUBLIC
 * user can visit the node but:
 * - no history
 * - no messagery
 * - no logging
 */
/**
 * Guided browsing:
 * if a guide is selected, the node id selected by the  student is overrided
 * $guide_user_id = id number of tutor
 */

// node
	$id_node = $nodeObj->id;
	$node_type = $nodeObj->type;
	$node_title = $nodeObj->name;
	$node_keywords = ltrim($nodeObj->title);
	$node_level = $nodeObj->level;
	$node_date = $nodeObj->creation_date;
	$node_icon = $nodeObj->icon;
	$node_version = $nodeObj->version;
	if (is_array($nodeObj->author)) {
		$authorHa = $nodeObj->author;
		$node_author_id = $authorHa['id'];
		$node_author = $authorHa['nome'] . " " . $authorHa['cognome'];
		$author_uname = $authorHa['username'];
	} else {
		$node_author = "";
		$author_uname = "";
	}
	$node_parent = $nodeObj->parent_id;
	$node_path = $nodeObj->findPathFN();
	$node_index = $nodeObj->indexFN('', 1, $user_level, $user_history, $id_profile);
	$node_family = $nodeObj->template_family;
	$next_node_id = $nodeObj->next_id;
	$sess_id_node = $id_node;
	$data = $nodeObj->filter_nodeFN($user_level, $user_history, $id_profile, '');


// info on author and tutor
$tutor_info_link = "<a href=\"$http_root_dir/admin/zoom_user.php?id=$tutor_id\">$tutor_uname</a>";
$author_info_link = "<a href=\"$http_root_dir/admin/zoom_user.php?id=$node_author_id\">$node_author</a>";

// E-portal
$eportal = PORTAL_NAME;

// banner
// $banner = include_once("../include/banner.inc.php"); TO BE COMPLETED
$banner = "";

		//show course istance name if isn't empty - valerio
		if (!empty($courseInstanceObj->title)) {
			$course_title .= ' - '.$courseInstanceObj->title;
		}

/**
 * content_data
 * @var array
 */
$content_dataAr = array(
	'banner' => $banner,
	'eportal' => $eportal,
	'course_title' => "<a href='main_index.php'>" . $course_title . "</a>",
	'user_name' => $user_name,
	'user_type' => $user_type,
	'user_level' => $user_level,
	'user_score' => $user_score,
	'status' => $status,
	'node_level' => $node_level,
	'visited' => $visited,
	'path' => $node_path,
	'title' => $node_title,
	'version' => $node_version,
	'date' => $node_date,
	// FIXME: non esiste ancora...??
	//	 'icon' => CourseViewer::getClassNameForNodeType($node_type),
	'icon' => $node_icon,
	'keywords' => "<a href=\"search.php?s_node_title=$node_keywords&submit=cerca&l_search=all\">$node_keywords</a>",
	'author' => $author_info_link, //'author'=>$node_author,
	'tutor' => $tutor_info_link //'tutor'=>$tutor_uname,
);

//dynamic data from $nodeObj->filter_nodeFN

$content_dataAr['text'] = $data['text'];
/* @FIXME
 * $data should NOT contain a translated string for null values but just NULL
 */
        
    if ($data['link']!= translateFN("Nessuno")) {
            $content_dataAr['link'] = $data['link'];
	} else {
            $content_dataAr['link'] = "";
	}			
$content_dataAr['media'] = $data['media'];
$content_dataAr['user_media'] = $data['user_media'];
    if ($data['exercises']!= translateFN("Nessuno<p>"))  {
            $content_dataAr['exercises'] = $data['exercises'];
	} else {
            $content_dataAr['exercises'] = "";
	}
	if ($node_index!= translateFN("Nessuno<p>"))  {
	    $content_dataAr['index'] = $node_index; 
	} else {
	    $content_dataAr['index'] = ""; 
        }
$content_dataAr['notes'] = $data['notes'];
$content_dataAr['personal'] = $data['private_notes'];
if ($node_type == ADA_GROUP_WORD_TYPE OR $node_type == ADA_LEAF_WORD_TYPE) {
	$content_dataAr['text'] .= $data['extended_node'];
	/*
	 * generate dattilo images DISABLED IN ADA

	$img_dir = $root_dir.'/browsing/dattilo/img';
	$url_dir = $http_root_dir.'/browsing/dattilo/img';
	if (file_exists($img_dir.'/a.jpg')) {
		$dattilo = converti_dattiloFN($node_title,$url_dir);
		$content_dataAr['dattilo'] = $dattilo;
	}
	* */
}

$PRINT_optionsAr = array(
		'id'=>$id_node,
		'url'=>$_SERVER['URI'],
		'course_title' => strip_tags($content_dataAr['course_title']),
		'portal' => $eportal,
		'onload_func' => 'window.print();'
);
ARE::render($layout_dataAR,$content_dataAr, ARE_PRINT_RENDER, $PRINT_optionsAr);

/**
 * preparing for static mode
 *
 * now managed by the class Cache Manager
 *
 */

$cacheObj->writeCachedData($id_profile,$layout_dataAR,$content_dataAr);
