<?php
/**
 * output_funcs.inc.php file
 * 
 * @package		view
 * @author		Stefano Penge <steve@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link				
 * @version		0.1
 */
/**
 * function translateFN: used to handle message translations
 * based on user language
 *
 * @param string $message - the message to be translated
 * @param string $language_from
 * @param string $language_to 2 char string
 * @return string the translated message, if a translation was found, the original message otherwise
 */
function translateFN($message, $language_from=null, $language_to=null) {

	if (is_null($language_to)) {
		$sess_userObj = $_SESSION['sess_userObj'];
		$languageId = $sess_userObj->getLanguage();
	} else {
		$languageId = $language_to;
	}

    if($languageId != 0) {
    $languageInfo = Translator::getLanguageInfoForLanguageId($languageId);
    $user_language_code = $languageInfo['codice_lingua'];
    } else {
      if (!isset($_SESSION['sess_user_language'])) {
          $user_language_code = ADA_LOGIN_PAGE_DEFAULT_LANGUAGE;
      }
      else {
          $user_language_code = $_SESSION['sess_user_language'];
      }
    }

    return Translator::translate($message, $user_language_code);
}