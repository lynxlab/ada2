<?php

/**
 * ADA collabora node attachments widget
 *
 * @package		module/collaboraacl
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		giorgio <g.consorti@lynxlab.com>
 *
 * @copyright	Copyright (c) 2020, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link 		widget
 * @version		0.1
 *
 * supported params you can pass either via XML or php array:
 *
 *  name="courseId"         optional,  value: course id from which to load the attachments
 *  name="courseInstanceId" optional,  value: course instance id from which to load the attachments
 *  name="nodeId"           mandatory,  value: node id from which to load the attachments
 *	name="userId"           mandatory, value: user id from which to load the attachments
 */

use Lynxlab\ADA\Module\CollaboraACL\CollaboraACLActions;
use Lynxlab\ADA\Module\CollaboraACL\CollaboraACLException;
use Lynxlab\ADA\Module\CollaboraACL\FileACL;

/**
 * Common initializations and include files
 */
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once realpath(dirname(__FILE__)) . '/../../config_path.inc.php';
require_once ROOT_DIR . '/widgets/include/widget_includes.inc.php';

/**
 * Users (types) allowed to access this module.
 */
list($allowedUsersAr, $neededObjAr) = array_values(CollaboraACLActions::getAllowedAndNeededAr());
$trackPageToNavigationHistory = false;
require_once ROOT_DIR . '/include/module_init.inc.php';
include_once ROOT_DIR . '/browsing/include/browsing_functions.inc.php';
extract(BrowsingHelper::init($neededObjAr));

