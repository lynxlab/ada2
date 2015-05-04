<?php
/**
 * Standard configuration file for ADA
 *
 * DO NOT MODIFY THIS FILE
 *
 * @package
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009, Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @link		ada_config_main
 * @version		0.1
 */

/**
 * Prefix for GET/POST/Cookie variables when imported with
 * import_request_variables
 */
define('ADA_GP_VARIABLES_PREFIX','');

/**
 * Logging output.
 */
define('ADA_LOGGER_NULL_LOG'  , 0);
define('ADA_LOGGER_SCREEN_LOG', 1);
define('ADA_LOGGER_FILE_LOG'  , 2);


define('ADA_SELECTED_LOGGER'  , ADA_LOGGER_FILE_LOG);

define('ADA_FILE_LOGGER_OUTPUT_FILE', ROOT_DIR.'/log/trace.log');
/**
 * Operations to be logged
 */
define('ADA_LOG_DB'     , 1);
define('ADA_LOG_GENERIC', 2);

define('ADA_LOG_DB_SELECTED_LOGGER', ADA_LOGGER_FILE_LOG);
define('ADA_LOG_DB_FILE_LOG_OUTPUT_FILE', ROOT_DIR.'/log/db.log');

define('ADA_LOG_GENERIC_SELECTED_LOGGER', ADA_LOGGER_FILE_LOG);
define('ADA_LOG_GENERIC_FILE_LOG_OUTPUT_FILE', ROOT_DIR.'/log/generic.log');

define('ADA_LOG_ERROR_SELECTED_LOGGER', ADA_LOGGER_FILE_LOG);
define('ADA_LOG_ERROR_FILE_LOG_OUTPUT_FILE', ROOT_DIR.'/log/error.log');

define('ADA_LOGGING_LEVEL',  ADA_LOG_DB | ADA_LOG_GENERIC);

/**
 * Some default values
 */
define('ADA_DEFAULT_COURSE', '1');
define('ADA_DEFAULT_USER',   '0');
define('ADA_DEFAULT_NODE',   '0');


/**
 * ADA user subscription status
 */
define('ADA_STATUS_REGISTERED',    0);
define('ADA_STATUS_PRESUBSCRIBED', 1);
define('ADA_STATUS_SUBSCRIBED',    2);
define('ADA_STATUS_REMOVED',       3);
define('ADA_STATUS_VISITOR',       4);
define('ADA_STATUS_COMPLETED',     5);
define('ADA_STATUS_TERMINATED',    6);

/**
 * ADA user service subscription status
 */
define('ADA_SERVICE_SUBSCRIPTION_STATUS_UNDEFINED' , ADA_STATUS_REGISTERED);
define('ADA_SERVICE_SUBSCRIPTION_STATUS_REQUESTED' , ADA_STATUS_PRESUBSCRIBED);
define('ADA_SERVICE_SUBSCRIPTION_STATUS_ACCEPTED'  , ADA_STATUS_SUBSCRIBED);
define('ADA_SERVICE_SUBSCRIPTION_STATUS_SUSPENDED' , ADA_STATUS_REMOVED);
define('ADA_SERVICE_SUBSCRIPTION_STATUS_COMPLETED' , ADA_STATUS_COMPLETED);
define('ADA_SERVICE_SUBSCRIPTION_STATUS_TERMINATED', ADA_STATUS_TERMINATED);

/**
 * ADA node 
 */
define('ADA_LEAF_TYPE',         0);
define('ADA_GROUP_TYPE',        1);
define('ADA_NOTE_TYPE',         2);
define('ADA_PRIVATE_NOTE_TYPE', 21);

define('ADA_STANDARD_EXERCISE_TYPE',       3);
define('ADA_OPEN_MANUAL_EXERCISE_TYPE',    4);
define('ADA_OPEN_AUTOMATIC_EXERCISE_TYPE', 5);
define('ADA_CLOZE_EXERCISE_TYPE',          6);
define('ADA_OPEN_UPLOAD_EXERCISE_TYPE',    7);


