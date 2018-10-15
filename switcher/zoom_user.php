<?php
/**
 * ZOOM TUTOR.
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */

/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');

/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_SWITCHER => array('layout')
);


require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  'switcher';  // = switcher!

include_once 'include/'.$self.'_functions.inc.php';

/**
 * This will at least import in the current symbol table the following vars.
 * For a complete list, please var_dump the array returned by the init method.
 *
 * @var boolean $reg_enabled
 * @var boolean $log_enabled
 * @var boolean $mod_enabled
 * @var boolean $com_enabled
 * @var string $user_level
 * @var string $user_score
 * @var string $user_name
 * @var string $user_type
 * @var string $user_status
 * @var string $media_path
 * @var string $template_family
 * @var string $status
 * @var array $user_messages
 * @var array $user_agenda
 * @var array $user_events
 * @var array $layout_dataAr
 * @var History $user_history
 * @var Course $courseObj
 * @var Course_Instance $courseInstanceObj
 * @var ADAPractitioner $tutorObj
 * @var Node $nodeObj
 *
 * WARNING: $media_path is used as a global somewhere else,
 * e.g.: node_classes.inc.php:990
 */
SwitcherHelper::init($neededObjAr);

/*
 * YOUR CODE HERE
 */
include_once ROOT_DIR.'/include/HtmlLibrary/BaseHtmlLib.inc.php';

if(DataValidator::is_uinteger($id) !== FALSE) {
  /*
   * Aggiungere un controllo per verificare che l'utente di cui si vuole vedere
   * il profilo sia un utente seguito da questo switcher?
   */
  $tutoredUserObj = MultiPort::findUser($id);

  $user_dataAr = array(
    translateFN('Id utente')              => $tutoredUserObj->getId(),
    translateFN('Nome')                   => $tutoredUserObj->getFirstName(),
    translateFN('Cognome')                => $tutoredUserObj->getLastName(),
    translateFN('E-mail')                 => $tutoredUserObj->getEmail(),
    translateFN('Username')               => $tutoredUserObj->getUserName(),
    translateFN('Indirizzo')              => $tutoredUserObj->getAddress(),
    translateFN('Città')                  => $tutoredUserObj->getCity(),
    translateFN('Provincia')              => $tutoredUserObj->getProvince(),
    translateFN('Nazione')                => $tutoredUserObj->getCountry(),
    translateFN('Codice fiscale')         => $tutoredUserObj->getFiscalCode(),
    translateFN('Data di Nascita')        => $tutoredUserObj->getBirthDate(),
  	translateFN('Comune o stato estero di nascita') => $tutoredUserObj->getBirthCity(),
  	translateFN('Provincia di nascita')   => $tutoredUserObj->getBirthProvince(),
    translateFN('Sesso')                  => $tutoredUserObj->getGender(),
    translateFN('Telefono')               => $tutoredUserObj->getPhoneNumber(),
    translateFN('Status')                 => $tutoredUserObj->getStatus()
  );
  $data = BaseHtmlLib::plainListElement('',$user_dataAr);
}
else {
  $data = new CText(translateFN("Id dell'utente non valido"));
}

$banner = include ROOT_DIR.'/include/banner.inc.php';

$status = translateFN("Caratteristiche dell'utente");

// preparazione output HTML e print dell' output
$title = translateFN('ADA - dati epractitioner');

$content_dataAr = array(
  'menu'      => $menu,
  'banner'    => $banner,
  'dati'      => $data->getHtml(),
  'help'      => $help,
  'status'    => $status,
  'user_name' => $user_name,
  'user_type' => $user_type,
  'messages'  => $user_messages->getHtml(),
  'agenda'    => $user_agenda->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);