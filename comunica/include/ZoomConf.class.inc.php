<?php

use Lynxlab\ADA\Module\ZoomIntegration\ZoomIntegrationException;

/**
 * Zoom Conference specific class
 *
 * @package   videochat
 * @author    Stefano Penge <steve@lynxlab.com>
 * @author    Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author    giorgio consorti <g.conorti@lynxlab.com>
 * @copyright Copyright (c) 2020, Lynx s.r.l.
 * @license	  http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version	  0.1
 */

class ZoomConf extends videoroom implements iVideoRoom
{

    const iframeAttr = ' class=\'ada-videochat-embed zoom\' allowfullscreen allow=\'camera; microphone;\' sandbox=\'allow-forms allow-scripts allow-same-origin\'';
    const videochattype = 'Z';

    private $zoomAPI = null;
    private $meetingID = null;
    private $meetingPWD = null;

    public function __construct($id_course_instance = "")
    {
        parent::__construct($id_course_instance);
        $this->zoomAPI = new Lynxlab\ADA\Module\ZoomIntegration\ADAZoomApi();
    }

    /*
     * Creazione videochat
     */
    public function addRoom($name = 'service', $sess_id_course_instance, $sess_id_user, $comment = 'Inserimento automatico via ADA', $num_user = 25, $course_title = 'service', $selected_provider = ADA_PUBLIC_TESTER)
    {
        try {
            $interval = 60 * 60 * 24 * 365; // 1 year
            $videoroom_dataAr = array();
            $videoroom_dataAr['id_room'] = 0; // will be set to the id by the datahandler
            $videoroom_dataAr['id_istanza_corso'] = $sess_id_course_instance;
            $videoroom_dataAr['id_tutor'] = $sess_id_user;
            $videoroom_dataAr['tipo_videochat'] = self::videochattype;
            $videoroom_dataAr['descrizione_videochat'] = $name;
            $videoroom_dataAr['tempo_avvio'] = time();
            $videoroom_dataAr['tempo_fine'] = $videoroom_dataAr['tempo_avvio'] + $interval; // unused
            $videoroom_dataAr['room_name'] = $course_title;
            $videoroom_data = $this->zoomAPI->create($videoroom_dataAr);
            $this->id_room = $videoroom_data['openmeetings_room_id'];
            $this->id_istanza_corso = $videoroom_data['id_istanza_corso'];
            $this->meetingID = $videoroom_data['meetingID'];
            $this->meetingPWD = $videoroom_data['meetingPWD'];
            return $this->id_room;
        } catch (ZoomIntegrationException $e) {
            return false;
        }
    }

    public function videoroom_info($id_course_instance, $tempo_avvio = NULL, $interval = NULL, $more_query = NULL)
    {
        // load parent info
        if (is_null($more_query)) $more_query = 'AND `tipo_videochat`="'.self::videochattype.'" ORDER BY `tempo_avvio` DESC';
        parent::videoroom_info($id_course_instance,$tempo_avvio, $interval, $more_query);
        // load Zoom own info and check that meeting does exists
        $video_roomAr = $this->zoomAPI->getInfo($this->id);
        $this->setMeetingID(null)->setMeetingPWD(null);
        if (is_array($video_roomAr) && count($video_roomAr)>0) {
            if (isset($video_roomAr['meetingID'])) {
                $this->setMeetingID($video_roomAr['meetingID']);
            }
            if (isset($video_roomAr['meetingPWD'])) {
                $this->setMeetingPWD($video_roomAr['meetingPWD']);
            }
        }
        $this->full = !is_null($this->getMeetingID()) && !is_null($this->getMeetingPWD());
    }

    public function serverLogin()
    {
        $this->login = 1;
        return true;
    }

    public function roomAccess($username, $nome, $cognome, $user_email, $sess_id_user, $id_profile, $selected_provider)
    {
        $this->link_to_room = MODULES_ZOOMCONF_HTTP . '/ada-zoom-bridge.php';
    }

    public function generateSignature($role = 0, $apiKey = ZOOMCONF_APIKEY, $apiSecret=ZOOMCONF_APISECRET)
    {
        $time = time() * 1000 - 30000; //time in milliseconds (or close enough)
        $data = base64_encode($apiKey . $this->getMeetingID() . $time . $role);
        $hash = hash_hmac('sha256', $data, $apiSecret, true);
        $_sig = $apiKey . "." . $this->getMeetingID() . "." . $time . "." . $role . "." . base64_encode($hash);
        //return signature, url safe base64 encoded
        return rtrim(strtr(base64_encode($_sig), '+/', '-_'), '=');
    }

    public function getLogoutUrl()
    {
        return $this->zoomAPI->getLogoutUrl();
    }

    public function getRoom($id_room)
    {
    }

    /**
     * Get the value of meetingID
     */
    public function getMeetingID()
    {
        return $this->meetingID;
    }

    /**
     * Set the value of meetingID
     *
     * @return  self
     */
    public function setMeetingID($meetingID)
    {
        $this->meetingID = $meetingID;

        return $this;
    }

    /**
     * Get the value of meetingPWD
     */
    public function getMeetingPWD()
    {
        return $this->meetingPWD;
    }

    /**
     * Set the value of meetingPWD
     *
     * @return  self
     */
    public function setMeetingPWD($meetingPWD)
    {
        $this->meetingPWD = $meetingPWD;

        return $this;
    }
}
