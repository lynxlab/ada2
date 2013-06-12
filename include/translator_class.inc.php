<?php
/**
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * class Translator: used to retrieve message translations, given a message to translate.
 */
class Translator
{
	static private $already_translated_messages;

  /**
   * function messageHash: used to calculate a unique hash to temporary store translations without access database every time.
   *
   * @param  string $message       - the message to translate
   * @param  string $user_language_code - the user language in which translate the message
   * @return string - the correspondent hash.
   */
	public static function messageHash($message,$code) {
		return md5($message.$code);
	}

  /**
   * function translate: used to obtain the translation in the given user language for the given message.
   * If no translation is found, returns the given message.
   *
   * @param  string $message       - the message to translate
   * @param  string $user_language_code - the user language in which translate the message
   * @return string - the translated message or the given message if no translation is found.
   */
  public static function translate($message, $user_language_code) {
    //ADALogger::log('Translator::translate');

    $common_dh = $GLOBALS['common_dh'];

    $language_code = $user_language_code;

	if (!empty(self::$already_translated_messages[self::messageHash($message,$language_code)])) {
		return self::$already_translated_messages[self::messageHash($message,$language_code)];
	}

    self::$already_translated_messages[self::messageHash($message,$language_code)] = $common_dh->find_message_translation($message, $language_code);
    if (AMA_DataHandler::isError($translated_message)) {
      /*
       * In case an error occurs during translation retrieval,
       * return the original message to the user.
       */
      return $message;
    }
    return self::$already_translated_messages[self::messageHash($message,$language_code)];
  }

  /**
   * function loadSupportedLanguagesInSession: used to load ADA supported languages for
   * user interface messages translation into a session variable.
   *
   * @return TRUE if there arn't errors, ADA_Error object otherwise
   */
  public static function loadSupportedLanguagesInSession() {
    unset($_SESSION['sess_ada_supported_languages']);

    $common_dh = $GLOBALS['common_dh'];

    $supported_languages = array();
    $supported_languages = $common_dh->find_languages();

    if(AMA_DataHandler::isError($supported_languages)) {
      // FIXME: qui si verifica questo errore anche quando c'è un errore di connessione al database.
      $errObj = new ADA_Error($supported_languages,
                           'No languages for user interface translation were found.',
                           'Translator');
    }

    $_SESSION['sess_ada_supported_languages'] = $supported_languages;
    return TRUE;
  }

  /**
   * function getSupportedLanguages(): returns ADA supported languages as stored in
   * the session variable sess_ada_supported_languages.
   * If this variable isn't set, return an ADA_Error.
   *
   * @return mixed - array of supported languages or ADA_Error object.
   */
  public static function getSupportedLanguages() {
    if (!isset($_SESSION['sess_ada_supported_languages'])) {
      $errObj = new ADA_Error(NULL,
                           'No languages for user interface translation were found.',
                           'Translator');
    }
    return $_SESSION['sess_ada_supported_languages'];
  }

  public static function getLanguagesIdAndName() {
    if (!isset($_SESSION['sess_ada_supported_languages'])) {
      $errObj = new ADA_Error(NULL,'lingua non trovata', 'Translator');
    }

    $l = $_SESSION['sess_ada_supported_languages'];
    $languages = array();
    foreach($l as $language) {
        $languages[$language['id_lingua']] = $language['nome_lingua'];
    }

    return $languages;
  }

  /**
   * function getLanguageCodeForLanguageName: used to obtain the ISO 639-1 code associated with
   * the user language name passed as argument.
   *
   * @param string $user_language - the user language name (e.g. 'italiano', 'english', etc...)
   *
   * @return string - the ISO 639-1 code associated with $user_language (e.g. 'it' for 'italiano', 'en' for 'english', etc...)
   */

  public static function getLanguageCodeForLanguageName($user_language) {
    if (!isset($_SESSION['sess_ada_supported_languages'])) {
      $errObj = new ADA_Error(NULL,'lingua non trovata', 'Translator');
    }

    $l = $_SESSION['sess_ada_supported_languages'];

    foreach($l as $language) {
      if($language['nome_lingua'] == $user_language) {
        return $language['codice_lingua'];
      }
    }

    $errObj = new ADA_Error(NULL,'Language code not found.', 'Translator');
  }


