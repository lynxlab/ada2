<?php
/**
 * LOGIN MODULE - config page for option sets of the provider type
 * 
 * @package 	login module
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2015, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */
 
ini_set('display_errors', '0'); error_reporting(E_ALL);
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
require_once MODULES_LOGIN_PATH .'/config/config.inc.php';
$self = whoami();

foreach (abstractLogin::getLoginProviders(true) as $id=>$className) {
	if (intval($id)===intval($_GET['id'])) {
		$providerClassName = $className;
		require_once MODULES_LOGIN_PATH . '/include/'.$providerClassName.'.class.inc.php';
		$loginObj = new $providerClassName($id);
		break;
	}
}

if (isset($loginObj) && is_object($loginObj) && is_a($loginObj, 'abstractLogin')) {
	/**
	 * generate HTML for 'New Option set' button and the table
	 */
	$configIndexDIV = CDOMElement::create('div','id:configindex');
	$newButton = CDOMElement::create('button');
	$newButton->setAttribute('class', 'newButton top');
	$newButton->setAttribute('title', translateFN('Clicca per creare un nuova fonte'));
	$newButton->setAttribute('onclick', 'javascript:editOptionSet(null);');
	$newButton->addChild (new CText(translateFN('Nuova Fonte')));
	$configIndexDIV->addChild($newButton);
	$tableOutData = array();
	$optionSetList = $loginObj->getAllOptions();
	$i = count($optionSetList);
	if (!AMA_DB::isError($optionSetList)) {
	
		$labels = array (translateFN('nome'), translateFN('host'),  translateFN('stato'),
				translateFN('azioni'));
		foreach ($optionSetList as $i=>$elementArr) {
			$links = array();
			$linksHtml = "";
				
			for ($j=0;$j<5;$j++) {
				switch ($j) {
					case 0:
						$type = 'edit';
						$title = translateFN('Modifica Fonte');
						$link = 'editOptionSet('.$i.');';
						break;
					case 1:
						$type = 'delete';
						$title = translateFN ('Cancella Fonte');
						$link = 'deleteOptionSet($j(this), '.$i.' , \''.urlencode(translateFN("Questo cancellerà l'elemento selezionato")).'\');';
						break;
					case 2:
						$isEnabled = intval($elementArr['enabled'])===1;
						$type = ($isEnabled) ? 'disable' : 'enable';
						$title = ($isEnabled) ? translateFN('Disabilita') : translateFN('Abilita');
						$link = 'setEnabledOptionSet($j(this), '.$i.', '.($isEnabled ? 'false' : 'true').');';
						break;
					case 3:
						$type = 'up';
						$title = translateFN('Sposta su');
						$link = 'moveOptionSet($j(this),'.$i.',-1);';
						break;
					case 4:
						$type = 'down';
						$title = translateFN('Sposta giu');
						$link = 'moveOptionSet($j(this),'.$i.',1);';
						break;
				}
	
				if (isset($type)) {
					$links[$j] = CDOMElement::create('li','class:liactions');
					$linkshref = CDOMElement::create('button');
					$linkshref->setAttribute('onclick','javascript:'.$link);
					$linkshref->setAttribute('class', $type.'Button tooltip');
					$linkshref->setAttribute('title',$title);
					$links[$j]->addChild ($linkshref);
					// unset for next iteration
					unset ($type);
				}
			}
			if (!empty($links)) {
				$linksul = CDOMElement::create('ul','class:ulactions');
				foreach ($links as $link) $linksul->addChild ($link);
				$linksHtml = $linksul->getHtml();
			} else $linksHtml = '';
	
			$tableOutData[$i] = array (
					$labels[0]=>$elementArr['name'],
					$labels[1]=>$elementArr['host'],
					$labels[2]=>((intval($elementArr['enabled'])===1) ? translateFN('Abilitata') : translateFN('Disabilitata') ),
					$labels[3]=>$linksHtml);
		}
	
		$OutTable = BaseHtmlLib::tableElement('id:complete'.strtoupper($providerClassName).'List',
				$labels,$tableOutData,'',translateFN('Elenco delle fonti '.strtoupper($loginObj->loadProviderName())));
		$configIndexDIV->addChild($OutTable);
	
		// if there are more than 10 rows, repeat the add new button below the table
		if ($i>10) {
			$bottomButton = clone $newButton;
			$bottomButton->setAttribute('class', 'newButton bottom');
			$configIndexDIV->addChild($bottomButton);
		}
	} // if (!AMA_DB::isError($optionSetList))
	$data = $configIndexDIV->getHtml();
	$title = translateFN('Configurazioni '.strtoupper($loginObj->loadProviderName()));
	$optionsAr['onload_func'] = 'initDoc(\''.$providerClassName.'\');';
} else {
	$data = translateFN('Impossibile caricare i dati').'. '.translateFN('Login provider ID non riconosciuto').'.';
	$title = translateFN('Erorre login provider');
	$optionsAr = null;
}

$content_dataAr = array(
		'user_name' => $user_name,
		'user_type' => $user_type,
		'messages' => $user_messages->getHtml(),
		'agenda' => $user_agenda->getHtml(),
		'status' => $status,
		'title' => $title,
		'data' => $data,
);
$layout_dataAr['JS_filename'] = array(
		JQUERY,
		JQUERY_DATATABLE,
		JQUERY_DATATABLE_DATE,
		JQUERY_UI,
		JQUERY_NO_CONFLICT
);
$layout_dataAr['CSS_filename'] = array(
		JQUERY_UI_CSS,
		JQUERY_DATATABLE_CSS,
		MODULES_LOGIN_PATH.'/layout/tooltips.css'
);
ARE::render($layout_dataAr, $content_dataAr, NULL, $optionsAr);
?>