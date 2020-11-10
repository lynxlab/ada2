<?php

/**
 * @package 	ADA Jitsi Integration
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\JitsiIntegration;

class ADAJitsiApi
{
    /**
     * @var AMAJitsiIntegrationDataHandler
     */
    private $dh;

    public function __construct($tester = null)
    {
        if (isset($GLOBALS['dh']) && $GLOBALS['dh'] instanceof AMAJitsiIntegrationDataHandler) {
            $this->dh = $GLOBALS['dh'];
        } else {
            if (is_null($tester)) {
                if (isset($_SESSION) && array_key_exists('sess_selected_tester', $_SESSION)) {
                    $tester = $_SESSION['sess_selected_tester'];
                } else if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && strlen($GLOBALS['user_provider']) > 0) {
                    $tester = $GLOBALS['user_provider'];
                }
            }
            $this->dh = AMAJitsiIntegrationDataHandler::instance(\MultiPort::getDSN($tester));
        }
    }

    public function create($meetingData)
    {
        try {
            $meetingData = $this->dh->add_videoroom($meetingData);
            return $meetingData;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getInfo($roomId) {
        try {
            // load meetingID from the DB
            $meetingData = $this->dh->getInfo($roomId);
            return $meetingData;
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getLogoutUrl() {
        return MODULES_JITSI_HTTP . '/endvideochat.php';
    }
}
