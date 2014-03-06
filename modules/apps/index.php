<?php
/**
 * APPS MODULE.
 *
 * @package        apps module
 * @author         Giorgio Consorti <g.consorti@lynxlab.com>         
 * @copyright      Copyright (c) 2014, Lynx s.r.l.
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link           oauth2
 * @version		   0.1
 */

/**
 * This module is responsible for generating client id and client secret pairs for each user
 * requesting them. Typically, this is done by a switcher. The generated (or retreived from the
 * common DB) pairs are then used to get an access token by the ada-php-sdk (or by the developer
 * not using the sdk by itself).
 * 
 * curl examples for getting the access token and using it to obtain a resource are:
 * 
 * # using HTTP Basic Authentication
 * $ curl -u TestClient:TestSecret https://api.mysite.com/token -d 'grant_type=client_credentials'
 * # using POST Body
 * $ curl https://api.mysite.com/token -d 'grant_type=client_credentials&client_id=TestClient&client_secret=TestSecret'
 */

/**
 * Base config file
*/
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');
/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER);

/**
 * Get needed objects
*/
$neededObjAr = array(
		AMA_TYPE_SWITCHER => array('layout')
);

/**
 * Performs basic controls before entering this module
*/
require_once(ROOT_DIR.'/include/module_init.inc.php');
require_once(ROOT_DIR.'/browsing/include/browsing_functions.inc.php');

// MODULE's OWN IMPORTS
require_once MODULES_APPS_PATH .'/config/config.inc.php';

$self = whoami();

/**
 * TODO: Add your own code here
 */

$container = CDOMElement::create('div','id:gettokenpage');

	$getButton = CDOMElement::create('button','id:getButton');
	$getButton->setAttribute('onclick', 'javascript:getAppSecretAndID('.$userObj->getId().');');
	$getButton->addChild (new CText(translateFN('Generate API App ID and Secret NOW!')));

	$output = CDOMElement::create('div','id:outputtoken');
	$output->setAttribute('style', 'display:none');
	
$container->addChild ($getButton);
$container->addChild($output);

$data = $container->getHtml();

/**
 * include proper jquery ui css file depending on wheter there's one
 * in the template_family css path or the default one
*/
if (!is_dir(MODULES_APPS_PATH.'/layout/'.$userObj->template_family.'/css/jquery-ui'))
{
	$layout_dataAr['CSS_filename'] = array(
			JQUERY_UI_CSS
	);
}
else
{
	$layout_dataAr['CSS_filename'] = array(
			MODULES_APPS_PATH.'/layout/'.$userObj->template_family.'/css/jquery-ui/jquery-ui-1.10.3.custom.min.css'
	);
}

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'title' => translateFN('oauth2'),
		'data' => $data,
);

$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_UI,
		JQUERY_NO_CONFLICT
);

$optionsAr['onload_func'] = 'initDoc();';

ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>
