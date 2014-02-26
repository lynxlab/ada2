<?php
/**
 * RSS Feed reader
 * uses: simplepie.inc.php
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
	/**
	 * checks and inits to be done if this has been called in async mode
	 * (i.e. with a get request)
	 */
	if(isset($_SERVER['HTTP_REFERER'])){
		if(preg_match("#^".HTTP_ROOT_DIR."($|/.*)#", $_SERVER['HTTP_REFERER']) != 1){
			die ('Only local execution allowed.');
		}
	}
	extract ($_GET);
}

$widgetMode = ADA_WIDGET_SYNC_MODE;

/**
 * Your code starts here
 */

if (!isset($course_id) || intval($course_id)<=0) $course_id = PUBLIC_COURSE_ID_FOR_NEWS;
if (!isset($showDescription) || !is_numeric($showDescription)) $showDescription=0;
if (!isset($count) || !is_numeric($count)) $count=NEWS_COUNT;

/**
 * get the correct testername
 */
if (!MULTIPROVIDER)
{
	if (isset($GLOBALS['user_provider']) && !empty($GLOBALS['user_provider']))
	{
		$testerName = $GLOBALS['user_provider'];
	} else {
		$errsmsg = translateFN ('Nessun fornitore di servizi &egrave; stato configurato');
	}
} else  {
	$testers = $_SESSION['sess_userObj']->getTesters();
	$testerName = $testers[0];
} // end if (!MULTIPROVIDER)

if (isset($testerName))
{
	$tester_dh = AMA_DataHandler::instance(MultiPort::getDSN($testerName));
	// select nome or empty string (whoever is not null) as title to diplay for the news
	$newscontent = $tester_dh->find_course_nodes_list(
			array ( "COALESCE(if(nome='NULL' OR ISNULL(nome ),NULL, nome), '')", "testo" ) ,
			"1 ORDER BY data_creazione DESC LIMIT ".$count,
			$course_id);
	
	// watch out: $newscontent is NOT associative
	$output = '';
	$maxLength = 600;
	if (!AMA_DB::isError($newscontent) && count($newscontent)>0)
	{
		foreach ( $newscontent as $num=>$aNews )
		{
			$aNewsDIV = CDOMElement::create('div','class:news,id:news-'.($num+1));
			$aNewsTitle = CDOMElement::create('a', 'class:newstitle,href:'.HTTP_ROOT_DIR.'/browsing/view.php?id_course='.
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

			foreach ($removeIds as $removeId)
			{
				$removeElement = $html->getElementById($removeId);
				if (!is_null($removeElement)) $removeElement->parentNode->removeChild($removeElement);
			}
				
			// output in newstext only the <body> of the generated html
			if ($showDescription) {
				$newstext = '';
				foreach ($html->getElementsByTagName('body')->item(0)->childNodes as $child)
				{
					$newstext .= $html->saveXML($child);
				}
				// strip off html tags
				$newstext = strip_tags($newstext);
				// check if content is too long...
				if (strlen($newstext) > $maxLength)
				{
					// cut the content to the first $maxLength characters of words (the $ in the regexp does the trick)
					$newstext = preg_replace('/\s+?(\S+)?$/', '', substr($newstext, 0, $maxLength+1));
					$addContinueLink = true;
				}
				else $addContinueLink = false;
	
				$aNewsDIV->addChild (new CText("<p class='newscontent'>".$newstext.'</p>'));
			}

			if ($addContinueLink)
			{
				$contLink = CDOMElement::create('a', 'class:continuelink,href:'.HTTP_ROOT_DIR.'/browsing/view.php?id_course='.
						$course_id.'&id_node='.$aNews[0]);
				$contLink->addChild (new CText(translateFN('Continua...')));
				$aNewsDIV->addChild ($contLink);
			}
			$output .= $aNewsDIV->getHtml();
		}
	} else $output = translateFN('Spiacente, non ci sono corsi che hanno l\'id richiesto');
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
