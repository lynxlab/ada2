<?php

/**
 * Jitsi meet specific class
 *
 * @package   videochat
 * @author	  giorgio consorti <g.conorti@lynxlab.com>
 * @copyright Copyright (c) 2020, Lynx s.r.l.
 * @license	  http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version	  0.1
 */

require_once ROOT_DIR . '/comunica/include/videoroom.classes.inc.php';
if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider']) && is_readable(ROOT_DIR . '/clients/' . $GLOBALS['user_provider'] . '/Jitsi.config.inc.php')) {
	require_once ROOT_DIR . '/clients/' . $GLOBALS['user_provider'] . '/Jitsi.config.inc.php';
} else {
	require_once ROOT_DIR . '/comunica/include/Jitsi.config.inc.php';
}

class Jitsi extends videoroom implements iVideoRoom
{

	const onload_js = "if (\$j('#jitsi-meet-placeholder').length>0) {
		\$j.getScript('../js/comunica/ada-jitsi.js.php?parentId=".JITSI_HTML_PLACEHOLDER_ID."');
	}";

	public function __construct($id_course_instance = "") {
		parent::__construct($id_course_instance);
	}

	public function addRoom($name = 'service', $sess_id_course_instance, $sess_id_user, $comment = 'Inserimento automatico via ADA', $num_user = 25, $course_title = 'service', $selected_provider=ADA_PUBLIC_TESTER) {

	}

	/**
	 * TODO: this is mock-up that just forces full to be non zero
	 * implement it if needed, or remove it
	 *
	 * @param [type] $id_course_instance
	 * @param [type] $tempo_avvio
	 * @param [type] $interval
	 * @return void
	 */
	public function videoroom_info($id_course_instance,$tempo_avvio=NULL, $interval=NULL) {
		parent::videoroom_info($id_course_instance,$tempo_avvio, $interval);
		if ($this->full == 0) $this->full = 1;
	}

	public function serverLogin() {
		$this->login = true;
	}

	public function roomAccess($username, $nome, $cognome, $user_email, $sess_id_user, $id_profile, $selected_provider) {
		$this->link_to_room = CDOMElement::create('div','id:'.JITSI_HTML_PLACEHOLDER_ID);
		$this->link_to_room->setAttribute('data-domain', JITSI_CONNECT_HOST);
		$this->link_to_room->setAttribute('data-width', FRAME_WIDTH);
		$this->link_to_room->setAttribute('data-height', FRAME_HEIGHT);
	}

	public function getRoom($id_room) {

	}
}
