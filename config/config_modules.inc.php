<?php
         //defines for modules code_man
	define('MODULES_CODEMAN_PATH', ROOT_DIR.'/modules/code_man');
	if (file_exists(MODULES_CODEMAN_PATH.'/index.php')) {
		define('MODULES_CODEMAN', true);
		define('MODULES_CODEMAN_HTTP', HTTP_ROOT_DIR.'/modules/code_man');
	}
	else {
		define('MODULES_CODEMAN', false);
	}
	//defines for modules test
	define('MODULES_TEST_PATH', MODULES_DIR.'/test');
	if (file_exists(MODULES_TEST_PATH.'/index.php') 
	 && file_exists(MODULES_TEST_PATH.'/edit_test.php')
	 && file_exists(MODULES_TEST_PATH.'/tutor.php')) {
		require_once(MODULES_TEST_PATH.'/config/config.inc.php');

		define('MODULES_TEST', true);
		define('MODULES_TEST_HTTP', HTTP_ROOT_DIR.'/modules/test');
	}
	else {
		define('MODULES_TEST', false);
	}
	
	//defines for module newsletter
	define('MODULES_NEWSLETTER_PATH', MODULES_DIR.'/newsletter');
	if (file_exists(MODULES_NEWSLETTER_PATH.'/index.php'))
	 {
		require_once(MODULES_NEWSLETTER_PATH.'/config/config.inc.php');
	
		define('MODULES_NEWSLETTER', true);
		define('MODULES_NEWSLETTER_HTTP', HTTP_ROOT_DIR.'/modules/newsletter');
	}
	else {
		define('MODULES_NEWSLETTER', false);
	}
	
	//defines for module service-complete
	define('MODULES_SERVICECOMPLETE_PATH', MODULES_DIR.'/service-complete');
	if (file_exists(MODULES_SERVICECOMPLETE_PATH.'/index.php'))
	{
		require_once(MODULES_SERVICECOMPLETE_PATH.'/config/config.inc.php');
	
		define('MODULES_SERVICECOMPLETE', true);
		define('MODULES_SERVICECOMPLETE_HTTP', HTTP_ROOT_DIR.'/modules/service-complete');
	}
	else {
		define('MODULES_SERVICECOMPLETE', false);
	}
	
	//defines for module apps
	define('MODULES_APPS_PATH', MODULES_DIR.'/apps');
	if (file_exists(MODULES_APPS_PATH.'/index.php'))
	{
		require_once(MODULES_APPS_PATH.'/config/config.inc.php');
	
		define('MODULES_APPS', true);
		define('MODULES_APPS_HTTP', HTTP_ROOT_DIR.'/modules/apps');
	}
	else {
		define('MODULES_APPS', false);
	}
	
	
	//defines for module impexport
	define ('MODULES_IMPEXPORT_PATH', MODULES_DIR.'/impexport');
	if (file_exists(MODULES_IMPEXPORT_PATH.'/import.php'))
	{
		require_once(MODULES_IMPEXPORT_PATH.'/config/config.inc.php');
	
		define('MODULES_IMPEXPORT', true);
		define('MODULES_IMPEXPORT_HTTP', HTTP_ROOT_DIR.'/modules/impexport');
	}
	else {
		define('MODULES_IMPEXPORT', false);
	}
	
	//defines for module classroom
	define ('MODULES_CLASSROOM_PATH', MODULES_DIR.'/classroom');
	if (file_exists(MODULES_CLASSROOM_PATH.'/index.php'))
	{
		require_once(MODULES_CLASSROOM_PATH.'/config/config.inc.php');
	
		define('MODULES_CLASSROOM', true);
		define('MODULES_CLASSROOM_HTTP', HTTP_ROOT_DIR.'/modules/classroom');
	}
	else {
		define('MODULES_CLASSROOM', false);
	}
	
	//defines for module classagenda
	define ('MODULES_CLASSAGENDA_PATH', MODULES_DIR.'/classagenda');
	if (file_exists(MODULES_CLASSAGENDA_PATH.'/index.php'))
	{
		require_once(MODULES_CLASSAGENDA_PATH.'/config/config.inc.php');
	
		define('MODULES_CLASSAGENDA', true);
		define('MODULES_CLASSAGENDA_HTTP', HTTP_ROOT_DIR.'/modules/classagenda');
	}
	else {
		define('MODULES_CLASSAGENDA', false);
	}
?>
