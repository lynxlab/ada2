<?php
/**
 * Add exercise
 *
 * @package
 * @author		Valerio Riva <valerio@lynxlab.com>
 * @copyright	Copyright (c) 2012, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array();

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_TUTOR);
/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
        AMA_TYPE_TUTOR => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
//require_once(ROOT_DIR.'/include/HtmlLibrary/ServicesModuleHtmlLib.inc.php');

require_once(MODULES_TEST_PATH.'/include/init.inc.php');
//needed to promote AMADataHandler to AMATestDataHandler. $sess_selected_tester is already present in session
$GLOBALS['dh'] = AMATestDataHandler::instance(MultiPort::getDSN($_SESSION['sess_selected_tester']));

switch($_GET['mode']) {
	default:
		$res = false;
	break;
	case 'comment':
		$res = $dh->test_updateAnswer($_GET['id_answer'],array('commento'=>$_POST['comment']));
		if (!$dh->isError($res) && $res && $_POST['notify'] == true) {
			$answer = $dh->test_getAnswer($_GET['id_answer']);
			$answer = $answer[0];
			$history = $dh->test_getHistoryTest(array('id_history_test'=>$answer['id_history_test']));
			$history = $history[0];
			$test = $dh->test_getNode($history['id_nodo']);
			$studentObj = read_user_from_DB($answer['id_utente']);
			if (!$dh->isError($studentObj) && !$dh->isError($answer) && !$dh->isError($history) && !$dh->isError($test)) {
				$what = '';
				$link = '';

				if ($test['tipo']{0} == ADA_TYPE_TEST) {
					$what = 'test';
					$name = 'test';
				}
				else if ($test['tipo']{0} == ADA_TYPE_SURVEY) {
					$what = 'survey';
					$name = 'sondaggio';
				}
				$link = '';
				if ($what) {
					$href = MODULES_TEST_HTTP.'/history.php?op='.$what.'&id_course='.$answer['id_corso'].'&id_course_instance='.$answer['id_istanza_corso'].'&id_test='.$test['id_nodo'].'&id_history_test='.$answer['id_history_test'];
					$link = '<a href="'.$href.'">'.translateFN('Visualizza').' '.translateFN($name).'</a>';
				}

				$titolo = sprintf(translateFN('Messaggio dal tutor sul %s:'),translateFN($name)).' '.$test['titolo'];
				$testo = $_POST['comment'];
				$testo.= '<br /><br />'.$link;

				$message_ha = array(
					'destinatari' => $studentObj->getUserName(),
					'data_ora' => 'now',
					'tipo' => ADA_MSG_SIMPLE,
					'mittente' => $_SESSION['sess_userObj']->getUserName(),
					'titolo' => $titolo,
					'testo' => $testo,
					'priorita' => 2
				);
				require_once(ROOT_DIR.'/comunica/include/MessageHandler.inc.php');
				$mh = MessageHandler::instance(MultiPort::getDSN($sess_selected_tester));
				$result = $mh->send_message($message_ha);
			}
		}
	break;
	case 'answer':
		$res = $dh->test_updateAnswer($_GET['id_answer'],array('correzione_risposta'=>$_POST['answer']));
	break;
	case 'points':
		$res = $dh->test_updateAnswer($_GET['id_answer'],array('punteggio'=>$_GET['points']));
	break;
	case 'repeatable':
		$res = ($dh->test_setHistoryTestRepeatable($_GET['id_history_test'],$_GET['repeatable'])
			&& $dh->test_recalculateHistoryTestPoints($_GET['id_history_test']));
	break;
}

if (!$dh->isError($res) && $res) {
	echo 1;
}
else {
	echo 0;
}