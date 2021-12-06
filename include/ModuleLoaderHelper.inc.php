<?php

/**
 * @package     module
 * @author      giorgio <g.consorti@lynxlab.com>
 * @copyright   Copyright (c) 2020, Lynx s.r.l.
 * @license	    http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version     0.1
 */

/**
 * Module loader helper
 *
 * base class used to load ada/wisp modules
 *
 * Loaded module configuration file is searched in the following order:
 * 1. ROOT_DIR / config / MODULENAME / CONFIG FILE
 * 2. ROOT_DIR / modules / MODULENAME / config / CONFIG FILE
 *
 */
class ModuleLoaderHelper
{

	/**
	 * default config directory name
	 *
	 * @var string
	 */
	const configdir = 'config';

	/**
	 * default config file name
	 *
	 * @var string
	 */
	const defaultfile = 'config.inc.php';

	/**
	 * look for the passed module configuration file
	 * if no $configfile is passed, the defaultfile is use (i.e. config.inc.php)
	 *
	 * @param string $modulename
	 * @param string|null $moduledir
	 * @param string $configfile
	 * @return string|null
	 */
	protected static function getModuleIncludeConfig($modulename, $moduledir = null, $configfile = self::defaultfile)
	{
		$noconfig = [
			'codeman'
		];
		$checks = [
			ROOT_DIR . DIRECTORY_SEPARATOR . self::configdir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $modulename . DIRECTORY_SEPARATOR . $configfile,
			MODULES_DIR . DIRECTORY_SEPARATOR . $modulename . DIRECTORY_SEPARATOR . self::configdir . DIRECTORY_SEPARATOR . $configfile
		];
		if (!is_null($moduledir)) {
			array_push(
				$checks,
				ROOT_DIR . DIRECTORY_SEPARATOR . self::configdir . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $moduledir . DIRECTORY_SEPARATOR . $configfile,
				MODULES_DIR . DIRECTORY_SEPARATOR . $moduledir . DIRECTORY_SEPARATOR . self::configdir . DIRECTORY_SEPARATOR . $configfile
			);
		}
		foreach ($checks as $check) {
			if (file_exists($check)) return $check;
		}
		return (in_array($modulename, $noconfig) ? '' : null);
	}

	/**
	 * load condition for the module. if this module returns false the module will not be loaded
	 *
	 * @param string $modulename
	 * @param string $moduledir
	 * @return bool
	 */
	protected static function checkModuleLoadCondtion($modulename, $moduledir)
	{
		switch ($modulename) {
			case 'test':
				return
					file_exists(MODULES_DIR . DIRECTORY_SEPARATOR . $moduledir . '/index.php') &&
					file_exists(MODULES_DIR . DIRECTORY_SEPARATOR . $moduledir . '/edit_test.php') &&
					file_exists(MODULES_DIR . DIRECTORY_SEPARATOR . $moduledir . '/tutor.php');
				break;
			case 'login':
				return
					file_exists(MODULES_DIR . DIRECTORY_SEPARATOR . $moduledir . '/include/abstractLogin.php');
				break;
			case 'apps':
			case 'classbudget':
			case 'classagenda':
			case 'classroom':
			case 'codeman':
			case 'servicecomplete':
			case 'slideimport':
				return
					file_exists(MODULES_DIR . DIRECTORY_SEPARATOR . $moduledir . '/index.php');
				break;
			default:
				return true;
				break;
		}
	}

	/**
	 * loads a single module
	 *
	 * @param string $modulename
	 * @param string|null $moduledir
	 * @param bool $forcedisable
	 * @return void
	 */
	public static function loadModule($modulename, $moduledir = null, $forcedisable = false)
	{
		if (is_null($moduledir)) $moduledir = $modulename;
		$basedefine = strtoupper('MODULES_' . $modulename);
		if (!defined($basedefine)) {
			$modConfig = ModuleLoaderHelper::getModuleIncludeConfig($modulename, $moduledir);
			if (!$forcedisable && !is_null($modConfig) && ModuleLoaderHelper::checkModuleLoadCondtion($modulename, $moduledir)) {
				$root_dir = ROOT_DIR;
				$defval = true;
				define($basedefine . '_NAME', $modulename);
				define($basedefine . '_PATH', MODULES_DIR . DIRECTORY_SEPARATOR . $moduledir);
				define($basedefine . '_HTTP', HTTP_ROOT_DIR . str_replace(ROOT_DIR, '', MODULES_DIR) . '/' . $moduledir);
				if (strlen($modConfig) > 0) {
					$tmp = require_once($modConfig);
					if (is_bool($tmp)) {
						$defval = $tmp;
					}
				}
				define($basedefine, $defval);
			} else {
				define($basedefine, false);
			}
		}
	}

	/**
	 * loads multiple modules at once, passed as array of 'name' and 'dirname'
	 *
	 * @param array $modules
	 * @return void
	 */
	public static function loadModuleFromArray($modules)
	{
		if (is_array($modules)) {
			foreach ($modules as $module) {
				if (array_key_exists('name', $module)) {
					if (!array_key_exists('dirname', $module)) $module['dirname'] = $module['name'];
					if (!array_key_exists('forcedisable', $module)) $module['forcedisable'] = false;
					self::loadModule($module['name'], $module['dirname'], $module['forcedisable']);
				}
			}
		}
	}
}