define('ADA_LEAF_WORD_TYPE',   8);
define('ADA_GROUP_WORD_TYPE',  81);

define('ADA_PERSONAL_EXERCISE_TYPE',  9);

/**
 * ADA instance services types
 */
define('ADA_COURSEINSTANCE_STATUS_PRIVATE',  0);
define('ADA_COURSEINSTANCE_STATUS_RESERVED', 1);
define('ADA_COURSEINSTANCE_STATUS_PUBLIC',   2);

/**
 * ADA generic services types
 */
define('ADA_SERVICE_ONLINECOURSE', 1);
define('ADA_SERVICE_PRESENCECOURSE', 2);
define('ADA_SERVICE_MIXEDCOURSE', 3);
define('ADA_SERVICE_TUTORCOMMUNITY', 4);
define('DEFAULT_SERVICE_TYPE', ADA_SERVICE_ONLINECOURSE);

/**
 * Take the ADA generic services types defined in config/config_main.inc.php
 * and group them in onLine and presence service types
 */
$GLOBALS['onLineServiceTypes'] = array (ADA_SERVICE_ONLINECOURSE, ADA_SERVICE_TUTORCOMMUNITY);
$GLOBALS['presenceServiceTypes'] = array (ADA_SERVICE_PRESENCECOURSE, ADA_SERVICE_MIXEDCOURSE);

/**
 * ADA token
 */
define('ADA_TOKEN_IS_NOT_VALID', 0);
define('ADA_TOKEN_IS_VALID'    , 1);

define('ADA_TOKEN_FOR_REGISTRATION',  1);
/*
 * Expiration time is expressed in seconds.
 * Expiration time for a registration token is set to 7 days.
 * In seconds it is: 7 days * 24 hours in a day *
 *                   60 minutes in a hour * 60 seconds in a minute
 * 604800 seconds
 */
define('ADA_TOKEN_FOR_REGISTRATION_EXPIRES_AFTER', 604800);

define('ADA_TOKEN_FOR_PASSWORD_CHANGE', 2);
/*
 * Expiration time for a password change request is set to 1 day
 * In seconds it is: 24 * 60 * 60 = 86400
 */
define('ADA_TOKEN_FOR_PASSWORD_CHANGE_EXPIRES_AFTER', 86400);

/**
 *  Access tracking
 */
define('ADA_GENERIC_ACCESS', 0);
define('ADA_KIOSK_ACCESS'  , 1);
define('ADA_EGSTATION_ACCESS'  , 2);
define('ADA_RESERVED_ACCESS'  , 3);


/**
 * User types
 */
define('AMA_TYPE_AUTHOR',       1);
define('AMA_TYPE_ADMIN',        2);
define('AMA_TYPE_STUDENT',      3);
define('AMA_TYPE_TUTOR',        4);
define('AMA_TYPE_VISITOR',      5);
define('AMA_TYPE_SWITCHER',     6);
define('AMA_TYPE_SUPERTUTOR',   7);

/**
 * Message types
 */
define('ADA_MSG_SIMPLE',   'S');
define('ADA_MSG_AGENDA',   'A');
define('ADA_MSG_CHAT',     'C');
define('ADA_MSG_PRV_CHAT', 'P');
define('ADA_MSG_MAIL',     'M');
define('ADA_MSG_MAIL_ONLY','O'); // only e-mail, no associated internal message

/*
 * Message flags
 */

define('ADA_EVENT_CONFIRMED',       1);
define('ADA_EVENT_PROPOSED',        2);
define('ADA_EVENT_PROPOSAL_OK',     4);
define('ADA_EVENT_PROPOSAL_NOT_OK', 8);

define('ADA_CHAT_EVENT',      256);
define('ADA_VIDEOCHAT_EVENT', 512);
define('ADA_PHONE_EVENT',     1024);
define('ADA_IN_PLACE_EVENT',  2048);
/**
 * Time
 */
