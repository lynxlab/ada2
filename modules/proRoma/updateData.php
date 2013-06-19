<?php
/**
 * @package     Openlabor
 * @author	Maurizio Graffio Mazzoneschi <grafifo@lynxlab.com>
 * @copyright	Copyright (c) 2013, Lynx s.r.l.
 * @license	http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version	0.1
 */


/**
 * Base config file
 */
require_once (realpath(dirname(__FILE__)) . '/../../config_path.inc.php');
require_once(ROOT_DIR.'/config/ada_config.inc.php');
require_once (CORE_LIBRARY_PATH .'/includes.inc.php');
/**
 * Users (types) allowed to access this module.
 */
$allowedUsersAr = array(AMA_TYPE_VISITOR, AMA_TYPE_SWITCHER, AMA_TYPE_ADMIN);

/**
 * Get needed objects
 */
$neededObjAr = array(
AMA_TYPE_VISITOR => array('layout','default_tester'),
AMA_TYPE_SWITCHER => array('layout','default_tester'),
AMA_TYPE_ADMIN => array('layout','default_tester')
);

/**
 * Performs basic controls before entering this module
 */
require_once(ROOT_DIR.'/include/module_init.inc.php');

require_once(MODULES_DIR.'/proRoma/config/config.inc.php');
require_once(MODULES_DIR.'/proRoma/include/includes.inc.php');

//needed to promote AMADataHandler to AMATestDataHandler. $sess_selected_tester is already present in session
//$GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN($sess_selected_tester));
$GLOBALS['dh'] = AMAOpenLaborDataHandler::instance(MultiPort::getDSN(DATA_PROVIDER));
$dh = $GLOBALS['dh'];
$self = whoami();
if (!isset($typeData)) $typeData = 'jobs';

switch ($typeData) {
    case 'jobs':
       $remoteJobs = new remoteXMLResource(URL_OL_PROV_ROMA,$labels,$elements);
       $jobsData =  $remoteJobs->contents;
       $Jobs = $dh->addJobOffers($jobsData);
        print_r($Jobs);
        

        break;
    case 'CPI':
       $cpiObj = new remoteXMLResource(URL_CPI,$cpiElements,$cpiElements);
       $cpiAr = $cpiObj->contents;
       $lastCPI = $dh->addCPI($cpiAr);
       print_r($lastCPI);
       break;
    
}