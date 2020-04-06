<?php
/**
 * IMPORT MODULE
 *
 * @package		export/import course
 * @author		giorgio <g.consorti@lynxlab.com>
 * @copyright	Copyright (c) 2019, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		impexport
 * @version		0.1
 */

/**
 * Base config file
 */
require_once(realpath(dirname(__FILE__)) . '/../../../config_path.inc.php');

/**
 * Clear node and layout variable in $_SESSION
*/
$variableToClearAR = array('node', 'layout', 'course', 'user');

/**
 * Users (types) allowed to access this module.
*/
$allowedUsersAr = array(AMA_TYPE_SWITCHER, AMA_TYPE_AUTHOR);

/**
 * Get needed objects
*/
$neededObjAr = array(
        AMA_TYPE_SWITCHER => array('layout'),
        AMA_TYPE_AUTHOR => array('layout')
);

/**
 * Performs basic controls before entering this module
 */
$trackPageToNavigationHistory = false;
require_once ROOT_DIR.'/include/module_init.inc.php';
require_once ROOT_DIR.'/browsing/include/browsing_functions.inc.php';
BrowsingHelper::init($neededObjAr);

// MODULE's OWN IMPORTS

/**
 * @var AMARepositoryDataHandler $rdh
 */
require_once MODULES_IMPEXPORT_PATH .'/include/AMARepositoryDataHandler.inc.php';
$rdh = AMARepositoryDataHandler::instance();

$result = [ 'data' => [] ];
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
	/**
	 * it's a GET
	 */
    $getParams = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
    $what = array_key_exists('what', $getParams) ? ucfirst(trim($getParams['what'])): null;
    $canDo = [
        'edit'  => in_array($userObj->getType(), [ AMA_TYPE_SWITCHER ]),
        'trash' => in_array($userObj->getType(), [ AMA_TYPE_SWITCHER ]),
        'import'=> in_array($userObj->getType(), [ AMA_TYPE_SWITCHER ])
    ];
    if (!is_null($what)) {
        list ($entity, $action) = explode('::',$what);
        if ($entity == 'Repository') {
            $whereArr = [];
            if (!MULTIPROVIDER && isset ($GLOBALS['user_provider'])) {
                $whereArr['id_tester'] = $rdh->getTesterIDFromPointer();
            }
            $list = $rdh->getRepositoryList($whereArr);

            if (!\AMA_DB::isError($list) && is_array($list) && count($list)>0) {
                $result['data'] = array_map(function($el) use ($canDo) {
                    $actions = [];
                    // if ($canDo['edit']) {
                        //     $actions['edit'] = CDOMElement::create('a', 'class:tiny teal ui button, title:'.translateFN('Modifica'));
                        //     $actions['edit']->setAttribute('href', MODULES_IMPEXPORT_HTTP .'/editRepoItem.php?id='.$el['id'];
                        //     $actions['edit']->addChild(new CText(translateFN('Modifica')));
                        // }
                    if ($canDo['import']) {
                        $actions['import'] = CDOMElement::create('a', 'class:tiny purple ui button, title:'.translateFN('Importa'));
                        $actions['import']->setAttribute('href', MODULES_IMPEXPORT_HTTP . '/import.php?repofile='.urlencode($el['id_course'] . DIRECTORY_SEPARATOR . MODULES_IMPEXPORT_REPODIR. DIRECTORY_SEPARATOR. $el['filename']));
                        $actions['import']->addChild(new CText(translateFN('Importa')));
                    }
                    if ($canDo['trash']) {
                        $actions['trash'] = CDOMElement::create('a', 'class:tiny red ui button, title:'.translateFN('Cancella'));
                        $actions['trash']->setAttribute('href', 'javascript:(new initDoc()).deleteRepoItem($j(this),\''.$el['id'].'\');');
                        $actions['trash']->addChild(new CText(translateFN('Cancella')));
                    }

                    if (isset($el['filename'])) unset($el['filename']);
                    $retArr =  $el;
                    $retArr['actions']  =  array_reduce($actions, function($carry, $item) {
                        if (strlen($carry) <= 0) $carry = '';
                        $carry .= ($item instanceof \CBase ? $item->getHtml() : '');
                        return $carry;
                    });

                    return $retArr;

                }, $list);
            }
        }
    }
}

header('Content-Type: application/json');
die(json_encode($result));
