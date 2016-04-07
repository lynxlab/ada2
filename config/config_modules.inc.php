<?php
if (!defined('MODULES_CODEMAN')) {
	// defines for modules code_man
	define('MODULES_CODEMAN_PATH', ROOT_DIR.'/modules/code_man');
	if (file_exists(MODULES_CODEMAN_PATH.'/index.php')) {
		define('MODULES_CODEMAN', true);
		define('MODULES_CODEMAN_HTTP', HTTP_ROOT_DIR.'/modules/code_man');
	} else {
		define('MODULES_CODEMAN', false);
	}
}

if (!defined('MODULES_TEST')) {
	// defines for modules test
	define('MODULES_TEST_PATH', MODULES_DIR.'/test');
	if (file_exists(MODULES_TEST_PATH.'/index.php')
			&& file_exists(MODULES_TEST_PATH.'/edit_test.php')
			&& file_exists(MODULES_TEST_PATH.'/tutor.php')) {

		require_once(MODULES_TEST_PATH.'/config/config.inc.php');
		define('MODULES_TEST', true);
		define('MODULES_TEST_HTTP', HTTP_ROOT_DIR.'/modules/test');
	} else {
		define('MODULES_TEST', false);
	}
}

if (!defined('MODULES_NEWSLETTER')) {
	// defines for module newsletter
	define('MODULES_NEWSLETTER_PATH', MODULES_DIR.'/newsletter');
	if (file_exists(MODULES_NEWSLETTER_PATH.'/config/config.inc.php')) {

		require_once(MODULES_NEWSLETTER_PATH.'/config/config.inc.php');
		define('MODULES_NEWSLETTER', true);
		define('MODULES_NEWSLETTER_HTTP', HTTP_ROOT_DIR.'/modules/newsletter');
	} else {
		define('MODULES_NEWSLETTER', false);
	}
}

if (!defined('MODULES_SERVICECOMPLETE')) {
	// defines for module service-complete
	define('MODULES_SERVICECOMPLETE_PATH', MODULES_DIR.'/service-complete');
	if (file_exists(MODULES_SERVICECOMPLETE_PATH.'/index.php')) {

		require_once(MODULES_SERVICECOMPLETE_PATH.'/config/config.inc.php');
		define('MODULES_SERVICECOMPLETE', true);
		define('MODULES_SERVICECOMPLETE_HTTP', HTTP_ROOT_DIR.'/modules/service-complete');
	} else {
		define('MODULES_SERVICECOMPLETE', false);
	}
}

if (!defined('MODULES_APPS')) {
	// defines for module apps
	define('MODULES_APPS_PATH', MODULES_DIR.'/apps');
	if (file_exists(MODULES_APPS_PATH.'/index.php')) {

		require_once(MODULES_APPS_PATH.'/config/config.inc.php');
		define('MODULES_APPS', true);
		define('MODULES_APPS_HTTP', HTTP_ROOT_DIR.'/modules/apps');
	}
	else {
		define('MODULES_APPS', false);
	}
}

if (!defined('MODULES_IMPEXPORT')) {
	// defines for module impexport
	define ('MODULES_IMPEXPORT_PATH', MODULES_DIR.'/impexport');
	if (file_exists(MODULES_IMPEXPORT_PATH.'/import.php')) {

		require_once(MODULES_IMPEXPORT_PATH.'/config/config.inc.php');
		define('MODULES_IMPEXPORT', true);
		define('MODULES_IMPEXPORT_HTTP', HTTP_ROOT_DIR.'/modules/impexport');
	} else {
		define('MODULES_IMPEXPORT', false);
	}
}

if (!defined('MODULES_CLASSROOM')) {
	// defines for module classroom
	define ('MODULES_CLASSROOM_PATH', MODULES_DIR.'/classroom');
	if (file_exists(MODULES_CLASSROOM_PATH.'/index.php')) {

		require_once(MODULES_CLASSROOM_PATH.'/config/config.inc.php');
		define('MODULES_CLASSROOM', true);
		define('MODULES_CLASSROOM_HTTP', HTTP_ROOT_DIR.'/modules/classroom');
	} else {
		define('MODULES_CLASSROOM', false);
	}
}

if (!defined('MODULES_CLASSAGENDA')) {
	// defines for module classagenda
	define ('MODULES_CLASSAGENDA_PATH', MODULES_DIR.'/classagenda');
	if (file_exists(MODULES_CLASSAGENDA_PATH.'/index.php')) {

		require_once(MODULES_CLASSAGENDA_PATH.'/config/config.inc.php');
		define('MODULES_CLASSAGENDA', true);
		define('MODULES_CLASSAGENDA_HTTP', HTTP_ROOT_DIR.'/modules/classagenda');
	} else {
		define('MODULES_CLASSAGENDA', false);
	}
}

if (!defined('MODULES_CLASSBUDGET')) {
	// defines for module classbudget
	define ('MODULES_CLASSBUDGET_PATH', MODULES_DIR.'/classbudget');
	if (file_exists(MODULES_CLASSBUDGET_PATH.'/index.php')) {

		require_once(MODULES_CLASSBUDGET_PATH.'/config/config.inc.php');
		define('MODULES_CLASSBUDGET', true);
		define('MODULES_CLASSBUDGET_HTTP', HTTP_ROOT_DIR.'/modules/classbudget');
	} else {
		define('MODULES_CLASSBUDGET', false);
	}
}

if (!defined('MODULES_LOGIN')) {
	// defines for module login
	define ('MODULES_LOGIN_PATH', MODULES_DIR.'/login');
	if (file_exists(MODULES_LOGIN_PATH.'/include/abstractLogin.class.inc.php')) {

		require_once(MODULES_LOGIN_PATH.'/config/config.inc.php');
		define('MODULES_LOGIN', true);
		define('MODULES_LOGIN_HTTP', HTTP_ROOT_DIR.'/modules/login');
	} else {
		define('MODULES_LOGIN', false);
	}
}

if (!defined('MODULES_SLIDEIMPORT')) {
	// defines for module slideimport
	define ('MODULES_SLIDEIMPORT_PATH', MODULES_DIR.'/slideimport');
	if (file_exists(MODULES_SLIDEIMPORT_PATH.'/index.php')) {

		require_once(MODULES_SLIDEIMPORT_PATH.'/config/config.inc.php');
		define('MODULES_SLIDEIMPORT', true);
		define('MODULES_SLIDEIMPORT_HTTP', HTTP_ROOT_DIR.'/modules/slideimport');
	} else {
		define('MODULES_SLIDEIMPORT', false);
	}
}

if (!defined('MODULES_FORMMAIL')) {
	// defines for module formmail
	define ('MODULES_FORMMAIL_PATH', MODULES_DIR.'/formmail');
	if (file_exists(MODULES_FORMMAIL_PATH.'/config/config.inc.php')) {

		require_once(MODULES_FORMMAIL_PATH.'/config/config.inc.php');
		define('MODULES_FORMMAIL', true);
		define('MODULES_FORMMAIL_HTTP', HTTP_ROOT_DIR.'/modules/formmail');
	} else {
		define('MODULES_FORMMAIL', false);
	}
}
?>
