<?php
/**
 * Client specific configuration file.
 *
 * PHP version >= 5.0
 *
 * @author		Stefano Penge <steve@lynxlab.com>
 * @author		Maurizio "Graffio" Mazzoneschi <graffio@lynxlab.com>
 * @author		Vito Modena <vito@lynxlab.com>
 * @copyright   (c) 2009-2010 Lynx s.r.l.
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU Public License v.2
 */

if (!MULTIPROVIDER)
{
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
 *
 * @name SAMPLE_DB_TYPE
 */
define('SAMPLE_DB_TYPE',  'mysql');

/**
 *
 * @name SAMPLE_DB_NAME
 */
define('SAMPLE_DB_NAME',  'ada_provider_SAMPLE');

/**
 *
 * @name SAMPLE_DB_USER
 */
define('SAMPLE_DB_USER',  'ada_db_SAMPLE');

/**
 *
 * @name SAMPLE_DB_PASS
 */
define('SAMPLE_DB_PASS',  'SAMPLE');

/**
 *
 * @name SAMPLE_DB_HOST
 */
define('SAMPLE_DB_HOST',  'localhost');

/**
 *
 * @name SAMPLE_TIMEZONE
 */
define('SAMPLE_TIMEZONE',  'Europe/Rome');
