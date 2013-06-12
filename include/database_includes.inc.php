<?php
/**
 * 
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		database_includes			
 * @version		0.1
 */

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
 * model
 */
require_once ROOT_DIR.'/include/node_classes.inc.php';
require_once ROOT_DIR.'/include/courses_classes.inc.php';
require_once ROOT_DIR.'/include/Course.inc.php';
require_once ROOT_DIR.'/include/CourseInstance.inc.php';

/*
 * data access functions
 */
require_once ROOT_DIR.'/include/DB_read.inc.php';
?>