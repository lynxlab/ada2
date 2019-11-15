<?php
/**
 * Standard configuration file for ADA
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
 * DB and interface constants
 * This section can be modified  by the installer
 */
/*
 * ADA Common database
 */
define('ADA_COMMON_DB_TYPE', 'mysql');
define('ADA_COMMON_DB_NAME', getenv('MYSQL_DATABASE') ?: 'ada2.0_common');
define('ADA_COMMON_DB_USER', getenv('MYSQL_USER') ?: 'root');
define('ADA_COMMON_DB_PASS', getenv('MYSQL_PASSWORD') ?:'');
define('ADA_COMMON_DB_HOST', getenv('MYSQL_HOST') ?:'localhost');

/*
 * ADA default provider.
 */
define('ADA_PUBLIC_TESTER', getenv('DEFAULT_PROVIDER_POINTER') ?: 'client0');
define('ADA_DEFAULT_TESTER_DB_TYPE', 'mysql');
define('ADA_DEFAULT_TESTER_DB_NAME', getenv('DEFAULT_PROVIDER_DB') ?: 'ada2.0_provider0');
define('ADA_DEFAULT_TESTER_DB_USER', getenv('MYSQL_USER') ?: 'root');
define('ADA_DEFAULT_TESTER_DB_PASS', getenv('MYSQL_PASSWORD') ?: '');
define('ADA_DEFAULT_TESTER_DB_HOST', getenv('MYSQL_HOST') ?: 'localhost');
/*
 * Session Cookie Lifetime in days
 * If 0 the session end when the browser is closed
 */
define('ADA_SESSION_LIFE_TIME',0);

if (MULTIPROVIDER) {
	/**
	 * In a single provider environment, each one
	 * shall have these set in its own config file
	 */

	/**
	 * ID of the public course to get the latest news
	 */
	define ('PUBLIC_COURSE_ID_FOR_NEWS', 1);
	/**
	 * How many news to get from the above mentioned course
	 */
	define ('NEWS_COUNT', 3);
}

/**
 * URL
 * DO NOT REMOVE the trailing // *js_import*
 */
if (!defined('HTTP_ROOT_DIR')) define('HTTP_ROOT_DIR', getenv('HTTP_ROOT_DIR') ?: 'http://localhost/ada22'); // *js_import*

define('ADA_DEFAULT_AVATAR','default_avatar.png');

/**
 * portal name string - displayed in window titlebar
*/
if (!defined('PORTAL_NAME')) {
    if (getenv('PORTAL_NAME')){
        define('PORTAL_NAME', getenv('PORTAL_NAME'));
    } else {
        define('PORTAL_NAME', getenv('ADA_OR_WISP') ? translateFN('Benvenuto su') .' '.getenv('ADA_OR_WISP') : 'ADA 2.2');
    }
}

/**
 * set to true to always display the maintenance page
 * and set the template to be used if you wish
 */
if (!defined('MAINTENANCE_MODE')) define('MAINTENANCE_MODE', false);
if (!defined('MAINTENANCE_TPL') && MAINTENANCE_MODE === true) define ('MAINTENANCE_TPL', 'maintenancemode');

// key used to cipher urls sent to adaProxy.php
if (!defined('ADAPROXY_ENC_KEY')) define ('ADAPROXY_ENC_KEY', 'adaProxyKey');

/**
 * DataBase abstraction layer,
 * kept for possible future implementations.
 *
 * Only possible value is PDO_DB as of 30/mag/2013
 */
define('PDO_DB', 1);
define('DB_ABS_LAYER', PDO_DB);


/**
 * Caching mode (OLD CONSTANTS: THEY AREN'T USED AND SHOULD NOT BE HERE)
 */
define('ADA_NO_STATIC',   0);
/*
 * partially static: only the body of file is loaded,
 * and is placed in 'data' placeholder
 */
define('ADA_CORE_STATIC', 1);
/*
 * the body of external page is placed
 */
define('ADA_BODY_STATIC', 2);
/*
 * entirely static: all the file is read (written)...
 */
define('ADA_ALL_STATIC',  3);
/*
 * the file is loaded in an external window
 */
define('ADA_EXT_STATIC',  4);

/**
 * Caching mode NEW CONSTANTS USED BY VIEW
 * default behaviour for Guest Users
 */

