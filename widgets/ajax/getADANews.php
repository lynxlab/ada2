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
 *	name="orderby"		   optional,  value: orderby section of the query string
 *                                           if invalid or omitted its value is 'data_creazione' DESC
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
$trackPageToNavigationHistory = false;
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
			preg_match("#^".trim(HTTP_ROOT_DIR,"/")."($|/.*)#", $_SERVER['HTTP_REFERER']) != 1){
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
if (!isset($orderby) || strlen($orderby)<=0) $orderby = 'data_creazione DESC';

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
				"tipo IN (". ADA_LEAF_TYPE .",". ADA_GROUP_TYPE .") ORDER BY $orderby LIMIT ".$count,
				$course_id);

		// watch out: $newscontent is NOT associative
		$output = '';
		$maxLength = 600;
		if (!AMA_DB::isError($newscontent) && count($newscontent)>0) {
			$newsContainer = CDOMElement::create('div','class:ui three column divided stackable grid');
			$newsContainer->setAttribute('data-courseID', $course_id);
			$newsRow = CDOMElement::create('div','class:equal height row');
			$newsContainer->addChild($newsRow);

			foreach ( $newscontent as $num=>$aNews ) {

				// @author giorgio 01/ott/2013
				// remove unwanted div ids: tabs
				// NOTE: slider MUST be removed BEFORE tabs because tabs can contain slider and not viceversa
				$removeIds = array ('slider','tabs');

				if (strlen(trim($aNews[2]))>0) {
					$aNewsDIV = CDOMElement::create('div','class:column news,id:news-'.($num+1));
					$newsRow->addChild($aNewsDIV);
					$aNewsTitle = CDOMElement::create('a', 'class:newstitle ui header,href:'.HTTP_ROOT_DIR.'/browsing/view.php?id_course='.
							$course_id.'&id_node='.$aNews[0]);
					$aNewsTitle->addChild (new CText($aNews[1]));
					$aNewsDIV->addChild ($aNewsTitle);
					$html = new DOMDocument('1.0', ADA_CHARSET);
					/**
					 * HTML uses the ISO-8859-1 encoding (ISO Latin Alphabet No. 1) as default per it's specs.
					 * So add a meta the should do the encoding hint, and output some PHP warings as well that
					 * are being suppressed with the @
					 */
					@$html->loadHTML('<meta http-equiv="content-type" content="text/html; charset='.ADA_CHARSET.'">'.trim($aNews[2]));

					foreach ($removeIds as $removeId) {
						$removeElement = $html->getElementById($removeId);
						if (!is_null($removeElement)) $removeElement->parentNode->removeChild($removeElement);
					}

					// output in newstext only the <body> of the generated html
					$addContinueLink = false;
					if ($showDescription) {
						$newstext = '';
						foreach ($html->getElementsByTagName('body')->item(0)->childNodes as $child) {
							$newstext .= $html->saveXML($child);
						}
						// strip off html tags
						$newstext = strip_tags($newstext,'<p><a><br>');
						// check if content is too long...
						if (strlen(strip_tags($newstext)) > $maxLength) {
							// cut the content to the first $maxLength characters of words
							$newstext = truncateHtml($newstext, $maxLength, '');
							$addContinueLink = true;
						}

						$aNewsDIV->addChild (new CText("<p class='newscontent'>".$newstext.'</p>'));
					}

					if ($addContinueLink) {
						$contLink = CDOMElement::create('a', 'class:continuelink,href:'.HTTP_ROOT_DIR.'/browsing/view.php?id_course='.
								$course_id.'&id_node='.$aNews[0]);
						$contLink->addChild (new CText(translateFN('Continua...')));
						$aNewsDIV->addChild($contLink);
					}
					// $output .= $aNewsDIV->getHtml();
				}
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

/**
 * truncateHtml can truncate a string up to a number of characters while preserving whole words and HTML tags
 * see: https://pastebin.com/FCprUf9k probably this is coming from CakePHP
 *
 * @param string $text String to truncate.
 * @param integer $length Length of returned string, including ellipsis.
 * @param string $ending Ending to be appended to the trimmed string.
 * @param boolean $exact If false, $text will not be cut mid-word
 * @param boolean $considerHtml If true, HTML tags would be handled correctly
 *
 * @return string Trimmed string.
 */

function truncateHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
	if ($considerHtml) {
		// if the plain text is shorter than the maximum length, return the whole text
		if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
			return $text;
		}
		// splits all html-tags to scanable lines
		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
		$total_length = mb_strlen($ending);
		$open_tags = array();
		$truncate = '';
		foreach ($lines as $line_matchings) {
			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
			if (!empty($line_matchings[1])) {
				// if it's an "empty element" with or without xhtml-conform closing slash
				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
					// do nothing
					// if tag is a closing tag
				} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
					// delete tag from $open_tags list
					$pos = array_search($tag_matchings[1], $open_tags);
					if ($pos !== false) {
						unset($open_tags[$pos]);
					}
					// if tag is an opening tag
				} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
					// add tag to the beginning of $open_tags list
					array_unshift($open_tags, mb_strtolower($tag_matchings[1]));
				}
				// add html-tag to $truncate'd text
				$truncate .= $line_matchings[1];
			}
			// calculate the length of the plain text part of the line; handle entities as one character
			$content_length = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
			if ($total_length+$content_length> $length) {
				// the number of characters which are left
				$left = $length - $total_length;
				$entities_length = 0;
				// search for html entities
				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
					// calculate the real length of all entities in the legal range
					foreach ($entities[0] as $entity) {
						if ($entity[1]+1-$entities_length <= $left) {
							$left--;
							$entities_length += mb_strlen($entity[0]);
						} else {
							// no more characters left
							break;
						}
					}
				}
				$truncate .= mb_substr($line_matchings[2], 0, $left+$entities_length);
				// maximum lenght is reached, so get off the loop
				break;
			} else {
				$truncate .= $line_matchings[2];
				$total_length += $content_length;
			}
			// if the maximum length is reached, get off the loop
			if($total_length>= $length) {
				break;
			}
		}
	} else {
		if (mb_strlen($text) <= $length) {
			return $text;
		} else {
			$truncate = mb_substr($text, 0, $length - mb_strlen($ending));
		}
	}
	// if the words shouldn't be cut in the middle...
	if (!$exact) {
		// ...search the last occurance of a space...
		$spacepos = mb_strrpos($truncate, ' ');
		if (isset($spacepos)) {
			// ...and cut the text in this position
			$truncate = mb_substr($truncate, 0, $spacepos);
		}
	}
	// add the defined ending to the text
	$truncate .= $ending;
	if($considerHtml) {
		// close all unclosed html-tags
		foreach ($open_tags as $tag) {
			$truncate .= '</' . $tag . '>';
		}
	}
	return $truncate;
}
