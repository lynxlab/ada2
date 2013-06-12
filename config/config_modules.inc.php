<?php

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

?>
