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

if (!defined('CONFERENCE_TO_INCLUDE')) {
  define('CONFERENCE_TO_INCLUDE', 'ZoomConf');
}

if (!defined('DATE_CONTROL')) {
  define('DATE_CONTROL', FALSE);
}

require_once ROOT_DIR . '/include/data_validation.inc.php';
$request_body = file_get_contents('php://input');
$data = [];
if (strlen($request_body) > 0) {
  parse_str($request_body, $data);
}
if (count($data) == 0) $data = $_REQUEST;

if (isset($data['p']) && strlen($data['p']) && DataValidator::validate_testername($data['p'])) {

  require_once ROOT_DIR . '/include/multiport.inc.php';
  require_once ROOT_DIR . '/include/ama.inc.php';
  require_once ROOT_DIR . '/comunica/include/videoroom.classes.inc.php';

  $GLOBALS['dh'] = new \AMA_DataHandler(\MultiPort::getDSN($data['p']));
  $videoroomObj = videoroom::getVideoObj();
  $logData = [
    'event' => videoroom::EVENT_EXIT,
    'id_user' => intval($data['id_user']),
    'id_room' => intval($data['id_room']),
    'id_istanza_corso' => intval($data['id_istanza_corso']),
    'is_tutor' => intval($data['ist']),
  ];
} else {
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

  /**
   * Performs basic controls before entering this module
   */
  $trackPageToNavigationHistory = false;
  require_once ROOT_DIR . '/include/module_init.inc.php';
  require_once ROOT_DIR . '/comunica/include/comunica_functions.inc.php';

  ComunicaHelper::init($neededObjAr);
  $logData = null;
}

if (isset($videoroomObj)) {
  $videoroomObj->logExit($logData);
}
?>
<script type="text/javascript">
  window.parent.postMessage('endVideochat', '<?php echo HTTP_ROOT_DIR; ?>');
</script>
