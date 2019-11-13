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
   * if a '.env' file is there, load it using $_ENV and getenv/putenv only
   */
  if ((false !== @include_once(ROOT_DIR.'/vendor/autoload.php')) && is_file('.env') && is_readable('.env')) {
    $dotenv = Dotenv\Dotenv::create(ROOT_DIR, null,
      new Dotenv\Environment\DotenvFactory([
          new Dotenv\Environment\Adapter\EnvConstAdapter(),
          new Dotenv\Environment\Adapter\PutenvAdapter(),
      ]));
    $dotenv->load();
    unset($dotenv);
  }

 /**
  * sets multiprovider flag, true is the default
  * multiprovider behaviour, false is single provider
  * each with its own home page and anonymous pages
  */
  define ('MULTIPROVIDER', isset($_ENV['MULTIPROVIDER']) ? boolval($_ENV['MULTIPROVIDER']) : true);

 /**
  * Main include file
  */
  require_once(ROOT_DIR.'/config/ada_config.inc.php');