function cleanFilename($complete_file_name)
{
    $filename = $complete_file_name;
    $filenameAr = explode('_', $complete_file_name);
    $stop = count($filenameAr) - 1;
    // $course_instance = isset($filenameAr[0]) ? $filenameAr[0] : null;
    $id_sender  = isset($filenameAr[1]) ? $filenameAr[1] : null;
    // $id_course = isset($filenameAr[2]) ? $filenameAr[2] : null;
    if (is_numeric($id_sender)) {
        // $id_node =  $filenameAr[2]."_".$filenameAr[3];
        $filename = '';
        for ($k = 5; $k <= $stop; $k++) {
            $filename .=  $filenameAr[$k];
            if ($k < $stop)
                $filename .= "_";
        }
    }
    return $filename;
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    // session_write_close();
    extract($_GET);
    if (!isset($widgetMode)) $widgetMode = ADA_WIDGET_ASYNC_MODE;
    /**
     * checks and inits to be done if this has been called in async mode
     * (i.e. with a get request)
     */
    if (isset($_SERVER['HTTP_REFERER'])) {
        if (
            $widgetMode != ADA_WIDGET_SYNC_MODE &&
            preg_match("#^" . trim(HTTP_ROOT_DIR, "/") . "($|/.*)#", $_SERVER['HTTP_REFERER']) != 1
        ) {
            die('Only local execution allowed.');
        }
    }
    /**
     * Your code starts here
     */
    try {
        if (!isset($userId)) throw new CollaboraACLException(translateFN("Specificare un id studente"));
        if (!isset($nodeId)) throw new CollaboraACLException(translateFN("Specificare un id nodo"));
        if (!isset($courseId)) {
            $courseId = $_SESSION['sess_id_course'];
            $courseObj = $_SESSION['sess_courseObj'];
        }
        if (!isset($courseInstanceId) || (isset($courseInstanceId) && $courseInstanceId < 0)) {
            $courseInstanceId = $_SESSION['sess_id_course_instance'];
        }

        /**
         * get the correct testername
         */
        if (!MULTIPROVIDER) {
            if (isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider'])) {
                $testerName = $GLOBALS['user_provider'];
            } else {
                throw new CollaboraACLException(translateFN('Nessun fornitore di servizi &egrave; stato configurato'));
            }
        } else if (isset($courseId)) {
            $testerInfo = $GLOBALS['common_dh']->get_tester_info_from_id_course($courseId);
            if (!AMA_DB::isError($testerInfo) && is_array($testerInfo) && isset($testerInfo['puntatore'])) {
                $testerName = $testerInfo['puntatore'];
            }
        } // end if (!MULTIPROVIDER)

        if (!isset($testerName)) throw new CollaboraACLException(translateFN('Spiacente, non so a che fornitore di servizi sei collegato'));

        if (defined('MODULES_COLLABORAACL') && MODULES_COLLABORAACL) {
            $aclDH = \Lynxlab\ADA\Module\CollaboraACL\AMACollaboraACLDataHandler::instance(\MultiPort::getDSN($testerName));

            if (!isset($courseObj)) {
                $courseObj = new Course($courseId);
            }

            if (!($courseObj instanceof \Course)) {
                throw new CollaboraACLException(translateFN('Impossibile caricare il corso'));
            }

            if ($courseObj->media_path != "") {
                $media_path = $courseObj->media_path;
            } else {
                $media_path = MEDIA_PATH_DEFAULT . $courseObj->id_autore;
            }
            $download_path = ROOT_DIR . $media_path;

            $elencofile = leggidir($download_path);
            $outputArr = [];
            if (is_array($elencofile) && count($elencofile) > 0) {
                $filesACL = $aclDH->findBy('FileACL', ['id_corso' => $courseId, 'id_istanza' => $courseInstanceId, 'id_nodo' => $nodeId]);
                if ($userObj->getType() != AMA_TYPE_TUTOR) {
                    $elencofile = array_filter($elencofile, function ($fileel) use ($filesACL, $userObj) {
                        $elPath = str_replace(ROOT_DIR . DIRECTORY_SEPARATOR, '', $fileel['path_to_file']);
                        return FileACL::isAllowed($filesACL, $userObj->getId(), $elPath, CollaboraACLActions::READ_FILE);
                    });
                }
                $elencofile = array_filter($elencofile, function ($singleFile) use ($nodeId, $courseInstanceId) {
                    $filenameAr = explode('_', $singleFile['file']);
                    $file_courseInstanceId = isset($filenameAr[0]) ? $filenameAr[0] : null;
                    $file_nodeId = null;
                    if (isset($filenameAr[2]) && isset($filenameAr[3])) {
                        $file_nodeId =  $filenameAr[2] . "_" . $filenameAr[3];
                    }
                    return ($file_nodeId == $nodeId && $file_courseInstanceId == $courseInstanceId);
                });

                if (is_array($elencofile) && count($elencofile) > 0) {

                    usort($elencofile, function($a, $b) {
                        return strcasecmp(cleanFileName($a['file']), cleanFileName($b['file']));
                    });

                    $icongeneric = 'attachment';
                    $iconcls = [
                        'notset' => 'attachment',
                        _IMAGE => 'photo',
                        _SOUND => 'music',
                        _VIDEO => 'video',
                        _DOC => 'text file outline',
                    ];
                    $title = CDOMElement::create('div','class:nodeattachments title');
                    $title->addChild(CDOMElement::create('i','class:dropdown icon'));
                    $title->addChild(new CText(translateFN('Files allegati al nodo')));
                    $outputArr[] = $title;

                    $maincontent = CDOMElement::create('div','class:nodeattachments content');
                    $cont = CDOMElement::create('div', 'class:ui feed basic segment');
                    $maincontent->addChild($cont);

                    foreach ($elencofile as $singleFile) {
                        $filenameAr = explode('_', $singleFile['file']);
                        if (isset($filenameAr[4]) && array_key_exists($filenameAr[4], $iconcls)) {
                            $icon = $iconcls[$filenameAr[4]];
                        } else {
                            $icon = $icongeneric;
                        }
                        $event = CDOMElement::create('div', 'class:event');
                        $cont->addChild($event);
                        $label = CDOMElement::create('div', 'class:label');
                        $label->addChild(CDOMElement::create('i', 'class:icon ' . $icon));
                        $event->addChild($label);
                        $content = CDOMElement::create('div', 'class:content');
                        $event->addChild($content);
                        $date = CDOMElement::create('div', 'class:date');
                        $content->addChild($date);
                        $date->addChild(new CText($singleFile['data']));
                        $summary = CDOMElement::create('div', 'class:summary');
                        $content->addChild($summary);
                        $link = CDOMElement::create('a', 'target:_blank,href:download.php?file=' . $singleFile['file']);
                        $link->addChild(new CText(cleanFileName($singleFile['file'])));
                        $summary->addChild($link);
                    }
                    $outputArr[] = $maincontent;
                }
            }
            $output = implode(PHP_EOL, array_map(function ($el) {
                return $el->getHtml();
            }, $outputArr));
        }
    } catch (CollaboraACLException $e) {
        $divClass = 'error';
        $divMessage = basename($_SERVER['PHP_SELF']) . ': ' . $e->getMessage();
        $outDIV = \CDOMElement::create('div', "class:ui $divClass message");
        $closeIcon = \CDOMElement::create('i', 'class:close icon');
        $closeIcon->setAttribute('onclick', 'javascript:$j(this).parents(\'.ui.message\').remove();');
        $outDIV->addChild($closeIcon);
        $errorSpan = \CDOMElement::create('span');
        $errorSpan->addChild(new \CText($divMessage));
        $outDIV->addChild($errorSpan);
        $output = $outDIV->getHtml();
    }

    if (!isset($output)) $output = '';
    /**
     * Common output in sync or async mode
     */
    switch ($widgetMode) {
        case ADA_WIDGET_SYNC_MODE:
            return $output;
            break;
        case ADA_WIDGET_ASYNC_MODE:
        default:
            die($output);
    }
}