define('ADA_CACHEMODE',ADA_UPDATE_CACHE); // defined in config_main.inc.php
/*
 * Maximum number of students in a course instance
 */
define('ADA_COURSE_INSTANCE_STUDENTS_NUMBER', 25);

/*
 * Media preferences
 */

/*
 * how to display images 0 (icon), 1 or 2 (inline)
 */
define ('IMG_VIEWING_MODE',2);
/*
 *
 */
define('AUDIO_PLAYING_MODE', 2);
/*
 *
 */
define('VIDEO_PLAYING_MODE', 2);
/*
 *
 */
define('DOC_VIEWING_MODE',   2);

/*
 * size of icon image reduction
 */
define('MAX_WIDTH', "200");
define('MAX_HEIGHT', "200");

/*
 * default video width and height
*/
define('DEFAULT_VIDEO_WIDTH', 720);
define('DEFAULT_VIDEO_HEIGHT', 405);
/*
define('DEFAULT_VIDEO_WIDTH', 290);
define('DEFAULT_VIDEO_HEIGHT', 250);

 define('DEFAULT_VIDEO_WIDTH', 528);
 define('DEFAULT_VIDEO_HEIGHT', 297);
 */
/*
 * size of Avatar image reduction
 */
define('AVATAR_MAX_WIDTH', "600");
define('AVATAR_MAX_HEIGHT', "500");

/**
 * Default admin mail address
 */
define('ADA_ADMIN_MAIL_ADDRESS', getenv('ADA_ADMIN_MAIL_ADDRESS') ?: 'graffio@lynxlab.com');

/**
 * Default noreply mail address
 */
define('ADA_NOREPLY_MAIL_ADDRESS', getenv('ADA_NOREPLY_MAIL_ADDRESS') ?: 'noreply@lynxlab.com');

/**
 * ADA version
 */
define('ADA_VERSION','2.2');

/**
 * URL
 * DO NOT REMOVE the trailing // *js_import*
 */
define('MODULES_DIR',ROOT_DIR.'/modules'); // *js_import*

/**
 *
 */
define('ADA_METAKEYWORDS','ADA metakeywords');
define('ADA_METADESCRIPTION','ADA metadescription');
define('ADA_MESSAGE_LANGUAGE','English');
define('ADA_LOGIN_PAGE_DEFAULT_LANGUAGE', 'en');
define('ADA_DYNAMIC_LANGUAGE', TRUE);
define('ADA_CHARSET', 'UTF-8');
define('SERVER_TIMEZONE', 'Europe/Rome');

/*
 * if true the system search in the text node the word in the glossary
 */
define('SEARCH_WORD_IN_NODE',0);

/*
 * if true the system show the node the node extended fields glossary
 */
define('SHOW_NODE_EXTENDED_FIELDS',1);

/*
 * after login, if true the system redirect the user to the course instance he is subscribed to
 */

define ("ADA_USER_AUTOMATIC_ENTER",FALSE); // feature disabled !

/*
 * if true the system allow to printing a certificate
 */

define ("ADA_PRINT_CERTIFICATE",FALSE);

/*
 * If there isn't db_common.service_type table, use this to define default service type.
 */
define('DEFAULT_SERVICE_TYPE_NAME','Corso Online');

/*
 * Set medias rendered with jPlayer to autoplay
 */
define ('JPLAYER_AUTOPLAY', false);

/**
 * Environment constants and global variables.
 * This section can be modified only by an expert installer.
 */


/**
 * Paths
 */
$http_root_dir = HTTP_ROOT_DIR;
$root_dir      = ROOT_DIR;
$modules_dir   = MODULES_DIR;

define('ADA_DEFAULT_EMAIL_FOOTER', 'This message has been sent to you by ADA. For additional information please visit the following address: ' . HTTP_ROOT_DIR);
/**
 * default author upload path
 */
define('ADA_UPLOAD_PATH', ROOT_DIR.'/upload_file/uploaded_files/');

/**
 * default HTTP upload path for each user
 */
define('HTTP_UPLOAD_PATH', HTTP_ROOT_DIR.'/upload_file/uploaded_files/');

/**
 * default tutor upload path
 */
define('TUTOR_UPLOAD_PATH', ROOT_DIR.'/upload_file/uploaded_files/tutors/');

/**
 * default media path (in case author's mediapath = "")
 */
define('MEDIA_PATH_DEFAULT','/services/media/');

/**
 * writable directory permissions
 */
