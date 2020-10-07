<?php

/**
 * Openmeetings V3.X specific class
 *
 * @package     videochat
 * @author	Stefano Penge <steve@lynxlab.com>
 * @author	Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author	giorgio consorti <g.conorti@lynxlab.com>
 * @copyright   Copyright (c) 2017, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version	0.1
 */

class OpenMeetingV3 extends videoroom implements iVideoRoom {

	public function __construct($id_course_instance = "") {
		parent::__construct ($id_course_instance);
	}

	/*
	 * Creazione videochat in openmeetings v3 e registrazione dei dati nel DB locale
	 */
	 public function addRoom($name = "service", $sess_id_course_instance, $sess_id_user, $comment = "Inserimento automatico via ADA", $num_user = 25, $course_title = 'service', $selected_provider=ADA_PUBLIC_TESTER) {

		$dh = $GLOBALS['dh'];
		$host = OPENMEETINGS_HOST;
		$port = OPENMEETINGS_PORT;
		$dir = OPENMEETINGS_DIR;

		$room_type = intval (OM_ROOM_TYPE);
		$isPublic = ROOM_IS_PUBLIC === 'true' ? true : false;

		// Create the SoapClient object
		$this->client_room = new SoapClient ( "http://" . $host . $port . "/" . $dir . "/services/RoomService?wsdl" );

		$addRoomWithModerationParams = array (
				'name' => $name,
				'type' => 'conference',
				'comment' => $comment,
				'numberOfPartizipants' => intval ($num_user),
				'isPublic' => $isPublic,
				'appointment' => FALSE,
				'demo' => FALSE,
				'closed' => FALSE,
				'moderated' => TRUE,
				'allowUserQuestions' => TRUE,
				'allowRecording' => TRUE,
				'waitForRecording' => FALSE,
				'audioOnly' => FALSE
		);

		$this->resultAddRoom = $this->client_room->add (array(
				'sid' => $this->session_id,
				'room' => $addRoomWithModerationParams
		));

		$this->id_room = $this->resultAddRoom->return->id;

		$interval = 60 * 60;
		$videoroom_dataAr= array();
		$videoroom_dataAr['id_room'] = $this->id_room;
		$videoroom_dataAr['id_istanza_corso'] = $sess_id_course_instance;
		$videoroom_dataAr['id_tutor'] = $sess_id_user;
		$videoroom_dataAr['tipo_videochat'] = $room_type;
		$videoroom_dataAr['descrizione_videochat'] = $name;
		$videoroom_dataAr['tempo_avvio'] = time();
		$videoroom_dataAr['tempo_fine'] = time() + $interval;

		$videoroom_data = $dh->add_videoroom ($videoroom_dataAr);

		if (AMA_DB::isError ($videoroom_data)) {
			return false;
		}

		return $this->id_room;
	}

	public function list_rooms() {
	}

	public function getRoom($id_room) {

		$host = OPENMEETINGS_HOST;
		$port = OPENMEETINGS_PORT;
		$dir = OPENMEETINGS_DIR;

		// Create the SoapClient object
		$this->client_room = new SoapClient ( "http://" . $host . $port . "/" . $dir . "/services/RoomService?wsdl" );

		$rooms_params = array (
				'sid' => $this->session_id,
				'id' => $id_room
		);
		$this->room_properties = $this->client_room->getRoomById ($rooms_params);
	}

	public function serverLogin() {

		$host = OPENMEETINGS_HOST;
		$port = OPENMEETINGS_PORT;
		$dir = OPENMEETINGS_DIR;

		$this->client_user = new SoapClient ( "http://" . $host . $port . "/" . $dir . "/services/UserService?wsdl" );

		$login_params = array (
				'user' => OPENMEETINGS_ADMIN,
				'pass' => OPENMEETINGS_PASSWD
		);

		$loginResult = $this->client_user->login ( $login_params );
		$this->login = $loginResult->return;

		if ($this->login->code <= 0) {
			$this->error_openmeetings = true;
		} else {
			// get new session_id for accessing and creating rooms
			$this->session_id = $this->login->message;
		}
	}

	public function roomAccess($username, $nome, $cognome, $user_email, $sess_id_user, $id_profile, $selected_provider) {

		$host = OPENMEETINGS_HOST;
		$port = OPENMEETINGS_PORT;
		$dir = OPENMEETINGS_DIR;

		$becomeModerator = FALSE;
		$allowRecording = FALSE;

		if ($id_profile == AMA_TYPE_TUTOR) {
			$becomeModerator = TRUE;
			$allowRecording = TRUE;
		}

		$room_id = $this->id_room;
		$externalUserId = $sess_id_user;
		$externalUserType = "ADA"; // potrebbe essere preso da $userObj->type?
		$showAudioVideoTest = FALSE; // 0 = no audio/video test
		if (OPENMEETINGS_VERSION > 0) {
			$user_params = array (
					'login' => $username,
					'firstname' => $nome,
					'lastname' => $cognome,
					'profilePictureUrl' => "",
					'email' => $user_email,
					'externalId' => $externalUserId,
					'externalType' => $externalUserType
			);
			$room_params = array (
					'roomId' => $room_id,
					'moderator' => $becomeModerator,
					'showAudioVideoTest' => $showAudioVideoTest,
					'allowRecording' => $allowRecording,
					'allowSameURLMultipleTimes' => TRUE
			);

			$this->secureHash = $this->client_user->getRoomHash (array (
					'sid' => $this->session_id,
					'user' => $user_params,
					'options' => $room_params
			));
			$secureHash = $this->secureHash->return->message;
		}

		/*
		 * LINK A STANZA
		 */
		$language = ROOM_DEFAULT_LANGUAGE;
		$sess_lang = $_SESSION ['sess_user_language'];
		$videochat_lang = "VIDEOCHAT_LANGUAGE_" . strtoupper ( $sess_lang );
		if (defined ( $videochat_lang )) {
			$language = constant ( $videochat_lang );
		}

		if (OPENMEETINGS_VERSION > 0) {
			$this->link_to_room = "http://" . $host . $port . "/" . $dir . "/hash?secure=" . $secureHash . "&language=" . $language;
		}
	}

	public function delete_room($id_room) {

		$dh = $GLOBALS ['dh'];
		$host = OPENMEETINGS_HOST;
		$port = OPENMEETINGS_PORT;
		$dir = OPENMEETINGS_DIR;

		// Create the SoapClient object
		$this->client_room = new SoapClient ( "http://" . $host . $port . "/" . $dir . "/services/RoomService?wsdl" );

		$params = array (
				'sid' => $this->session_id,
				'id' => $id_room
		);

		$result_openmeetings = $this->client_room->delete ( $params );
		if ($result_openmeetings->return->code == $id_room) { // if deleted ok in openmeetings delete in DB too
			$result = $dh->delete_videoroom ($id_room);
		}
	}
}
