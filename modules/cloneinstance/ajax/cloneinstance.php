<?php

/**
 * @package 	cloneinstance module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2022, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

use Lynxlab\ADA\Module\CloneInstance\AMACloneInstanceDataHandler;
use Lynxlab\ADA\Module\CloneInstance\CloneInstanceActions;
use Lynxlab\ADA\Module\CloneInstance\CloneInstanceException;

/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

// MODULE's OWN IMPORTS

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Get Users (types) allowed to access this module and needed objects
 */
list($allowedUsersAr, $neededObjAr) = array_values(CloneInstanceActions::getAllowedAndNeededAr());

/**
 * Performs basic controls before entering this module
 */
require_once ROOT_DIR . '/include/module_init.inc.php';
// neededObjArr grants access to switcher only
require_once ROOT_DIR . '/switcher/include/switcher_functions.inc.php';
SwitcherHelper::init($neededObjAr);

/**
 * @var AMACloneInstanceDataHandler $dh
 */
$dh = AMACloneInstanceDataHandler::instance(\MultiPort::getDSN($_SESSION['sess_selected_tester']));

$data = new stdClass();
$data->title = '<i class="basic error icon"></i>' . translateFN('Errore clonazione');
$data->status = 'ERROR';
$data->message = translateFN("Errore durante la clonazione dell'istanza");
$data->cloneRecap = [];

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        /**
         * it's a POST, clone the instance
         */
        $postParams = filter_input_array(INPUT_POST, [
            'debugForm' => FILTER_VALIDATE_INT,
            'id_course_instance' => FILTER_VALIDATE_INT,
            'selectedCourses' => [
                'filter' => FILTER_VALIDATE_INT,
                'flags' => FILTER_REQUIRE_ARRAY,
            ],
        ]);
        $data->cloneRecap = $dh->cloneInstance($postParams['id_course_instance'], $postParams['selectedCourses']);
        $data->title = '<i class="info icon"></i>' . translateFN('Istanza clonata');
        $data->status = 'OK';
        $data->message = translateFN("L'istanza è stata clonata correttamente");
    } catch (\Exception $e) {
        header(' ', true, 400);
        $data->title .= ' (' . $e->getCode() . ')';
        $data->message = $e->getMessage();
        $data->errorMessage = $e->getCode() . PHP_EOL . $e->getMessage();
        if (array_key_exists('debugForm', $postParams) && intval($postParams['debugForm']) === 1) {
            $data->errorTrace = $e->getTraceAsString();
        }
    }
}

header('Content-Type: application/json');
die(json_encode($data));