define('ADA_WRITABLE_DIRECTORY_PERMISSIONS', 0755);

define('ADA_COURSE_MODELS_PATH', ADA_UPLOAD_PATH . 'models');

// default media path (in case author's mediapath = "")
define('AUTHOR_COURSE_PATH_DEFAULT', ADA_COURSE_MODELS_PATH);


// default media local path
define('MEDIA_LOCAL_PATH',''); // default: disabled

/**
 * Default template family
 * DO NOT REMOVE the trailing // *js_import*
 */
//define('ADA_TEMPLATE_FAMILY', 'standard');   // *js_import*
define('ADA_TEMPLATE_FAMILY', 'ada_blu');   // *js_import*
/**
 * Default class for semantic ui tables class (both datatables and normal tables)
 */
define('ADA_SEMANTICUI_TABLECLASS', 'ui padded table');   // *js_import*

// default templates path
define('ADA_TEMPLATE_PATH', ROOT_DIR.'/templates/main');

/*
 * CORE LIBRARY PATH
 */
define('CORE_LIBRARY_PATH',ROOT_DIR.'/include/COREv0.1');

// default date format
define('ADA_DATE_FORMAT', '%d/%m/%Y');

// default type of field in templates
//  define('ADA_STATIC_TEMPLATE_FIELD',0); // with reg.exp. we can use chars, crlf, &nbsp; etc in template fields between <> and </> (slower)
define('ADA_STATIC_TEMPLATE_FIELD',1); // we cannnot use crlf, &nbsp; etc in template fields (more efficient)

// including sub-templates allowed
define('USE_MICROTEMPLATES',1);

// needed by class NavigationHistory: it defines the maximum number of pages
// to mantain in navigation history
define('NAVIGATION_HISTORY_SIZE', 5);
//
// defines needed while editing or adding node
//
define('ADA_FILE_UPLOAD_MAX_FILESIZE', 20000000);
define('ADA_FILE_UPLOAD_ACCEPTED_MIMETYPE',_GO);

/**
 * system messages translation
 * this one  defines how many messages will be presented to the user after
 * performing a search in the translation module.
 */
define('ADA_SYSTEM_MESSAGES_SHOW_SEARCH_RESULT_NUM', 10);

/**
 * Global variables (for retrocompatibility)
 */

// time to refresh cached pages
$ic_lifetime = IC_LIFE_TIME;

// characteristic times for the cleaning mechanism (in seconds)
$SimpleSpool_ntc = NTC_TIME;
$SimpleSpool_rtc = RTC_TIME;
$AgendaSpool_ntc = NTC_TIME;
$AgendaSpool_rtc = RTC_TIME;
$ChatSpool_ntc = CS_NTC_TIME;
$ChatSpool_rtc = CS_RTC_TIME;

$language = ADA_MESSAGE_LANGUAGE;

$tpl_fileextension = ".tpl"; // .html,  .dwt, ...

// code for fields in templates
$replace_field_code = "<template_field class=\"template_field\" name=\"%field_name%\">%field_name%</template_field>"; // more general and fast!

// code for microtemplates fields (to include extra bits of html code)
$replace_microtemplate_field_code = "<template_field class=\"microtemplate_field\" name=\"%field_name%\">%field_name%</template_field>";

// templates are in separated directories under ada/templates/... directory
$duplicate_dir_structure = 1;

// TODO: vengono ancora utilizzati?
// path to the ADA Middle API library  (absolute path)
$ama_lib = ROOT_DIR.'/include/ama_class.inc.php';
// path to the Stack library  library (absolute path)
$stack_class =  ROOT_DIR.'/include/stack.inc.php';
// path to the Rbstack library (absolute path)
$rbstack_class = ROOT_DIR.'/include/rbstack.inc.php';


