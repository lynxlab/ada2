<?php

/**
 * @package 	ADA BigBlueButton Integration
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

namespace Lynxlab\ADA\Module\BBBIntegration;

class ADABBBApi extends \BigBlueButton\BigBlueButton
{
    /**
     * @var AMABBBIntegrationDataHandler
     */
    private $dh;

    public function __construct($tester = null)
    {
        if (isset($GLOBALS['dh']) && $GLOBALS['dh'] instanceof AMABBBIntegrationDataHandler) {
            $this->dh = $GLOBALS['dh'];
        } else {
            if (is_null($tester)) {
                if (array_key_exists('sess_selected_tester', $_SESSION)) {
                    $tester = $_SESSION['sess_selected_tester'];
                } else if (!MULTIPROVIDER && isset($GLOBALS['user_provider']) && strlen($GLOBALS['user_provider']) > 0) {
                    $tester = $GLOBALS['user_provider'];
                }
            }
            $this->dh = AMABBBIntegrationDataHandler::instance(\MultiPort::getDSN($tester));
        }

        if (defined('BBB_SERVER_BASE_URL') && strlen('BBB_SERVER_BASE_URL') > 0) {
            putenv('BBB_SERVER_BASE_URL=' . BBB_SERVER_BASE_URL);
        }
        if (defined('BBB_SECRET') && strlen('BBB_SECRET') > 0) {
            putenv('BBB_SECRET=' . BBB_SECRET);
        }
        parent::__construct();
    }

    public function create($meetingData)
    {
        try {
            $meetingData = $this->dh->add_videoroom($meetingData);

            $createParams = new \BigBlueButton\Parameters\CreateMeetingParameters(
                $meetingData['meetingID']->toString(),
                $meetingData['room_name']
            );
            $createParams->setAttendeePassword($meetingData['attendeePW']->toString())
                ->setModeratorPassword($meetingData['moderatorPW']->toString())
                ->setRecord(true);
            $this->createMeeting($createParams);
            return $meetingData;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getInfo($roomId) {
        try {
            // load meetingID and passwords from the DB
            $meetingData = $this->dh->getInfo($roomId);
            // check if the meetingID is still at the BBB server
            $meetingParams = new \BigBlueButton\Parameters\GetMeetingInfoParameters(
                isset($meetingData['meetingID']) ? $meetingData['meetingID'] : null,
                isset($meetingData['attendeePW']) ? $meetingData['attendeePW'] : null
            );
            $serverData = $this->getMeetingInfo($meetingParams);
            if ($serverData->failed()) {
                $meetingData['meetingID'] = null;
                if (isset($meetingData['moderatorPW'])) unset($meetingData['moderatorPW']);
                if (isset($meetingData['attendeePW'])) unset($meetingData['attendeePW']);
                $this->dh->delete_videoroom($roomId);
            }
            return $meetingData;
        } catch (\Exception $e) {
            return [];
        }
    }
}
