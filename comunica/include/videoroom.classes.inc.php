<?php

/**
 * videoroom abstract class
 *
 * @package             videochat
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		giorgio consorti <g.conorti@lynxlab.com>
 * @copyright           Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

abstract class videoroom
{
    var $id;
    var $id_room;
    var $id_istanza_corso;
    var $id_tutor;
    var $tipo_videochat;
    var $descrizione_videochat;
    var $tempo_avvio;
    var $tempo_fine;
    var $full;

    var $client_user;
    var $session_id;
    var $login;
    var $error_videochat;

    var $client_room;
    var $roomTypes;
    var $rooms; // elenco stanze disponibili sul server
    var $link_to_room;
    var $room_properties;
    var $list_room; // elenco stanze disponibili sul server

    const EVENT_ENTER = 1;
    const EVENT_EXIT = 2;

    public function __construct($id_course_instance = "")
    {
        $dh            =   $GLOBALS['dh'];
        $error         =   $GLOBALS['error'];
        $debug         =   isset($GLOBALS['debug']) ? $GLOBALS['debug'] : null;
        $root_dir      =   $GLOBALS['root_dir'];
        $http_root_dir =   $GLOBALS['http_root_dir'];
    }

    public static function getVideoObj()
    {
        if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider']) && is_readable(ROOT_DIR . '/clients/' . $GLOBALS['user_provider'] . '/' . CONFERENCE_TO_INCLUDE . '.config.inc.php')) {
            require_once ROOT_DIR . '/clients/' . $GLOBALS['user_provider'] . '/' . CONFERENCE_TO_INCLUDE . '.config.inc.php';
        } else {
            require_once ROOT_DIR . '/comunica/include/' . CONFERENCE_TO_INCLUDE . '.config.inc.php';
        }
        require_once $GLOBALS['root_dir'] . '/comunica/include/' . CONFERENCE_TO_INCLUDE . '.class.inc.php';
        $videoObjToInstantiate = CONFERENCE_TO_INCLUDE;
        return new $videoObjToInstantiate();
    }
    /*
     * retrieve infos about room memorized in local DB
     */
    public function videoroom_info($id_course_instance, $tempo_avvio = NULL, $interval = NULL, $more_query = NULL)
    {
        $dh            =   $GLOBALS['dh'];
        $error         =   $GLOBALS['error'];
        $debug         =   isset($GLOBALS['debug']) ? $GLOBALS['debug'] : null;
        $root_dir      =   $GLOBALS['root_dir'];
        $http_root_dir =   $GLOBALS['http_root_dir'];
        $video_roomAr = $dh->get_videoroom_info($id_course_instance, $tempo_avvio, $more_query);

        if (AMA_DataHandler::isError($video_roomAr) || !is_array($video_roomAr)) {
            $this->full = 0;
        } else {
            $this->id_room = $video_roomAr['id_room'];
            $this->id_tutor = $video_roomAr['id_tutor'];
            $this->id = $video_roomAr['id'];
            $this->id_istanza_corso = $video_roomAr['id_istanza_corso'];
            $this->descrizione_videochat = $video_roomAr['descrizione_videochat'];
            $this->tempo_avvio = $video_roomAr['tempo_avvio'];
            $this->tempo_fine = $video_roomAr['tempo_fine'];
            $this->tipo_videochat = $video_roomAr['tipo_videochat'];
            $this->full = 1;
        }
    }

    public static function xml_attribute($object, $attribute)
    {
        if (isset($object[$attribute]))
            return (string) $object[$attribute];
    }

    public function logEnter() {
        return $this->logEvent(self::EVENT_ENTER);

    }

    public function logExit() {
        return $this->logEvent(self::EVENT_EXIT);
    }

    protected function logEvent ($event) {
        $retval = false;
        $dh = $GLOBALS['dh'];
        if ($event == self::EVENT_ENTER) {
            $dh->log_videoroom([
                'event' => $event,
                'id_user' => $_SESSION['sess_userObj']->getId(),
                'id_room' => $this->id_room,
                'id_istanza_corso' => $this->id_istanza_corso,
                'is_tutor' => $_SESSION['sess_userObj']->getType() == AMA_TYPE_TUTOR,
            ]);

        } else if ($event == self::EVENT_EXIT) {
            $dh->log_videoroom([
                'event' => $event,
                'id_user' => $_SESSION['sess_userObj']->getId(),
                'id_room' => $this->id_room,
                'id_istanza_corso' => $this->id_istanza_corso,
                'is_tutor' => $_SESSION['sess_userObj']->getType() == AMA_TYPE_TUTOR,
            ]);
        }
        return $retval;
    }
}

interface iVideoRoom
{
    public function addRoom($name = 'service', $sess_id_course_instance, $sess_id_user, $comment = 'Inserimento automatico via ADA', $num_user = 25, $course_title = 'service', $selected_provider=ADA_PUBLIC_TESTER);
    public function serverLogin();
    public function roomAccess($username, $nome, $cognome, $user_email, $sess_id_user, $id_profile, $selected_provider);
    public function getRoom($id_room);
}
