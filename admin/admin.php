<?php
/**
 * ADMIN.
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
require_once realpath(dirname(__FILE__)).'/../config_path.inc.php';

/**
 * Clear node and layout variable in $_SESSION
 */
$variableToClearAR = array('node', 'layout', 'course', 'course_instance');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_ADMIN);

/**
 * Performs basic controls before entering this module
 */
$neededObjAr = array(
  AMA_TYPE_ADMIN => array('layout')
);

require_once ROOT_DIR.'/include/module_init.inc.php';
$self =  whoami();  // = admin!

include_once 'include/admin_functions.inc.php';

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
AdminHelper::init($neededObjAr);

/*
 * YOUR CODE HERE
 */

$testers_dataAr = MultiPort::getDataForTesterActivityReport();

$table = AdminModuleHtmlLib::getTestersActivityReport($testers_dataAr);

$label = translateFN("Home dell'amministratore");
$help  = translateFN("Report sintetico dell'attivit&agrave; dei tester");

$menu_dataAr = array(
  array('href' => 'add_tester.php', 'text' => translateFN('Aggiungi provider')),
  array('href' => 'add_service.php', 'text' => translateFN('Aggiungi servizio')),
  array('href' => 'add_user.php', 'text' => translateFN('Aggiungi utente')),
//  array('href' => 'edit_news.php', 'text' => translateFN('Edit home page news')),
  array('href' => 'import_language.php', 'text' => translateFN('Import Language'))
  );

/**
 * giorgio 12/ago/2013
 *
 * add content editing to menu only if it's a multiprovider environment
 */
// grab available content types for editing and build menu items
// $availableTypes = ;
// if (MULTIPROVIDER)
// {
	foreach (dirTree (ROOT_DIR.'/docs') as $aType) {
		/**
		 * if is singleprovider, admin cannot edit news content
		 * It will not be shown anyway
		 */
		if (!MULTIPROVIDER && $aType=='news') continue;
    	array_push($menu_dataAr,
    		array ('href' => 'edit_content.php?type='.$aType, 'text' => 'Edit '.$aType.' content' )
    	);
	}
// }



$actions_menu = AdminModuleHtmlLib::createActionsMenu($menu_dataAr);

$content_dataAr = array(
  'user_name'    => $user_name,
  'user_type'    => $user_type,
  'status'       => $status,
  'actions_menu' => $actions_menu->getHtml(),
  'label'        => $label,
  'help'         => $help,
  'data'         => $table->getHtml(),
  'module'       => $label,
  'messages'     => $user_messages->getHtml()
);


/**
 * Sends data to the rendering engine
 */
ARE::render($layout_dataAr,$content_dataAr);
?>