//accepted mimetypes for upload
// was $mimetypeHa + $mimetypeCodeHa in 1.7
// replaced also in upload functions
$ADA_MIME_TYPE["application/pdf"]['permission'] = _GO;
$ADA_MIME_TYPE["application/x-pdf"]['permission'] = _GO;
$ADA_MIME_TYPE["application/x-zip-compressed"]['permission'] = _GO;
$ADA_MIME_TYPE["application/zip-compressed"]['permission'] = _GO;
$ADA_MIME_TYPE["application/zip"]['permission'] = _GO;
$ADA_MIME_TYPE["audio/mpeg"]['permission'] = _GO;
$ADA_MIME_TYPE["audio/x-mp3"]['permission'] = _GO;
$ADA_MIME_TYPE["audio/basic"]['permission'] = _GO;
$ADA_MIME_TYPE["audio/wav"]['permission'] = _GO;
$ADA_MIME_TYPE["audio/x-wav"]['permission'] = _GO;
$ADA_MIME_TYPE["audio/x-pn-realaudio-plugin"]['permission'] = _GO;
$ADA_MIME_TYPE["audio/midi"]['permission'] = _GO;
$ADA_MIME_TYPE["audio/x-midi"]['permission'] = _GO;
$ADA_MIME_TYPE["audio/aiff"]['permission'] = _GO;
$ADA_MIME_TYPE["audio/x-aiff"]['permission'] = _GO;
$ADA_MIME_TYPE["image/gif"]['permission'] = _GO;
$ADA_MIME_TYPE["image/jpeg"]['permission'] = _GO;
$ADA_MIME_TYPE["image/pjpeg"]['permission'] = _GO;
$ADA_MIME_TYPE["image/png"]['permission'] = _GO;
$ADA_MIME_TYPE["image/x-png"]['permission'] = _GO;
$ADA_MIME_TYPE["text/html"]['permission'] = _GO;
$ADA_MIME_TYPE["text/css"]['permission'] = _GO;
$ADA_MIME_TYPE["text/csv"]['permission'] = _GO;
$ADA_MIME_TYPE["text/plain"]['permission'] = _GO;
$ADA_MIME_TYPE["text/richtext"]['permission'] = _GO;
$ADA_MIME_TYPE["application/rtf"]['permission'] = _GO;
$ADA_MIME_TYPE["text/xml"]['permission'] = _GO;
$ADA_MIME_TYPE["video/mp4"]['permission'] = _GO;
$ADA_MIME_TYPE["video/avi"]['permission'] = _GO;
$ADA_MIME_TYPE["video/quicktime"]['permission'] = _GO;
$ADA_MIME_TYPE["video/mpeg"]['permission'] = _GO;
$ADA_MIME_TYPE["video/msvideo"]['permission'] = _GO;
$ADA_MIME_TYPE["video/x-msvideo"]['permission'] = _GO;
$ADA_MIME_TYPE["video/x-flv"]['permission'] = _GO;
$ADA_MIME_TYPE["video/flv"]['permission'] = _GO;
$ADA_MIME_TYPE["application/x-director"]['permission'] = _GO;
$ADA_MIME_TYPE["application/x-shockwave-flash"]['permission'] = _GO;
$ADA_MIME_TYPE["application/toolbook"]['permission'] = _GO;
$ADA_MIME_TYPE["application/msword"]['permission'] = _GO;
$ADA_MIME_TYPE["application/mspowerpoint"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.ms-powerpoint"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.ms-excel"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.ms-office"]['permission'] = _GO;
$ADA_MIME_TYPE["application/x-xml"]['permission'] = _GO;
// docx, xslx, pptx etc...
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.wordprocessingml.document"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.wordprocessingml.template"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.spreadsheetml.template"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.presentationml.presentation"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.presentationml.template"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.presentationml.slideshow"]['permission'] = _GO;
// odt, ods, odp etc...
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.text"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.database"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.spreadsheet"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.presentation"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.graphics"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.chart"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.formula"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.image"]['permission'] = _GO;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.text-master"]['permission'] = _GO;

$ADA_MIME_TYPE["application/octet-stream"]['permission'] = _STOP;


//  $mimetypeCodeHa

