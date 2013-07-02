<?php
//defines
define('PRO_ROMA_DIR',MODULES_DIR.'/proRoma');
define('PRO_ROMA_HTTP',HTTP_ROOT_DIR.'/modules/proRoma');

require_once(ROOT_DIR.'/api/'.API_VERSION.'/include/AMAOpenLaborDataHandler.inc.php');
require_once(PRO_ROMA_DIR.'/include/extract_class.inc.php');

/*
 * needed in order to have the data access layer working
 */
require_once ROOT_DIR.'/include/logger_class.inc.php';
require_once ROOT_DIR.'/include/error_class.inc.php';
require_once AMA_LIB;
require_once ROOT_DIR.'/include/multiport.inc.php';

require_once ROOT_DIR.'/include/user_classes.inc.php';

/*
 * needed in order to have the initialization script phase working
 */
require_once ROOT_DIR.'/include/data_validation.inc.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
