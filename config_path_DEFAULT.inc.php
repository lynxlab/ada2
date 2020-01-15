<?php
/**
 * Config path
 *
 * Defines the roor dir relative config path and requires the main config file
 *
 * PHP version >= 5.0
 *
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright	Copyright (c) 2009,  Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 * @version		0.1
 */

 /**
  *  Root dir relative path
  */
  define('ROOT_DIR', __DIR__);

  /**
   * Load env file built by the installer
   */
  define('ENV_FILENAME', ROOT_DIR . '/config/env_install.inc.php');
  if (is_file(ENV_FILENAME)) require_once ENV_FILENAME;

 /**
  * sets multiprovider flag, true is the default
  * multiprovider behaviour, false is single provider
  * each with its own home page and anonymous pages
  */
  define ('MULTIPROVIDER', getenv('MULTIPROVIDER') !== false ? (bool)intval(getenv('MULTIPROVIDER')) : true);

 /**
  * Main include file
  */
  require_once(ROOT_DIR.'/config/ada_config.inc.php');