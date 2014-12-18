<?php
/**
 * CREDITS.
 *
 * @package		main
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		info
 * @version		0.1
 */


/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/config_path.inc.php';
/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_ADMIN,AMA_TYPE_AUTHOR, AMA_TYPE_TUTOR,AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_VISITOR      => array('layout'),
  AMA_TYPE_STUDENT         => array('layout'),
  AMA_TYPE_TUTOR => array('layout'),
  AMA_TYPE_AUTHOR       => array('layout'),
  AMA_TYPE_ADMIN        => array('layout')
);
require_once ROOT_DIR.'/include/module_init.inc.php';


/**
 * Get needed objects
 */
include_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';

$self = 'default';

$credits_data = "<p>"
              . translateFN("ADA &egrave; un software libero sviluppato da")
              . ' ' ."<a href='http://www.lynxlab.com'; target='_blank'>Lynx s.r.l.</a>"
              .  "<p>".translateFN("E' rilasciato con licenza ")." <a href='".HTTP_ROOT_DIR . "/browsing/external_link.php?file=gpl.txt'; target='_blank'>GNU GPL.</a></p>".
              "Hanno contribuito allo sviluppo:".
              "<ul>
              <li>Maurizio Mazzoneschi</li>
              <li>Stefano Penge</li>
              <li>Vito Modena</li>
              <li>Giorgio Consorti</li>
              <li>Sara Capotosti</li>
              <li>Valerio Riva</li>
              <li>Guglielmo Celata</li>
              <li>Stamatis Filippis</li>          
              </ul>".
              "Hanno contribuito al disegno dell'interfaccia:".
              "<ul>
              <li>Gianluca Toni</li>
              <li>Francesco Fagnini</li>
              <li>Chiara Codino</li>
              </ul>".
              "</p>";

//$banner = include ROOT_DIR.'/include/banner.inc.php';

$title=translateFN('Credits');

$content_dataAr = array(
  'home'=>isset($home) ? $home : '',
  'user_name' => isset($user_name) ? $user_name : '',
  'user_type' => isset($user_type) ? $user_type : '',
  'user_level' => isset($user_level) ? $user_level : '',
  'status' => isset($status) ? $status :'',
  'help'=> isset($credits_data) ? $credits_data : '',
  'menu'=> isset ($menu) ? $menu : '',
  'course_title' => translateFN("Credits"),
  //'banner'=>$banner,
  'message'=> isset($message) ? $message : '',
  'agenda_link'=> isset($agenda_link) ? $agenda_link : '',
  'msg_link'=> isset($msg_link) ? $msg_link : ''
);

ARE::render($layout_dataAr, $content_dataAr);
?>
