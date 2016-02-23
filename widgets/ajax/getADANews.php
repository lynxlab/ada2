<?php
/**
 * ADA last course's node reader
 * will dysplay the newest nodes content from the passed course id
 *
 * @package		widget
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		giorgio <g.consorti@lynxlab.com>
 *
 * @copyright	Copyright (c) 2013, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link 		widget
 * @version		0.1
 *
 * supported params you can pass either via XML or php array:
 *
 *  name="course_id"	   optional,  value: course id from which to load the news
 *                                           if invalid or omitted, PUBLIC_COURSE_ID_FOR_NEWS is used
 *  name="showDescription" optional,  value: shows or hides the post description. values: 0 or nonzero
 *                                           if invalid or omitted, description will be hidden
 *	name="count"		   optional,  value: how many news to display
 *                                           if invalid or omitted NEWS_COUNT entries are displayed
 *
 *  NOTE: THIS WIDGET WORKS ONLY IN SYNC MODE FOR SESSION SETTING PROBLEMS!
 *  	  async XML param is IGNORED
 */

/**
 * Common initializations and include files
 */
ini_set('display_errors', '0'); error_reporting(E_ALL);

require_once realpath(dirname(__FILE__)).'/../../config_path.inc.php';
require_once ROOT_DIR.'/widgets/include/widget_includes.inc.php';

/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_STUDENT,AMA_TYPE_TUTOR, AMA_TYPE_AUTHOR, AMA_TYPE_ADMIN);
require_once ROOT_DIR.'/include/module_init.inc.php';

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {

	extract ($_GET);
	if (!isset($widgetMode)) $widgetMode = ADA_WIDGET_ASYNC_MODE;

	/**
	 * checks and inits to be done if this has been called in async mode
	 * (i.e. with a get request)
	 */
	if(isset($_SERVER['HTTP_REFERER'])){
		if($widgetMode!=ADA_WIDGET_SYNC_MODE &&
			preg_match("#^".HTTP_ROOT_DIR."($|/.*)#", $_SERVER['HTTP_REFERER']) != 1){
			die ('Only local execution allowed.');
		}
	}

}

/**
 * Your code starts here
 */

if (!isset($course_id) || intval($course_id)<=0) $course_id = PUBLIC_COURSE_ID_FOR_NEWS;
if (!isset($showDescription) || !is_numeric($showDescription)) $showDescription=0;
if (!isset($count) || !is_numeric($count)) $count=NEWS_COUNT;

/**
 * get the correct testername
 */
if (!MULTIPROVIDER) {
	if (isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider'])) {
		$testerName = $GLOBALS['user_provider'];
	} else {
		$errsmsg = translateFN ('Nessun fornitore di servizi &egrave; stato configurato');
	}
} else {
	$testerInfo = $GLOBALS['common_dh']->get_tester_info_from_id_course($course_id);
	if (!AMA_DB::isError($testerInfo) && is_array($testerInfo) && isset($testerInfo['puntatore'])) {
		$testerName = $testerInfo['puntatore'];
	}
} // end if (!MULTIPROVIDER)

