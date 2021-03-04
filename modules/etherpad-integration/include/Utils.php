<?php

/**
 * @package     Etherpad module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2021, Lynx s.r.l.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

namespace Lynxlab\ADA\Module\EtherpadIntegration;

use DataValidator;

class Utils
{
    /**
     * build the etherpad url, with or without the api endpoint
     *
     * @param boolean $withApi
     * @return string
     */
    public static function getEtherpadURL($withApi = true)
    {
        $url = rtrim(MODULES_ETHERPAD_HOST, '/');
        if (defined('MODULES_ETHERPAD_PORT') && strlen(MODULES_ETHERPAD_PORT) > 0) {
            $url .= ':' . MODULES_ETHERPAD_PORT;
        }
        if ($withApi && defined('MODULES_ETHERPAD_APIBASEURL') && strlen(MODULES_ETHERPAD_APIBASEURL) > 0) {
            $url .= '/' . trim(MODULES_ETHERPAD_APIBASEURL, '/');
        }
        return $url;
    }

    /**
     * method called by the menu items to check if they're enabled
     *
     * @param array $params
     * @return boolean
     */
    public static function enableSharedDocMenuItem($params = [])
    {
        try {
            $enabled = false;
            $nodeId = false;
            if (array_key_exists('sess_selected_tester', $_SESSION) && array_key_exists('sess_id_course_instance', $_SESSION)) {
                $etDH = AMAEtherpadDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));
                if (EtherpadActions::canDo(EtherpadActions::ACCESS_PAD)) {
                    $hasPad = false;
                    // load getherpad group mapped to the session course instance
                    $group = $etDH->findOneBy('Groups', [
                        'instanceId' => intval($_SESSION['sess_id_course_instance']),
                        'isActive' => true,
                    ]);
                    if ($group instanceof Groups) {
                        /**
                         * this method must be called by a a menu item,
                         * it should be safe to assume that $params is [ 'nodeId' => id_node|Pads::instancePadId ]
                         */
                        if (array_key_exists('nodeId', $params)) {
                            $nodeId = ($params['nodeId'] === Pads::instancePadId)  ? Pads::instancePadId : DataValidator::validate_node_id($params['nodeId']);
                        } else if (array_key_exists('sess_id_node', $_SESSION)) {
                            // if no $params['nodeId'] then use session node
                            $nodeId = $_SESSION['sess_id_node'];
                        }
                        $pad = $etDH->findOneBy('Pads', [
                            'groupId' => $group->getGroupId(),
                            'nodeId' => $nodeId,
                            'isActive' => true,
                        ]);
                        $hasPad = $pad instanceof Pads;
                    }
                }
            }
            if ($hasPad || (!$hasPad && EtherpadActions::canDo(EtherpadActions::CREATE_PAD))) {
                $enabled = $nodeId === Pads::instancePadId ? MODULES_ETHERPAD_INSTANCEPAD : MODULES_ETHERPAD_NODEPAD;
            }
            return $enabled;
        } catch (\Exception $e) {
            return false;
        }
    }
}