$ADA_MIME_TYPE["application/pdf"]['type'] = _DOC;
$ADA_MIME_TYPE["application/x-pdf"]['type'] = _DOC;
$ADA_MIME_TYPE["application/x-zip-compressed"]['type'] = _DOC;
$ADA_MIME_TYPE["application/zip-compressed"]['type'] = _DOC;
$ADA_MIME_TYPE["application/zip"]['type'] = _DOC;
$ADA_MIME_TYPE["audio/mpeg"]['type'] = _SOUND;
$ADA_MIME_TYPE["audio/x-mp3"]['type'] = _SOUND;
$ADA_MIME_TYPE["audio/basic"]['type'] = _SOUND;
$ADA_MIME_TYPE["audio/wav"]['type'] = _SOUND;
$ADA_MIME_TYPE["audio/x-wav"]['type'] = _SOUND;
$ADA_MIME_TYPE["audio/x-pn-realaudio-plugin"]['type'] = _SOUND;
$ADA_MIME_TYPE["audio/midi"]['type'] = _SOUND;
$ADA_MIME_TYPE["audio/x-midi"]['type'] = _SOUND;
$ADA_MIME_TYPE["audio/aiff"]['type'] = _SOUND;
$ADA_MIME_TYPE["audio/x-aiff"]['type'] = _SOUND;
$ADA_MIME_TYPE["image/gif"]['type'] = _IMAGE;
$ADA_MIME_TYPE["image/jpeg"]['type'] = _IMAGE;
$ADA_MIME_TYPE["image/pjpeg"]['type'] = _IMAGE;
$ADA_MIME_TYPE["image/png"]['type'] = _IMAGE;
$ADA_MIME_TYPE["image/x-png"]['type'] = _IMAGE;
$ADA_MIME_TYPE["text/html"]['type'] = _LINK;
$ADA_MIME_TYPE["text/css"]['type'] = _LINK;
$ADA_MIME_TYPE["text/csv"]['type'] = _DOC;
$ADA_MIME_TYPE["text/plain"]['type'] = _DOC;
$ADA_MIME_TYPE["text/richtext"]['type'] = _DOC;
$ADA_MIME_TYPE["application/rtf"]['type'] = _DOC;
$ADA_MIME_TYPE["text/xml"]['type'] = _DOC;
$ADA_MIME_TYPE["video/avi"]['type'] = _VIDEO;
$ADA_MIME_TYPE["video/mp4"]['type'] = _VIDEO;
$ADA_MIME_TYPE["video/quicktime"]['type'] = _VIDEO;
$ADA_MIME_TYPE["video/mpeg"]['type'] = _VIDEO;
$ADA_MIME_TYPE["video/msvideo"]['type'] = _VIDEO;
$ADA_MIME_TYPE["video/x-msvideo"]['type'] = _VIDEO;
$ADA_MIME_TYPE["video/x-flv"]['type'] = _VIDEO;
$ADA_MIME_TYPE["video/flv"]['type'] = _VIDEO;
$ADA_MIME_TYPE["application/x-director"]['type'] = _VIDEO;
$ADA_MIME_TYPE["application/x-shockwave-flash"]['type'] = _VIDEO;
$ADA_MIME_TYPE["application/toolbook"]['type'] = _EXE;
$ADA_MIME_TYPE["application/msword"]['type'] = _DOC;
$ADA_MIME_TYPE["application/mspowerpoint"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.ms-powerpoint"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.ms-excel"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.ms-office"]['type'] = _DOC;
$ADA_MIME_TYPE["application/x-xml"]['type'] = _DOC;
// docx, xslx, pptx etc...
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.wordprocessingml.document"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.wordprocessingml.template"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.spreadsheetml.template"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.presentationml.presentation"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.presentationml.template"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.openxmlformats-officedocument.presentationml.slideshow"]['type'] = _DOC;
// odt, ods, odp etc...
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.text"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.database"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.spreadsheet"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.presentation"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.graphics"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.chart"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.formula"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.image"]['type'] = _DOC;
$ADA_MIME_TYPE["application/vnd.oasis.opendocument.text-master"]['type'] = _DOC;

$ADA_MIME_TYPE["application/octet-stream"]['type'] = _EXE;

// default session mode
define('ADA_SESSION_MODE', 		"auto");

// default session time (durata minima di una sessione in secondi)
define('ADA_SESSION_TIME', 		300);

// default code language
define('ADA_DEFAULT_LANGUAGE', "italiano");

// Parameters for evaluation  of acts  (used by class Student_class)
define("NOTE_PAR",          7);
define("HIST_PAR",          1);
define("MSG_PAR",           3);
define("EXE_PAR",           5);

// max user level
define('ADA_MAX_USER_LEVEL', 100);
define('ADA_MAX_SCORE',100);

// path to the standard error page
$error = HTTP_ROOT_DIR.'/admin/error.php';

/**
 * Preferences array
 */

