<?php
/**
 * LOGIN MODULE - module config main page
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

$loginProviders = abstractLogin::getLoginProviders(null,true); 

if (!is_null($loginProviders) && is_array($loginProviders)) {
			
	/**
	 * generate HTML for 'New Provider' button and the table
	 */
	$configIndexDIV = CDOMElement::create('div','id:configindex');
	$newButton = CDOMElement::create('button');
	$newButton->setAttribute('class', 'newButton top tooltip');
	$newButton->setAttribute('title', translateFN('Clicca per creare un nuovo login provider'));
	$newButton->setAttribute('onclick', 'javascript:editProvider(null);');
	$newButton->addChild (new CText(translateFN('Nuovo Provider')));
	$configIndexDIV->addChild($newButton);
	$tableOutData = array();
		
	if (!AMA_DB::isError($loginProviders)) {
	
		$labels = array (translateFN('id'), translateFN('posizione'), translateFN('className'),  translateFN('Nome'),
				translateFN('Abilitato'), translateFN('Bottone'),
				translateFN('azioni'));
		$hasDefault = false;
		foreach ($loginProviders as $i=>$elementArr) {
			$links = array();
			$linksHtml = "";
			$skip = $elementArr['className']==MODULES_LOGIN_DEFAULT_LOGINPROVIDER && !$hasDefault;	
			for ($j=0;$j<6;$j++) {
				switch ($j) {
					case 0:
						if(!$skip) {
							$type = 'edit';
							$title = translateFN('Modifica');
							$link = 'editProvider('.$i.');';
						}
						break;
					case 1:
						$type = 'config';
						$title = translateFN ('Configura');
						$link = 'document.location.href=\''.MODULES_LOGIN_HTTP.'/config.php?id='.$i.'\'';
						break;
					case 2:
						if (!$skip) {
							$type = 'delete';
							$title = translateFN ('Cancella');
							$link = 'deleteProvider($j(this), '.$i.' , \''.urlencode(translateFN("Questo cancellerà l'elemento selezionato")).'\');';
						}
						break;
					case 3:
						if (!$skip || count($loginProviders)>1) {
							$isEnabled = intval($elementArr['enabled'])===1;
							$type = ($isEnabled) ? 'disable' : 'enable';
							$title = ($isEnabled) ? translateFN('Disabilita') : translateFN('Abilita');
							$link = 'setEnabledProvider($j(this), '.$i.', '.($isEnabled ? 'false' : 'true').');';
						}
						break;
					case 4:
						$type = 'up';
						$title = translateFN('Sposta su');
						$link = 'moveProvider($j(this),'.$i.',-1);';
						break;
					case 5:
						$type = 'down';
						$title = translateFN('Sposta giu');
						$link = 'moveProvider($j(this),'.$i.',1);';
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
			if ($skip) $hasDefault = true;
			if (!empty($links)) {
				$linksul = CDOMElement::create('ul','class:ulactions');
				foreach ($links as $link) $linksul->addChild ($link);
				$linksHtml = $linksul->getHtml();
			} else $linksHtml = '';
			
			$tableOutData[$i] = array (
					$labels[0]=>$i,
					$labels[1]=>$elementArr['displayOrder'],
					$labels[2]=>$elementArr['className'],
					$labels[3]=>$elementArr['name'],
					$labels[4]=>((intval($elementArr['enabled'])===1) ? translateFN('Abilitato') : translateFN('Disabilitato') ),
					$labels[5]=>$elementArr['buttonLabel'],
					$labels[6]=>$linksHtml);
		}
	
		$OutTable = BaseHtmlLib::tableElement('id:loginProvidersList',
				$labels,$tableOutData,'',translateFN('Elenco dei login provider'));
		$configIndexDIV->addChild($OutTable);
	
		// if there are more than 10 rows, repeat the add new button below the table
		if (count($loginProviders)>10) {
			$bottomButton = clone $newButton;
			$bottomButton->setAttribute('class', 'newButton bottom tooltip');
			$configIndexDIV->addChild($bottomButton);
		}
	} // if (!AMA_DB::isError($optionSetList))
	$data = $configIndexDIV->getHtml();
	$title = translateFN('Configurazione Login Provider');
	$optionsAr['onload_func'] = 'initDoc();';
} else {
	$data = translateFN('Impossibile caricare i dati').'. '.translateFN('nessun login provider trovato').'.';
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