define('NTC_TIME',	1000000);
define('RTC_TIME',	86400);

define('CS_NTC_TIME',	60);
define('CS_RTC_TIME',	0);

define('IC_LIFE_TIME',	3600);

/**
 * Media types
 */
define('_IMAGE','1');
define('_SOUND','2');
define('_VIDEO','3');
define('_LINK', '4');
define('_DOC',  '5');
define('_EXE',  '6');
define('INTERNAL_LINK', '7');
define('POSSIBLE_TYPE','7');
define('_PRONOUNCE','21'); //→ audio
define('_FINGER_SPELLING','31'); //→ video
define('_LABIALE','32'); //→ video
define('_LIS','33'); //→ video
define('_MONTESSORI','11'); //→ immagine, simbolo montessoriano

define('_GO'  ,'GO');
define('_STOP','STOP');

/**
 * Node editor
 */
define('EDITOR_INSERT_EXTERNAL_LINK'                 , 1);
define('EDITOR_INSERT_INTERNAL_LINK'                 , 2);
define('EDITOR_UPLOAD_FILE'                          , 4);
define('EDITOR_SELECT_FILE'                          , 8);
define('EDITOR_SELECT_EXTERNAL_LINK'                 , 16);
define('EDITOR_ALLOW_SWITCHING_BETWEEN_EDITING_MODES', 32);

// node data
define('EDITOR_SHOW_NODE_LEVEL'          , 256);
define('EDITOR_SHOW_NODE_ICON'           , 512);
define('EDITOR_SHOW_NODE_BGCOLOR'        , 1024);
define('EDITOR_SHOW_NODE_TYPE'           , 2048);
define('EDITOR_SHOW_NODE_POSITION'       , 4096);
define('EDITOR_SHOW_PARENT_NODE_SELECTOR', 8192);
define('EDITOR_SHOW_NODE_ORDER'          , 16384);

// operations on node
define('ADD_OPERATION', 0);
define('EDIT_OPERATION', 1);

/**
 * AMA
 */

/**
 * AMA_LIB
 */
define('AMA_LIB', ROOT_DIR.'/include/ama.inc.php');

// Constant signaling that a connection with the DB is not established
define('AMA_DB_NOT_CONNECTED',	NULL);
// Separator between errors
define('AMA_SEP',  '<br>');
// Rollback messages
define('AMA_ROLLBACK_SUCCESSFUL', 'Rollback was completed successfully');
define('AMA_ROLLBACK_NOT_SUCCESSFUL', 'Attention: Rollback failed!');
/**
 * AMA_DB
 */
/**
 * Retrieve results as an array.
 * @var unknown_type
 */
define('AMA_FETCH_ORDERED', PDO::FETCH_NUM);
/**
 * Retrieve results as an associative array.
 */
define ('AMA_FETCH_ASSOC', PDO::FETCH_ASSOC);
/**
 * Retrieve results as objects.
 * @var unknown_type
 */
define('AMA_FETCH_OBJECT', PDO::FETCH_OBJ);
/**
 * Default fetch mode.
 */
define ('AMA_FETCH_DEFAULT', AMA_FETCH_ORDERED);
/**
 * Both fetch mode.
 */
define ('AMA_FETCH_BOTH', PDO::FETCH_BOTH);

/**
 * Success
 * @var unknown_type
 */
define('AMA_DB_OK', 1);

// vito, 2 apr 2009s
define('AMA_SECONDS_IN_A_DAY', 86400);


/*
 * From ada1.8 config_main file
 */

// CONSTANTS:

// error handling mode
define('ADA_ERROR_HANDLING_MODE', 2);
// vito, 14 nov 2008
define ('ADA_SHOW_ERRORS_BACKTRACE', 0);
// exercise interaction
/*
 * Exercises
 */