$ADA_ELEMENT_VIEWING_PREFERENCES = array();
$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_LEAF_TYPE][ADD_OPERATION]  = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                   EDITOR_UPLOAD_FILE | EDITOR_SELECT_FILE |
                                                                                   EDITOR_SHOW_NODE_TYPE | EDITOR_SHOW_NODE_LEVEL |
                                                                                   EDITOR_SHOW_NODE_POSITION | EDITOR_SHOW_NODE_ICON |
                                                                                   EDITOR_SHOW_PARENT_NODE_SELECTOR | EDITOR_SELECT_EXTERNAL_LINK |
                                                                                   EDITOR_SHOW_NODE_ORDER |
                                                                                   EDITOR_ALLOW_SWITCHING_BETWEEN_EDITING_MODES;

$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_LEAF_TYPE][EDIT_OPERATION] = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                   EDITOR_UPLOAD_FILE | EDITOR_SELECT_FILE | EDITOR_SHOW_NODE_POSITION |
                                                                                   EDITOR_SHOW_NODE_ICON | EDITOR_SHOW_PARENT_NODE_SELECTOR |
                                                                                   EDITOR_SELECT_EXTERNAL_LINK | EDITOR_SHOW_NODE_LEVEL|
                                                                                   EDITOR_SHOW_NODE_ORDER |
                                                                                   EDITOR_ALLOW_SWITCHING_BETWEEN_EDITING_MODES;

$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_PERSONAL_EXERCISE_TYPE][ADD_OPERATION] = $ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_LEAF_TYPE][ADD_OPERATION];
$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_PERSONAL_EXERCISE_TYPE][EDIT_OPERATION] = $ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_LEAF_TYPE][EDIT_OPERATION];

$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_GROUP_TYPE][ADD_OPERATION]  = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                    EDITOR_UPLOAD_FILE | EDITOR_SELECT_FILE |
                                                                                    EDITOR_SHOW_NODE_TYPE | EDITOR_SHOW_NODE_LEVEL |
                                                                                    EDITOR_SHOW_NODE_POSITION | EDITOR_SHOW_PARENT_NODE_SELECTOR |
                                                                                    EDITOR_SELECT_EXTERNAL_LINK|
                                                                                    EDITOR_SHOW_NODE_ORDER |
                                                                                    EDITOR_ALLOW_SWITCHING_BETWEEN_EDITING_MODES;

$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_GROUP_TYPE][EDIT_OPERATION] = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                    EDITOR_UPLOAD_FILE | EDITOR_SELECT_FILE |
                                                                                    EDITOR_SHOW_NODE_ICON | EDITOR_SHOW_NODE_LEVEL | EDITOR_SHOW_NODE_POSITION |
                                                                                    EDITOR_SHOW_PARENT_NODE_SELECTOR | EDITOR_SELECT_EXTERNAL_LINK |
                                                                                    EDITOR_SHOW_NODE_TYPE|
                                                                                    EDITOR_SHOW_NODE_ORDER |
                                                                                    EDITOR_ALLOW_SWITCHING_BETWEEN_EDITING_MODES;
$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_LEAF_WORD_TYPE][ADD_OPERATION]  = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                   EDITOR_UPLOAD_FILE | EDITOR_SELECT_FILE |
                                                                                   EDITOR_SHOW_NODE_TYPE | EDITOR_SHOW_NODE_LEVEL |
                                                                                   EDITOR_SHOW_NODE_POSITION | EDITOR_SHOW_NODE_ICON |
                                                                                   EDITOR_SHOW_PARENT_NODE_SELECTOR | EDITOR_SELECT_EXTERNAL_LINK |
                                                                                   EDITOR_SHOW_NODE_ORDER |
                                                                                   EDITOR_ALLOW_SWITCHING_BETWEEN_EDITING_MODES;

$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_LEAF_WORD_TYPE][EDIT_OPERATION] = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                   EDITOR_UPLOAD_FILE | EDITOR_SELECT_FILE | EDITOR_SHOW_NODE_POSITION |
                                                                                   EDITOR_SHOW_NODE_ICON | EDITOR_SHOW_PARENT_NODE_SELECTOR |
                                                                                   EDITOR_SELECT_EXTERNAL_LINK | EDITOR_SHOW_NODE_LEVEL|
                                                                                   EDITOR_SHOW_NODE_ORDER |
                                                                                   EDITOR_ALLOW_SWITCHING_BETWEEN_EDITING_MODES;