  /**
   * function getLanguageNameForLanguageCode: used to obtain name of the language associated to the ISO 639-1 code 
   * passed as argument.
   *
   * @param string $user_language - the user language name (e.g. 'italiano', 'english', etc...)
   *
   * @return string - the ISO 639-1 code associated with $user_language (e.g. 'it' for 'italiano', 'en' for 'english', etc...)
   */

  public static function getLanguageNameForLanguageCode($ISO_code) {
    if (!isset($_SESSION['sess_ada_supported_languages'])) {
      $errObj = new ADA_Error(NULL,'lingua non trovata', 'Translator');
    }

    $l = $_SESSION['sess_ada_supported_languages'];

    foreach($l as $language) {
      if($language['codice_lingua'] == $ISO_code) {
        return $language['nome_lingua'];
      }
    }
    return NULL;
//    $errObj = new ADA_Error(NULL,'Language code not found.', 'Translator');
  }
  
  

  public static function getLanguageInfoForLanguageId($language_id) {
    if (!isset($_SESSION['sess_ada_supported_languages'])) {
      $errObj = new ADA_Error(NULL,'lingua non trovata', 'Translator');
    }

    $l = $_SESSION['sess_ada_supported_languages'];

    foreach($l as $language) {
      if($language['id_lingua'] == $language_id) {
        return $language;
      }
    }

    return array('id_lingua' => 0, 'nome_lingua' => '', 'codice_lingua' => '');
  }


//  public static function getLanguageCodeForNativeName($native_name) {
//    if (!isset($_SESSION['sess_ada_supported_languages'])) {
//      $errObj = new ADA_Error(NULL,'lingua non trovata', 'Translator');
//    }
//
//    $l = $_SESSION['sess_ada_supported_languages'];
//    foreach($l as $language_code => $language_native_name) {
//      if($language_native_name == $native_name) {
//        return $language_code;
//      }
//    }
//    $errObj = new ADA_Error(NULL,'Language code not found.','Translator');
//  }

  public static function negotiateLoginPageLanguage($lang_get=NULL) {
    $server_http_accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $dynamicLanguage = ADA_DYNAMIC_LANGUAGE;
    if (!$dynamicLanguage) {
        return ADA_LOGIN_PAGE_DEFAULT_LANGUAGE;
    }
    /*
     * the following regexp searches for user's browser accepted language
     * preferences.
     */
    $regexp = "/([a-z]{2}\-[a-zA-Z]{2},)?([a-z]{2})?(?:;q=(0\.[1-9]{1}))?/";
    $matches = array();
    preg_match_all($regexp,$server_http_accept_language,$matches);

    $l1 = $matches[1];
    $l2 = $matches[2];
    $q  = $matches[3];

    $user_defined_languages_count  = count($l2);

    $ada_supported_languages       = self::getSupportedLanguages();
    $ada_supported_languages_count = count($ada_supported_languages);

    if($ada_supported_languages_count == 0) {
      $errObj = new ADA_Error(NULL,'No supported languages found.','Translator');
    }
    /*
     * No user defined languages were given, return a default language
     */
    if($user_defined_languages_count == 0) {
      //return ADA_LOGIN_PAGE_DEFAULT_LANGUAGE;
      // FIXME: dovrebbe restituire la lingua di default, ora
      // restituisce la prima che trova
      return $ada_supported_languages[0]['codice_lingua'];
    }

    /*
     * Find a user defined language that is supported by ADA
     */
    if ($lang_get != NULL) {
    	for($j = 0; $j < $ada_supported_languages_count; $j++) {
        if($lang_get == $ada_supported_languages[$j]['codice_lingua']) {
          return $ada_supported_languages[$j]['codice_lingua'];
        }
      }
    }
    for ($i = 0; $i < $user_defined_languages_count; $i++) {
      for($j = 0; $j < $ada_supported_languages_count; $j++) {
        if($l2[$i] == $ada_supported_languages[$j]['codice_lingua']) {
          return $ada_supported_languages[$j]['codice_lingua'];
        }
      }
    }
    /*
     * No supported user language found, return a default language
     */
    //return ADA_LOGIN_PAGE_DEFAULT_LANGUAGE;
    // FIXME: dovrebbe restituire la lingua di default, ora
    // restituisce la prima che trova
    return $ada_supported_languages[0]['codice_lingua'];
  }
}