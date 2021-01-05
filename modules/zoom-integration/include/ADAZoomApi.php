<?php

/**
 * @package 	ADA Zoom Meeting Integration
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\ZoomIntegration;

class ADAZoomApi
{
    /**
     * @var AMAZoomIntegrationDataHandler
     */
    private $dh;

    private $zoom;

    public function __construct($tester = null)
    {
        if (isset($GLOBALS['dh']) && $GLOBALS['dh'] instanceof AMAZoomIntegrationDataHandler) {
            $this->dh = $GLOBALS['dh'];
        } else {
            if (is_null($tester)) {
                $tester = self::getTesterToUse();
            }
            $this->dh = AMAZoomIntegrationDataHandler::instance(\MultiPort::getDSN($tester));
        }

        $this->zoom = new ZoomAPIWrapper(ZOOMCONF_APIKEY, ZOOMCONF_APISECRET);
    }

    public function create($meetingData)
    {
        try {
            $meetingData['meetingPWD'] = bin2hex(random_bytes(5));
            $timezone = \MultiPort::getTesterTimeZone(self::getTesterToUse());
            $zoomuser = 'me';
            if (array_key_exists('zoomUser', $meetingData)) {
                $zoomuser = trim($meetingData['zoomUser'], ' /');
            }

            $requestBody = [
                'topic' => $meetingData['room_name'],
                'agenda' => $meetingData['room_name'],
                'type'  => 2, // scheduled meeting
                'start_time' => date_format(date_create(null, timezone_open($timezone)),"Y-m-d\TH:i:s"),
                'timezone' => $timezone,
                'duration' => 4 * 60, // four hours
                'password' => $meetingData['meetingPWD'],
                'settings' => [
                    'host_video' => true, // start video when host joins
                    'participant_video' => false, // do NOT start video when participant joins
                    'join_before_host' => false,
                    'mute_upon_entry' => true,
                    'waiting_room' => false,
                ],
            ];
            $response = $this->zoom->doRequest('POST',"/users/$zoomuser/meetings",[], [], json_encode($requestBody));

            if ($response != false) {
                $meetingData['meetingID'] = $response['id'];
                $meetingData = $this->dh->add_videoroom($meetingData);
            } else return false;

            return $meetingData;

        } catch (\Exception $e) {
            return false;
        }
    }

    public function getInfo($roomId) {
        try {
            // load meetingID and passwords from the DB
            $meetingData = $this->dh->getInfo($roomId);
            if (array_key_exists('meetingID', $meetingData) && strlen($meetingData['meetingID'])>0) {
                // check if the meetingID is still at the Zoom server
                $serverData = $this->zoom->doRequest('GET','/meetings/{meetingId}' , [], [ 'meetingId' => $meetingData['meetingID'] ]);
                if (!(is_array($serverData) && array_key_exists('id', $serverData))) {
                    $meetingData['meetingID'] = null;
                    if (isset($meetingData['meetingPWD'])) unset($meetingData['meetingPWD']);
                    // $this->dh->delete_videoroom($roomId);
                    return [];
                }
            } else {
                $meetingData = [];
            }

            return $meetingData;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getLogoutUrl() {
        return MODULES_ZOOMCONF_HTTP . '/endvideochat.php';
    }

    private static function getTesterToUse() {
        if (isset($_SESSION) && array_key_exists('sess_selected_tester', $_SESSION)) {
            return $_SESSION['sess_selected_tester'];
        } else if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && strlen($GLOBALS['user_provider']) > 0) {
            return $GLOBALS['user_provider'];
        }
        return null;
    }
}
