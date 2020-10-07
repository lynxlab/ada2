<?php

/**
 * @package 	zoom integration module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array();
array_push($variableToClearAR, 'layout');
array_push($variableToClearAR, 'user');
array_push($variableToClearAR, 'course');
array_push($variableToClearAR, 'course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_STUDENT => array('layout', 'tutor', 'course', 'course_instance', 'videoroom'),
  AMA_TYPE_TUTOR => array('layout', 'tutor', 'course', 'course_instance', 'videoroom')
);

if (!defined('CONFERENCE_TO_INCLUDE')) {
  define('CONFERENCE_TO_INCLUDE', 'ZoomConf'); // Zoom
}

if (!defined('DATE_CONTROL')) {
  define('DATE_CONTROL', FALSE);
}

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR . '/include/module_init.inc.php';
require_once ROOT_DIR . '/comunica/include/comunica_functions.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
ComunicaHelper::init($neededObjAr);

/*
 * Redirect to correct home if comunication not enabled
 */
if ($userObj->getType() == AMA_TYPE_VISITOR) {
  $homepage = $userObj->getHomepage();
  $msg =   translateFN("Utente non autorizzato");
  header("Location: $homepage?err_msg=$msg");
  exit;
}
$width = FRAME_WIDTH;
$height = FRAME_HEIGHT;

if (is_null($videoroomObj->link_to_room)) {
  $errdiv = CDOMElement::create('div','class:ui icon error message');
  $errdiv->addChild(CDOMElement::create('i','class: ban circle icon'));
  $content = CDOMElement::create('div','class:content');
  $header = CDOMElement::create('div','class:header');
  $header->addChild(new CText(translateFN('Video Conferenza')));
  $content->addChild($header);
  $content->addChild(new \CText('<p>'.translateFN('Video Conferenza non ancora iniziata').'</p>'));
  $errdiv->addChild($content);
  die($errdiv->getHtml());
} else if (is_string($videoroomObj->link_to_room) && strlen($videoroomObj->link_to_room) > 0) {
  $className = get_class($videoroomObj);
  $iframe = "<iframe src='$videoroomObj->link_to_room' width='$width' height = '$height'";
  if (defined($className . '::iframeAttr')) {
    $iframe .= constant($className . '::iframeAttr');
  }
  $iframe .= "></iframe>";
  $videoroomObj->logEnter();
  die($iframe);
} else {
  header(' ', true, 500);
}
