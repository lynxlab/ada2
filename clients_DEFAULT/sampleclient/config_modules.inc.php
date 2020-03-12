<?php
/**
 * client own modules configuration
 *
 * To disable a module that would otherwise be enabled by /config/config_modules.inc.php
 * set to false the proper constant.
 * E.g. to disable MODULES_TEST for this client only, just add the line
 *
 * define('MODULES_TEST', false);
 *
 * If a module is to be enabled for this client only, pls add the proper inclusion code here
 * E.g. to add MODULES_DUMMY for this client only, add the following:
 *
 * ModuleLoaderHelper::loadModule('dummy', 'dummy-module-dir');
 *
 * or
 *
 * ModuleLoaderHelper::loadModuleFromArray([
 *  [ 'name' => 'dummy', 'dirname' => 'dummy-module-dir' ]
 * ]);
 *
 * NOTE: dirname is optional, if module dir name equals module dir
 *
 * pls look the ModuleLoaderHelper for more info
 */