if (isset($testerName)) {
	$tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($testerName));
	// setting of the global is needed to load the course object
	$GLOBALS['dh'] = $tester_dh;

	// load course
	$courseObj = new Course($course_id);
	$courseOK = false;
	if ($courseObj instanceof Course && $courseObj->isFull()) {
		// it it's public, go on and show contents
		$courseOK = $courseObj->getIsPublic();
		if (!$courseOK && isset($_SESSION['sess_userObj']) && $_SESSION['sess_userObj'] instanceof ADALoggableUser) {
			// if it's not public, check if user is subscribed to course
			$instanceCheck = $tester_dh->get_course_instance_for_this_student_and_course_model($_SESSION['sess_userObj']->getId(),$courseObj->getId(), true);
			if (!AMA_DB::isError($instanceCheck) && is_array($instanceCheck) && count($instanceCheck)>0) {
				$goodStatuses = array(ADA_STATUS_SUBSCRIBED, ADA_STATUS_COMPLETED, ADA_STATUS_TERMINATED);
				$instance = reset($instanceCheck);
				do {
					$courseOK = in_array($instance['status'], $goodStatuses);
				} while ((($instance = next($instanceCheck))!== false) && !$courseOK);
			}
		}
	}
	// courseOK is true either if course is public or the user is subscribed to it
	if ($courseOK) {
		// select nome or empty string (whoever is not null) as title to diplay for the news
		$newscontent = $tester_dh->find_course_nodes_list(
				array ( "COALESCE(if(nome='NULL' OR ISNULL(nome ),NULL, nome), '')", "testo" ) ,
				"tipo IN (". ADA_LEAF_TYPE .",". ADA_GROUP_TYPE .") ORDER BY data_creazione DESC LIMIT ".$count,
				$course_id);

		// watch out: $newscontent is NOT associative
		$output = '';
		$maxLength = 600;
		if (!AMA_DB::isError($newscontent) && count($newscontent)>0) {
			$newsContainer = CDOMElement::create('div','class:ui three column divided grid');
			$newsContainer->setAttribute('data-courseID', $course_id);
			$newsRow = CDOMElement::create('div','class:equal height row');
			$continueRow = CDOMElement::create('div','class:continuelink row');
			$newsContainer->addChild($newsRow);
			$newsContainer->addChild($continueRow);

			foreach ( $newscontent as $num=>$aNews ) {
				$aNewsDIV = CDOMElement::create('div','class:column news,id:news-'.($num+1));
				$newsRow->addChild($aNewsDIV);
				$aNewsTitle = CDOMElement::create('a', 'class:newstitle ui header,href:'.HTTP_ROOT_DIR.'/browsing/view.php?id_course='.
						$course_id.'&id_node='.$aNews[0]);
				$aNewsTitle->addChild (new CText($aNews[1]));
				$aNewsDIV->addChild ($aNewsTitle);

				// @author giorgio 01/ott/2013
				// remove unwanted div ids: tabs
				// NOTE: slider MUST be removed BEFORE tabs because tabs can contain slider and not viceversa
				$removeIds = array ('slider','tabs');

				$html = new DOMDocument('1.0', ADA_CHARSET);
				/**
				 * HTML uses the ISO-8859-1 encoding (ISO Latin Alphabet No. 1) as default per it's specs.
				 * So add a meta the should do the encoding hint, and output some PHP warings as well that
				 * are being suppressed with the @
				 */
				@$html->loadHTML('<meta http-equiv="content-type" content="text/html; charset='.ADA_CHARSET.'">'.$aNews[2]);

				foreach ($removeIds as $removeId) {
					$removeElement = $html->getElementById($removeId);
					if (!is_null($removeElement)) $removeElement->parentNode->removeChild($removeElement);
				}

				// output in newstext only the <body> of the generated html
				if ($showDescription) {
					$newstext = '';
					foreach ($html->getElementsByTagName('body')->item(0)->childNodes as $child) {
						$newstext .= $html->saveXML($child);
					}
					// strip off html tags
					$newstext = strip_tags($newstext);
					// check if content is too long...
					if (strlen($newstext) > $maxLength) {
						// cut the content to the first $maxLength characters of words (the $ in the regexp does the trick)
						$newstext = preg_replace('/\s+?(\S+)?$/', '', substr($newstext, 0, $maxLength+1));
						$addContinueLink = true;
					}
					else $addContinueLink = false;

					$aNewsDIV->addChild (new CText("<p class='newscontent'>".$newstext.'</p>'));
				}

				if ($addContinueLink) {
					$contLink = CDOMElement::create('a', 'class:column continuelink,href:'.HTTP_ROOT_DIR.'/browsing/view.php?id_course='.
							$course_id.'&id_node='.$aNews[0]);
					$contLink->addChild (new CText(translateFN('Continua...')));
					$continueRow->addChild ($contLink);
				} else {
					$continueRow->addChild(CDOMElement::create('span','class:column'));
				}
				// $output .= $aNewsDIV->getHtml();
			}
			$output = $newsContainer->getHtml();
		} else $output = translateFN('Spiacente, non ci sono corsi che hanno l\'id richiesto');
	} else $output = translateFN('Corso non valido o utente non iscritto al corso specificato');
}  else $output = translateFN('Spiacente, non so a che fornitore di servizi sei collegato');

/**
 * Common output in sync or async mode
 */
 switch ($widgetMode) {
		case ADA_WIDGET_SYNC_MODE:
			return $output;
			break;
		case ADA_WIDGET_ASYNC_MODE:
		default:
			echo $output;

}
?>
