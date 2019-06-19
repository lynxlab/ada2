<?php
use Lynxlab\ADA\Module\ForkedPaths\AMAForkedPathsDataHandler;
use Dompdf\Exception;
use Lynxlab\ADA\Module\ForkedPaths\ForkedPathsNode;
use Lynxlab\ADA\Module\ForkedPaths\ForkedPathsException;

/**
 * Base config file
 */
require_once (realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');
require_once MODULES_GDPR_PATH . '/config/config.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_STUDENT, AMA_TYPE_TUTOR);

/**
 * Get needed objects
 */
$neededObjAr = array(
    AMA_TYPE_STUDENT => array('node', 'layout', 'tutor', 'course', 'course_instance'),
    AMA_TYPE_TUTOR => array('node', 'layout', 'course', 'course_instance'),
);

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
BrowsingHelper::init($neededObjAr);

$GLOBALS['dh'] = AMAForkedPathsDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));
$postParams = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

$data = new stdClass();
$data->title = '<i class="basic error icon"></i>'.translateFN('Errore percorso a bivi');
$data->status = 'ERROR';
$data->message = translateFN('Errore sconosciuto');

$error = true;

if (array_key_exists('fromId', $postParams) && array_key_exists('toId', $postParams)) {

    try {
        if (!ForkedPathsNode::checkNode($fromNode = new \Node($postParams['fromId']))) {
            throw new ForkedPathsException(translateFN('Nodo di partenza non è storia a bivi'));
        }

        if (!in_array($postParams['toId'], $fromNode->children)) {
            throw new ForkedPathsException(translateFN('Nodo di destinazione non è figlio di quello di partenza'));
        }

        if (!ForkedPathsNode::checkNode($toNode = new \Node($postParams['toId']))) {
            throw new ForkedPathsException(translateFN('Nodo di destinazione non è storia a bivi'));
        }

        $userLevel = $userObj->get_student_level($userObj->getId(), $courseInstanceObj->getId());

        if ((int) $toNode->level > (int) $userLevel) {
            if (AMA_DataHandler::isError($GLOBALS['dh']->set_student_level($courseInstanceObj->getId(), [$userObj->getId()], $toNode->level))) {
                throw new ForkedPathsException(translateFN("Errore nell'aggiornamento del livello utente"));
            }
        }

        $result = $GLOBALS['dh']->saveForkedPathHistory([
            'nodeFrom' => $fromNode->id,
            'nodeTo' => $toNode->id,
            'userLevelFrom' => $userLevel,
            'userLevelTo' => max($toNode->level, $userLevel),
            'userId' => $userObj->getId(),
            'courseInstanceId' => $courseInstanceObj->getId()
        ]);

    } catch (\Exception $e) {
        $result = $e;
    }
}

$error = AMA_DB::isError($result) || $result instanceof \Exception;

if ($error === true) {
    header(' ', true, 400);
    if ($result instanceof \Exception) {
            /* @var \Exception $result */
            $data->title .= ' ('.$result->getCode().')';
            $data->message = $result->getMessage();
            $data->errorTrace = $result->getTraceAsString();
    }
} else {
    $data = new stdClass();
    $data->status = 'OK';
    $data->redirectTo = $toNode->id;
}

header('Content-Type: application/json');
die (json_encode($data));