define('ADA_BLIND_EXERCISE_INTERACTION','2'); // no feedback
define('ADA_FEEDBACK_EXERCISE_INTERACTION','1'); // with feedback
define('ADA_RATING_EXERCISE_INTERACTION','0'); // with feedback and rating
// test mode
define('ADA_SINGLE_EXERCISE_MODE','0'); // only one exercise
define('ADA_SEQUENCE_EXERCISE_MODE','1'); // next exercise will be shown (order)
define('ADA_RANDOM_EXERCISE_MODE','2'); // a randomly picked exercise will be shown
// test semplification
define('ADA_NORMAL_EXERCISE_SIMPLICITY', '0');
define('ADA_SIMPLIFY_EXERCISE_SIMPLICITY', '1');
define('ADA_MEDIUM_EXERCISE_SIMPLICITY', '2');
// test barrier
define('ADA_NO_EXERCISE_BARRIER', '0');
define('ADA_YES_EXERCISE_BARRIER', '1');
define('NO_ANSWER', "<NOANSWER>");
define('ADA_EXERCISE_MODIFIED_ITEM',1);
define('ADA_EXERCISE_DELETED_ITEM', 2);

/*
 * ARE constants
 */
define ('ARE_PRINT_RENDER',1);
define ('ARE_XML_RENDER',  2);
define ('ARE_FILE_RENDER', 3);
define ('ARE_HTML_RENDER', 4);
define ('ARE_PDF_RENDER',  5);

/*
 * Other constants
 */
define('NO_LOG',	0);  // no log  at all
define('DB_LOG',	5); // logging all db operations
define('DB_START',	4); //logging only the beginning of db operations

define('ADA_SECONDS_IN_A_DAY', 86400);

/**
 * Caching mode
 */
define('ADA_NO_CACHE',   		0);		//always dynamically read from DB 
define('ADA_READONLY_CACHE',	1); 	//read only: the file is always loaded but never rewritten
define('ADA_UPDATE_CACHE',  	2); 	//static rw: the node content is read from file only if lifetime is > $ic_lifetime
										// otherwise it is read from DB and then written back to file
define('ADA_FORCE_UPDATE_CACHE',3); 	//static rw: the node content  is read from DB and then written back to file
										
define('JQUERY',				ROOT_DIR.'/js/include/jquery/jquery-1.11.1.min.js');
define('JQUERY_UI',				ROOT_DIR.'/js/include/jquery/ui/jquery-ui-1.11.1.custom.min.js');
define('JQUERY_UI_CSS',			ROOT_DIR.'/js/include/jquery/ui/jquery-ui-1.11.1.custom.min.css');
define('JQUERY_DATATABLE',		ROOT_DIR.'/js/include/jquery/dataTables/jquery.dataTables.min.js');
define('JQUERY_MASKEDINPUT',	ROOT_DIR.'/js/include/jquery/maskedinput/jquery.maskedinput.min.js');
define('JQUERY_DATATABLE_DATE',	ROOT_DIR.'/js/include/jquery/dataTables/dateSortPlugin.js');
define('JQUERY_DATATABLE_CSS',	ROOT_DIR.'/js/include/jquery/dataTables/jquery.dataTables.css');
define('JQUERY_NO_CONFLICT',	ROOT_DIR.'/js/include/jquery.noConflict.js');
define('JQUERY_NIVOSLIDER',		ROOT_DIR.'/js/include/jquery/nivo-slider/jquery.nivo.slider.pack.js');
define('JQUERY_NIVOSLIDER_CSS', ROOT_DIR.'/js/include/jquery/nivo-slider/nivo-slider.css');
define('SEMANTICUI_CSS',		ROOT_DIR.'/js/include/semantic/css/semantic.min.css');
define('SEMANTICUI_JS',	 		ROOT_DIR.'/js/include/semantic/javascript/semantic.min.js');
define('SMARTMENUS_CSS',		ROOT_DIR.'/js/include/smartmenus/css/sm-core-css.css');
define('SMARTMENUS_JS',	 		ROOT_DIR.'/js/include/smartmenus/jquery.smartmenus.min.js');