$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_GROUP_WORD_TYPE][ADD_OPERATION]  = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                    EDITOR_UPLOAD_FILE | EDITOR_SELECT_FILE |
                                                                                    EDITOR_SHOW_NODE_TYPE | EDITOR_SHOW_NODE_LEVEL |
                                                                                    EDITOR_SHOW_NODE_POSITION | EDITOR_SHOW_PARENT_NODE_SELECTOR |
                                                                                    EDITOR_SELECT_EXTERNAL_LINK|
                                                                                    EDITOR_SHOW_NODE_ORDER |
                                                                                    EDITOR_ALLOW_SWITCHING_BETWEEN_EDITING_MODES;

$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_GROUP_WORD_TYPE][EDIT_OPERATION] = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                    EDITOR_UPLOAD_FILE | EDITOR_SELECT_FILE |
                                                                                    EDITOR_SHOW_NODE_ICON | EDITOR_SHOW_NODE_POSITION |
                                                                                    EDITOR_SHOW_PARENT_NODE_SELECTOR | EDITOR_SELECT_EXTERNAL_LINK |
                                                                                    EDITOR_SHOW_NODE_TYPE|
                                                                                    EDITOR_SHOW_NODE_ORDER |
                                                                                    EDITOR_ALLOW_SWITCHING_BETWEEN_EDITING_MODES;

$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_AUTHOR][ADA_PERSONAL_EXERCISE_TYPE][EDIT_OPERATION] = EDITOR_SHOW_NODE_POSITION | EDITOR_SHOW_PARENT_NODE_SELECTOR |
                                                                                    EDITOR_SELECT_EXTERNAL_LINK | EDITOR_SHOW_NODE_LEVEL|
                                                                                    EDITOR_SHOW_NODE_ORDER |
                                                                                    EDITOR_ALLOW_SWITCHING_BETWEEN_EDITING_MODES;

//  $ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_STUDENT][ADA_NOTE_TYPE][ADD_OPERATION]  = 0;
//  $ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_STUDENT][ADA_NOTE_TYPE][EDIT_OPERATION] = 0;
//  $ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_STUDENT][ADA_PRIVATE_NOTE_TYPE][ADD_OPERATION]  = 0;
//  $ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_STUDENT][ADA_PRIVATE_NOTE_TYPE][EDIT_OPERATION] = 0;
//
// $ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_TUTOR][ADA_NOTE_TYPE][EDIT_OPERATION] = EDITOR_ALLOW_SWITCHING_BETWEEN_EDITING_MODES;

$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_STUDENT][ADA_NOTE_TYPE][ADD_OPERATION]  = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                    EDITOR_SELECT_FILE | EDITOR_SELECT_EXTERNAL_LINK;

$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_STUDENT][ADA_NOTE_TYPE][EDIT_OPERATION] = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                    EDITOR_SELECT_FILE | EDITOR_SELECT_EXTERNAL_LINK;
$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_STUDENT][ADA_PRIVATE_NOTE_TYPE][ADD_OPERATION]  = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                    EDITOR_SELECT_FILE | EDITOR_SELECT_EXTERNAL_LINK;
$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_STUDENT][ADA_PRIVATE_NOTE_TYPE][EDIT_OPERATION] = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                    EDITOR_SELECT_FILE | EDITOR_SELECT_EXTERNAL_LINK;

$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_TUTOR][ADA_NOTE_TYPE][ADD_OPERATION]  = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                  EDITOR_UPLOAD_FILE | EDITOR_SELECT_FILE | EDITOR_SELECT_EXTERNAL_LINK;

$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_TUTOR][ADA_NOTE_TYPE][EDIT_OPERATION] = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                  EDITOR_UPLOAD_FILE | EDITOR_SELECT_FILE | EDITOR_SELECT_EXTERNAL_LINK;
$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_TUTOR][ADA_PRIVATE_NOTE_TYPE][ADD_OPERATION]  = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                    EDITOR_UPLOAD_FILE | EDITOR_SELECT_FILE | EDITOR_SELECT_EXTERNAL_LINK;
$ADA_ELEMENT_VIEWING_PREFERENCES[AMA_TYPE_TUTOR][ADA_PRIVATE_NOTE_TYPE][EDIT_OPERATION] = EDITOR_INSERT_EXTERNAL_LINK | EDITOR_INSERT_INTERNAL_LINK |
                                                                                    EDITOR_UPLOAD_FILE | EDITOR_SELECT_FILE | EDITOR_SELECT_EXTERNAL_LINK;