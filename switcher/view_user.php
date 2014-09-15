<?php

/**
 * View user - this module shows the profile of an existing user
 *
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link
 * @version		0.1
 */
/**
 * Base config file
 */
require_once realpath(dirname(__FILE__)) . '/../config_path.inc.php';

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

require_once ROOT_DIR . '/include/module_init.inc.php';
$self = whoami();

include_once 'include/switcher_functions.inc.php';
include_once ROOT_DIR . '/admin/include/AdminUtils.inc.php';
/*
 * YOUR CODE HERE
 */
require_once ROOT_DIR . '/include/Forms/UserProfileForm.inc.php';
$userId = DataValidator::is_uinteger($_GET['id_user']);
if($userId === false) {
    $data = new CText('Utente non trovato');
}
else {

    $user_info = $dh->_get_user_info($userId);
    if(AMA_DataHandler::isError($userId)) {
        $data = new CText('Utente non trovato');
    } else {
        $viewedUserObj = MultiPort::findUser($userId);
        $viewedUserObj->toArray();
        $user_dataAr = array(
            'id' => $viewedUserObj->getId(),
            'tipo' => $viewedUserObj->getTypeAsString(),
            'nome e cognome' => $viewedUserObj->getFullName(),
            'data di nascita' => $viewedUserObj->getBirthDate(),
        	'Comune o stato estero di nascita' => $viewedUserObj->getBirthCity(),
        	'Provincia di nascita' => $viewedUserObj->getBirthProvince(),
            'genere' => $viewedUserObj->getGender(),
            'email' => $viewedUserObj->getEmail(),
            'telefono' => $viewedUserObj->getPhoneNumber(),
            'indirizzo' => $viewedUserObj->getAddress(),
            'citta' => $viewedUserObj->getCity(),
            'provincia' => $viewedUserObj->getProvince(),
            'nazione' => $viewedUserObj->getCountry(),
        	'confermato' => ($viewedUserObj->getStatus()==ADA_STATUS_REGISTERED) ? translateFN("Si") : translateFN("No")
        );

        $data = BaseHtmlLib::labeledListElement('class:view_info', $user_dataAr);
    }
}    

$label = translateFN('Profilo utente');
$help = translateFN('Da qui il provider admin può visualizzare il profilo di un utente esistente');

$content_dataAr = array(
    'user_name' => $user_name,
    'user_type' => $user_type,
    'status' => $status,
    'label' => $label,
    'help' => $help,
    'data' => $data->getHtml(),
    'edit_profile'=>$userObj->getEditProfilePage(),
    'module' => $module,
    'messages' => $user_messages->getHtml()
);

ARE::render($layout_dataAr, $content_dataAr);